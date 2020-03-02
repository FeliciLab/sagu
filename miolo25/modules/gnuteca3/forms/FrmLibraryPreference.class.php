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
 * LibraryPreference form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2010
 *
 **/
class FrmLibraryPreference extends GForm
{
    public $tables;
    public $domainList;
    public $listLibraryUnit;
    public $listAssociatedLibraryUnit;
    public $listPreference;
    public $listLibraryPreference;
    
    public $busLibraryUnit;
    public $busLibraryUnitConfig;
    public $busDomain;
    
    public $fieldsOfGroup;

    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();

    	$this->setAllFunctions('Preference', null, array('moduleConfig','parameter'), 'parameter');
        $this->setTransaction('gtcLibraryPreference');

        $this->busLibraryUnit       = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busLibraryUnitConfig = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnitConfig');

        //Cria lista especial de bibliotecas
        $this->listLibraryUnit[] = array($this->business->getGeneralId(), '<b>'._M('-- Geral --', $this->module).'</b>');
        $this->listLibraryUnit    = array_merge($this->listLibraryUnit, $this->busLibraryUnit->listLibraryUnit(null, true));

        //Cria lista de bibliotecas associadas
        foreach ($this->listLibraryUnit as $key => $val)
        {
            $this->listAssociatedLibraryUnit[$val[0]] = $val[1];
        }

        //Cria lista de preferencias com array associativo
        $this->business->filterGroupByNotNull = true;
        $listPreference = $this->business->searchPreference( true, 'groupBy,orderBy' );

        if ($listPreference)
        {
            foreach ($listPreference as $key => $val)
            {
                $this->listPreference[$val->parameter] = $val;
            }
        }

        $libraryPreference = $this->busLibraryUnitConfig->searchLibraryUnitConfig(true);

        if ($libraryPreference)
        {
            foreach ($libraryPreference as $key => $val)
            {
                if (!in_array($val->libraryUnitId, array_keys($this->listAssociatedLibraryUnit))) //Operador nao possui permissao para esta biblioteca
                {
                    continue;
                }
                $this->listLibraryPreference[$val->libraryUnitId][$val->parameter] = $val->value;
            }
        }

        parent::__construct();

