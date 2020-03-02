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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 08/01/2009
 *
 * */
$MIOLO = MIOLO::getInstance();
$MIOLO->getClass('gnuteca3', 'controls/GGridNavigator');

class GGrid extends MGrid
{
    public $transaction;
    public $actionUpdate = false;
    public $actionDelete = false;
    public $useCSV = TRUE;
    public $comma = ',';
    public $categoryAndLevelMenus; //menus de categoria e nivel utilizados para montar link de filhos
    public $materialPermission;
    public $busMaterialControl;
    public $busSpreadSheet;
    public $MIOLO;
    public $module;
    public $count;
    public $printCSVTitleLine = FALSE;

    /**
     * Identificador de chave primária
     * @var string
     */
    private $primaryKey;

    /**
     * Código do workflow
     * @var string
     */
    public $workflowId;

    /**
     * Coluna que mantém o código relacionado com workflow (tableId)
     * 
     * @var integer
     */
    public $workflowTableIdColumn = '0';
    protected $worflowAction; //readOnly
    /**
     * Objeto de label, para ser acessível em quem extender
     *
     * @var object
     */
    protected $countLabel;

    /**
     * Alinhamento das ações
     * 
     * @var string pode ser horizontal ou vertical
     */
    public $actionAlign = 'horizontal';

    public function __construct($data, $columns, $href, $pageLength = null, $index = null, $name = null, $useSelecteds = null, $useNavigator = null)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = 'gnuteca3';

        $this->setTransaction($data->transaction);

        //verificações de segurança que garante integridade do sistema
        if ( !$href )
        {
            $href = $this->MIOLO->getCurrentURL();
        }

        //tem que ter is_null, pode pode ser passado 0, que significa sem paginação
        if ( is_null($pageLength) )
        {
            $pageLength = LISTING_NREGS;
        }

        if ( !$name )
        {
            $name = get_class($this);
        }

        if ( !$index )
        {
            $index = 0;
        }

        parent::__construct(null, $columns, $href, $pageLength, $index, $name, $useSelecteds, $useNavigator);

        $empty['img'] = new MImage('emptyImg', null, Gutil::getImageTheme('info.png'));
        $empty['img']->addStyle('width', '50px');
        $empty['img']->addStyle('height', '50px');
        $empty['img']->addStyle('padding', '5px');        
        $empty[] = new MDiv('emptyMsg', _M('Sua pesquisa não encontrou nenhum resultado.</br> Certifique-se de que todas as palavras estejam escritas corretamente.', 'gnuteca3'));
        $emptyMsg = new MDiv('', $empty);

