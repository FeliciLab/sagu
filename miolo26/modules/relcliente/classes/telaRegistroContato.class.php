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

$MIOLO->uses('forms/frmDinamicoBusca.class.php', 'base');
$MIOLO->uses('classes/rccEmail.class.php', 'relcliente');
$MIOLO->uses('tipos/rccInadimplentes.class.php', 'relcliente');
$MIOLO->uses('classes/bFormBusca.class.php', 'base');

class telaRegistroContato extends MDialog
{
    public function __construct($personid)
    {
        $MIOLO = MIOLO::getInstance();
        $tipo = bTipo::instanciarTipo('rccorigemdecontato', 'relcliente');
        $tipoPessoa = bTipo::instanciarTipo('basPerson', 'relcliente');
        
        $filtros = new stdClass();
        $filtros->personid = $personid;        
        
        $nome = $tipoPessoa->buscar($filtros, 'name');
        
        $campos[] = self::obterInformacoesDoContato($personid);
        $campos[] = new MDiv('divRegistroContatoAlert');
        $campos['personid'] = new MIntegerField('personid', $personid);
        $campos['personid']->setVisibility(false);
        $campos[] = new MSelection('origem', null, 'Origem', $tipo->buscarParaSelection(null, 'origemdecontatoid, descricao'));
        $campos['agendar'] = new MCheckBox('agendar', 't', 'Agendar', false);
        $campos['agendar']->addEvent('change', ':agendarClick');
        $campos[] = new MDiv('dataDiv');
        
        $campos[] = new MFormContainer('respostaDiv', array(new MMUltiLineField('mensagem', null, _M('Mensagem'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO)));
        $campos[] = new MDiv('orientacaoDiv');
        
        $botoes = array();
        $imagem = $MIOLO->getUI()->getImageTheme(NULL, 'botao_salvar.png');
        $botoes = new MButton('registraContato', _M('Registrar Contato'),  MUtil::getAjaxAction('registraContato', $argumentos), $imagem);
        $campos[] = MUtil::centralizedDiv($botoes);
        
        parent::__construct('popupRegistroContato', _M('Registrar Contato'), $campos);
        $this->show();

    }      
    
    public static function obterInformacoesDoContato($personid)
    {
        $dadosPessoa = bTipo::instanciarTipo('basphysicalperson', 'relcliente');
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
                     <br/><b>Telefone(s): </b> " . implode(', ', $fones) .
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
