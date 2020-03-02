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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 * */

/**
 * Grid used by form to display search results
 * */
class GrdRenew extends GSearchGrid
{

    public $MIOLO;
    public $module;
    public $action;
    private $busRenew;
    public $busExemplaryControl;
    public $busMaterial;
    public $busSearchFormat;
    private $busLoan;

    public function __construct($data)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busRenew = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');

        $home = 'main:configuration:renew';

        $columns = array(
            new MGridColumn(_M('Código', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, false),
            new MGridColumn(_M('Código do empréstimo', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Código da pessoa', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Pessoa', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Número do exemplar', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Número do tomo', $this->module), MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Dados', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Tipo de renovação', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Data prevista da devolução', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Data de renovação', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Nova data prevista de devolução', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Operador', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
        );

        parent::__construct($data, $columns);


        $args = array(
            'function' => 'update',
            'renewId' => '%0%',
            'personId'=>'%2%'
        );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        $args = array(
            'function' => 'delete',
            'renewId' => '%0%',
        );

        $this->setIsScrollable();
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete(GUtil::getAjax('tbBtnDelete_click', $args));

        $this->setRowMethod($this, 'checkValues');
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        /**
         * ATENÇÃO  
         * O código abaixo foi escrito desta maneira para não impactar na circulação de material.
         * Como pode ser observado abaixo, foi trocada a ordem das colunas 8 e 9, e também foi adicionado a coluna 10, que é a nova data prevista
         * de devolução. Ticket #7250, comentários #10 a #13.
         */
        
        $renewDate = new GDate($columns[8]->control[$i]->value);
        $returnForecastDate = new GDate($columns[9]->control[$i]->value);
        
        //seta data prevista de devolução
        $renew = $this->busRenew->getRenewsOfLoan($columns[1]->control[$i]->value, $columns[0]->control[$i]->value); //passa o código de empréstimo e de renovação por parametro        
        $columns[8]->control[$i]->setValue(GDate::construct($renew[0]->returnforecastdate)->getDate(GDate::MASK_DATE_USER));

        //seta data de devolução
        $columns[9]->control[$i]->setValue($renewDate->getDate(GDate::MASK_TIMESTAMP_USER));

        //seta operador
        $operator = $columns[10]->control[$i]->value;
        $columns[11]->control[$i]->setValue($operator);
        
        
        //seta nova data de renovação
        $historyOfLoan = $this->busRenew->getRenewsOfLoan($columns[1]->control[$i]->value, $columns[0]->control[$i]->value);
        $returnForecastDateLoan = new GDate($historyOfLoan[0]->newreturnforecastdate);
        $columns[10]->control[$i]->setValue($returnForecastDateLoan->getDate(GDate::MASK_DATE_USER));

        $itemNumber = $columns[4]->control[$i]->value;
        $controlNumber = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        if ($controlNumber)
        {
            $data = $this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);
            $columns[6]->control[$i]->setValue($data);
        }
    }

}

?>
