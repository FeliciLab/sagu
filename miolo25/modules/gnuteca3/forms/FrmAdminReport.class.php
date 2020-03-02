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
 * Class created on 29/07/2008
 *
 * */
$MIOLO->GetClass('gnuteca3', 'codabar');

class FrmAdminReport extends GForm
{
    public $business;
    public $reportData;

    const INTERVAL_CONTINUOUS = 1;
    const INTERVAL_DISCRETE = 2;
    const OPTION_CONTROL_NUMBER = 1;
    const OPTION_ITEM_NUMBER = 2;
    
    CONST TOTAL_SELECIONE = 0;
    const TOTAL_CONTAGEM = 1;
    const TOTAL_SOMA = 2;

    public function __construct()
    {
        global $navbar;
        $this->MIOLO = $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $this->business = $MIOLO->getBusiness($module, 'BusReport');

        $this->setTransaction('gtcAdminReport');
        $this->reportData = $this->getReportData();
        parent::__construct();
        $this->setTitle(_M('Relatório', $module) . ' - ' . $this->reportData->Title);
    }

    /**
     * Return all Data of current Report
     *
     * @return object the report data
     */
    public function getReportData()
    {
        $data = $this->business->getReport($this->getReportId());
        return $data;
    }

    /**
     * Return the selected reportId
     *
     * @return the selected reportId
     */
    public function getReportId()
    {
        return MIOLO::_REQUEST('reportId');
    }

    /**
     * Create the Fields of the Form
     *
     */
    public function createFields()
    {
        $GFunction = new GFunction();
        $GFunction->SetExecuteFunctions(true);
        $data = $this->getReportData();

        //Descrição do relatório a ser mostrada no topo da tela
        if ( $data->description )
        {
            $fields[] = new MDiv('divDescription', $data->description, 'reportDescription');
        }

        if ( $data->parameters )
        {
            foreach ( $data->parameters as $line => $info )
            {
                $value = $GFunction->interpret($info->defaultValue);
                $identifier = $GFunction->interpret($info->identifier);
                $label = $GFunction->interpret($info->label);
                $lastValue = $GFunction->interpret($info->lastValue);

                $value = ( $lastValue ) ? $lastValue : $value;
                $value = $value ? $value : MIOLO::_REQUEST($identifier);

                if ( $info->type == 'string' )
                {
                    $fields[] = new MTextField($identifier, $value, $label, FIELD_DESCRIPTION_SIZE);
                }
                else if ( $info->type == 'int' )
                {
                    $fields[] = new MTextField($identifier, $value, $label, FIELD_DESCRIPTION_SIZE);
                    $valids[] = new MIntegerValidator($identifier);
                }
                else if ( $info->type == 'date' )
                {
                    $fields[] = new MCalendarField($identifier, $value, $label);
                    $valids[] = new MDATEDMYValidator($identifier);
                }
                else if ( $info->type == 'select' )
                {
                    $options = $GFunction->interpret($info->options);

                    if ( !is_array($options) ) //se sobrou string tentar fazer parser
                    {
                        $optionsTemp = explode("\n", $options);

                        unset($options);

                        if ( $optionsTemp )
                        {
                            foreach ( $optionsTemp as $l => $i )
                            {
                                $temp = explode(' ', $i);
                                $options[$temp[0]] = $temp[1];
                            }
                        }
                    }

                    $fields[] = new GSelection($identifier, $value, $label, $options);
                }
                else if ( $info->type == 'itemNumber' )
                {
                    $options = array(
                        array( _M('Contínuo', $this->module), self::INTERVAL_CONTINUOUS ),
                        array( _M('Discreto', $this->module), self::INTERVAL_DISCRETE ),
                    );

                    $controls[] = $interval = new GRadioButtonGroup($identifier, _M('Intervalo', $this->module), $options, self::INTERVAL_CONTINUOUS, null, 'vertical');
                    $form = $this->manager->page->getFormId();
                    $jsOnchangeInterval = "miolo.doAjax( (dojo.byId('{$identifier}_0').checked ? 'getContinuousFields' : 'getDiscreteFields') ,'','{$form}');";
                    $interval->addAttribute('onchange', $jsOnchangeInterval);

                    //se primeiro acesso recarregado campo de intervalo 
                    if ( $this->primeiroAcessoAoForm() )
                    {
                        $this->page->onload($jsOnchangeInterval);
                        //caso for primeiro acesso ao form limpa o repetitive
                        GRepetitiveField::clearData('codes');
                    }

                    $options = array(
                        array( _M('Número do exemplar', $this->module), self::OPTION_ITEM_NUMBER ),
                        array( _M('Número de controle', $this->module), self::OPTION_CONTROL_NUMBER ),
                    );

                    $controls[] = new GRadioButtonGroup('exemplarys', _M('Exemplares', $this->module), $options, self::OPTION_ITEM_NUMBER, null, 'vertical');
                    $controls[] = new MDiv('divInterval', $this->getContinuousFields(TRUE));

                    $fields[] = new MBaseGroup('', $label, $controls);
                    $fields[] = new MSeparator('</br>');
                }

                $valids[] = new MRequiredValidator($identifier);
            }
        }

        $this->forceFormContent = true;
        $this->setFields($fields);
        $this->setValidators($valids);

        $this->_toolBar->disableButtons(array( MToolBar::BUTTON_NEW, MToolBar::BUTTON_SEARCH ));

        //ler dados do formulário
        $form = 'frmadminreport' . MIOLO::_REQUEST('reportId');
        $this->className = $form;

        $this->busFormContent->loadFormValues($this); //forma padrão

        $formContent = $this->busFormContent->loadFormValues($this, true); //obter coluna total
    }

