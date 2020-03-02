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
 * Class created on 17/11/2008
 *
 **/
class FrmUnitTest extends GForm
{
    public function __construct()
    {
        $this->setTransaction('gtcPreference');
        parent::__construct(_M('Teste unitários', MIOLO::getCurrentModule()));
    }

    /**
     * Lista todos testes unitários
     * @return array
     */
    public function getTestList()
    {
        $path  = $this->MIOLO->getConf('home.miolo').'/modules/gnuteca3/unittest';
        $list  = glob("$path/*.php");

        if ( is_array( $list ))
        {
            foreach ( $list as $line => $file )
            {
                $test = str_replace( "Test",'', str_replace( ".class.php",'', basename($file) ) ) ;
                $tList[$test] = $test;
            }
        }

        return $tList;
    }

    public function mainFields()
    {
        $list   = $this->getTestList();
        $list[] = array('',_M('Todos',$this->module));

        $imgCheck   = GUtil::getImageTheme('accept.png');
        $fields[]   = new Mdiv( null, _M('Aplica uma série de testes ao sistema', $this->module).' :', 'reportDescription') ;
        $fields[]   = new MSeparator('<br/>');
        $fields[]   = new GSelection('testName', null, _M('Selecione o teste a executar', $module ),$list ,null, null, null, true);
        $fields[]   = new MButton('checkAll' , _M('Testar', $this->module),GUtil::getAjax('checkAll') , $imgCheck);

        if ( is_array( $dList))
        {
            foreach($dList as $line => $info )
            {
                $table[$line][0] = $info[1];
                $table[$line][1] = new MDiv('div'.$info[0] , _M('Por favor aguarde...', $this->module) );
            }
        }
        
        $columnTitle1 = _M('Teste', $this->module);
        $columnTitle2 = _M('Resultado', $this->module);
        $table = new MTableRaw(_M('Lista de testes', $this->module), $table, array($columnTitle1,$columnTitle2), $name);
        $table->addAttribute('width','100%');
        $table->setAlternate(true);

        $fields[]   = new MSeparator('<br/>');
        $fields[]   = new MDiv('checkAllDiv', $table->generate() );

        $this->setFields($fields);
    }

    /**
     * Executa os testes
     * @param stdClass $args
     */
    public function checkAll($args)
    {
        $dList = array_values( $this->getTestList() );
        $testName = $args->testName;

        if ( $testName )
        {
            unset($dList);
            $dList[] = $testName;
        }

        if ( is_array( $dList))
        {
            foreach( $dList as $line => $info )
            {
                $table[$line][0] = $div = new MDiv('',$info);
                $div->addStyle('width','400px');
                $table[$line][1] = new MDiv('div'.$info , _M('Por favor aguarde...', $this->module) );
                
                $js .= 'javascript:'.GUtil::getAjax('doCheck',array('test' => $info) ) .";\n";
            }
        }

        $this->page->onload($js);

        $columnTitle1 = _M('Teste', $this->module);
        $columnTitle2 = _M('Resultado', $this->module);
        $fields[] = $table = new MTableRaw(_M('Lista de testes', $this->module), $table, array($columnTitle1,$columnTitle2), $name);

        $table->addAttribute('width','100%');
        $table->setAlternate(true);

        $this->setResponse($fields, 'checkAllDiv');
    }

    /**
     * Executa cada um dos testes
     */
    public function doCheck()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass( 'gnuteca3' ,'controls/GTree' );

        $testName   = MIOLO::_REQUEST('test');
        $testFile   = 'Test'.$testName.'.class.php';

        try
        {
            $unitTest   = GUtil::executeUnitTest( $testFile );

            $result     = $unitTest[0];
            $message    = $unitTest[1];
            $message    = explode("\n",$message);

            unset($message[0]); //tira o cabecalho do php unit
        }
        catch ( Exception $e )
        {
            $message[] = _M('Erro ao executar teste unitário','gnuteca3');
            $message[] = $e->getMessage();
        }

        $lastLine = $message[count($message)];

        $imgCheck    = GUtil::getImageTheme('accept.png');
        $imgCheck    = new MImage('imgCheck', null, $imgCheck);
        $imgError    = GUtil::getImageTheme('delete-16x16.png');
        $imgError    = new MImage('imgError', null, $imgError);

        $lastLine = $result ? $imgCheck->generate() . $lastLine: $imgError->generate() . $lastLine;

        unset( $message[count($message)] );

        $treeData[0]->content = implode("<br/>", $message);;
        $treeData[0]->title = $lastLine;
        
        $message = new GTree('tree'.$testName , $treeData);
        $message->setClosed(true);
    
        $this->setResponse( $message->generate(), 'div'.$testName);
    }
}
?>
