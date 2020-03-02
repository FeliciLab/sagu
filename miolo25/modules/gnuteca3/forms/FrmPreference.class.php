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
 * Preference form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 28/07/2008
 *
 **/
class FrmPreference extends GForm
{
    public $tables;
    public $busLibraryUnit;

    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
    	$this->setAllFunctions('Preference', null, array('moduleConfig','parameter'), 'parameter');
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');

        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            $this->tables['libraryUnitConfig']->clearData();
        }
    }


    public function createFields()
    {
        $moduleConfig = new MTextField('moduleConfig', strtoupper($this->module), _M('Módulo',$this->module), 20);
        $moduleConfig->setReadOnly(true);
        $fields[] = $moduleConfig;
        $validators[] = new MRequiredValidator('moduleConfig');

        if ( $this->function != 'update' )
        {
            $parameter = new MTextField('parameter', $this->parameter->value, _M('Parâmetro',$this->module), FIELD_DESCRIPTION_SIZE);
            #$this->setFocus('parameter');
            $fields[] = $parameter;
            $validators[] = new MRequiredValidator('parameter', '', 255);
        }
        else
        {
            $parameter = new MTextField('parameter', $this->parameter->value, _M('Parâmetro',$this->module), FIELD_DESCRIPTION_SIZE);
            #$this->setFocus('configValue');
            $parameter->setReadOnly(true);
            $fields[] = $parameter;
            $validators[] = new MRequiredValidator('parameter', '', 255);
        }
        $configValue = new MMultiLineField('configValue', $this->getFormValue('configValue',$data->value), _M('Conteúdo',$this->module), 20, 5, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = $configValue;

        $description = new MMultiLineField('description', $this->getFormValue('description',$data->description), _M('Descrição',$this->module), 40, 5, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = $description;
        $validators[] = new MRequiredValidator('description');

        $type = new GSelection('type', $this->getFormValue('type',$data->type), _M('Tipo do campo',$this->module), BusinessGnuteca3BusDomain::listForSelect('PREFERENCE_TYPE') );
        $fields[] = $type;
        $validators[] = new MRequiredValidator('type');

        $fields[] = new GSelection('groupBy', null, _M('Grupo', $this->module), BusinessGnuteca3BusDomain::listForSelect('ABAS_PREFERENCIA')  );

        $fields[] = new MTextField('orderBy', null, _M('Ordem',$this->module), FIELD_ID_SIZE);
        $validators[] = new MIntegerValidator('orderBy', _M('Order by', $this->module));

        $fields[] = new MTextField('label', null, _M('Etiqueta',$this->module), FIELD_DESCRIPTION_SIZE);

        //
        //Library unit config Subdetail
        //
        unset($flds, $columns, $valids);

        $flds[]     = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(null, true));
        $valids[]   = new GnutecaUniqueValidator('libraryUnitId', _M('Unidade de biblioteca', $this->module), 'required');
        $flds[]     = new MMultiLineField('content_', null, _M('Conteúdo', $this->module), 20, 5, FIELD_MULTILINE_COLS_SIZE);
        $valids[]   = new MRequiredValidator('content_', _M('Conteúdo', $this->module));

        $columns[] = new MGridColumn( _M('Código da biblioteca',$this->module),     'left', true, null, false,'libraryUnitId' );
        $columns[] = new MGridColumn( _M('Unidade de biblioteca', $this->module),            'left', true, null, true, 'libraryName' );
        $columns[] = new MGridColumn( _M('Conteúdo', $this->module),                    'left', true, null, true, 'content_' );

        $this->tables['libraryUnitConfig'] = new GRepetitiveField('libraryUnitConfig', _M('Configuração de unidade de biblioteca', $this->module), NULL, NULL, array('edit', 'remove'));
        $this->tables['libraryUnitConfig']->setFields($flds);
        $this->tables['libraryUnitConfig']->setColumns($columns);
        $this->tables['libraryUnitConfig']->setValidators($valids);
        $fields[] = new MSeparator();
        $fields[] = $this->tables['libraryUnitConfig'];
        //Library unit config Subdetail END

        $this->setFields($fields);
        if ( isset($validators) )
        {
            $this->setValidators($validators);
        }
    }

    public function loadFields()
    {
        if (!MIOLO::_REQUEST('moduleConfig') || !MIOLO::_REQUEST('parameter'))
        {
            return;
        }

        $data = $this->business->getPreference( MIOLO::_REQUEST('moduleConfig'), MIOLO::_REQUEST('parameter') );
        $this->setData( $this->business , false );

        $libraries = array();
        $tmpLibraries = $this->busLibraryUnit->listLibraryUnit(false, true);
        if ($tmpLibraries)
        {
            foreach ($tmpLibraries as $key => $val)
            {
                $libraries[] = $val[0];
            }
        }
        $luc = $this->parseLibraryUnitConfig($data->libraryUnitConfig);
        if ($luc)
        {
            $_luc = array();
            foreach ($luc as $key => $val)
            {
                if (in_array($val->libraryUnitId, $libraries)) //Se operador tem acesso a esta biblioteca
                {
                    $val->content_ = $val->value;
                    $_luc[] = $val;
                }
            }
        }
        
        $this->tables['libraryUnitConfig']->setData($_luc);
    }

    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();

        //Parse penalty data
        $luc = $this->tables['libraryUnitConfig']->getData();
        //$luc = $data->libraryUnitConfig;

        if ($luc)
        {
            foreach ($luc as $key => $val)
            {
                $luc[$key]->parameter = $data->parameter;
                $luc[$key]->value       = $val->content_;
            }
        }
        $data->libraryUnitConfig = $luc;
        $allowed = $this->busLibraryUnit->listLibraryUnit(false, true);
        if ($allowed) //Monta lista de bibilotecas permitidas
        {
            foreach ($allowed as $v)
            {
                $data->allowedLibraryUnitConfig[] = $v[0];
            }
        }

        parent::tbBtnSave_click($sender, $data);
    }

    public function addToTable($args, $forceMode = FALSE)
    {
    	$item = $args->GRepetitiveField;
    	switch($item)
    	{
    		case 'libraryUnitConfig':
                        $args = $this->parseLibraryUnitConfig($args);
                        break;
    	}
    	($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }

    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }

    public function parseLibraryUnitConfig($data)
    {
    	if (is_array($data))
    	{
    		$arr = array();
    		foreach ($data as $val)
    		{
    			$arr[] = $this->parseLibraryUnitConfig($val);
    		}
    		return $arr;
    	}
    	else if (is_object($data))
    	{
            $data->libraryName = $this->busLibraryUnit->getLibraryUnit($data->libraryUnitId)->libraryName;
            return $data;
    	}
    }
}
?>