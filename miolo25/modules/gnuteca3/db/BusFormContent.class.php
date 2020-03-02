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
 * gtcFormContent business
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 07/04/2009
 *
 **/


class BusinessGnuteca3BusFormContent extends GBusiness
{
    public $busAuthenticate;
    public $busFormContentDetail;

    public $formContentDetail = array();
    public $doUpdate = FALSE;

    public $formContentId;
    public $operator;
    public $form;
    public $name;
    public $description;
    public $formContentType;

    public $formContentIdS;
    public $operatorS;
    public $formS;
    public $nameS;
    public $descriptionS;
    public $formContentTypeS;
    
    /**
     * Nome do formulário utilizado para localização iqualitária
     *
     * @var string
     */
    public $formEqualS;


    public function __construct()
    {
        $table = 'gtcFormContent';
        $pkeys = 'formContentId';
        $cols  = 'operator,
                  form,
                  name,
                  description,
                  formContentType';
        parent::__construct($table, $pkeys, $cols);
        $this->busAuthenticate      = $this->MIOLO->getBusiness($this->module, 'BusAuthenticate');
        $this->busFormContentDetail = $this->MIOLO->getBusiness($this->module, 'BusFormContentDetail');
    }


    public function insertFormContent()
    {
        $this->formContentId = $this->db->getNewId('seq_formContentId');
        $ok = $this->autoInsert();
        if ($ok) //Insert data on gtcFormContentDetail
        {
        	$this->busFormContentDetail->deleteFormContentDetail($this->formContentId);
        	
	        foreach ($this->formContentDetail as $data)
	        {
	        	$data->formContentId = $this->formContentId;
	            $this->busFormContentDetail->setData($data);
	            $this->busFormContentDetail->insertFormContentDetail();
	        }
        }
        return $ok;
    }


    public function updateFormContent($updateFormContentDetail = TRUE)
    {
        $ok = $this->autoUpdate();
        if (($ok) && ($updateFormContentDetail)) //Insert data on gtcFormContentDetail
        {
        	$this->busFormContentDetail->deleteFormContentDetail($this->formContentId);
            foreach ($this->formContentDetail as $data)
            {
            	$data->formContentId = $this->formContentId;
            	$this->busFormContentDetail->setData($data);
            	$this->busFormContentDetail->insertFormContentDetail($data);
            }
        }
        return $ok;
    }


    public function deleteFormContent($formContentId)
    {
    	if (!$formContentId)
    	{
    		return FALSE;
    	}
    	$this->busFormContentDetail->deleteFormContentDetail($formContentId);
        return $this->autoDelete($formContentId);
    }


    public function getFormContent($formContentId)
    {
        $this->clear();

        //Load data from gtcFormContentDetail
        $this->busFormContentDetail->formContentIdS = $formContentId;
        $this->formContentDetail = $this->busFormContentDetail->searchFormContentDetail(TRUE);

        return $this->autoGet($formContentId);
    }


