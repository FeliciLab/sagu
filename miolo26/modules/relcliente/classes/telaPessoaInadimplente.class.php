<?php
/**
 * @author Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Artur Bernardo Koefender [artur@solis.coop.br]
 *
 * @since
 * Class created on 11/01/2013
 *
 **/

$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
$MIOLO->uses('tipos/rccInadimplentes.class.php', 'relcliente');

class telaPessoaInadimplente extends MDialog
{
    public function __construct($personid)
    {
        $MIOLO = MIOLO::getInstance();

        $campos[] = self::obterInformacoesDoContato($personid);
        
        //tablerow
        $campos[] = self::criaTable($personid);

        parent::__construct('popupInfoPessoa', _M('Informações da Pessoa'), $campos);
        $this->show();
    }
    
    
    public static function criaTable($personid)
    {
        $filtros = new stdClass();
        $filtros->personid = $personid;
        $dados = rccInadimplentes::buscarPessoa($filtros); 
        
        $tableRaw = new MTableRaw(_M('Titulos em atraso', $module), $dados, array( 'Código da cobrança', 'Data', 'Valor'));
        $tableRaw->addStyle('width', '500px');
        $tableRaw->addStyle('heigth', '500px');
        return $tableRaw;
        
    }
    
    public static function obterInformacoesDoContato($personid)
    {
        $dadosPessoa = bTipo::instanciarTipo('basperson', 'relcliente');
        $dadosPessoa->personid = $personid;
        $dadosPessoa->popular();
        
        $cidade = bTipo::instanciarTipo('bascity');
        $cidade->cityid = $dadosPessoa->cityid;
        $cidade->popular();
        
        $basPhone = bTipo::instanciarTipo('basphone');
        $basPhoneFiltro = new stdClass();
        $basPhoneFiltro->personid = $dadosPessoa->personid;
        $telefones = $basPhone->buscar($basPhoneFiltro);
        foreach($telefones as $telefone)
        {
            $fones[] = $telefone->phone;
        }
        
        $conteudo = "<b>Nome:</b> {$dadosPessoa->name}
                     <br/><b>Telefone(s): </b>" . implode(', ', $fones) .
                     "<br/><b>E-mail: </b> {$dadosPessoa->email}
                     <br/><b>E-mail Alternativo: </b> {$dadosPessoa->emailalternative}
                     <br/><b>Cidade: </b> {$cidade->name}
                     <br/><b>Logradouro: </b> {$dadosPessoa->location}
                     <br/><b>Número: </b> {$dadosPessoa->number}
                     <br/><b>Complemento: </b> {$dadosPessoa->complement}";
         
        return MMessage::getStaticMessage('', $conteudo, MMessage::TYPE_INFORMATION);
    }
}
?>