        $this->emptyMsg = $emptyMsg;
    }

    /**
     * Define um contador diferenciado para a navegação da grid.
     *
     * @param integer $count um contador diferenciado para a navegação da grid.
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    public function setTransaction($transaction = null)
    {
        $this->transaction = $transaction;
    }

    public function setWorkflow($workflowId, $workflowTableIdColumn = 0)
    {
        $this->workflowId = $workflowId;
        $this->workflowTableIdColumn = $workflowTableIdColumn;
    }

    public function getWorkflow()
    {
        return $this->workflowId;
    }

    public function addActionUpdate($href)
    {
        if ( GPerms::checkAccess($this->transaction, 'update', false) )
        {
            $this->actionUpdate = true;
            $this->addActionIcon(_M("Editar"), 'edit', $href, 0, 'edit');
        }
    }

    public function addActionDelete($href)
    {
        if ( GPerms::checkAccess($this->transaction, 'delete', false) )
        {
            $this->actionDelete = true;
            parent::addActionDelete($href);
        }
    }

    public function addActionIcon($alt, $icon, $href=null, $index = 0, $imageName = 'edit')
    {
        $object = new GGridActionIcon($this, $icon, $href, $alt, $imageName);
        $this->actions[] = $object;
        return $object;
    }

    public function setCSV($useCSV = true, $comma = ',')
    {
        $this->useCSV = $useCSV;
        $this->comma = $comma;
    }

    public function generateData()
    {
        if ( !$this->data )
        {
            return;
        }

        $this->orderby = $this->page->request('orderby');

        if ( $this->ordered = isset($this->orderby) )
        {
            $this->applyOrder($this->orderby);
            $this->page->setViewState('orderby', $this->orderby, $this->name);
        }

        //caso use contador personalizado
        if ( $this->count )
        {
            $this->pn = new GGridNavigator($this->pageLength, $this->count, $this);
        }
        else if ( $this->pageLength )
        {
            $this->pn = new GGridNavigator($this->pageLength, $this->rowCount, $this);
            $this->data = $this->getPage();
        }
        else //caso não tenha dados zera o páginador
        {
            $this->pn = null;
        }
    }

    public function getPage()
    {
        if ( count($this->data) && is_array($this->data) )
        {
            return array_slice($this->data, $this->pn->getFirstPaginedRegister(), $this->pn->pageLength);
        }
    }

    public function generateNavigationHeader()
    {
        if ( $this->showHeaders() )
        {
            $pageCount = 1;

            if ( $this->pn )
            {
                $pageCount = $this->pn->getPageCount();
                $count = $this->pn->getRowCount() ? $this->pn->getRowCount() : $this->rowCount;
            }
            else
            {
                $count = $this->rowCount;
            }

            $msgCount = _M("A busca gerou <b>@1</b> resultado(s) listados em <b>@2</b> página(s)", MIOLO::getCurrentModule(), $count, $pageCount);

            $this->countLabel = new MDiv('countLabel', $msgCount);
            $fields[0] = $this->countLabel;
            $fields[0]->addAttribute('tabindex', '0');
            $fields[0]->addAttribute('alt', strip_tags($msgCount));
            $fields[0]->addAttribute('title', strip_tags($msgCount));

            //mostra ordenação para usuário
            $orderby = MIOLO::_REQUEST('orderby');

            if ( $orderby )
            {
                $column = $this->columns[$orderby];

                $fields[1] = new MDiv('orderLabel', _M('Ordenando por: ', $this->module) . $column->title, 'mGridOrderBy');
            }

            $d = new MDiv('', $fields, 'mGridNavigation');
            return $d;
        }
        else
        {
            return null;
        }
    }

    public function generateNavigationFooter()
    {
        return new MDiv('', $this->pn, 'mGridNavigation mGridNavigationFooter');
    }

    public function generateActions(&$tbl)
    {
        $i = $this->currentRow;

        if ( $this->hasDetail )
        {
            $i += $this->currentRow;
        }

        $c = 0; // colNumber

        $spanClass = ($this->select != NULL) ? ' tall' : '';
        $control = new MSpan('', '&nbsp;');

        if ( $this->actionDefault->href )
        {
            $control->addAttribute('onclick', $this->actionDefault->generate());
        }

        $control->setClass($this->buttonSelectClass, false);
        $tbl->setCell($i, $c, $control);
        $tbl->setCellClass($i, $c, 'btn');

        if ( $this->hasDetail )
        {
            $tbl->setCell($i + 1, $c, new MDiv('ddetail' . $this->currentRow, NULL));
            $tbl->setCellClass($i + 1, $c, 'action-default');
        }

        $c++;
        $inicio = $c;
        $countActions = count($this->actions);

        if ( $countActions )
        {
            // generate action links
            while ( $c < ( $countActions + 1 ) )
            {
                $action = $this->actions[$c - 1];
                $tbl->setCell($i, $c, $action->generate(), $action->attributes());

                if ( $this->hasDetail )
                {
                    $tbl->setCell($i + 1, $c, new MDiv('adetail' . $this->currentRow, NULL));
                }

                $action = $c == ($inicio) ? 'action' : 'action-hide';
                $tbl->setCellClass($i, $c, $action);

                //para funcionar corretamente a grid em outros temas
                if ( $action == 'action-hide' )
                {
                    $tbl->setCellAttribute($i, $c, 'style', 'display:none');
                }

                $tmp[] = $tbl->cell[$i][$c]->generate();
                $tbl->cell[$i][$c] = null;
                $c++;
            }
        }

        //monta conforme alinhamento das ações
        if ( $this->actionAlign == 'vertical' )
        {
            $tbl->cell[$i][$inicio] = new MTableRaw('', $tmp, null, 'actionAlignVertical', false);
        }
        else //default horizontal
        {
            $tbl->cell[$i][$inicio] = new Div(null, implode(' ', $tmp ? $tmp : array() ), 'gridActions');
        }

        if ( $this->select != NULL )
        {
            $tbl->setRowAttribute($i, 'id', "row" . $this->name . "[{$this->currentRow}]");
            $tbl->setCellClass($i, $c, 'data select');
            $select = $this->select->generate();
            $select->checked = (array_search($i, $this->selecteds) !== false);
            $tbl->cell[$i][$c] = $select;
            if ( $this->hasDetail )
            {
                $tbl->setCell($i + 1, $c, new MDiv('sdetail' . $this->currentRow, NULL));
            }
        }
    }

    public function generateColumnsHeading(&$tbl)
    {
        $spanClass = ''; // adjusted via javascript
        $p = 0;
        //$this->page->onLoad("miolo.grid.ajustSelect('linkbtn');");
        //$this->page->onLoad("miolo.grid.ajustTHead();");
        $tbl->setColGroup($p);
        $span = new MSpan('', '&nbsp;', $this->buttonSelectClass);
        $tbl->setHead($p, $span);
        $tbl->setHeadClass($p++, 'btn');
        if ( $n = count($this->actions) )
        {
            $tbl->setColGroup($p, "span={$n}");
            $tbl->setHead($p, new MSpan('', _M('Ação'), $spanClass));
            //$tbl->setHeadAttribute($p,'colspan',$n); //TODO
            $tbl->setHeadClass($p++, 'action');
        }
        if ( $this->select != NULL )
        {
            $rowCount = count($this->data);
            $this->page->onLoad("miolo.grid.checkEachRow($rowCount,'" . $this->name . "');");
            $tbl->setColGroup($p);
            $check = new MCheckBox("chkAll", 'chkAction', '');
            $check->addAttribute('onclick', "javascript:miolo.grid.checkAll(this,$rowCount,'" . $this->name . "');");
            $check->_addStyle('padding', '0px');
            $tbl->setHead($p, new MSpan('', $check, 'select'));
            $tbl->setHeadClass($p++, 'select');
        }

        // generate column headings
        $tbl->setColGroup($p);
        $c = 0;
        $last = count($this->columns) - 1;
        foreach ( $this->columns as $k => $col )
        {
            if ( (!$col->visible) || (!$col->title) )
            {
                continue;
            }

            if ( $col->order )
            {
                $this->orderby = $k;
                $this->orderMask = $col->orderMask;
                $link = new MLinkButton('', $col->title, $this->getURL($this->filtered, true, false, $col->orderMask));
                $link->setClass('order');
                $colTitle = new MSpan('', $link, $spanClass);
                $tbl->setHeadClass($p + $c, 'order');
            }
            else
            {
                $colTitle = new MSpan('', $col->title, $spanClass);
                $tbl->setHeadClass($p + $k, 'data');
            }

            if ( ($col->width ) )
            {
                $attr = ($k != $last) ? " width=\"$col->width\"" : " width=\"100%\"";
            }
            else
            {
                // scrollable tables need col width
                if ( $this->scrollable )
                {
                    $this->manager->logMessage(_M("[WARNING] Using scrollable table, it's necessary to inform column width. "));
                }
            }

            //$tbl->setColGroupCol($p,$c,$attr);
            $tbl->setHead($p + $c++, $colTitle);
        }
    }

    /**
     * Gera a grid e o csv dela , se quiser que nao gere o CSV passe o comma como null
     *
     * @param String $comma
     * @return String
     */
    function generate()
    {
        if ( $this->workflowId )
        {
            $argsW['tableId'] = "%{$this->workflowTableIdColumn}%";
            $argsW['workflowId'] = $this->workflowId;
            $argsW['tableName'] = $this->transaction;

            $this->worflowAction = $this->addActionIcon(_M('Histórico do Workflow'), GUtil::getImageTheme('workflow-16x16.png'), GUtil::getAjax('workflowHistory', $argsW));
        }

        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $csv = "";
        $columns = $this->columns;
        $data = $this->data;
        $comma = $this->comma;
        $other = $comma == ',' ? ';' : ','; //troca ',' por ';' caso contenha vírgula no texto
        $event = GForm::getEvent();

        #caso a grid não possua um nome, define um nome padrão, em função dos nomes dos arquivos
        if ( !$this->name )
        {
            $this->name = 'grid';
        }

        $footer = is_array($this->footer) ? $this->footer : array( $this->footer );

        if ( is_array($data) && ($comma) && $this->useCSV )
        {
            $colTitles = array( );
            $array = array( );

            //Prepara os dados
            if ( is_array($columns) )
            {
                foreach ( $columns as $line => $column )
                {
                    $title = null;

                    if ( $column->visible )
                    {
                        $colTitles[] = new GString($column->title);
                    }
                }
            }

            foreach ( $data as $line => $info )
            {
                if ( !is_array($info) )
                {
                    continue;
                }
                foreach ( $info as $l => $i )
                {
                    $column = $columns[$l];
                    $tempData = null;

                    if ( $column->visible )
                    {
                        if ( is_object($i) || is_array($i) )
                        {
                            $i = print_r($i, 1);
                        }

                        $array[$line][] = $i;
                    }
                }

                //caso exista o método reportLine E for geraçao de PDF ou CSV, passa as informações para ele, para que as trate
                if ( method_exists($this, 'reportLine') && ( $event == 'generateGridCSV' || $event == 'generateGridPdf' ) )
                {
                    $array[$line] = $this->reportLine($array[$line]);
                }
            }

            if ( $event == 'generateGridCSV' )
            {
                if ( count($colTitles) > 0 && $this->printCSVTitleLine )
                {
                    $csv .= implode($comma, $colTitles) . " \n";
                }
                
                foreach ( $array as $lineContent )
                {
                    $csv .= implode($comma, $lineContent) . " \n";
                }
                
                $name = $this->name;
                
                // Caso esteja na pesquisa/ minha biblioteca, concatena o código do usuário se estiver autenticado.
                if ( stripos(MIOLO::_REQUEST('action'), 'main:search') !== false )
                {
                    $userLoggedId = BusinessGnuteca3BusAuthenticate::getUserCode();

                    if ( $userLoggedId )
                    {
                        $name .= '_' . $userLoggedId;
                    }
                }                
                
                //Disponibiliza download
                BusinessGnuteca3BusFile::openDownload('grid', $name . '.csv', $csv);
            }
            else if ( $event == 'generateGridPdf' )
            {
                //Instancia e gera PDF
                $MIOLO->uses('classes/GPDFTable.class.php', $module);
                $pdf = new GPDFTable('L', 'pt');
                $pdf->addTable(new MTableRaw(null, $array, $colTitles));

                $name = $this->name;
                
                // Caso esteja na pesquisa/ minha biblioteca, concatena o código do usuário se estiver autenticado.
                if ( stripos(MIOLO::_REQUEST('action'), 'main:search') !== false )
                {
                    $userLoggedId = BusinessGnuteca3BusAuthenticate::getUserCode();

                    if ( $userLoggedId )
                    {
                        $name .= '_' . $userLoggedId;
                    }
                }
                
                //Disponibiliza o download
                BusinessGnuteca3BusFile::openDownload('grid', $name . '.pdf', $pdf->Output(null, 'S'));
            }

            // Só mostra Obter PDF e CSV caso tenha dados.
            if ( count($this->data) )
            {
                //Generate CSV button
                $url = GUtil::getAjax('generateGridCSV');
                $footer[] = $btnCsv = new MButton('getCsv', ' ', $url, GUtil::getImageTheme('csv-16x16.png'));
                $btnCsv->addAttribute('alt', _M('Obter CSV', $module));
                $btnCsv->addAttribute('title', _M('Obter CSV', $module));

                //Generate PDF button
                $url = GUtil::getAjax('generateGridPdf');
                $footer[] = $btnPdf = new MButton('getPdf', ' ', $url, GUtil::getImageTheme('document-16x16.png'));
                $btnPdf->addAttribute('alt', _M('Obter PDF', $module));
                $btnPdf->addAttribute('title', _M('Obter PDF', $module));
            }

            //guarda a página que a grid esta atualmente para consulta posterior
            $footer[] = new MHiddenField('gridPage', MIOLO::_REQUEST('pn_page'));
            $footer[] = new MHiddenField('orderby', MIOLO::_REQUEST('orderby'));
        }

        //coloca campo de contador personalizado
        if ( $this->count )
        {
            $count = $this->count ? $this->count : MIOLO::_REQUEST('gridCount');
            $footer[] = new MHiddenField('gridCount', $count);
        }

        $this->setFooter($footer);

        $generate = parent::generate();
        return $generate;
    }

    public function getMaterialPermission()
    {
        if ( !isset($this->materialPermission) )
        {
            $this->materialPermission = ( GPerms::checkAccess('gtcMaterial', 'insert', false) && GOperator::isLogged() );
        }
        return $this->materialPermission;
    }

    public function getURL($filter = false, $order = false, $item = '')
    {
        $params['__filter'] = ($filter) ? '1' : '0';
        $params['orderby'] = $this->orderby;
        $params['orderMask'] = $this->orderMask;
        $params['order'] = 'ASC';

        $pn_page = MIOLO::_REQUEST('pn_page');

        if ( (is_numeric($pn_page)) || (!$pn_page) )
        {
            $params['pn_page'] = $pn_page;
        }
        else
        {
            $dec = GUtil::decodeJsArgs($pn_page);
            $params['pn_page'] = $dec->pn_page;
        }

        return GUtil::getAjax('searchFunction', $params);
    }

    /**
     * Retorna chave primária da grid
     *
     * @return array $primaryKey
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Define chave primária da grid
     * @param array $primaryKey
     */
    public function setPrimaryKey(array $primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }
}

?>