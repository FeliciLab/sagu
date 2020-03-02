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
 * SearchFormat form
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
 *
 * @since
 * Class created on 28/11/2008
 *
 **/


/**
 * Form to manipulate a preference
 **/
class FrmSearchableField extends GForm
{
	public $MIOLO;
	public $module;
    public $businessGroupAccess;
    public $businessLibraryGroup;
    public $businessUserGroup;
    
    private $busBond;

    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
        $this->businessLibraryGroup   = $this->MIOLO->getBusiness($this->module, 'BusLibraryGroup');
        $this->businessUserGroup      = $this->MIOLO->getBusiness($this->module, 'BusUserGroup');
        $this->busBond                = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->setAllFunctions('SearchableField', null, array('searchableFieldId'), array('description', 'field', 'identifier', 'isRestricted', 'level'));
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') && strlen(MIOLO::_REQUEST('event')) == 0 )
        {
            GRepetitiveField::clearData('group');
        }
    }

    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[]  = new MTextField('searchableFieldId', null, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
            $validators[] = new MRequiredValidator('searchableFieldId');
        }

        $fields[] = new MTextField('description', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $validators[] = new MRequiredValidator('description');

        $label = new MLabel(_M('Campo', $this->module) );
        $field = new MTextField('field', null, '', FIELD_DESCRIPTION_SIZE);
        $divHint = new MDiv('divHint', _M('Utilize a vírgula "," para separá-los e o sinal de mais "+"', $this->module) . "<br>" . _M('para unir dois campos em um só.', $this->module));
        $fields[] = new GContainer('contField', array($label, $field, $divHint));
        $validators[] = new MRequiredValidator('field');

        $fields[] = new MTextField('identifier', null, _M('Identificador', $this->module), FIELD_DESCRIPTION_SIZE);
        $validators[] = new MRequiredValidator('identifier');
        $fields[] = new MMultiLineField('observation', null, _M('Observação', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);

        $fields[] = new GRadioButtonGroup('isRestricted', _M('É restrita', $this->module), GUtil::listYesNo(1), DB_FALSE, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new MTextField('level', $this->level->value, _M('Nível',$this->module), FIELD_ID_SIZE);
        $validators[] = new MIntegerValidator('level', null, 'required');
        $fields[]  = new GSelection('fieldType', $this->fieldType->value, _M('Tipo do campo', $this->module), $this->business->listFieldType(), null, null, null, TRUE);
        
        //Adicionado o tipo de filtro, 1 para simples, 2 para avançado
        $fields[]  = new GSelection('filterType', $this->filterType->value, _M('Tipo de filtro', $this->module), $this->business->listFilterType(), null, null, null, TRUE);
        $validators[] = new MRequiredValidator('filterType');
        
        $fields[] = new MTextField('helps', $this->help->value, _M('Ajuda',$this->module), FIELD_DESCRIPTION_SIZE);

        $fldGroup[] = new GSelection('linkId', '', _M('Código do vínculo', $this->module), $this->busBond->listBond(true));
        
        $columns   = null;
        $valids    = null;
        $columns[] = new MGridColumn( _M('Código',        $this->module), 'left', true, null, true, 'linkId' );
        $columns[] = new MGridColumn( _M('Descrição', $this->module), 'left', true, null, true, 'linkDescription' );
        $valids[]  = new MIntegerValidator('linkId', _M('Código do vínculo', $this->module), 'required');

        $fields[] = new MSeparator('<br/>');
        $fields[] = $group = new GRepetitiveField('group', _M('Grupos com acesso', $this->module), $columns, $fldGroup, array('edit', 'remove'));
        $group->setValidators($valids);

        $this->setFields($fields);
        $this->setValidators($validators);
    }

    public function loadFields()
    {
        $this->business->getSearchableField( MIOLO::_REQUEST('searchableFieldId') );
        $this->setData($this->business);
        GRepetitiveField::setData($this->groupParse($this->business->group), 'group');
    }
    
    public function validaCamposAvancados($sender)
    {
        //Se filtro cadastrado for do tipo 'Simples'
        if($sender->filterType == ADVANCED_TYPE_FIELD)
        {
            //Marcas tipos: numéricos, strings e datas.
            $arrOfAdvFilter = array();
            
            //Validação para tipode filtro
            if(in_array($sender->fieldType, $arrOfAdvFilter))
            {
                throw new Exception("O tipo de campo registrado, não pertence ao tipo de filtro registrado.");
            }
            else
            {
                /* Validação para informações no formulário de campo
                Verifica se tem os caracteres '+' ou ',' no 'campo' */
                if(strpos($sender->field, '+') || strpos($sender->field, ','))
                {
                    throw new Exception("As informações de 'campo' não estão coerentes. Verifique!");
                }
            }
        }
        else
        {
            //Marca tipo comboBox e Período
            $arrOfAdvFilter = array("4", "5");
            if(in_array($sender->fieldType, $arrOfAdvFilter))
            {
                throw new Exception("O tipo de campo registrado, não pertence ao tipo de filtro registrado.");
            }
        }
    }

    public function tbBtnSave_click($sender)
    {
        $this->validaCamposAvancados($sender);
        
        $data = $this->getData();

        if ($data->group)
        {
            foreach ($data->group as $g)
            {
                $linkId = $this->businessUserGroup->getUserGroup($g->linkId)->linkId;

                if (!$g->linkId || $g->linkId != $linkId)
                {
                    $errors[] = _M('Sem grupo para @1.', $this->module, $g->linkId);
                }
            }
        }

        parent::tbBtnSave_click($sender, $data, $errors);
    }
    
    /**
     * Método reescrito para fazer o parser da descrição do vínculo
     * @param type $args
     * @param type $forceMode 
     */
    public function addToTable($args, $forceMode = FALSE)
    {
        $args = $this->groupParse($args);
        ($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }


    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }
    
    
     /**
     * Método que trata os dados da repetitive de vínculos
     */
    public function groupParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->groupParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
            $link = $this->busBond->listBond();
            
            if ( is_array($link) )
            {
                foreach( $link as $key => $values )
                {
                    if ( $values[0] == $data->linkId )
                    {
                        $data->linkDescription = $values[1];
                        break;
                    }
                }
            }

            return $data;
        }
    }
}
?>