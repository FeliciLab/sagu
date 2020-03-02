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
 * Grid for Congelado
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 28/04/2008
 *
 **/
class GrdCongelado extends GGrid
{
    public $MIOLO;
    public $module;
    public $home;
    public $columns;
    public $busAuthenticate;
    public $busRequestChangeExemplaryStatus;
    public $busRequestChangeExemplaryStatusAccess;
    public $busExemplaryControl;
    public $busSearchFormat;
    public $requestChangeExemplaryStatusId;
    public $personId;
    public $action;
    public $imagePlus;
    public $imageMinus;
    public $periodInformation;


    function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->action   = MIOLO::getCurrentAction();
        $this->home     = 'main:configuration:congelado';
        $this->busAuthenticate                          = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busRequestChangeExemplaryStatusAccess    = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusAccess');

        //Só mostrar todas as colunas para grupos que tem permissão de requisição
        $view = $this->busRequestChangeExemplaryStatusAccess->checkPersonAccess();

        $this->columns = array
        (
            new MGridColumn( _M('Código',           $this->module), MGrid::ALIGN_CENTER,   null, null, $view, null, true ),
            new MGridColumn( _M('Dados'           , $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Disciplina'     ,  $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Pessoa',           $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Estado',           $this->module), MGrid::ALIGN_LEFT,     null, null, $view, null, true ),
            new MGridColumn( _M('Estado futuro',    $this->module), MGrid::ALIGN_LEFT,     null, null, $view, null, true ),
            new MGridColumn( _M('Data',             $this->module), MGrid::ALIGN_CENTER,   null, null, $view, null, true, MSort::MASK_DATE_BR ),
            new MGridColumn( _M('Data final',       $this->module), MGrid::ALIGN_CENTER,   null, null, $view, null, true, MSort::MASK_DATE_BR ),
            new MGridColumn( _M('Biblioteca',       $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Aprovar apenas um',$this->module), MGrid::ALIGN_LEFT,     null, null, false, null, true ),
            new MGridColumn( _M('Código do estado', $this->module), MGrid::ALIGN_LEFT,     null, null, false, null, true ),
            new MGridColumn( _M('Composição',       $this->module), MGrid::ALIGN_LEFT,     null, null, false, null, true ),
        );

        $this->busAuthenticate                  = $this->MIOLO->getBusiness( $this->module, 'BusAuthenticate');
        $this->busRequestChangeExemplaryStatus  = $this->MIOLO->getBusiness( $this->module, 'BusRequestChangeExemplaryStatus');
        $this->busExemplaryControl              = $this->MIOLO->getBusiness( $this->module, 'BusExemplaryControl');
        $this->busSearchFormat                  = $this->MIOLO->getBusiness( $this->module, 'BusSearchFormat');
        
        parent::__construct($data , $this->columns, $this->MIOLO->getCurrentURL(), LISTING_NREGS, null, null);

        //Se preferência estiver como falso, não mostra botão CSV
        if ( CSV_MYLIBRARY == DB_FALSE )
        {
            $this->setCSV(false);
        }
        
        $this->periodInformation = $this->busRequestChangeExemplaryStatus->getPeriodInterval();
        
        $opts['requestChangeExemplaryStatusId'] = '%0%';
        $this->addActionIcon( _M('Cancelar', $this->module), GUtil::getImageTheme('cancel-16x16.png'), GUtil::getAjax('cancelReserveConfirm', $opts) );

        $this->imagePlus    = new MImage('imagePlus', _M('Mais', $this->module) , GUtil::getImageTheme('plus-8x8.png'));
        $this->imageMinus   = new MImage('imagePlus', _M('menos', $this->module) , GUtil::getImageTheme('minus-8x8.png'));
        
        $this->page->addJsCode("
            function changeDisplay(divId, divId2)
            {
                image   = document.getElementById(divId2);
                image   = image.childNodes[1];
                if (element.style.display == 'none' )
                {
                    image.src = '{$imageMinus}';
                }
                else
                {
                    image.src = '{$imagePlus}';
                }
            }
        ");

        if ( $view )
        {
            $this->addActionSelect();

            $this->setFooter( new MButton('btnRenew', _M('Renovar', $this->module), ':btnFinalize', GUtil::getImageTheme('renew-16x16.png')) );
        }

        $this->setIsScrollable();
        $this->setRowMethod($this, 'checkValues');
    }


    /**
     * Método que permite a modificação dos dados da grid
     *
     * @param unknown_type $i
     * @param unknown_type $row
     * @param unknown_type $actions
     * @param unknown_type $columns
     */
    public function checkValues($i, $row, $actions, $columns)
    {
        //Organizacao das colunas (deve seguir a mesma organizacao das posicoes do $this->columns)
        $colCode        = $columns[0]->control[$i];
        $colData        = $columns[1]->control[$i];
        $finalDate      = $columns[7]->control[$i];
        $colStatus      = $columns[10]->control[$i];
        $colComposition = $columns[11]->control[$i];

        //Só permite cancelar quando usuário da requisição for o que estiver logado
        $userCode = $this->busAuthenticate->getUserCode();
        $requestChangeExemplaryStatusId     = $colCode->value;
        $requestStatusId                    = $colStatus->value;
        $personId = $this->busRequestChangeExemplaryStatus->getRequestChangeExemplaryStatus($requestChangeExemplaryStatusId)->personId;

        //Se o dia de hoje estiver no invervalo do período, libera chackbox para renovar congelamento.
        if ($finalDate->value)
        {
            $finalDate = new GDate($finalDate->value);
            $finalDate->addDay(- $this->periodInformation->requestChangeDays);
            $isRenewPeriod = (GDate::now()->diffDates($finalDate,true)->days > 0) ? true:false;
        }

        if( $userCode == $personId )
        {
            $actions[0]->enable();
        }        

        /*
         * Atualização para poder cancelar congelamento solicitado
         * Antiga implementação:
               
               if ( !$isRenewPeriod || //Não for o periodo
                    $userCode != $personId ||  // Não for a pessoa
                    $requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL || //Não for o estado de requisição
                    $requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE || //Não for o estado de requisição
                    $requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED) //Não for o estado de requisição
         * 
         */
        if ( $userCode != $personId || 
             $requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_CANCEL || //Estado não seja cancelado
             $requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_CONCLUDE || //Estado não seja concluído
             $requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_REPROVED ) //Estado não seja reprovado
    	{
            $this->page->onLoad("dojo.byId('selectGrdCongelado[{$i}]').disabled = 'disabled';
                                 dojo.byId('selectGrdCongelado[{$i}]').style.display = 'none';");
                                 
            $actions[0]->disable();
        }

        // TRABALHA OS DADOS DA COMPOSICAO
        $content = $colComposition->getValue();
        $content = explode(";", $content);
        
        foreach ($content as $lines)
        {
            $tableData[] = explode("|", $lines);
        }

        $colTitle = array
        (
            _M("Número do exemplar",   $this->module),
            _M("Confirmado",     $this->module),
            _M("Aplicado",       $this->module),
        );

        $table = new MTableRaw(null, $tableData, $colTitle);
        $table->addAttribute('width', '100%');
        $table->addAttribute('vertical-align', 'top');
        $table->setCellAttribute(0, 0, 'width', '110');
        $table->setAlternate(true);

        $colComposition->setValue($table->generate());


        //FIXME precisa verificar como esconder algumas checkbox conforme o caso
        //Se a requisição for da pessoa logada, estiver no estado Aprovada e dentro do perÃ­odo permitido para renovar.
        //if ( ($personId == $userCode) && ($requestStatusId == REQUEST_CHANGE_EXEMPLARY_STATUS_CONFIRMED) && ($renew[0]) )
        //{
	    //    $cb = new MCheckBox("selectGrdCongelado[{$i}]");
	    //    $cb->addAttribute('class', 'selectCongelado');
	    //    $cb->setValue($requestChangeExemplaryStatusId);
	    //    $colCheckbox->setValue( $cb->generate() );
        //}

        $itemNumber     = $tableData[0][0];
        $controlNumber  = $this->busExemplaryControl->getControlNumber($itemNumber);

        $colData->setValue( $this->busSearchFormat->getFormatedString( $controlNumber , FAVORITES_SEARCH_FORMAT_ID ) );
    }
}
?>