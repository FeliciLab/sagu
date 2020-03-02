<?php
/**
 * Basic form.
 *
 * @author moises
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 28/03/2013
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('classes/capformdinamico.class.php', 'contaspagar');
$MIOLO->uses('classes/capform.class.php', 'contaspagar');
class frmconfiguracao extends capformdinamico
{
    public function __construct()
    {
        parent::__construct(NULL, _M('Configurações', MIOLO::getCurrentModule()));

        $this->eventHandler();
        $this->setShowPostButton(FALSE);
    }

    public function definirCampos()
    {
	$MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        parent::definirCampos(FALSE);
        
        $this->barraDeFerramentas->disableButton(bBarraDeFerramentas::BOTAO_BUSCAR);
        
        $cap = new capconfiguracao();

        // Fluxo da solicitacao
        $controls[] = new MRadioButtonGroup('fluxoSolicitacao', _M('Fluxo da solicitação'), array(
            array(_M('Não necessita deferimento'), capconfiguracao::NAO_NECESSITA_DEFERIMENTO),
            array(_M('Necessita deferimento'), capconfiguracao::NECESSITA_DEFERIMENTO),
        ), $cap->obterTipoSolicitacaoPagto());

        $tabs = new MTabbedBaseGroup('mytabs');
        $fields[] = $tabs;
        
        $tabs->createTab('tab1', _M('Geral'), $controls);

	$this->addFields($fields);
    }
    
    public function botaoSalvar_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $cap = new capconfiguracao();
        $cap->salvaTipoSolicitacaoPagto($args->fluxoSolicitacao);

        new MMessageSuccess(_M('Configurações salvas com sucesso'));
    }

}

?>
