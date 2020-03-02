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
 * Class created on 22/10/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdMyFine extends GGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busMaterial;
    public $busExemplaryControl;
    public $busLoan;
    public $busFine;
    public $busSearchFormat;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busExemplaryControl = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busSearchFormat = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busLoan = $this->MIOLO->getBusiness($this->module, 'BusLoan');
        $this->busFine = $this->MIOLO->getBusiness($this->module, 'BusFine');

        $columns = array(
            new MGridColumn(_M('Código da multa', $this->module),          MGrid::ALIGN_CENTER,   null, null, true, null, true),
            new MGridColumn(_M('Código do empréstimo', $this->module),          MGrid::ALIGN_CENTER,   null, null, true, null, true),
            new MGridColumn(_M('Tomo', $this->module),               MGrid::ALIGN_CENTER,   null, null, false, null, true),
            new MGridColumn(_M('Dados', $this->module),               MGrid::ALIGN_LEFT,     null, null, true, null, true),
            new MGridColumn(_M('Data da multa', $this->module),          MGrid::ALIGN_CENTER,   null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Valor', $this->module),              MGrid::ALIGN_CENTER,   null, null, true, null, true),
            new MGridColumn(_M('Estado da multa', $this->module),        MGrid::ALIGN_CENTER,   null, null, true, null, true),
            new MGridColumn(_M('Data final', $this->module),           MGrid::ALIGN_RIGHT,    null, null, false, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Código da pessoa', $this->module),        MGrid::ALIGN_RIGHT,    null, null, false, null, true),
            new MGridColumn(_M('Nome da pessoa', $this->module),        MGrid::ALIGN_RIGHT,    null, null, false, null, true),
            new MGridColumn( _M('Código da biblioteca', $this->module), MGrid::ALIGN_RIGHT,    null, null, false, null, true ),
            new MGridColumn( _M('Unidade de biblioteca', $this->module),      MGrid::ALIGN_LEFT,     null, null, true,  null, true )
        );

        parent::__construct($data, $columns, $this->MIOLO->getActionURL($this->module, $this->action), LISTING_NREGS, 0, 'gridMyPenalty');
        $this->setIsScrollable();

        if ( !MIOLO::_REQUEST('notShowAction') )
        {
            $imageLoan        = GUtil::getImageTheme('loan-16x16.png');
            $this->addActionIcon( _M('Detalhes do empréstimo', $this->module), $imageLoan, GUtil::getAjax('showLoan', '%1%'));

            $imageRenew        = GUtil::getImageTheme('renew-16x16.png');
            $this->addActionIcon( _M('Renovação', $this->module), $imageRenew, GUtil::getAjax('showRenew', '%1%'));
        }
        //Se preferência estiver como falso, não mostra botão CSV
        if ( (CSV_MYLIBRARY == 'f') && (MIOLO::_REQUEST('action') != 'main:materialMovement') )
        {
            $this->setCSV(false);
        }
        $this->setRowMethod($this, 'checkValues');

    }

    public function checkValues($i, $row, $actions, $columns)
    {
        $loanId         = $columns[1]->control[$i]->value;
        $itemNumber     = $this->busLoan->getLoan($loanId)->itemNumber;
        $controlNumber  = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        if ($controlNumber)
        {
            $data = $this->busSearchFormat->getFormatedString($controlNumber, FAVORITES_SEARCH_FORMAT_ID);
            $columns[3]->control[$i]->setValue($data);
        }
        $data = new GDate($columns[4]->control[$i]->value);
        $columns[4]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
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
        $loanId = $line[1];
        $itemNumber = $this->busExemplaryControl->getItemNumberByLoan($loanId);
        $controlNumber = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        
        //Se material existe
        if ( $controlNumber )
        {
            //Pega a string dos dados e adiciona na posição 5 da grid retirando excesso de espaços via expressão regular.
            $line[2] = preg_replace( '/\s+/', ' ', trim(strip_tags($this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID))));
        }
        return $line;
    }
}
?>
