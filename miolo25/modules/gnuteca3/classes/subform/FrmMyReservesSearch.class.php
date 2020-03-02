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
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 03/10/2008
 *
 **/
class FrmMyReservesSearch extends GSubForm
{
    public  $function,
            $localFields,
            $UI,
            $grid,
            $business,
            $businessReserve,
            $busOperationReserve,
            $busAuthenticate,
            $busExemplaryControl,
            $busFavorite;
    public  $action;


    function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->function = MIOLO::_REQUEST('function');
        $this->UI       = $this->MIOLO->getUI();
        
        $this->business            = $this->MIOLO->getBusiness($this->module, 'BusMyReserves');
        $this->businessReserve     = $this->MIOLO->getBusiness($this->module, 'BusReserve');
        $this->busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        $this->busAuthenticate     = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busFavorite         = $this->MIOLO->getBusiness($this->module, 'BusFavorite');

        $this->MIOLO->getClass( $this->module, 'controls/GMaterialDetail' );

        parent::__construct( _M("Minhas reservas", $this->module) );
    }

    /**
     * Create Default Fileds for Search Form
     *
     * @return void
     */
    public function createFields()
    {
        $this->defaultButton = false;
        $this->grid = $this->UI->getGrid( $this->module, 'GrdMyReserves' );
        $this->grid->setData( $this->business->getMyReserves() );
        //Mensagem a ser mostrada no topo da tela
        $fields[] = new MDiv( '', LABEL_MY_RESERVES );
        $fields[] = $this->grid;
        $this->setFields($fields);
    }

    /**
     * Cancela uma determinada reserva
     */
    public function cancel($reserveId)
    {
        GForm::question( _M('Tem certeza que deseja cancelar a reserva?', $this->module), 'javascript:'.GUtil::getAjax('cancelConfirm', $reserveId) );
    }

    public function cancelConfirm($reserveId)
    {
        if ( $this->busOperationReserve->cancelReserve($reserveId) )
        {
            GForm::information('Reserva cancelada!', $this->getCloseAndReloadAction());
        }
        else
        {
            GForm::error( MSG_RECORD_ERROR );
        }
    }

    /**
     * Ao clicar na estrela dos favoritos
     *
     * @param object $args stdclass miolo ajax object
     */
    public function favorites( $reserveId )
    {
        $personId = $this->busAuthenticate->getUserCode();
        
        if (!($personId) || !($reserveId))
        {
            return;
        }
        
        $reserve             = $this->businessReserve->getReserve($reserveId);
        $itemNumber          = $reserve->reserveComposition[0]->itemNumber;
        $controlNumber       = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        $data                = new StdClass();
        $data->controlNumber = $controlNumber;
        $data->personId      = $personId;
        $data->entraceDate   = GDate::now()->getDate(GDate::MASK_TIMESTAMP_DB);
        $favorite = $this->busFavorite->getFavorite($data->personId, $data->controlNumber);

        if ( $favorite->personId )
        {
            GForm::error( _M('Este material já está nos favoritos', $this->module) );
        }
        else
        {
            $this->busFavorite->setData($data);
            $this->busFavorite->insertFavorite();
            GForm::information( _M('Este material foi adicionado aos favoritos com sucesso!', $this->module));
        }
    }


    /**
     * Mostra detalhes da obra
     *
     */
    public function showDetail($reserveId)
    {
    	$reserve       = $this->businessReserve->getReserve($reserveId);
    	$itemNumber    = $reserve->reserveComposition[0]->itemNumber;
    	$controlNumber = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;

        $args->disableReserveButton = TRUE;
    	$fields[] = new GMaterialDetail($controlNumber, null, ADMINISTRATION_SEARCH_FORMAT_ID, null, true, $args);
    	GForm::injectContent($fields, false, _M('Detalhe', $this->module));
    }
}
?>
