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
 * Class created on 08/12/2008
 *
 **/
class GrdMyRenew extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busExemplaryControl;
    public $busSearchFormat;

    public function __construct($data)
    {
    	global $MIOLO, $module;

        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');

        $checkAll = new MCheckBox('checkAll');
        $checkAll->addAttribute('onclick', "checkAllBox();");

        $checkAll = $checkAll->generate();

        $columns = array(
            new MGridColumn( GForm::isGenerateDocumentEvent() ? '-' : $checkAll, MGrid::ALIGN_CENTER, false, false, true, false, false),
            new MGridColumn(_M('Número do exemplar', $this->module),        MGrid::ALIGN_CENTER, null, null, true, false, true),
            new MGridColumn(_M('Dados', $this->module),                  MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Data prevista da devolução', $this->module),  MGrid::ALIGN_CENTER, null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Renovações permitidas na biblioteca', $module),  MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Renovações permitidas na Web', $module),         MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Quantidade de reservas', $module),        MGrid::ALIGN_CENTER, null, null, true, null, true),
            new MGridColumn(_M('Unidade de biblioteca', $module),                MGrid::ALIGN_LEFT,   null, null, true, null, true),
        );

        parent::__construct($data, $columns, $MIOLO->getCurrentURL(), 0, 0);

        $this->setShowHeaders(false);
        $this->setIsScrollable();
        //Se preferência estiver como falso, não mostra botão CSV
        if (CSV_MYLIBRARY == 'f')
        {
            $this->setCSV(false);
        }
        $this->setRowMethod($this, 'checkValues');
    }


    public function checkValues($i, $row, $actions, $columns)
    {
        $date = new GDate($columns[3]->control[$i]->value);
        $dateNow = GDate::now();
        
        //Se emprestimo estiver em atraso, destaca conteudo coluna data prevista
        if ( $dateNow->diffDates($date)->days > 1 ) 
        {
            $date = new MLabel($date->getDate(GDate::MASK_DATE_USER), 'red !important', false);
        }
        else
        {
            $date = $date->getDate(GDate::MASK_DATE_USER);
        }
        
        $columns[3]->control[$i]->setValue($date);
    }
}
?>
