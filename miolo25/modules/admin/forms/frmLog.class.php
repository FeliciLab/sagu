<?php

class frmLog extends MForm
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        parent::__construct( _M('Log', $module) );
        $this->setIcon( $MIOLO->getUI()->getImage('admin','log-16x16.png') );
        if (($f = $this->page->request('cpaint_function')) != "") 
        {
            $this->manager->getTheme()->clearContent();
            $this->$f($this->page->request('cpaint_argument'));
            $this->page->generateMethod = 'generateAJAX';
        }
        else
        {
            $this->eventHandler();
        }
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();
        $module = MIOLO::getCurrentModule();

        if( $this->getFormValue('startDate') )
        {
            $startDate = $this->getFormValue('startDate');
            $endDate   = $this->getFormValue('endDate');
        }
        else
        {
            $startDate = $endDate = date('d/m/Y');
        }

        $db = $MIOLO->getBusiness($module, 'module');
        $rsModules = $db->listAll()->result;
        if( ! empty($rsModules) )
        {
            foreach($rsModules as $rsModule)
            {
                $modules[$rsModule[0]] = $rsModule[0];
            }
        }
        $db = $MIOLO->getBusiness($module, 'user');
        $rsUsers = $db->listAll()->result;
        if( ! empty($rsUsers) )
        {
            foreach($rsUsers as $rsUser)
            {
                $users[$rsUser[1]] = $rsUser[1];
            }
        }

        $contS1[]  = new MTextLabel('tlSQLType', null, _M('SQL Type', $module).':');
        $contS1[]  = new MSelection('SQLType', null, null, array('SELECT'=>'SELECT', 'INSERT'=>'INSERT', 'UPDATE'=>'UPDATE', 'DELETE'=>'DELETE'));
//        $contS1[]  = new MMultiLineField('SQLType', null, null, array('SELECT'=>'SELECT', 'INSERT'=>'INSERT', 'UPDATE'=>'UPDATE', 'DELETE'=>'DELETE'));
        $contEsq[] = new MHContainer('contS1', $contS1);
        $contS2[]  = new MTextLabel('tlModule', null, _M('Module', $module).':');
        $contS2[]  = new MSelection('mod',     null, null,  $modules);
        $contEsq[] = new MHContainer('contS2', $contS2);
        $contS3[]  = new MTextLabel('tlUser', null, _M('User', $module).':');
        $contS3[]  = new MSelection('user',     null, null,  $users);
        $contEsq[] = new MHContainer('contS3', $contS3);
        $cont1[]   = new MTextLabel('tlStartDate', null, _M('Start Date', $module).':');
        $cont1[]   = new MCalendarField('startDate', $startDate);
        $contEsq[] = new MHContainer('cont1', $cont1);
        $cont2[]   = new MTextLabel('tlEndDate', null, _M('End Date', $module).':');
        $cont2[]   = new MCalendarField('endDate',   $endDate);
        $contEsq[] = new MHContainer('cont2', $cont2);
        $btnSearch = new MButton('btnConsultar', 'Consultar');
        $contBtn[] = $btnSearch;
        $contBtn[] = new MButton('btnExportar',  'Exportar CSV');
        $contEsq[] = new MHContainer('contBtn', $contBtn);
        $contEsq   = new MVContainer('contEsq', $contEsq);
        
        $contDir[] = $div = new MDiv('divInfo');
//        $div->style = 'position:absolute';
        $contDir   = new MVContainer('contDir', $contDir);

        $fields[]  = new MHContainer('contH', array($contEsq, $contDir));

        $this->defaultButton = false;
        
        $this->setFields($fields);
        $this->page->setAction( $MIOLO->getActionURL($module, $action, null, array('event'=>'btnConsultar:click')) );
        $this->tlStartDate->width = '100px';
        $this->tlEndDate  ->width = '100px';
        $this->tlSQLType  ->width = '100px';
        $this->tlModule   ->width = '100px';
        $this->tlUser     ->width = '100px';
        $this->setLabelWidth('100');
        $validators = array( new MRequiredValidator('mod'),
                             new MRequiredValidator('startDate'),
                             new MRequiredValidator('endDate') );
        $this->setValidators($validators);

        $this->page->addScript('x/x_core.js');
        $this->page->addScript('cpaint/cpaint.inc.js');
        $code = "
    xGetElementById('divInfo').style.position = 'absolute';
    xGetElementById('divInfo').style.left = '285px';
    xGetElementById('divInfo').style.color = 'gray';
