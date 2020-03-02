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
$MIOLO->getClass('gnuteca3', 'GDependencyCheck');

class FrmDependencyCheck extends GForm
{
    public $dependency;

    public function __construct()
    {
        $this->dependency = new GDependencyCheck();
        $this->setTransaction( 'gtcDependencyCheck' );
        parent::__construct( _M('DependencyCheck', MIOLO::getCurrentModule()) );
    }

    public function mainFields()
    {
        $list   = $this->dependency->listDependency();
        $list[] = array('',_M('Tudo',$this->module));

        $imgCheck   = GUtil::getImageTheme('accept.png');
        $fields[]   = new Mdiv( null, _M('Verifica algumas dependências deste aplicativo', $this->module).' :', 'reportDescription') ;
        $fields[]   = new MSeparator('<br/>');
        $fields[]   = new GSelection('methodName', null, _M('Dependência', $module ),$list ,null, null, null, true);
        $fields[]   = new MButton('checkAll' , _M('Conferir', $this->module),GUtil::getAjax('checkAll') , $imgCheck);

        if ( is_array( $dList))
        {
            foreach($dList as $line => $info )
            {
                $table[$line][0] = $info[1];
                $table[$line][1] = new MDiv('div'.$info[0] , _M('Por favor aguarde...', $this->module) );
            }
        }
        
        $columnTitle1 = _M('Dependência', $this->module);
        $columnTitle2 = _M('Resultado', $this->module);
        $table = new MTableRaw(_M('Lista de dependências', $this->module), $table, array($columnTitle1,$columnTitle2), $name);
        $table->addAttribute('width','100%');
        $table->setAlternate(true);

        $fields[]   = new MSeparator('<br/>');
        $fields[]   = new MDiv('checkAllDiv', $table->generate() );

        $this->setFields($fields);
    }

    public function checkAll($args)
    {
        $dList = $this->dependency->listDependency();
        $methodName = $args->methodName;

        if ( $methodName )
        {
            unset($dList);
            $dList[] = array($methodName,$this->dependency->getDependencyLabel($methodName));
        }

        if ( is_array( $dList))
        {
            foreach($dList as $line => $info )
            {
                $table[$line][0] = $info[1];
                $table[$line][1] = new MDiv('div'.$info[0] , _M('Por favor aguarde...', $this->module) );

                $js .= 'javascript:'.GUtil::getAjax('doCheck',array('dependency' => $info[0]) ) .";\n";
            }
        }

        $this->page->onload($js);

        $columnTitle1 = _M('Dependência', $this->module);
        $columnTitle2 = _M('Resultado', $this->module);
        $fields[] = $table = new MTableRaw(_M('Lista de dependências', $this->module), $table, array($columnTitle1,$columnTitle2), $name);
 
        $table->addAttribute('width','100%');
        $table->setAlternate(true);

        $this->setResponse($fields, 'checkAllDiv');
    }

    public function doCheck()
    {
        $methodName = MIOLO::_REQUEST('dependency');

        if ( method_exists( $this->dependency, $methodName ) )
        {
            $result = $this->dependency->$methodName();
        }

        $imgCheck    = GUtil::getImageTheme('accept.png');
        $imgCheck    = new MImage('imgCheck', null, $imgCheck);
        $imgError    = GUtil::getImageTheme('delete-16x16.png');
        $imgError    = new MImage('imgError', null, $imgError);

        $msg = $result ? $imgCheck->generate() : $imgError->generate();

        $message = $msg . $this->dependency->getMessage();

        $this->setResponse( $message, 'div'.$methodName);
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