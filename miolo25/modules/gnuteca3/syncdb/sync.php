<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 * 
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 * 
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 06/01/2011
 *
 * */
$MIOLO = MIOLO::getInstance();

global $messagesG;
$messagesG = array( );

function addMessage($message)
{
    global $messagesG;
    $messagesG[][] = $message;
}
$gtcRootGroupId = 1000;
$rights['Acesso'] = 1;
$rights['Inserção'] = 2;
$rights['Remoção'] = 4;
$rights['Atualização'] = 8;

// Permissões
addMessage('Removendo acessos gtcRoot');
bBaseDeDados::consultar("DELETE FROM miolo_access WHERE idgroup = $gtcRootGroupId;", "admin");

foreach ( $rights as $perm => $right )
{
    addMessage("Inserindo acessos para gtcRoot - " . $perm);
    bBaseDeDados::consultar("INSERT INTO miolo_access ( idtransaction, idgroup ,rights ) ( SELECT idtransaction,$gtcRootGroupId, $right FROM miolo_transaction WHERE idmodule = 'gnuteca3' );", "admin");
}

/**
  Direito de empréstimo momentâneo
  O código abaixo realiza a insersão dos direitos de empréstimo momentâneo. A inserção deve ser executada somente uma vez, devido a isso não é possível ter um XML para isso.
 */
// Obtém o código do tipo de empréstimo momentâneo
$idOperationMomentary = bBaseDeDados::consultar("SELECT value FROM basConfig WHERE parameter = 'ID_OPERATION_LOAN_MOMENTARY';");
$idOperationMomentary = $idOperationMomentary[0][0];

// Obtém o código do tipo de empréstimo padrão
$idOperationLoan = bBaseDeDados::consultar("SELECT value FROM basConfig WHERE parameter = 'ID_LOANTYPE_DEFAULT';");
$idOperationLoan = $idOperationLoan[0][0];

if ( $idOperationMomentary)
{
    // Obtém a quantidade de direitos de empréstimo momentâneo
    $countRight = bBaseDeDados::consultar($sql = "SELECT count(*) FROM gtcRight WHERE operationid = $idOperationMomentary;");
    $countRight = $countRight[0][0];

    // Caso já tiver algum direito de empréstimo momentâneo, não insere os direitos
    if ( $countRight == 0 )
    {
        addMessage("Adicionando direito de empréstimo momentâneo");
        bBaseDeDados::consultar("INSERT INTO gtcright (SELECT privilegegroupid, linkid, materialgenderid, $idOperationMomentary FROM gtcright where operationid = $idOperationLoan);");
    }

}

//verifica se tem coluna city
$colunasBasPerson = bCatalogo::obterColunasDaTabela('public','basperson');

//quando tiver coluna de pessoa tem que converter city para cityid
if ( isset($colunasBasPerson['city']) )
{
    //atualiza sequencia
    bBaseDeDados::consultar("SELECT SETVAL('seq_cityid', (SELECT MAX(cityid) FROM bascity) + 1);");
    //  Insere todas cidades não constante no
    bBaseDeDados::consultar("INSERT INTO bascity (name,stateid,countryid) SELECT foo.city,'RS','BRA' FROM (SELECT distinct city FROM ONLY basperson WHERE length(trim(city)) > 0 EXCEPT (SELECT name from bascity)) as foo ;");
    //  Passa o cityId para as cidades que não o tem definido
    bBaseDeDados::consultar("UPDATE ONLY basperson SET cityId = (select cityId FROM bascity WHERE name = city LIMIT 1 ) WHERE cityid is null;");
    // DROPA coluna basperson.city
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'city');
}

//quando tiver coluna de pessoa tem que converter city para cityid
if ( isset($colunasBasPerson['entrydate']) )
{
    bBaseDeDados::consultar("UPDATE ONLY basperson SET datein = entrydate;");
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'entrydate');
}

//Se existir coluna observation ela deve ter seu conteúdo migrado para obs e depois ser dropada
if ( isset($colunasBasPerson['observation']) )
{
    bBaseDeDados::consultar("UPDATE ONLY basperson SET obs = observation;");
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'observation');
}

if ( isset($colunasBasPerson['baseldap']) )
{
    bBaseDeDados::consultar("INSERT INTO gtclibperson ( personid,baseldap,sex,profession,workplace,school,datebirth,persongroup ) 
                                 (SELECT personid,baseldap,sex,profession,workplace,school,datebirth,persongroup 
                               FROM ONLY basperson
                                   WHERE personid NOT IN (SELECT personid FROM gtclibperson));");
    
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'baseldap');
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'sex');
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'profession');
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'workplace');
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'school');
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'datebirth');
    bCatalogo::removerColunaSeExistir('public', 'basperson', 'persongroup');
}


//Verifica se Base possui sequencia miolo_user_iduser_seq. Adicionado Gnuteca 3.8
$var = bCatalogo::listarSequencias("public", "miolo_user_iduser_seq", "admin");
if(empty ($var))
{
    bBaseDeDados::executar("CREATE SEQUENCE miolo_user_iduser_seq;", "admin");
    //MUtil::flog("SQL ->  CREATE SEQUENCE miolo_user_iduser_seq; ");
    
    bBaseDeDados::executar("ALTER TABLE miolo_user ALTER COLUMN iduser SET DEFAULT  nextval('miolo_user_iduser_seq');", "admin");
    //MUtil::flog("SQL -> ALTER TABLE miolo_user ALTER COLUMN iduser SET DEFAULT  nextval('miolo_user_iduser_seq');");
    
    bBaseDeDados::executar("SELECT setval('miolo_user_iduser_seq',(SELECT MAX(iduser) FROM miolo_user));", "admin");
    //MUtil::flog("SQL -> SELECT setval('miolo_user_iduser_seq',(SELECT MAX(iduser) FROM miolo_user)); ");
    
    bBaseDeDados::executar("DROP SEQUENCE seq_iduser;", "admin");
    //MUtil::flog("SQL -> DROP SEQUENCE seq_iduser;");
}


//Se existir, remover baspersonoperationprocess pois ela não será mais utilizada.
bCatalogo::removerTabelaSeExistir('public', 'baspersonoperationprocess');
//remove a coluna caso exista, problemas antigos do DIA
bCatalogo::removerColunaSeExistir(null, 'gtclibraryunitaccess', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtclibraryunitisclosed', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtclibraryassociation', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcnewsaccess', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcpersonlibraryunit', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcrequestchangeexemplarystatusaccess', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcsearchablefieldaccess', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcsearchformataccess', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcsearchformatcolumn', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcsoapaccess', 'bug_dia2sql_ignorar');
bCatalogo::removerColunaSeExistir(null, 'gtcsuppliertypeandlocation', 'bug_dia2sql_ignorar');

// TODO: arrumar sequências

$fields[] = new MTableRaw('Script de sincronização', $messagesG, array( _M('Message', 'gnuteca3') ), 'message');
$fields[] = new MSeparator('<br/>');
$theme->appendContent($fields);
?>