public function doInfo(pointer, mod)
{
    xGetElementById('divInfo').innerHTML = 'Loading...<img src=\"images/loading.gif\"/>';
    cpaint_call('".str_replace('&amp;', '&', $MIOLO->getCurrentURL())."&pointer='+pointer+'&mod='+mod, 'POST', 'showFormInfo', null, showInfo, 'TEXT');
}
public function showInfo(result)
{
    xGetElementById('divInfo').innerHTML = result;
    stopShowLoading();
}
";
        $this->page->addJsCode($code); 
    }

    public function showFormInfo()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $formInfo = $MIOLO->getUI()->getForm($module, 'frmLogInfo');
        $formInfo->setWidth('450px');
        $this->manager->getTheme()->setContent($formInfo);
    }

    public function btnConsultar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
       
        set_time_limit(0);
        $MIOLO->uses('classes/logReader.class', $module);
        $ui   = $MIOLO->getUI();
        $grid = $ui->getGrid($module, 'grdLog');
        $tmAntes = time();
        $log = new logReader($this->getFormValue('mod'));
        $data = $log->listByDate($this->startDate->getValue(), $this->endDate->getValue());
        $tmDepois = time();
        $tsDemora = $tmDepois - $tmAntes;
        $demora = date('i:s', $tsDemora);
        $this->addField( new MLabel('Execution time(M:S): '.$demora) );
//        MIOLO::vd($data);
        if( ! empty($data) )
        {
            foreach ($data as $lData)
            {
                $lData[6] = substr($lData[6], 1, 6);
                if(    (!$this->getFormValue('SQLType') or ($lData[6] == strtoupper($this->getFormValue('SQLType'))))
                   and (!$this->getFormValue('user')    or (strtolower($lData[5]) == strtolower($this->getFormValue('user')))) )
                {
                    $gridData[] = $lData;
                }
            }
        }   
        $grid->setData($gridData);
        $this->addField($grid);
    }

    public function btnExportar_click()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
       
        set_time_limit(0);
        $MIOLO->uses('classes/logReader.class', $module);
        $tmAntes = time();
        $log = new logReader($this->getFormValue('mod'));
        $data = $log->listByDate($this->startDate->getValue(), $this->endDate->getValue());
        $tmDepois = time();
        $tsDemora = $tmDepois - $tmAntes;
        $demora = date('i:s', $tsDemora);
        $this->addField( new MLabel('Execution time(M:S): '.$demora) );
//        MIOLO::vd($data);
        if( ! empty($data) )
        {
            foreach ($data as $lData)
            {
//                $lData[6] = substr($lData[6], 1, 6);
                if(    (!$this->getFormValue('SQLType') or ($lData[6] == strtoupper($this->getFormValue('SQLType'))))
                   and (!$this->getFormValue('user')    or (strtolower($lData[5]) == strtolower($this->getFormValue('user')))) )
                {
                    foreach($lData as $sData)
                    {
                        $lStrData .= ';'.$sData;
                    }
                    $strData[] = substr($lStrData, 1, strlen($lStrData));
                }
            }
        }   
        if( ! empty($strData) )
        {
            array_unshift($strData, 'Log point;IP;Date;Time;Module;User;SQL');
            $fileContent = implode(chr(13) . chr(10), $strData);
            $fileContent.= chr(13) . chr(10);
            $this->returnAsFile('log.csv', $fileContent, 'text/txt-file');
        }
        else
        {
            $this->addField( new MLabel(_M('No records found!', $module)) );
        }
    }


    public function returnAsFile($fileName, $buffer, $contentType = 'text/plain')
    {
        if(ob_get_contents())
        {
            $this->error('Some data has already been output, can\'t send file');
        }
        if(php_sapi_name()!='cli')
        {
            header('Content-Type: '.$contentType);
            if(headers_sent())
            {
                $this->error('Some data has already been output to browser, can\'t send file');
            }
            header('Content-Length: '.strlen($buffer));
            header('Content-disposition: inline; filename="'.$fileName.'"');
        }
        echo $buffer;
        header('Content-Type: html');
        return '';
    }

}

?>
