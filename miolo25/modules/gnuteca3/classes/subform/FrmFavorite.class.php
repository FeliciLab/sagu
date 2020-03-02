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
 *
 * Favorite Form
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/08/2008
 *
 **/

class FrmFavorite extends GSubForm
{
    public $action;
    public $business;
    public $busAutenthicate;
    public $grid;
    public $function;

    public function __construct()
    {
        $this->manager              = MIOLO::getInstance();
        $this->module               = MIOLO::getCurrentModule();
        $this->action               = MIOLO::getCurrentAction();
        $this->business             = $this->manager->getBusiness( $this->module, 'BusFavorite');
        $this->busAutenthicate      = $this->manager->getBusiness( $this->module, 'BusAuthenticate');
        $this->function = MIOLO::_REQUEST('function');
        $this->manager->getClass( $this->module, 'controls/GMaterialDetail' );
        parent::__construct( _M('Meus favoritos', $this->module) );

        if ($this->function == 'showDetail' && !$this->getEvent())
        {
            $this->showDetail();
        }
    }


    public function createFields()
    {
        $beginEntraceDateS = new MCalendarField('beginEntraceDateS', null, _M('Data de entrada', $this->module));
        $endEntraceDateS = new MCalendarField('endEntraceDateS');
        $fields[] = new GContainer('hctDates', array($lblDate, $beginEntraceDateS, $endEntraceDateS));
        $validators[] = new MDateDMYValidator('beginEntraceDateS');
        $this->setFields( $fields, true );
    }

    public function getGrid()
    {
        $grid = $this->manager->getUI()->getGrid( $this->module, 'GrdFavorite');

        try
        {
            $data = (object) $_REQUEST;
            $this->business->setData( $data );
            $this->business->personIdS = BusinessGnuteca3BusAuthenticate::getUserCode();
            $this->business->entraceDateS = GDate::construct($this->business->entraceDateS)->getDate(GDate::MASK_DATE_DB);
            $data = $this->business->searchFavorite();
            $grid->setData($data);
        }
        catch ( EDatabaseException $e )
        {
            GForm::error( $e->getMessage() );
        }

        return $grid;
    }

    /**
     * Event triggered when user chooses Delete from the toolbar
     **/
    public function deleteFavoriteConfirm($controlNumber)
    {
        GForm::question( MSG_CONFIRM_RECORD_DELETE, 'javascript:'.GUtil::getAjax('deleteFavorite', $controlNumber));
    }

    /**
     * Event triggered when user chooses Yes from the Delete prompt dialog
     **/
    public function deleteFavorite($controlNumber)
    {
        $personId = BusinessGnuteca3BusAuthenticate::getUserCode();
        
        if (!is_numeric($controlNumber)  || !is_numeric($personId))
        {
            return;
        }

        $ok = $this->business->deleteFavorite( $personId, $controlNumber );

        if ( $ok )
        {
            //recarrega e chama o evento de busca denovo
            GForm::information( MSG_RECORD_DELETED, GUtil::getCloseAction( true ) . GUtil::getAjax('searchFunctionSub') );
        }
        else
        {
            GForm::error(MSG_RECORD_ERROR, $this->getCloseAndReloadAction());
        }
    }
}
?>