    public function searchFormContent($toObject = FALSE)
    {
        if (MIOLO::_REQUEST('action') == 'main:administration:formContent')
        {
            unset($this->formContentId); //estava ocorrendo bug pos-busca no formulario
        }

        $this->clear();

        if ( strlen($this->formContentId) > 0 )
        {
            $this->setWhere('formContentId = ?');
            $args[] = $this->formContentId;
        }
        else if ( strlen($this->formContentIdS) > 0 )
        {
            $this->setWhere('formContentId = ?');
            $args[] = $this->formContentIdS;
        }

        if ( strlen($this->operator) > 0 )
        {
            $this->setWhere('lower(operator) like lower(?)');
            $args[] = $this->operator . '%';
        }
        else if ( strlen($this->operatorS) > 0 )
        {
            $this->setWhere('lower(operator) like lower(?)');
            $args[] = $this->operatorS . '%';
        }

        if ( strlen($this->form) > 0 )
        {
            $this->setWhere('lower(form) like lower(?)');
            $args[] = $this->form.'%';
        }
        else if ( strlen($this->formS) > 0 )
        {
            $this->setWhere('lower(form) like lower(?)');
            $args[] = $this->formS.'%';
        }

        //usada para localização especifigca de um formulário, diferenciando-o do de busca
        if ( $this->formEqualS )
        {
            $this->setWhere('lower(form) = lower(?)');
            $args[] = $this->formEqualS;
        }

        if ( strlen($this->name) > 0 )
        {
            $this->setWhere('lower(name) like lower(?)');
            $args[] = $this->name . '%';
        }
        else if ( strlen($this->nameS) > 0 )
        {
            $this->setWhere('lower(name) like lower(?)');
            $args[] = $this->nameS . '%';
        }

        if ( strlen($this->description) > 0 )
        {
            $this->setWhere('lower(description) like lower(?)');
            $args[] = $this->description . '%';
        }
        else if ( strlen($this->descriptionS) > 0 )
        {
            $this->setWhere('lower(description) like lower(?)');
            $args[] = $this->descriptionS . '%';
        }

        if ( strlen($this->formContentType) > 0 )
        {
            $this->setWhere('formContentType = ?');
            $args[] = $this->formContentType;
        }
        else if ( strlen($this->formContentTypeS) > 0 )
        {
            $this->setWhere('formContentType = ?');
            $args[] = $this->formContentTypeS;
        }

        $this->setOrderBy('formContentId');
        $this->setColumns($this->columns);
        $this->setTables($this->tables);
        $sql = $this->select($args);
        
        return $this->query($sql, $toObject);
    }


    public function listFormContent($associative = FALSE)
    {
        return $this->autoList(null, $associative);
    }


    /**
     * Enter description here...
     *
     * @param MForm $form
     * @param Array $values Array associativo no estilo campo => valor contendo dados especificos
     */
    public function saveFormValues( $form, $data=null)
    {
    	if ($this->formContentType == FORM_CONTENT_TYPE_SEARCH)
    	{
            $this->operator = $this->busAuthenticate->getUserCode();
    	}
    	else if ($this->formContentType == FORM_CONTENT_TYPE_ADMINISTRATOR)
    	{
    		$this->operator = '';
    	}
    	else
    	{
            $this->formContentType = FORM_CONTENT_TYPE_OPERATOR;
            $this->operator        = GOperator::getOperatorId();
    	}

        $this->operatorS = $this->operator;
        $this->form = is_object( $form ) ? $form->className  : $form ;
        $this->formEqualS = $this->form;

        $search = $this->searchFormContent(TRUE);
        $this->formContentId = $search[0]->formContentId;

        if ( $this->formContentId )
        {
            $this->busFormContentDetail->deleteFormContentDetail( $this->formContentId );
            
            if ($this->doUpdate)
            {
                $this->updateFormContent(FALSE);
            }
        }
        else
        {
            $this->insertFormContent();
        }

        //lista de campos a serem negados/não inseridos
        $negate = array(
            '__mainForm__EVENTTARGETVALUE',
            'arrayItemTemp',
            'GRepetitiveField',
            '__mainForm__VIEWSTATE',
            '__mainForm__VIEWSTATE',
            '__mainForm__EVENTARGUMENT',
            '__THEMELAYOUT',
            '__ISFILEUPLOAD',
            'formContentId',
            'value',
            'field',
            '__mainForm__ISPOSTBACK' );

        //insere os dados
        if ( $data )
        {
            
	        foreach ($data as $field => $value)
	        {
	            if ( $value )
	            {
	                $data->formContentId = $this->formContentId;

                    //pula camopos que não são necessários salvar
                    if (in_array($field, $negate) )
                    {
                        continue;
                    }

                        $data->field         = $field;
	                $data->value         = $value;
	                $this->busFormContentDetail->setData($data);

	                $ok = $this->busFormContentDetail->insertFormContentDetail();

	                if( !$ok )
	                {
	                	return false;
	                }
	            }
	        }
        }
        return true;
    }


