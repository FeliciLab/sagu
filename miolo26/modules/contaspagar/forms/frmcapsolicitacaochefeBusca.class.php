<?php
/**
 *
 * @author moises
 *
 * @since
 * Class created on 02/04/2013
 */
$MIOLO->uses('tipos/captitulo.class.php', 'contaspagar');
$MIOLO->uses('classes/capformdinamicobusca.class.php', 'contaspagar');
$MIOLO->uses('tipos/caprateio.class.php', 'contaspagar');
$MIOLO->uses('tipos/capsolicitacaocomprador.class.php', 'contaspagar');

class frmcapsolicitacaochefeBusca extends capformdinamicobusca
{
    protected $botaoEditar = false;
    protected $botaoRemover = false;
    protected $botaoExplorar = false;
    protected $botaoNovo = false;
    
    public $_columns;
    
    protected function criarMenuDeContexto()
    {
        $menu = parent::criarMenuDeContexto();
        $menu->addCustomItem(_M('Autorizar pagamento'), $this->manager->getUI()->getAjax('deferirPgto:click'), MContextMenu::ICON_PASTE);
        $menu->addCustomItem(_M('Não autorizar'), $this->manager->getUI()->getAjax(capformdinamicobusca::ACAO_CANCELAR_SOLICITACAO), MContextMenu::ICON_DELETE);
        
        return $menu;
    }
    
    public function deferirPgto_click()
    {
        $MIOLO = MIOLO::getInstance();
        
        $itensSelecionados = MIOLO::_REQUEST('selectbSearchGrid');
        
        if ( count($itensSelecionados) > 1 )
        {
            new MMessageWarning('Selecione apenas uma solicitação para autorizar.');
            return;
        }
        foreach($itensSelecionados as $autorizacao)
        {
            $valor = str_replace('solicitacaoid|', '', $autorizacao);
            $solicitacaoid = str_replace('&', '', $valor);
        }
        $fields[] = $hidden = new MTextField('solicitacaoId', $solicitacaoid);
        $hidden->addBoxStyle('display', 'none');
        
        //Verifica se tem rateio
        $tipoSolicitacao = bTipo::instanciarTipo('capsolicitacao');
        $filtros = new stdClass();
        $filtros->solicitacaoid = $solicitacaoid;
        $solicitacao = $tipoSolicitacao->buscar($filtros);

        if(strlen($solicitacao[0]->costcenterid ==  0))
        {
            $colunas = array(
                _M('Centro de custo', $this->module),
                _M('Chefe de centro', $this->module),
                _M('Autorizado', $this->module),
                _M('Data da autorização', $this->module));

            $fields[] = new MTableRaw(_M('Autorizações', $this->modulo), caprateio::obtemInformacoesPopupAutorizacao($solicitacaoid), $colunas);

            $fields[] = new MButton('btnAutoriza', 'Autorizar solicitação');
            $fields[] = new MButton('btnFecharDialogo', _M('Fechar'), "document.querySelector('span.dijitDialogCloseIcon').click();");
            $div = new MDiv('div', $fields);

            $popup = new MDialog("popupAutorizacao", _M('Autorização de pagamento', $this->module), array($div));
            $popup->show(); 
                    
            MIOLO::getInstance()->page->addJsCode($js);
        }
        else
        {
            $titulo = new captitulo();
            $titulo->registrarTitulosDaSolicitacao($solicitacaoid);
        
            new MMessageSuccess( _M('Solicitação autorizada com sucesso.'));
        }
        $MIOLO->page->onLoad($jscode);
    }
    
    public function btnAutoriza_click($args)
    {
        $MIOLO = MIOLO::getInstance();

        // Autoriza a solicitação
        $data = new stdClass();
        $data->solicitacaoid = $args->solicitacaoId;
        $data->personidowner = SAGU::getUsuarioLogado()->personId;
        $data->autorizado = DB_TRUE;

        caprateio::atualizaAutorizacao($data);
        
        $autorizacao = caprateio::obterRateioAutorizacaoId($args->solicitacaoId);
        
        //Verifica se todos foram autorizados
        foreach ( $autorizacao as $aut)
        {
            if( $aut[1] == DB_FALSE )
            {
                $NoAut = true;
            } 
        }       
        if(!$NoAut)
        {
            $titulo = new captitulo();
            $titulo->registrarTitulosDaSolicitacao($args->solicitacaoId);            
        }
        
        new MMessageSuccess( _M('Solicitação(ões) autorizada(s) com sucesso.'));
        MIOLO::getInstance()->page->addJsCode("document.querySelector('span.dijitDialogCloseIcon').click()");
    }
    
    /**
     * Para ocultar o checkbox all
     */
    public function onLoad()
    {
        parent::onLoad();
        
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->onLoad("document.getElementById('chkAll').style.display = 'none';");
    }
}

?>