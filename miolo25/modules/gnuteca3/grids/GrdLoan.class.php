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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 04/08/2008
 *
 **/
class GrdLoan extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;
    public $busExemplaryControl;
    public $busMaterial;
    public $busSearchFormat;
    public $busRenew;

    public function __construct($data)
    {
        $this->MIOLO       = MIOLO::getInstance();
        $this->module      = MIOLO::getCurrentModule();
        $this->action      = MIOLO::getCurrentAction();
        $this->busExemplaryControl  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryControl');
        $this->busMaterial          = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busSearchFormat      = $this->MIOLO->getBusiness($this->module, 'BusSearchFormat');
        $this->busRenew             = $this->MIOLO->getBusiness($this->module, 'BusRenew');
        $columns = array(
            new MGridColumn( _M('Código do empréstimo', $this->module),            MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Código do tipo de empréstimo', $this->module),       MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Tipo de empréstimo', $this->module),            MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Código da pessoa', $this->module),          MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Pessoa', $this->module),               MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Número do exemplar', $this->module),          MGrid::ALIGN_RIGHT,  null, null, true,  null, true ),
            new MGridColumn( _M('Número do tomo', $this->module),       MGrid::ALIGN_RIGHT,  null, null, false,  null, true ),
            new MGridColumn( _M('Dados', $this->module),                 MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Data do empréstimo', $this->module),            MGrid::ALIGN_RIGHT,  null, null, true,  null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn( _M('Data prevista da devolução', $this->module), MGrid::ALIGN_RIGHT,  null, null, true,  null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn( _M('Data de devolução', $this->module),          MGrid::ALIGN_RIGHT,  null, null, true,  null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn( _M('Total das renovações', $this->module),       MGrid::ALIGN_RIGHT,  null, null, true, null, true),
            new MGridColumn( _M('Quantidade de renovações', $this->module),       MGrid::ALIGN_RIGHT,  null, null, true,  null, true ),
            new MGridColumn( _M('Quantidade de renovações web', $this->module),   MGrid::ALIGN_RIGHT,  null, null, true,  null, true ),
            new MGridColumn( _M('Bônus de renovações web', $this->module),    MGrid::ALIGN_CENTER, null, null, true,  GUtil::listYesNo(), true ),
            new MGridColumn( _M('Operador do empréstimo', $this->module),        MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Operador da devolução', $this->module),      MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
            new MGridColumn( _M('Código do vínculo', $this->module),            MGrid::ALIGN_RIGHT,  null, null, false, null, true ),
            new MGridColumn( _M('Vínculo', $this->module),                 MGrid::ALIGN_RIGHT,  null, null, true,  null, true ),
            new MGridColumn( _M('Código da biblioteca', $this->module),    MGrid::ALIGN_RIGHT,  null, null, false, null, true ),
            new MGridColumn( _M('Unidade de biblioteca', $this->module),         MGrid::ALIGN_LEFT,   null, null, true,  null, true ),
        );
        parent::__construct($data, $columns);
        $args['function']   = 'update';
        $args['loanId']     = '%0%';
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $args['function']   = 'search';
        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $imageHistoric        = GUtil::getImageTheme('info-16x16.png');
        $this->addActionIcon(_M('Histórico', $this->module), $imageHistoric, GUtil::getAjax('showRenew', $args) );
        $this->setRowMethod($this, 'checkValues');
        
        //Adiciona botões para ativar/desativar bit anti-furto
        if(RFID_INTEGRATION == DB_TRUE)
        {
            $ativaBit  = GUtil::getImageTheme('cancel-16x16.png');
            $this->addActionIcon(_M('Ativa anti-furto', $this->module), $ativaBit, GUtil::getAjax('ativaAntiFurto', $args) );
            
            $desativaBit  = GUtil::getImageTheme('cancel-off-16x16.png');
            $this->addActionIcon(_M('Desativa anti-furto', $this->module), $desativaBit, GUtil::getAjax('desativaAntiFurto', $args) );
            
        }//Fim da integração
        
    }


    public function checkValues($i, $row, $actions, $columns)
    {
    	$itemNumber = $columns[5]->control[$i]->value;
        $controlNumber = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;
        
        if ( $controlNumber )
        {
            $data = $this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID);
            $columns[7]->control[$i]->setValue($data);
        }
        
        
    	$loanId = $columns[0]->control[$i]->value;
        $countRenew = $this->busRenew->getCountRenew($loanId);
        $columns[11]->control[$i]->setValue($countRenew);
    }

    /**
     * Trata a linha($line) para gerar o texto da coluna posição 5 corretamente
     * nos relatórios, CSV e PDF.
     * 
     * @param $line
     * @return $line
     * */

    public function reportLine( $line )
    {
        
    	$itemNumber = $line[4];
        $controlNumber = $this->busExemplaryControl->getExemplaryControl($itemNumber)->controlNumber;

        //Se material existe
        if ( $controlNumber )
        {
            //Pega a string dos dados e adiciona na posição 5 da grid retirando excesso de espaços via expressão regular.
            $line[5] = preg_replace( '/\s+/', ' ', trim(strip_tags($this->busSearchFormat->getFormatedString($controlNumber, ADMINISTRATION_SEARCH_FORMAT_ID))));
        }

        return $line;
    }

}
?>
