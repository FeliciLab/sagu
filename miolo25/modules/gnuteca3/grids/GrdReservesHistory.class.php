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
 * Grid
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdReservesHistory extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busExemplaryControl;
    public $busSearchFormat;
    public $busReserveComposition;


    public function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busReserveComposition = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition');
        $home           = 'main:administration:loan';
        $columns = array(
            new MGridColumn( _M('Código da reserva', $this->module),         MGrid::ALIGN_RIGHT,  null, null, true,  null, true ),
            new MGridColumn( _M('Código da pessoa', $this->module),          MGrid::ALIGN_LEFT,   null, null, false,  null, true ),
            new MGridColumn( _M('Pessoa', $this->module),               MGrid::ALIGN_LEFT,   null, null, false,  null, true ),
            new MGridColumn( _M('Dados', $this->module),                 MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Autor', $this->module),               MGrid::ALIGN_LEFT,   null, null, false,  null, true ),
            new MGridColumn( _M('Data da solicitação', $this->module),       MGrid::ALIGN_LEFT,   null, null, true,  null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Data limite', $this->module),           MGrid::ALIGN_LEFT,   null, null, true,  null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Estado da reserva', $this->module),       MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Tipo de reserva', $this->module),         MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Unidade de biblioteca', $this->module),         MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
        );
        parent::__construct($data, $columns, $this->MIOLO->getCurrentURL(), LISTING_NREGS, 0, 'gridLoan');
        $args['function']           = 'update';
        $args['loanId']             = '%0%';
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $args['event']              = 'tbBtnDelete_click';
        $hrefDelete = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $this->setIsScrollable();
  /*      if ( !MIOLO::_REQUEST('notShowAction') )
        {
            $this->addActionUpdate( $hrefUpdate );
            $this->addActionDelete( $hrefDelete );
        }*/
        //Se preferência estiver como falso, não mostra botão CSV
        if ( (CSV_MYLIBRARY == 'f') && (MIOLO::_REQUEST('action') != 'main:materialMovement') )
        {
            $this->setCSV(false);
        }
        $this->setRowMethod($this, 'checkValues');
    }


    function checkValues($i, $row, $actions, $columns)
    {
        $this->busReserveComposition->reserveId = $columns[0]->control[$i]->value;
        $itemNumber = $this->busReserveComposition->getReserveComposition();
        $itemNumber = $itemNumber[0]->itemNumber;
        $controlNumber  = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        if ($controlNumber)
        {
            $data = $this->busSearchFormat->getFormatedString($controlNumber, FAVORITES_SEARCH_FORMAT_ID);
            $columns[3]->control[$i]->setValue($data);
        }

        $data = new GDate($columns[5]->control[$i]->value);
        $columns[5]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));

        $data = new GDate($columns[6]->control[$i]->value);
        $columns[6]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
    }

    /**
     * Trata a linha($line) para gerar o texto da coluna posição 5 
     * nos relatórios, CSV e PDF.
     * 
     * @param $line
     * @return $line
     * */

    public function reportLine( $line )
    {
        $reserveId = $line[0];
        $itemNumber = $this->busExemplaryControl->getItemNumberByReserve($reserveId);
        $controlNumber = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;

        //Se material existe
        if ( $controlNumber )
        {
            //Pega a string dos dados e adiciona na posição 5 da grid retirando excesso de espaços via expressão regular.
            $line[1] = preg_replace( '/\s+/', ' ', trim(strip_tags($this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID))));
        }
        return $line;
    }
}
?>