    /**
     * Salva dados do formulário
     * @param stdClass
     */
    public function tbBtnFormContent_click($args)
    {
        $data = $this->getData();

        $form = 'frmadminreport' . MIOLO::_REQUEST('reportId');

        if ( $this->busFormContent->saveFormValues($form, $data) )
        {
            $this->information(_M('Configurações salvas', $this->module), GUtil::getCloseAction(true));
        }
        else
        {
            $this->error(_M('Error saving settings', $this->module), GUtil::getCloseAction(true));
        }
    }

    /**
     * Obtem os fields para modo contínuo
     *
     * @param unknown_type $return
     * @return unknown
     */
    public function getContinuousFields($return = FALSE)
    {
        $lbl = new MLabel(_M('Código inicial', $this->module));
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $flds[] = $lbl;
        $flds[] = new MTextField('beginCode', null, null, FIELD_ID_SIZE);

        $flds[] = new MSeparator();
        $lbl = new MLabel(_M('Código final', $this->module));
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $flds[] = $lbl;
        $flds[] = new MTextField('endCode', null, null, FIELD_ID_SIZE);

        $hct = new MDiv('hctContinuous', $flds);

        if ( $return )
        {
            return $hct;
        }

        $this->setResponse($hct, 'divInterval');
        $this->setFocus('beginCode');
    }

    /**
     * Obtem os fields para modo discreto
     *
     */
    public function getDiscreteFields()
    {
        $flds[] = new MTextField('itemNumber_', null, _M('Número do exemplar', $this->module), FIELD_ID_SIZE);
        $cols[] = new MGridColumn(_M('Número do exemplar', $this->module), MGrid::ALIGN_LEFT, true, null, true, 'itemNumber_');
        $valids[] = new GnutecaUniqueValidator('itemNumber_', _M('Número do exemplar', $this->module), 'required');
        $interval = new GRepetitiveField('codes', _M('Itens', $this->module), $cols, $flds);
        $interval->setValidators($valids);
        $fields[] = $interval;

        $this->setResponse($fields, 'divInterval');
        $this->setFocus('itemNumber');
    }

    /**
     * Set the fields adding needed fields to report system
     *
     * @param array $fields the fields array
     */
    public function setFields($fields)
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3', 'BusFile');

        $typeOpt['list'] = _M('Lista', $this->module);
        $typeOpt['pdf'] = _M('PDF', $this->module);
        $typeOpt['csv'] = _M('CSV', $this->module);

        if ( $busFile->fileExists('odt', BusinessGnuteca3BusFile::getValidFilename($this->reportData->reportId) . '.') )
        {
            $typeOpt['odt'] = _M('Modelo ODT', $this->module);
        }