    /**
     * Carrega os campos do formulário
     *
     * @param MForm $form
     * @param boolean $returnData TRUE to return associative array with field => value, FALSE to try set field data on MForm object
     * @return (Array) $data
     */
    public function loadFormValues( $form, $returnData = FALSE)
    {
    	if ($this->formContentType == FORM_CONTENT_TYPE_SEARCH)
    	{
    		$operator = $this->busAuthenticate->getUserCode();
    	}
    	else if ($this->formContentType == FORM_CONTENT_TYPE_ADMINISTRATOR)
    	{
    		$operator = '';
    	}
    	else
    	{
            $operator = GOperator::getOperatorId();
            $this->formContentType = FORM_CONTENT_TYPE_OPERATOR;
    	}

        $formName = is_object( $form) ? $form->className : $form;
        $fields   = $this->processNamesArray( $form->fields );
        $this->operatorS = $operator;
        $this->formEqualS = $formName;

        $search = $this->searchFormContent(TRUE);
        $formContentId = $search[0]->formContentId;

        if ( $formContentId )
        {
        	$this->busFormContentDetail->formContentIdS = $formContentId;
        	$data = $this->busFormContentDetail->searchFormContentDetail(TRUE);
        }

        $out = array();
        
        if ($data)
        {
            foreach ($data as $v)
            {
                if ($returnData)
                {
                    $out[$v->field] = $v->value;
                }
                else if (($v->value) && (is_object($form->GetField($v->field))))
                {
                    $field = $form->GetField($v->field);

                    //caso tenha ido um dado errado, verifica, é importante manter esse código para compatibilidade com php 5.2.4
                    if ( $v->value == 'Array' )
                    {
                        continue;
                    }

                    //pula caso o campo seja um campo repetitivo
                    if ( $field instanceof GRepetitiveField )
                    {
                        continue;
                    }
                    
                    //Caso for checkbox nao pode chamar o setFieldValue pois o componente MCheckBox precisa do metodo setChecked para selecionar/descelesionar 
                    if ( $field instanceof MCheckBox )
                    {
                        $field->setChecked(MUtil::getBooleanValue($v->value));
                        continue;
                    }

                    $form->setFieldValue($v->field, $v->value);
                }
            }
        }

        if ($returnData)
        {
        	return $out;
        }
    }


    /**
     * Passa pelo campo detectando se existe campos internos ou não
     * Função usada para detectar relação de campos para javascripts
     *
     * @param object $field o campo a detectar, pode ser um array de campos tambem.
     */
    protected function processNamesArray( $field )
    {
        if (is_array($field))
        {
            foreach ($field as $value)
            {
                $this->processNamesArray($value);
            }
        }
        else if ( $field instanceof MDiv )
        {
            $this->processNamesArray( $field->getInner() );
        }
        else if ( $field instanceof MContainer )
        {
            //$this->processNamesArray( $field->getControls() );
            $this->processNamesArray( $field->getControls() );
        }
        else if ($field->name)
        {
            $this->namesArray[] = $field->name;
        }
        
        if ( $this->namesArray == null )
        {
            return $this->namesArray;
        }
        
        return array_unique($this->namesArray);
    }


     /**
     * Altera os registro de um usuário por outro
     *
     * @param integer $currentPersonId
     * @param integer $newPersonId
     * @return boolean
     */
    public function updatePersonId($currentPersonId, $newPersonId)
    {
        $this->clear();
        $this->setColumns("operator");
        $this->setTables($this->tables);
        $this->setWhere(' operator = ?');
        $sql = $this->update(array($newPersonId, $currentPersonId));
        $rs  = $this->execute($sql);
        return $rs;
    }


    /**
     * Método reescrito para fazer o setData do form e name
     *
     * @param (object) $data
     */
    public function setData($data)
    {
        $this->nameS = $data->_nameS;
        $this->formS = $data->_formS;

        parent::setData($data);
    }

}
?>
