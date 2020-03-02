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
class telaConfeccaoEmail extends MDialog
{
    /**
     * Método ajax para montar o popup de resposta de mensagem de ouvidoria.
     * 
     */
    public function __construct($emails)
    { 
        $MIOLO = MIOLO::getInstance();    
        $campos[] = new MDiv('divMensagemDialogEmail');
        $campos[] = $invisivel = new MTextField('emails', $emails);
        $invisivel->addStyle('display', 'none');
        $campos[] = $respostaCampo = new MMUltiLineField('mensagem', $resposta, _M('E-mail'), NULL, T_VERTICAL_TEXTO, T_HORIZONTAL_TEXTO);

        $botoes = array();

        $imagem = $MIOLO->getUI()->getImageTheme(NULL, 'botao_salvar.png');
        $botoes = new MButton('confeccaoEmail', _M('Enviar'),  MUtil::getAjaxAction('enviarEmail', $parametro), $imagem);
        $campos[] = MUtil::centralizedDiv($botoes);
        

        // Mostra o Popup em tela.
        parent::__construct('popupDesenvolvimentoEmail', _M('Enviar e-mail'), $campos);
        
        $this->show();
    }
}
?>