        $reportType = new GSelection('reportType', MIOLO::_REQUEST('reportType') ? MIOLO::_REQUEST('reportType') : 'list', _M('Tipo', $this->module), $typeOpt);
        $reportType->addAttribute('onchange', "
    	   var sel  = dojo.byId('reportType').selectedIndex;
    	   var type = dojo.byId('reportType').options[ sel ].value;
    	   dojo.byId('divPageOrientation').style.display = (type == 'pdf') ? 'block' : 'none';
    	");
        $fields[] = $reportType;

        //ista linha é para funcionar o formcontent
        $this->page->onload("dojo.byId('reportType').onchange();");

        $formats = array(
            'P' => _M('Retrato', $this->module),
            'L' => _M('Paisagem', $this->module),
        );

        $lbl = new MLabel(_M('Formato da página') . ':');
        $lbl->setWidth(FIELD_LABEL_SIZE);
        $pageOrientation = new GSelection('pageOrientation', MIOLO::_REQUEST('pageOrientation'), null, $formats, null, null, null, true);
        $fields[] = $hctPageOrientation = new GContainer('divPageOrientation', array( $lbl, $pageOrientation ));

        $fields[] = $total = new GSelection('total', self::TOTAL_SELECIONE, _M('Total'), array( self::TOTAL_SELECIONE => _M('Selecione'), self::TOTAL_CONTAGEM => _M('Contagem'), self::TOTAL_SOMA => _M('Soma') ));
        $total->addAttribute('onchange', "
    	   dojo.byId('totalColumn').parentNode.parentNode.style.display = this.value == '" . self::TOTAL_SOMA . "' ? 'block' : 'none';
    	");
        $valids[] = new MRequiredValidator('total');

        $this->page->onload("
            var show = dojo.byId('total').value == '" . self::TOTAL_SOMA . "';
            if ( !show )
            {
                dojo.byId('totalColumn').parentNode.parentNode.style.display = 'none';
            }
        ");

        $valids[] = new MRequiredValidator('reportType');
        $columns = $this->getSqlColumns($this->reportData->reportSql, $this->reportData->reportSubSql);
        $fields[] = new GSelection('totalColumn', null, _M('Coluna', $this->module), $columns, null, null, null, true);
        $fields[] = new MButton('btnSearch', _M('Gerar', $this->module), GUtil::getAjax('searchFunction'), GUtil::getImageTheme('document-16x16.png'));
        $fields[] = new MDiv(self::DIV_SEARCH);

        parent::setFields($fields);
        parent::setValidators($valids);

        //dispara a pesquisa
        if ( $this->primeiroAcessoAoForm() && MIOLO::_REQUEST('doSearch') )
        {
            $this->page->onload(GUtil::getAjax('searchFunction'));
        }
    }

    public function searchFunction($args)
    {
        $data = $this->reportData;
        $reportType = MIOLO::_REQUEST('reportType');
        $beginCode = $args->beginCode;
        $endCode = $args->endCode;
        $exemplarys = $args->exemplarys;

        //localiza um parametro com tipo itemNumber
        if ( $data->parameters )
        {
            foreach ( $data->parameters as $line => $param )
            {
                if ( $param->type == 'itemNumber' )
                {
                    $intervalName = $param->identifier;
                }
            }
        }

        //trata os dados caso exista um campo do tipo itemNumber
        if ( $intervalName )
        {
            $interval = $args->$intervalName;
            $busExemplaryControl = $this->manager->getBusiness('gnuteca3', 'BusExemplaryControl');

            if ( ( $interval == self::INTERVAL_CONTINUOUS ) && ( $exemplarys == self::OPTION_ITEM_NUMBER ) )
            {
                $lengthFirst = strlen(trim($beginCode));

                for ( $x = $beginCode; $x <= $endCode; $x++ )
                {
                    //completa os 0000 na frente
                    $itemNumber = GUtil::strPad($x, $lengthFirst, '0', STR_PAD_LEFT);
                    //$codes[$x] = $busExemplaryControl->getExemplaryControl( $itemNumber );
                    $codes[$itemNumber] = $itemNumber;
                }
            }
            else if ( ($interval == self::INTERVAL_CONTINUOUS) && ($exemplarys == self::OPTION_CONTROL_NUMBER) )
            {
                $lengthFirst = strlen(trim($beginCode));

                for ( $x = $beginCode; $x <= $endCode; $x++ )
                {
                    //completa os 0000 na frente
                    $itemNumber = GUtil::strPad($x, $lengthFirst, '0', STR_PAD_LEFT);
                    $exemplary = $busExemplaryControl->getExemplaryOfMaterial($itemNumber);

                    if ( $exemplary )
                    {
                        foreach ( $exemplary as $ex )
                        {
                            $codes[$ex->itemNumber] = $ex->itemNumber;
                        }
                    }
                }
            }
            else if ( ($interval == self::INTERVAL_DISCRETE) && ($exemplarys == self::OPTION_ITEM_NUMBER) )
            {
                $codeList = GRepetitiveField::getData('codes');
                $codes = null;

                if ( $codeList )
                {
                    foreach ( $codeList as $key => $c )
                    {
                        //Não adicionar exemplares excluídos
                        if ( !$c->removeData )
                        {
                            //$codes[$key] = $busExemplaryControl->getExemplaryControl($c->itemNumber_);
                            $codes[$c->itemNumber_] = $c->itemNumber_;
                        }
                    }
                }
            }
            else if ( ($interval == self::INTERVAL_DISCRETE) && ($exemplarys == self::OPTION_CONTROL_NUMBER) )
            {
                $codeList = GRepetitiveField::getData('codes');
                $codes = null;

                if ( $codeList )
                {
                    foreach ( $codeList as $c )
                    {
                        //Não adicionar números de controle excluídos
                        if ( !$c->removeData )
                        {
                            $exemplary = $busExemplaryControl->getExemplaryOfMaterial($c->itemNumber_);

                            if ( $exemplary )
                            {
                                foreach ( $exemplary as $ex )
                                {
                                    $codes[$ex->itemNumber] = $ex->itemNumber;
                                }
                            }
                        }
                    }
                }
            }

            //colca no post e request para tudo funcionar corretamente.
            $itemNumber = "'" . implode("','", $codes) . "'";
            $_POST[$intervalName] = $itemNumber;
            $_REQUEST[$intervalName] = $itemNumber;
        }

        if ( $reportType == 'list' || !$reportType )
        {
            $fields[] = $this->getGrid();
        }
        else if ( $reportType == 'csv' )
        {
            $csv = $this->getCSV(';');

            if ( $csv )
            {
                BusinessGnuteca3BusFile::openDownload('report', "{$data->Title}.csv", $csv);
            }
            else
            {
                $this->information(_M('O relatório não retornou dados'));
                return;
            }
        }
        else if ( $reportType == 'pdf' )
        {
            $pdf = $this->getPDF();

            if ( $pdf )
            {
                BusinessGnuteca3BusFile::openDownload('report', "{$data->Title}.pdf", $pdf);
            }
            else
            {
                $this->information(_M('O relatório não retornou dados'));
                return;
            }
        }
        else if ( $reportType == 'odt' )
        {

            try
            {
                $filename = $this->getOdt();
            }
            catch ( Exception $exc )
            {
                GForm::error($exc->getMessage());
            }

            if ( $filename )
            {
                BusinessGnuteca3BusFile::openDownload('report', $filename);
            }
        }

        $this->setResponse($fields, self::DIV_SEARCH);
    }

    /**
     * Adiciona o total ao array de dados, caso for necessário
     *
     * @param array $result os dados do relatório
     * @param array $columns array de colunas do relatório
     * @return array dados com adição de total
     */
    public function addTotal($result, $columns)
    {
        $total = MIOLO::_REQUEST('total') == self::TOTAL_SOMA ? true : false;
        $totalColumn = MIOLO::_REQUEST('totalColumn'); //para o usuário usar 1 ou maior

        if ( is_array($result) && $total && $totalColumn >= 0 )
        {
            //coloca a contagem de acordo com array considerando 0 como primeiro
            $collumCount = count($result[0]) - 1;

            if ( $collumCount <= $totalColumn )
            {
                foreach ( $result as $line => $info )
                {
                    $totalCount += $info[$totalColumn];
                }
            }

            $totalLine[] = _M('Total geral da coluna', $this->module) . ' ' . $columns[$totalColumn];

            //variável utilizada para alinhamento perfeito do total
            $extrasCol = count($result[0]) - 2;

            if ( $extrasCol > 0 )
            {
                for ( $i = 0; $i < $extrasCol; $i++ )
                {
                    $totalLine[] = '';
                }
            }

            //adiciona o total na última linha
            $totalLine[] = $totalCount;

            $result[] = $totalLine;
        }

        return $result;
    }

    public function getSqlColumns($sql, $subSql = null)
    {
        if ( $sql )
        {
            $columns = $this->business->parseSqlToColumnsArray($sql);

            if ( MIOLO::_REQUEST('reportType') != 'detail' )
            {
                if ( $subSql )
                {
                    $columns = array_merge($columns, $this->business->parseSqlToColumnsArray($subSql));
                }
            }
        }

        return $columns;
    }

    /**
     * Returns the grid ready to use
     *
     * @return object the grid object
     */
    public function getGrid()
    {
        $args = $this->getData();

        $errors = $this->validate($args);

        if ( !$errors )
        {
            return false;
        }

        $this->MIOLO->getClass('gnuteca3', 'GReport');
        $gReport = new GReport();
        $totalColumn = (MIOLO::_REQUEST('total') == self::TOTAL_SOMA) ? MIOLO::_REQUEST('totalColumn') : null;

        $grid = $gReport->executeReport($this->getReportId(), $args, GReport::REPORT_TYPE_GRID, $totalColumn);

        if ( MIOLO::_REQUEST('reportType') == 'detail' )
        {
            $gridArgs['0'] = '%0%';
            $gridArgs['event'] = 'showDetail';
            $hrefDetail = $this->MIOLO->getActionURL($this->module, $this->action, null, $gridArgs);
            $grid->addActionIcon(_M('Detalhes', $this->module), 'select', $hrefDetail);
        }

        return $grid;
    }

    public function getOdt()
    {
        try
        {
            $args = $this->getData();
            $this->MIOLO->getClass('gnuteca3', 'GReport');
            $gReport = new GReport();
            $totalColumn = (MIOLO::_REQUEST('total') == self::TOTAL_SOMA) ? MIOLO::_REQUEST('totalColumn') : null;
            $fileName = $gReport->executeReport($this->getReportId(), $args, GReport::REPORT_TYPE_ODT, $totalColumn);

            return $fileName;
        }
        catch ( Exception $exc )
        {
            echo Gform::error($exc->getMessage());
            return false;
        }
    }

    public function getCSV()
    {
        $args = $this->getData();
        $this->MIOLO->getClass('gnuteca3', 'GReport');
        $gReport = new GReport();
        $totalColumn = (MIOLO::_REQUEST('total') == self::TOTAL_SOMA) ? MIOLO::_REQUEST('totalColumn') : null;
        $csv = $gReport->executeReport($this->getReportId(), $args, GReport::REPORT_TYPE_CSV, $totalColumn);

        return $csv;
    }

    public function getPDF()
    {
        $args = $this->getData();
        $this->MIOLO->getClass('gnuteca3', 'GReport');
        $gReport = new GReport();
        $totalColumn = (MIOLO::_REQUEST('total') == self::TOTAL_SOMA) ? MIOLO::_REQUEST('totalColumn') : null;
        $gReport->executeReport($this->getReportId(), $args, null, $totalColumn);
        //Gera o relatorio no formado PDF e com a orientaçao da pagina
        $output = $gReport->generateReportAs(GReport::REPORT_TYPE_PDF, MIOLO::_REQUEST('pageOrientation'));
        return $output;
    }

    public function getData()
    {
        $request = (object) $_REQUEST;

        //Prepara os argumentos para não ter problema com falta de literals
        foreach ( $request as $argument => $value )
        {
            //FIXME itemNumber é gerado pelo sistema e é estragado pelo addslashes sistema. isto foi feito para resolver #15430
            if ( $argument != 'itemNumber' )
            {
                $request->$argument = addslashes($value);
            }
        }

        return $request;
    }

    /**
     * Retorna modo de busca para evitar mensagem de campos modificados
     * 
     * @return string 'search'
     */
    public function getFormMode()
    {
        return 'search';
    }
}

?>