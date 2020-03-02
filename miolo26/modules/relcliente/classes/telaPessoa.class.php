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

class telaPessoa extends MDialog
{
    public function __construct($personid)
    {
        $MIOLO = MIOLO::getInstance();
                
        $filtros = new stdClass();
        $filtros->personid = $personid;
        $campos[] = self::obterInformacoesDoContato($personid);        
        
        parent::__construct('popupInfoPessoa', _M('Informações da Pessoa'), $campos);
        
        $this->show();
    }
    
    
    public static function obterInformacoesDoContato($personid)
    {
        $dadosPessoa = bTipo::instanciarTipo('basperson', 'relcliente');
        $dadosPessoa->personid = $personid;
        $dadosPessoa->popular();
        
        $cidade = bTipo::instanciarTipo('bascity');
        $cidade->cityid = $dadosPessoa->cityid;
        $cidade->popular();
        
        $telefone = bTipo::instanciarTipo('basphone');
        $telefone->personid = $dadosPessoa->personid;
        $telefone->popular();
        
        $conteudo = "<b>Nome:</b> {$dadosPessoa->name}
                     <br/><b>Telefone: </b> {$telefone->phone}
                     <br/><b>E-mail: </b> {$dadosPessoa->email}
                     <br/><b>E-mail Alternativo: </b> {$dadosPessoa->emailalternative}
                     <br/><b>Cidade: </b> {$cidade->name}
                     <br/><b>Logradouro: </b> {$dadosPessoa->location}
                     <br/><b>Número: </b> {$dadosPessoa->number}
                     <br/><b>Complemento: </b> {$dadosPessoa->complement}";
         
        return MMessage::getStaticMessage('', $conteudo, MMessage::TYPE_INFORMATION);
    }
}
?>