        $this->setClass("libraryPreference");
    }


    public function mainFields()
    {
        $fields = array();

        if ( is_array( $this->listPreference ) )
        {
            foreach ( $this->listPreference as $line => $preference )
            {
                $type = strtoupper( $preference->type );
                $label = new MLabeL($preference->label . ':');

                if ( $preference->parameter == 'EMAIL_PASSWORD' )
                {
                     $field = new MPasswordField( $preference->parameter, null, null, FIELD_DESCRIPTION_SIZE);
                }
                else
                {
                    if ( $type == 'INT' || $type == 'INTEGER' )
                    {
                        $field = new MIntegerField( $preference->parameter );
                    }
                    else
                    if ( $type == 'TEXT' )
                    {
                        $field = new MMultiLineField( $preference->parameter , null, null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
                    }
                    else
                    if ( $type == 'VARCHAR' )
                    {
                        $field = new MMultiLineField( $preference->parameter , null, null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
                    }
                    else
                    if ( $type == 'BOOLEAN' )
                    {
                        $field = new MSelection($preference->parameter, null, null, GUtil::listYesNo());
                    }
                    else
                    if ( $type == 'CHAR' || !$type)
                    {
                        $field = new MTextField( $preference->parameter, null, null, FIELD_DESCRIPTION_SIZE);
                    }
                }

                $field->addAttribute('title',$preference->description);
                $field->addAttribute('alt',$preference->description);

                //separa o id's dos campos por atributo
                $this->fieldsOfGroup[$preference->groupBy][] = $line; 
                $fields[ $preference->groupBy ][$preference->parameter] = new GContainer(null, array( $label, $field ) );
            }
        }

        //obtem qual é a tab da url
        $groupBy = MIOLO::_REQUEST('tabId');

        unset($columns, $valids, $tableData);
        $dataFound = false;

        $columns[] = new MGridColumn( _M('Código da biblioteca',$this->module),     'left', true, null, false,"libraryUnitId{$groupBy}" );
        $columns[] = new MGridColumn( _M('Unidade de biblioteca', $this->module),            'left', true, null, true,  "libraryName{$groupBy}" );

        foreach ($this->listLibraryUnit as $k => $v)
        {
            $varName = "libraryUnitId{$groupBy}";
            $tableData[ $v[0] ]->$varName = $v[0];
        }

        //Verifica para cada biblioteca se existe um ou mais valor(es) de parametro, caso nao exista, nao adiciona na subdetail
        foreach ($this->listAssociatedLibraryUnit as $libraryUnitId => $libraryName)
        {
            $existsValue[$groupBy][$libraryUnitId] = $this->checkLibraryParameter($fields[$groupBy], $libraryUnitId);
        }

        foreach ($fields[$groupBy] as $_key => $_val)
        {
            $controls = $_val->getControls();
            $columns[] = new MGridColumn( $controls[1]->name, 'left', true, null, false,  $controls[1]->name );

            //Carrega dados
            foreach ($this->listAssociatedLibraryUnit as $libraryUnitId => $libraryName)
            {
                if (!$existsValue[$groupBy][$libraryUnitId])
                {
                    unset($tableData[$libraryUnitId]);
                    continue;
                }
                else //Encontrou alguma coisa, entao marca para adicionar tab
                {
                    $dataFound = true;
                }

                $varName = $controls[1]->name;
                if ($libraryUnitId == $this->business->getGeneralId())
                {
                    $tableData[$libraryUnitId]->$varName = $this->listPreference[$varName]->configValue;
                }
                else
                {
                    $tableData[$libraryUnitId]->$varName = $this->listLibraryPreference[$libraryUnitId][$varName];
                }
            }
        }

        $label = new MLabel(_M('Unidade de biblioteca', $this->module) . ':');
        $libraryUnitId = new GSelection("libraryUnitId{$groupBy}", null, null, $this->listLibraryUnit, null, null, null, true);
        $libraryUnitId = new GContainer(null, array( $label, $libraryUnitId ));
        $fields[$groupBy] = array_merge(array($libraryUnitId), $fields[$groupBy]);
        $valids[] = new GnutecaUniqueValidator("libraryUnitId{$groupBy}", _M('Unidade de biblioteca', $this->module), 'required');

        if ($dataFound)
        {
            $tabName = MIOLO::_REQUEST('tabName') ? MIOLO::_REQUEST('tabName') : _M('Preferences', $this->module);
            $this->tables[$groupBy] = new GRepetitiveField("subdetail{$groupBy}", $tabName, NULL, NULL, array('edit'));
            $this->tables[$groupBy]->setFields( array(new MVContainer('vctTag', $fields[$groupBy])) ); //pega os fields dinamicos deste grupo
            $this->tables[$groupBy]->setColumns($columns);
            $this->tables[$groupBy]->setValidators($valids);

            if ($this->primeiroAcessoAoForm())
            {
                $this->tables[$groupBy]->setData($this->parseSubDetail($tableData));
            }
        }
        
        $this->setFields( array( $this->tables[$groupBy] ), true );

        $this->_toolBar->disableButton( array( MToolBar::BUTTON_NEW, MToolBar::BUTTON_DELETE,MToolBar::BUTTON_SEARCH ));
    }

    public function tbBtnSave_click($sender=NULL)
    {
        //FIXME precisa chamar o parent
        $this->mainFields();
        $groupBy = MIOLO::_REQUEST('tabId');

        $tableData = GRepetitiveField::getData('subdetail'.$groupBy);

        foreach ($tableData as $_key => $_val)
        {
            $varLibraryUnitId   = "libraryUnitId{$groupBy}";
            $varLibraryName     = "libraryName{$groupBy}";
            $libraryUnitId      = $_val->$varLibraryUnitId;

            foreach ($_val as $__key => $__val)
            {
                if (in_array($__key, $this->fieldsOfGroup[$groupBy]))
                {
                    $dados[$libraryUnitId][$__key] = ($__val->removeData) ? null : $__val; //Define null caso foi marcado como removido na subdetail
                }
            }
        }

        $this->business->beginTransaction();
        $ok = $this->business->updateAll($dados);
        $this->business->commitTransaction();
        
        if ( $ok )
        {
            $this->setModified(false);
            $this->information( MSG_RECORD_UPDATED );
        }
        else
        {
            $this->error(MSG_RECORD_ERROR, null, null, null, true);
        }
    }
    
    public function addToTable($args, $forceMode = FALSE)
    {
    	$item = $args->GRepetitiveField;
        $args = $this->parseSubDetail($args);
    	($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }
   
    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }
   
    public function parseSubDetail($data)
    {
    	if (is_array($data))
    	{
    		$arr = array();
    		foreach ($data as $val)
    		{
    			$arr[] = $this->parseSubDetail($val);
    		}
    		return $arr;
    	}
    	else if (is_object($data))
    	{
            $groupBy = MIOLO::_REQUEST('tabId');
            
            $varName = "libraryUnitId{$groupBy}";
            $libraryUnitId = $data->$varName;
            
            if ($libraryUnitId)
            {
                $varName = "libraryName{$groupBy}";
                $data->$varName = $this->listAssociatedLibraryUnit[$libraryUnitId];
            }
            
            return $data;
    	}
    }

    /**
     * Verifica se existe algum parametro para esta biblioteca (e para este grupo), caso nao, nao adiciona ela ao subdetail
     *
     * @param $fields (array)
     * @param $libraryUnitId (int)
     *
     * @return $exists (boolean)
     */
    public function checkLibraryParameter($fields, $libraryUnitId)
    {
        if ($libraryUnitId == $this->business->getGeneralId())
        {
            return true;
        }
        else
        {
            $keys = array_keys($this->listLibraryPreference[$libraryUnitId]);
            $groupKeys = array_keys($fields);
            
            foreach ($groupKeys as $key)
            {
                if (in_array($key, $keys))
                {
                    return true;
                }
            }
        }
        return false;
    }
}
?>
