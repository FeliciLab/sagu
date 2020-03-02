<?php
$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/auditoria.class', 'basic');
$MIOLO->uses('/types/BasHistoricoAtualizacao.class', 'basic');

global $messagesG;
$messagesG = array();

// Sincroniza menu
$sreport = new SReport();
$sreport->synchronizeWithMenu();

$rootGroupId = 1;
$right = 31;

addMessage('Removendo acessos admin');
sDataBase::getInstance()->execute( "DELETE FROM miolo_access WHERE idgroup = $rootGroupId;" );

addMessage( "Inserindo acessos para admin".$perm);
sDataBase::getInstance()->execute( "INSERT INTO miolo_access ( idtransaction, idgroup ,rights ) ( SELECT idtransaction,$rootGroupId, $right FROM miolo_transaction );" );

sDataBase::getInstance()->execute(" UPDATE miolo_transaction SET parentm_transaction ='' WHERE char_length(parentm_transaction) IS NULL ");

//sDataBase::getInstance()->execute(" UPDATE miolo_transaction SET action = 'main:process:dispatch', parentm_transaction = 'ptcProcess' WHERE m_transaction = 'FrmDispatch'; ");

// Cria as funções que retornam as chaves primárias das tabelas que poderão ser auditadas.
if ( SAGU::getParameter('BASIC', 'AUDITAR_TODAS_TABELAS') == DB_TRUE )
{
    //Executa a função para auditar todas as tabelas
    $tabelasSemAuditoria = auditoria::obtemTabelasSemAuditoria();
    
    foreach ( $tabelasSemAuditoria as $tabela )
    {
        auditoria::criarTrigger($tabela[0], $tabela[1]);
    }
}

// Rotinas gerais de manutencao da base
$changes = new SQLChanges();
$changes->runChanges();

// Atualiza usuarios no postgresql
SDatabase::updateDbUsers();

// Multiunidade/centro
sMultiUnidade::executarSqlsManutencao();

if ( sMultiUnidade::estaHabilitada() )
{
    sMultiUnidade::inserirUnidadePadrao();
}

$table = new MTableRaw( 'Script de sincroniza��o', $messagesG, array(_M('Message','gnuteca3')), 'message');

if ( $theme )
{
    $theme->appendContent( $table );
}

//Caso ocorram problemas de codificacao HTML na base, mudar parametro para true
//e entao passar aqui o nome da tabela e o nome da coluna
if ( SAGU::getParameter('BASIC', 'CORRIGE_CODIFICACAO_HTML_NA_BASE') == DB_TRUE )
{
    SAGU::decodificarEntidadesHtmlNaBase('capsolicitacao', 'dadoscompra');
    SAGU::decodificarEntidadesHtmlNaBase('acdScheduleProfessorContent', 'description');
    
    sDataBase::getInstance()->execute(" UPDATE basConfig SET value = 'f' WHERE parameter = 'CORRIGE_CODIFICACAO_HTML_NA_BASE'");
}

//Correcao de sequencias na base de dados
sDataBase::getInstance()->execute("SELECT updateSequences()");

// Criacao de indices para chave estrangeira (perfomance)
sDataBase::getInstance()->execute(file_get_contents($MIOLO->getConf('home.miolo').'/modules/basic/syncdb/functions/f00400-create_index_to_foreign_key.sql'));
sDataBase::getInstance()->execute("SELECT * FROM createIndexToForeignKey();");

