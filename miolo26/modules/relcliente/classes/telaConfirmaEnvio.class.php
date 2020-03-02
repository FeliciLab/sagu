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
class telaConfirmaEnvio extends MDialog
{
    /**
     * Método ajax para montar o popup de resposta de mensagem de ouvidoria.
     * 
     * @param stdClass Parametros do ajax.
     */
    public function __construct($emails)
    {
        $MIOLO = MIOLO::getInstance();
        $campos = array();
        $campos[] = self::criaTable($emails);
        $campos[] = new MDiv('divMensagemDialogResposta');

        $botoes = array();

        $imagem = $MIOLO->getUI()->getImageTheme(NULL, 'botao_salvar.png');
        $botoes = new MButton('confeccao', _M('Enviar e-mail'),  MUtil::getAjaxAction('confeccaoEmail', $args), $imagem);
        $campos[] = MUtil::centralizedDiv($botoes);
        
        // Mostra o Popup em tela.
        parent::__construct('popupConfirmaEnvio', _M('Confirmar e prosseguir para confecção '), $campos);
        
        $this->show();
    }
    
    public static function criaTable($emails)
    {
       return new MTableRaw(_M('Pessoas selecionadas', $module), $emails, array('Pessoa', 'email'));
    }
}
?>