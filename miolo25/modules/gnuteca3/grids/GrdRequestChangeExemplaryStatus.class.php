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
 * @author Luiz G. Gregory Filho [luiz@solis.coop.br]
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
 * Class created on 08/04/2008
 *
 **/

class GrdRequestChangeExemplaryStatus extends GSearchGrid
{
    public  $MIOLO;
    public  $module;
    public  $home;
    public  $columns;
    public  $busExemplaryControl;
    public  $busSearchFormat;

    function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->home     = 'main:configuration:requestChangeExemplaryStatus';
        $this->busExemplaryControl = $this->MIOLO->getBusiness( $this->module, 'BusExemplaryControl');
        $this->busExemplaryControl = new BusinessGnuteca3BusExemplaryControl();
        $this->busSearchFormat     = $this->MIOLO->getBusiness( $this->module, 'BusSearchFormat');

        $this->columns = array
        (
            new MGridColumn( _M('Código',                     $this->module), MGrid::ALIGN_CENTER,   null, null, true,  null, true ),
            new MGridColumn( _M('Dados'           ,          $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Disciplina'     ,          $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Pessoa',                   $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Estado',                   $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Estado futuro',            $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Data',                     $this->module), MGrid::ALIGN_CENTER,   null, null, true,  null, true, MSort::MASK_DATE_BR ),
            new MGridColumn( _M('Data final',               $this->module), MGrid::ALIGN_CENTER,   null, null, true,  null, true, MSort::MASK_DATE_BR ),
            new MGridColumn( _M('Unidade de biblioteca',    $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Aprovar apenas um',          $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
            new MGridColumn( _M('Código do estado',                 $this->module), MGrid::ALIGN_LEFT,     null, null, false, null, true ),
            new MGridColumn( _M('Composição',              $this->module), MGrid::ALIGN_LEFT,     null, null, true,  null, true ),
        );

        parent::__construct($data, $this->columns);

        $args_upd = array ('function' => 'update', 'requestChangeExemplaryStatusId'    => '%0%',);
        $href_upd = $this->MIOLO->getActionURL( $this->module,$this->MIOLO->getCurrentAction(), null, $args_upd);
        $this->addActionUpdate( $href_upd );

        $args_del = array( 'function' => 'delete', 'requestChangeExemplaryStatusId' => '%0%');
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args_del) );

        //Só mostra se a pessoa tiver permissão de editar
        if (GPerms::checkAccess($this->transaction, 'update', false))
        {
            $args['_id']    = '%0%';
            $this->addActionIcon( _M('Cancelar', $this->module), GUtil::getImageTheme('cancel-16x16.png'),   GUtil::getAjax('cancelRequest', $args) );
            $this->addActionIcon( _M('Aprovar',  $this->module), GUtil::getImageTheme('accept-16x16.png'),   GUtil::getAjax('aproveRequest', $args) );
            $this->addActionIcon( _M('Reprovar', $this->module), GUtil::getImageTheme('delete-16x16.png'),   GUtil::getAjax('reproveRequest', $args)  );
            $this->addActionIcon( _M('Concluir', $this->module), GUtil::getImageTheme('gnuteca3-16x16.png'), GUtil::getAjax('concludeRequest', $args)  );
            $this->addActionIcon( _M('Renovar',  $this->module), GUtil::getImageTheme('renew-16x16.png'),    GUtil::getAjax('renewRequest', $args)  );
        }

        $this->setRowMethod($this, 'checkValues');
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $i
     * @param unknown_type $row
     * @param unknown_type $actions
     * @param unknown_type $columns
     */
    public function checkValues($i, $row, $actions, $columns)
    {
        $content = $columns[11]->control[$i]->getValue();
        $content = explode(";", $content);

        foreach ($content as $lines)
        {
            $tableData[] = explode("|", $lines);
        }

        $colTitle = array
        (
            _M("Número do exemplar",       $this->module),
            _M("Confirmado",         $this->module),
            _M("Aplicado",           $this->module),
        );

        $table = new MTableRaw(null, $tableData, $colTitle);
        $table->addAttribute('width', '100%');
        $table->addAttribute('vertical-align', 'top');
        $table->setCellAttribute(0, 0, 'width', '110');
        $table->setAlternate(true);

        //Gera coluna composição
        $columns[11]->control[$i]->setValue( $table->generate() );

        //Monta coluna Dados (Título, Autor e Classifição)
        $itemNumber     = $tableData[0][0];
        $controlNumber  = $this->busExemplaryControl->getControlNumber($itemNumber);

    	$tempData = $this->busSearchFormat->getFormatedString( $controlNumber , FAVORITES_SEARCH_FORMAT_ID );
        $columns[1]->control[$i]->setValue($tempData);
    }
}
?>
