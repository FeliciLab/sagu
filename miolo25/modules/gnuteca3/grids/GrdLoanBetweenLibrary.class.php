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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 16/12/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdLoanBetweenLibrary extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busExemplaryControl;
    public $busLoanBetweenLibraryComposition;
    public $busSearchFormat;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busExemplaryControl              = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busLoanBetweenLibraryComposition = $this->MIOLO->getBusiness($this->module, 'BusLoanBetweenLibraryComposition');
        $this->busSearchFormat      = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');

        $columns = array(
            new MGridColumn(_M('Código', $this->module),                      MGrid::ALIGN_RIGHT,  null, null, true,  null, true),
            new MGridColumn(_M('Data do empréstimo', $this->module),                 MGrid::ALIGN_RIGHT,  null, null, false, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Data prevista da devolução', $this->module),      MGrid::ALIGN_RIGHT,  null, null, false, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Data de devolução', $this->module),               MGrid::ALIGN_RIGHT,  null, null, false, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Data limite', $this->module),                MGrid::ALIGN_RIGHT,  null, null, false, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Código da biblioteca', $this->module),         MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn(_M('Biblioteca requisitante', $this->module),   MGrid::ALIGN_LEFT,   null, null, true,  null, true),
            new MGridColumn(_M('Código da pessoa', $this->module),               MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Código do estado', $this->module),               MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn(_M('Estado', $this->module),                    MGrid::ALIGN_LEFT,   null, null, true,  null, true),
            new MGridColumn(_M('Dados', $this->module),                      MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('Show cancel',                                  MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('Show accept/disaccept',                        MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('Show confirm receipt',                         MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('Show return material',                         MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('Show confirm return',                          MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('EditRecord',                                   MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn('LibrarySearch',                                MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn(_M('Exemplares', $this->module),               MGrid::ALIGN_LEFT,   null, null, true,  null, true),
        );

        parent::__construct($data, $columns);

        $args['loanBetweenLibraryId'] = '%0%';
        $args['libraryUnitId'] = '%17%';
        $args['function'] = 'update';
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $this->actionUpdate = $hrefUpdate;

        //muito importante que isto esteja aqui, sem isso as ações não iram funcionar
        $args['function'] = 'detail';

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $args_del['loanBetweenLibraryId'] = '%0%';
        $args_del['libraryUnitId'] = '%17%';
        $args_del['function'] = 'delete';
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args_del) );

        //Só mostra se a pessoa tiver permissão de editar
        if (GPerms::checkAccess($this->transaction, 'update', false))
        {
            $this->addActionIcon(_M('Cancelar', $this->module), GUtil::getImageTheme('delete-16x16.png'), GUtil::getAjax('cancel', $args));
            $this->addActionIcon(_M('Aprovar',$this->module), GUtil::getImageTheme('accept.png'), GUtil::getAjax('approve', $args));
            $this->addActionIcon(_M('Reprovar', $this->module), GUtil::getImageTheme('error-16x16.png'), GUtil::getAjax('disapprove', $args));
            $this->addActionIcon(_M('Confirmar recebimento', $this->module), GUtil::getImageTheme('loan-16x16.png'), GUtil::getAjax('confirmReceipt', $args) );
            $this->addActionIcon(_M('Devolução do material', $this->module), GUtil::getImageTheme('back-20x20.png'), GUtil::getAjax('returnMaterial', $args));
            $this->addActionIcon(_M('Confirmar retorno', $this->module), GUtil::getImageTheme('confirm-16x16.png'), GUtil::getAjax('confirmReturn', $args));
        }

        $this->setRowMethod($this, 'checkValues');
    }


    public function checkValues($i, $row, $actions, $columns)
    {
        $table = $this->getExemplaryGrid($columns[0]->control[$i]->value);
        $columns[18]->control[$i]->setValue($table);

        //Verify actions to display
        $x = 0;

        if ($this->actionUpdate)
        {
            $actionUpdate = $actions[$x++];
        }

        if ($this->actionDelete)
        {
            $actionDelete = $actions[$x++];
        }
        
        $actionCancel         = $actions[$x++];
        $actionApprove        = $actions[$x++];
        $actionDisapprove     = $actions[$x++];
        $actionConfirmReceipt = $actions[$x++];
        $actionReturnMaterial = $actions[$x++];
        $actionConfirmReturn  = $actions[$x++];

        //Só mostra se a pessoa tiver permissão de editar
        if (GPerms::checkAccess($this->transaction, 'update', false))
        {
            if ($columns[11]->control[$i]->value == DB_TRUE)
            {    
                $actionCancel->show();
            }
            else
            {
                $actionCancel->hide();
            }
            
            if ($columns[13]->control[$i]->value == DB_TRUE)
            {
                $actionConfirmReceipt->show();
            }
            else
            {
                $actionConfirmReceipt->hide();
            }
                
            if ($columns[14]->control[$i]->value == DB_TRUE)
            {
                $actionReturnMaterial->show();
            }
            else
            {
                $actionReturnMaterial->hide();
            }
            
            if ($columns[15]->control[$i]->value == DB_TRUE)
            {
                $actionConfirmReturn->show();
            } 
            else
            {
                $actionConfirmReturn->hide();
            }
            
            if ($columns[16]->control[$i]->value == DB_TRUE)
            {
                if ( $actionUpdate )
                {
                   $actionUpdate->enable();
                }
                
                if ( $actionDelete )
                {
                    $actionDelete->enable();
                }
            }
            else
            {
                if ( $actionUpdate )
                {
                    $actionUpdate->disable();
                }
                
                if ( $actionDelete )
                {
                    $actionDelete->disable();
                }
            }
             
            if ($columns[12]->control[$i]->value == DB_TRUE)
            {
            	$actionApprove->show();
            	$actionDisapprove->show();
            }
            else
            {
                $actionApprove->hide();
                $actionDisapprove->hide();
            }
        }
    }



    public function getExemplaryGrid($loanBetweenLibraryId, &$firstExemplary)
    {
    	//Get and display exemplaryes
        $search = $this->busLoanBetweenLibraryComposition->getCompositionExemplaryStatus($loanBetweenLibraryId);

        if(!$search)
        {
            return;
        }

    	foreach ($search as $l =>$val)
    	{
            $tableData[$l][0] = $val->itemNumber;
            $tableData[$l][1] = $val->description;
            $tableData[$l][2] = GUtil::getYesNo($val->isConfirmed);
            $tableData[$l][3] = $val->libraryname;
            $tableData[$l][4] = '';

            $firstExemplary = $this->busExemplaryControl->getExemplaryControl($val->itemNumber);
            if ($firstExemplary->controlNumber)
            {
                $data = $this->busSearchFormat->getFormatedString($firstExemplary->controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);
                 $tableData[$l][4] = $data;
            }
    	}

        $colTitle = array
        (
            _M("Número do exemplar",           $this->module),
            _M("Estado do exemplar",      $this->module),
            _M("Confirmado",             $this->module),
            _M("Biblioteca de origem",  $this->module),
            _M("Dados",                  $this->module),
        );

        $table = new MTableRaw(null, $tableData, $colTitle);
        $table->addAttribute('width', '100%');
        $table->addAttribute('vertical-align', 'top');
        $table->setCellAttribute(0, 0, 'width', '110');
        $table->setAlternate(true);

        return $table->generate();
    }

}
?>