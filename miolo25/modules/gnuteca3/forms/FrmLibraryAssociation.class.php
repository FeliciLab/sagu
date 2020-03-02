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
 * Class Library Association Form
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 31/jul/08
 *
 **/
class FrmLibraryAssociation extends GForm
{
	public $MIOLO;
	public $module;
    public $businessAssociation;
    public $businessLibraryUnit;
    public $tables;


    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->businessAssociation  = $this->MIOLO->getBusiness($this->module, 'BusAssociation');
        $this->businessLibraryUnit  = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->setAllFunctions('Association', null, array('associationId'), array('description'));
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            $this->tables['libraryAssociation']->clearData();
        }
    }


    public function mainFields()
    {
        $fields[]       = new MTextField("description", null,  _M("Descrição", $this->module), FIELD_DESCRIPTION_SIZE);
        $validators[]   = new MRequiredValidator("description", _M("Descrição", $this->module) );

        if ($this->function == 'update')
        {
            $fields[] = new MHiddenField("associationId", null, null, 0);
        }

        $libraryUnits = $this->businessLibraryUnit->listLibraryUnit();
        $tableFields[] = $selection = new GSelection('libraryUnitId', 1, _M('Unidade de biblioteca', $this->module), $libraryUnits);
        $selection->addAttribute("onchange", 'document.getElementById(\'libraryUnitDescription\').value = document.getElementById(\'libraryUnitId\').options[document.getElementById(\'libraryUnitId\').selectedIndex].text');
        $tableFields[] = new MHiddenField('libraryUnitDescription', $libraryUnits[0][1], '', 10);
        $tableFields[] = new MSeparator();

        $this->tables['libraryAssociation'] = new GRepetitiveField('LibraryUnits', _M('Biblioteca', $this->module));
        $this->tables['libraryAssociation']->setFields($tableFields);

        $columns = array
        (
            new MGridColumn( _M('Código',         $this->module), 'left',    true, "10%", true, 'libraryUnitId'              ),
            new MGridColumn( _M('Descrição',  $this->module), 'left',     true, "74%", true, 'libraryUnitDescription'     ),
        );

        $valids[] = new GnutecaUniqueValidator('libraryUnitId',_M('Unidade de biblioteca', $this->module));
        $valids[] = new MRequiredValidator('libraryUnitId');
        $this->tables['libraryAssociation']->setColumns($columns);
        $this->tables['libraryAssociation']->setValidators($valids);
        $fields[] = $this->tables['libraryAssociation'];

        $this->setFields( $fields );
        $this->setValidators($validators);
    }


    public function tbBtnSave_click($sender = null)
    {
        $data = $this->getData();
        $data->libraryAssociation = GRepetitiveField::getData('LibraryUnits');
        
        $error = !count($data->libraryAssociation);

        if ( $error )
        {
            $this->error(_M("Lista de bibliotecas está vazia!", $this->module));
        }
        else
        {
            parent::tbBtnSave_click($sender, $data);
        }
    }


    public function loadFields()
    {
        $this->business->getAssociation( MIOLO::_REQUEST('associationId') );
        $this->setData( $this->business );
        $this->tables['libraryAssociation']->setData( $this->business->libraryAssociation );
    }
}
?>
