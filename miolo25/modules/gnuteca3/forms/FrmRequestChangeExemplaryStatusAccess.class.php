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
 * Class Request change exemplary status access Form
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 24/04/09
 *
 **/
class FrmRequestChangeExemplaryStatusAccess extends GForm
{
    public $MIOLO;
    public $module;
    public $busExemplaryStatus,
           $busRequestChangeExemplaryStatusAccess;
    
    private $busBond;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();

        $saveArgs = array('basLinkId','exemplaryStatusId');
        $function   = MIOLO::_REQUEST('function');
        $this->busExemplaryStatus  = $this->MIOLO->getBusiness($this->module, 'BusExemplaryStatus');
        $this->busBond  = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->busRequestChangeExemplaryStatusAccess   = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatusAccess');
        $this->setAllFunctions('RequestChangeExemplaryStatusAccess', $saveArgs, $saveArgs, $saveArgs);
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('exemplaryStatus');
        }
    }


    public function mainFields()
    {
        $fields[] = $basLinkId = new GSelection('basLinkId', '', _M('Código do grupo de usuário', $this->module), $this->busBond->listBond(true));

        //bloqueia o campo na edição
        if ( $this->function == 'update' )
        {
            $basLinkId->setReadOnly(true);
        }
        
        $exemplaryStatus = $this->busExemplaryStatus->listExemplaryStatus();
        $tableFields[] = $selection = new GSelection('exemplaryStatusId', null, _M('Estado do exemplar', $this->module), $exemplaryStatus);
        $selection->addAttribute("onchange", 'document.getElementById(\'description\').value = document.getElementById(\'exemplaryStatusId\').options[document.getElementById(\'exemplaryStatusId\').selectedIndex].text');
        
        $tableFields[] = new MHiddenField('description', $exemplaryStatus[0][1]);
        $tableFields[] = new MSeparator();

        $tables = new GRepetitiveField('exemplaryStatus', _M('Estado do exemplar', $this->module));
        $tables->setFields($tableFields);

        $columns = array
        (
            new MGridColumn( _M('Código',     $this->module), 'right',    true, "10%", true, 'exemplaryStatusId'              ),
            new MGridColumn( _M('Descrição',  $this->module), 'left',     true, "74%", true, 'description'     ),
        );

        $valids[] = new MRequiredValidator('exemplaryStatusId');
        $valids[] = new GnutecaUniqueValidator('exemplaryStatusId');

        $tables->setColumns($columns);
        $tables->setValidators($valids);
        $fields[] = $tables;

        $this->setFields( $fields );

        $validators[] = new MRequiredValidator('basLinkId');
        $validators[] = new MRequiredValidator('exemplaryStatus');
        
        $this->setValidators($validators);
    }


    /**
     * Método reescrito
     */
    public function loadFields()
    {
    	$accesso = $this->busRequestChangeExemplaryStatusAccess->getRequestAcces( MIOLO::_REQUEST('basLinkId') );
        $this->setData( $this->business );
        GRepetitiveField::setData($this->busRequestChangeExemplaryStatusAccess->exemplaryStatus, 'exemplaryStatus');
    }
 }
?>