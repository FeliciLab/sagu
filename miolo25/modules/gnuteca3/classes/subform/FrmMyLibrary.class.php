<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
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
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/10/2011
 *
 **/
class FrmMyLibrary extends GSubForm
{
    public $MIOLO;
    public $module;
    public $action;
    public $business;
    public $grid;
    public $function;
    public $busAthenticate;
    public $busLoanType;
    public $busLibraryUnit;
    public $busRenew;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->business = $this->MIOLO->getBusiness( $this->module, 'BusMyLibrary');
        $this->function = MIOLO::_REQUEST('function');
        parent::__construct( _M('Minha biblioteca', $this->module) );
    }

    public function createFields()
    {
        $fields = $this->getMessages(null, 10); //obtém as 10 primeiras mensagens
        
        //Mensagem a ser mostrada no topo da tela
    	$fieldsArray[] = new MDIv('', LABEL_MYLIBRARY);
        
        $fieldsArray[] = GUtil::alinhaForm($fields);
        $fieldsArray[] = new MDiv('divMoreMessages');
        
        if ( $this->business->getTotalMessages() > 10 )
        {
            $fieldsArray[] = $div = new MDiv('divLinkAddMoreMessages', new MLink('addMoreMessages', _M('Mostrar mais mensagens'), 'javascript:' . GUtil::getAjax('addMoreMessages')));
            $div->setClass('addMoreMessages');
        }
        
        $this->setFields( $fieldsArray );
    }

    /**
     * Monta a div de mensagem
     * 
     * @param int $id ID da mensagem
     * @param String $msg mensagem da div
     * @return MHContainer 
     */
    private function getMessageDiv($id, $msg)
    {
        $link = new MImageLink('linkClose' . $id, _M('Fechar', 'gnuteca3'), "javascript:dojo.byId('linkClose{$id}').parentNode.parentNode.style.display = 'none';" . GUtil::getAjax('deleteMessage', $id), GUtil::getImageTheme('deleteMessage-16x16.png'));
        $link->setClass('mLink');
        
        $top = new MDiv('divUp' . $id, $link->generate() );
        $top->setClass('messageClose');
        
        $div = new MDiv($id, $msg);
        $container = new MHContainer('cont' . $id, array($top, $div));
        $container->setClass('messageContent');
        
        return $container;
    }
    
    /**
     * Método que obtém as mesagens de acordo com o limit e offset
     * @param int $offset inicio das mensagens
     * @param int $limit quantidade de mensagens
     * @return array de mensagens 
     */
    public function getMessages($offset=null, $limit=null)
    {
        $this->business->visibleS = DB_TRUE;
        $this->business->personIdS = BusinessGnuteca3BusAuthenticate::getUserCode(); //seta o usuário logado
        $messages = $this->business->searchMyLibrary(true, $offset, $limit, 'mylibraryid DESC');
        
        $fields = array();
        
        if ( is_array($messages) )
        {
            foreach ( $messages as $message )
            {
                $fields[] = $this->getMessageDiv($message->myLibraryId, $message->message);
            }
        }
        
        return $fields;
    }
    
    /**
     * Desativa mensagem
     * 
     * @param integer $id 
     */
    public function deleteMessage($id)
    {
        $this->business->getMyLibrary($id);
        $this->business->visible = DB_FALSE;
        $this->business->updateMyLibrary();
        
        $this->setResponse(null, 'limbo');
    }
    
    /**
     * Ajax que adiciona mensagens restantes
     * 
     * @param stdClass $args argumentos do ajax
     */
    public function addMoreMessages($args)
    {
        $MIOLO = MIOLO::getInstance();
        
        $fields = $this->getMessages(10); //obtém todas mensagens a partir da 10
        
        $responseFields = null;
        
        if ( $fields )
        {
            $responseFields = new MContainer('contMoreMessages', $fields, null, MControl::FORM_MODE_SHOW_SIDE);
        }
        
        //tira link para ver mais mensagens
         $MIOLO->page->onLoad("gnuteca.setDisplay( 'divLinkAddMoreMessages', true, 'none' );");
        
        $this->setResponse($responseFields, 'divMoreMessages');
    }
    
}
?>