//Insere acessos para permissao de matricula no portal novo igual a do portal antigo
sDataBase::getInstance()->execute("
    INSERT INTO miolo_access (idtransaction, idgroup, rights)
    SELECT (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'FrmEnrollWebAluno') AS idtransaction,
           A.idgroup,
           A.rights
      FROM miolo_access A
INNER JOIN miolo_transaction B
     USING (idtransaction)
     WHERE B.m_transaction = 'FrmEnrollWeb'
       AND A.idgroup <> 1
       AND (SELECT idtransaction FROM miolo_transaction WHERE m_transaction = 'FrmEnrollWebAluno') IS NOT NULL
       AND NOT EXISTS(SELECT 1 FROM miolo_access _A INNER JOIN miolo_transaction _B USING (idtransaction) WHERE _B.m_transaction = 'FrmEnrollWebAluno' AND _A.idgroup <> 1)
");

/**
 * Efetua o registro da versão do SAGU e GNUTECA na tabela de histórico de atualização
 */
BasHistoricoAtualizacao::registraVersoes();

/**
 * Executa changes padroes com verificacao nas tabelas
 * 
 * Obs: Estas rotinas nao sao oficiais, foram feitas para serem utilizadas temporariamente.
 */
class SQLChanges
{
    public function runChanges()
    {
        $addColumns = array(
            'miolo_custom_field' => array(
                'label' => array(
                    'type' => 'varchar(255)',
                    'desc' => 'Nome amig�vel ao usu�rio que deve ser exibido nos formul�rios, grids, relat�rios...',
                    'sqlAfter' => array(
                        "UPDATE miolo_custom_field SET label=name",
                        "UPDATE miolo_custom_field SET name=replace(lower(to_ascii(name)), ' ', '_')",
                    )
                ),
            ),
            'miolo_custom_value' => array(
                'name' => array(
                    'type' => 'varchar(255)',
                    'desc' => 'Nome do campo, mesmo nome do miolo_custom_field.name referenciado, utilizado por questoes de performance na hora de buscar os dados.',
                ),
            ),
            'acccostcenter' => array(
                'allowpaymentrequest' => array(
                    'type' => 'boolean',
                    'desc' => 'Aceita solicita��o de pagamento. Quando este estiver como VERDADEIRO, deve existir o chefe de centro de custo (personidowner)',
                    'default' => 'false',
                ),
                'personidowner' => array(
                    'type' => 'integer',
                    'desc' => 'Chefe do centro de custo',
                    'references' => 'basPhysicalPersonEmployee(personid)'
                )
            ),
            'fincountermovement' => array(
                'tituloid' => array(
                    'type' => 'integer',
                    'desc' => 'Titulo do contas a pagar',
//                    'references' => 'capTitulo(tituloid)' // FIXME ESTAVA OCORRENDO ERRO DE TRAVAR O POSTGRESQL COM ESTA REFERENCIA, VERIFICAR
                )
            ),
            'acdlearningperiod' => array(
                'limitregisterdate' => array(
                    'type' => 'date',
                    'desc' => 'Data limite para registro de digita��o de notas ou frequ�ncias via portal'
                )
            )
        );
        
        $this->executeAddColumns($addColumns);
    }
    
    public function executeAddColumns($addColumns = array())
    {
        $db = sDataBase::getInstance();
        
        foreach ( $addColumns as $table => $cols )
        {
            foreach ( $cols as $colName => $defs )
            {
                if ( !SDatabase::existeColunaDaTabela(null, $table, $colName) )
                {
                    $this->executeSqls($defs['sqlBefore']); // Pre-processamento
                    
                    $null = $defs['isnotnull'] ? 'NOT NULL' : '';
                    $default = strlen($defs['default']) > 0 ? 'DEFAULT ' . $defs['default'] : '';
                    $references = strlen($defs['references']) > 0 ? 'REFERENCES ' . $defs['references'] : '';
                    
                    $db->Execute('ALTER TABLE ' . $table . ' ADD ' . $colName . ' ' . $defs['type'] . ' ' . $null . ' ' . $references . ' ' . $default);
                    $db->Execute('COMMENT ON COLUMN ' . $table  . '.' . $colName . ' IS \'' . $defs['desc'] . '\'');

                    $this->executeSqls($defs['sqlAfter']); // Pos-processamento
                }
            }
        }
    }
        
    public function executeSqls($sqls = array())
    {
        foreach ( $sqls as $sql )
        {
            sDataBase::getInstance()->Execute($sql);
        }
    }
}
?>
