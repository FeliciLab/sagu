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
class GrdReserve extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busMaterial;
    public $busReserveComposition;
    public $busExemplaryControl;
    public $busSearchFormat;
    public $actionIndexes;
    public $actionNeglectReserve;

    public function __construct($data)
    {
        $this->MIOLO       = MIOLO::getInstance();
        $this->module      = MIOLO::getCurrentModule();
        $this->action      = MIOLO::getCurrentAction();
        $this->busMaterial           = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busReserveComposition = $this->MIOLO->getBusiness($this->module, 'BusReserveComposition');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $home           = 'main:administration:reserve';
        $columns = array(
            new MGridColumn( _M('Código da reserva', $this->module),         MGrid::ALIGN_RIGHT,  null, null, true,  null, true ),
            new MGridColumn( _M('Código da pessoa', $this->module),          MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Pessoa', $this->module),               MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Dados', $this->module),                MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Autor', $this->module),               MGrid::ALIGN_LEFT,   null, null, false,  null, true ),
            new MGridColumn( _M('Data da solicitação', $this->module),       MGrid::ALIGN_LEFT,   null, null, true,  null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Data limite', $this->module),           MGrid::ALIGN_LEFT,   null, null, true,  null, true, MSort::MASK_DATETIME_BR ),
            new MGridColumn( _M('Estado da reserva', $this->module),       MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Tipo de reserva', $this->module),         MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Unidade de biblioteca', $this->module),         MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Código Estado da reserva', $this->module),MGrid::ALIGN_LEFT,   null, null, false,  null, true ),            
        );

        parent::__construct($data, $columns);

        $args['reserveId']  = '%0%';
        $args['function']   = 'update';
        $hrefUpdate         = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $args['function']   = 'delete';
        $args['function']   = 'search';

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $imageHistoric        = GUtil::getImageTheme('info-16x16.png');
        $this->addActionIcon(_M('Histórico', $this->module), $imageHistoric, GUtil::getAjax('showDetail', $args) );
        $imageReserve         = GUtil::getImageTheme('folder-16x16.png');
        $this->addActionIcon(_M('Composição da reserva', $this->module), $imageReserve, GUtil::getAjax('showReserveComposition', $args) );
        
        $imageNeglectReserve   = GUtil::getImageTheme('back-20x20.png');
        $this->addActionIcon(_M('Desatender reserva', $this->module), $imageNeglectReserve, GUtil::getAjax('btnNeglectReserve', $args) );        
        
        //Cria estrutura para saber qual o indice
        $actionIndex = 0;
        $this->actionUpdate? $this->actionIndexes['update'] = $actionIndex++:null;
        $this->actionDelete? $this->actionIndexes['delete'] = $actionIndex++:null;
        $this->actionIndexes['showDetail'] = $actionIndex++;
        $this->actionIndexes['showReserveComposition'] = $actionIndex++;
        $this->actionIndexes['neglectReserve'] = $actionIndex++;
        
        //Garante que a acao nao se perdera no generateActions desta classe.
        $this->actionNeglectReserve = $this->actions[$this->actionIndexes['neglectReserve']];
        
        $this->setRowMethod( $this, 'checkRowDate' );
    }


    public function checkRowDate($i, $row, $actions, $columns)
    {
        $this->busReserveComposition->reserveId = $columns[0]->control[$i]->value;
        $itemNumber = $this->busReserveComposition->getReserveComposition();
        $itemNumber = $itemNumber[0]->itemNumber;

        $controlNumber  = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        if ($controlNumber)
        {
            $data = $this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);
            $columns[3]->control[$i]->setValue($data);
        }

        $data = new GDate($columns[5]->control[$i]->value);
        $data = $data->getDate(GDate::MASK_TIMESTAMP_USER);
        $columns[5]->control[$i]->value = substr($data, 0, strlen($data)-3);

        $data = new GDate($columns[6]->control[$i]->value);
        $columns[6]->control[$i]->value = $data->getDate(GDate::MASK_DATE_USER);
    }

    /**
     * Sobrescreve generateActions da GGrid para poder fazer logica
     * que mostra acao de desatender reserva conforme o conteudo da 
     * linha.
     * 
     * @param MSimpleTable $tbl
     */
    public function generateActions(&$tbl)
    {
        //Se for reserva atendida e tiver permissao de alterar.
        if ( GPerms::checkAccess($this->transaction, 'update', false) && $this->columns[10]->control[$this->currentRow]->value == ID_RESERVESTATUS_ANSWERED )
        {
            //Mostra opçao de desatender a reserva.
            $this->actions[$this->actionIndexes['neglectReserve']] = $this->actionNeglectReserve;
        }
        else
        {
            //Se nao for reserva atendida omite acao de desatender reserva.
            unset($this->actions[$this->actionIndexes['neglectReserve']]);
        }
        
        parent::generateActions($tbl);
    }    
}
?>
