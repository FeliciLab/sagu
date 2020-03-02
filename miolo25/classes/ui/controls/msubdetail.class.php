<?php

/**
 * MSubDetail class
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2009/01/29
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 * 
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_subdetail.js');

class MSubDetail extends MBaseGroup
{
    private $item = 'item';
    private $tableItem = '';
    private $actions = '';
    public $title = ''; // table Title
    private $table = NULL;
    private $fields = NULL;
    private $showButtons = true;
    private $overflowWidth = NULL;
    private $overflowHeight = NULL;
    private $overflowType = NULL;
    public $cleanFields = true;
    public $cleanHiddenF = false;
    public $actionCelWidth = false;
    public $transaction = NULL;
    protected $updateButton = false;
    private $namesArray;
    private $classesArray;
    private $validator;
    private static $doLookupAutocomplete = false;
    const IMGWIDTH = 20;
    const STATUS_ADD = 'add';
    const STATUS_EDIT = 'edit';
    const STATUS_REMOVE = 'remove';

    /**
     * Default constructor, need to pass Session Item.
     */
    public function __construct($name, $title, $columns = NULL, $fields = NULL, $opts = true, $align = 'vertical', $border = 'css', $formMode = MFormControl::FORM_MODE_SHOW_SIDE)
    {
        parent::__construct($name, $title, null, $align, $border, $formMode);
        $module = MIOLO::getCurrentModule();
        $MIOLO = MIOLO::getInstance();
        $this->title = $title;
        $this->item = $name;

        // adiciona coluna padrão
        $this->addColumn(_M('Actions'), 'left', true, '10%', true, '');
        $this->setColumns($columns);
        $this->setFields($fields);

        // limpa as ações para este subdetail
        self::setSessionValue('actions', null, $this->item);

        // adiciona ações padrão
        if ( $opts )
        {
            $ui = $MIOLO->getUI();
            $editImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'button_edit.png');
            $rmImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'button_drop.png');
            $upImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'button_up.png');
            $downImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'button_down.png');
            $dupliImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'button_duplicate.png');

            if ( $opts === true )
            {
                $this->addAction('editFromTable', $editImg);
                $this->addAction('removeFromTable', $rmImg);
            }
            else
            if ( is_array($opts) )
            {
                if ( in_array('edit', $opts) )
                {
                    $this->addAction('editFromTable', $editImg);
                }

                if ( in_array('duplicate', $opts) )
                {
                    $this->addAction('duplicate', $dupliImg);
                }

                if ( in_array('remove', $opts) )
                {
                    $this->addAction('removeFromTable', $rmImg);
                }

                if ( in_array('up', $opts) )
                {
                    $this->addAction('upFromTable', $upImg);
                }

                if ( in_array('down', $opts) )
                {
                    $this->addAction('downFromTable', $downImg);
                }

                if ( in_array('noButtons', $opts) )
                {
                    $this->showButtons = false;
                }

                if ( in_array('updateButton', $opts) )
                {
                    $this->setUpdateButton(true);
                }

                if ( in_array('noOrder', $opts) )
                {
                    $this->setOrder(false);
                }
            }
        }

        $this->addStyle('clear', 'both'); // para conseguir setar o width sem problema

        $this->setOrder(true);
        $MIOLO = MIOLO::getInstance();
        // cria o campo arrrayItemTemp automaticamente para este subdetail, sobre demanda
        $MIOLO->page->onload("msubdetail.addJsField('arrayItemTemp{$name}')");
    }

    /**
     * Define um valor padrão a ser mostrado em um campo específico a cada vez que os campos forem limpos
     *
     * Este valor é chamado ao limpar, após adicionar, e após editar.
     *
     * @param String $fieldId o id do campo a receber o valor
     * @param String $value  o valor padrão para o campo
     */
    public function setDefaultValue($fieldId, $value)
    {
        $MIOLO = MIOLO::getInstance();
        $id = $this->item . '_' . $fieldId . '_defaultValue';
        $MIOLO->page->onload("msubdetail.addJsField('$id','$value');");
    }

    /**
     * Define se é para mostrar ou não o botão de atualização
     *
     * @param boolean $show
     */
    public function setUpdateButton($show)
    {
        $this->updateButton = $show;
        self::setSessionValue('updateButton', $show, $this->item);
    }

    /**
     * Define se é para dar suporte as funções de ordenação automáticas ou não
     *
     * @param boolean $show
     */
    public function setOrder($show)
    {
        self::setSessionValue('order', $show, $this->item);
    }

    public function getOrder($itemForce)
    {
        return self::getSessionValue('order', $itemForce);
    }

    /**
     * Return se é para mostrar ou não o botão de atualização
     *
     * @return se é para mostrar ou não o botão de atualização
     *
     */
    public function getUpdateButton($itemForce = null)
    {
        $value = self::getSessionValue('updateButton', $itemForce);
        return $value;
    }

    /**
     * Se era para adicionar botões automaticos de adição e de limpar (add e clear)
     *
     * @param boolean $showButtons true para adicionar botões automaticos de adição e de limpar (add e clear)
     */
    public function setShowButtons($showButtons)
    {
        $this->showButtons = $showButtons;
    }

    /**
     * Retornar se é para adicionar botões automáticos
     *
     * @return boolean
     */
    public function getShowButtons()
    {
        return $this->showButtons;
    }

    /**
     * Make the table flow inside a div.
     *
     * @param integer $width in pixels (without 'px');
     * @param string  $type the type of overflow, see CSS overflow declaration
     */
    public function setOverFlowWidth($width, $type = 'auto')
    {
        $this->overflowWidth = $width;
        $this->overflowType = $type;
    }

    /**
     * Make the table flow inside a div.
     *
     * @param integer $width in pixels (without 'px');
     * @param string  $type the type of overflow, see CSS overflow declaration
     */
    public function setOverFlowHeight($height, $type = 'auto')
    {
        $this->overflowHeight = $height;
        $this->overflowType = $type;
    }

    /**
     * Definido automaticamente pelo subdetail no parseFields quando ele detecta in subdetail dentro dele.
     *
     * @param String $name nome/item/id do subdetail
     * @param String $childId nome/item/id do filho
     */
    function addChild($name, $childId)
    {
        if ( $name && $childId )
        {
            $childs = self::getChilds($name);
            $childs[$childId] = $childId;
            self::setSessionValue('childs', $childs, $name);
        }
    }

    /**
     * Retorna um array com os ids dos filho
     *
     * @param String $name
     * @return String um array com os ids dos filho
     */
    function getChilds($name)
    {
        return self::getSessionValue('childs', $name);
    }

    /**
     * Passa pelo campo (ou campos recursivamente) detectando se existe campos internos ou não
     * Função usada para detectar relação de campos para javascripts, e também faz parser e ajusta campos necessários
     *
     * @param Object $field
     * @param boolean $session
     * @param String $itemForce
     * @param boolean $neverEnterSessionIf
     * @return Object or array of objects
     */
    private function parseFields($field, $session = true, $itemForce = null, $neverEnterSessionIf = false)
    {
        if ( is_array($field) )
        {
            foreach ( $field as $line => $info )
            {
                $field[$line] = $this->parseFields($info);
            }
        }
        else
        if ( $field instanceof MSubDetail )
        {
            $this->namesArray[] = $this->item . '_' . $field->name;
            $this->classesArray[] = get_class($field); //duplo em função do namesArray ser duplo também
            //não faz parser do campos caso seja um subdetail, o subdetail filho faz seus próprio parser
            self::addChild($this->item, $field->name); //adiciona um filho ao subdetail
        }
        else
        if ( $field instanceof MTabbedBaseGroup )
        {
            //faz o parser dos campos internos mas não muda o id destes primeiros
            $this->parseFields($field->getControls());
        }
        else
        if ( ($field instanceof MContainer ) )
        {
            $field->setControls($this->parseFields($field->getControls()));
        }
        else
        if ( ($field instanceof MDiv ) )
        {
            $field->setInner($this->parseFields($field->getInner()));
        }
        else
        if ( ($field instanceof MTableRaw ) )
        {
            $field->setInner($this->parseFields($field->array));
        }

        //não relaciona situações abaixo na relação de campos
        if ( $field->name && (!$field instanceof MContainer || $field instanceof MRadioButtonGroup) && !$field instanceof MLabel && !$field instanceof MText && !$field instanceof MSubDetail && !$field instanceof MRadioButton )
        {
            $this->namesArray[] = $this->item . '_' . $field->name; //adiciona o campo com o nome do subdetail concatenado
            $this->classesArray[] = get_class($field); //duplo em função do namesArray ser duplo também
            //le valor padrã do campo e seta como valor padrão do componente no subdetail // defaultValue ;
            if ( $field->id && $field->value )
            {
                if ( $field instanceof MCheckBox || $field instanceof MCheckBox )
                {
                    self::setDefaultValue($field->id, $field->checked);
                }
                else
                {
                    self::setDefaultValue($field->id, $field->value);
                }
            }
        }

        if ( !$field instanceof MTabControl && !$field instanceof MTab && is_object($field) )
        {
            $field->name = $this->item . '_' . $field->name;
            $field->id = $this->item . '_' . $field->id;
        }

        if ( $field instanceof MLookupTextField )
        {
            $related = explode(',', $field->related);

            foreach ( $related as $rel )
            {
                $newRel[] = $this->item . '_' . $rel;
            }

            $field->related = implode(',', $newRel);

            if ( !is_array($field->filter) )
            {
                $field->filter = explode(',', $this->item . '_' . $field->filter);
            }

            $field->setContext($field->baseModule, $field->module, $field->item, $field->lookupEvent, $field->related, $field->filter, $field->autocomplete, $field->title);
            $field->lookup_name = "lookup_{$field->formId}_{$field->name}";
        }

        //coloca os campos na sessão
        if ( !$neverEnterSessionIf )
        {
            if ( $session || ($field instanceof MSelection) || ($field instanceof MRadioButtonGroup) || ($field instanceof MCheckBoxGroup) )
            {
                $column = self::getColumn($field->id, $itemForce);

                if ( $column->order || ($field instanceof MSelection) || ($field instanceof MRadioButtonGroup) || ($field instanceof MCheckBoxGroup) || ($field instanceof MLookupTextField) )
                {
                    $newField = new StdClass();
                    $newField->id = $field->id;
                    $newField->name = $field->name;
                    $newField->label = $field->label;
                    $newField->class = strtolower(get_class($field));

                    if ( $field->options )
                    {
                        $newField->options = $field->options;
                    }

                    if ( $field instanceof MLookupTextField )
                    {
                        $newField->module = $field->module;
                        $newField->baseModule = $field->baseModule;
                        $newField->item = $field->item;
                        $newField->related = $field->related;
                        $newField->filter = $field->filter;
                    }

                    $fields = self::getSessionValue('fields', $this->item);
                    $fields[$field->name] = serialize($newField); // foi serializado e encodado em função de enviar para a sessão, qualquer outra forma não funciona (não monta devolta no getFiels)


                    self::setSessionValue('fields', $fields, $this->item);
                }
            }
        }

        //adiciona * para campos requeridos dentro do subdetail
        if ( $field instanceof MContainer )
        {
            $containerControls = $field->getControls();
            $valid = self::getValidator($this->item, $containerControls[1]->id);

            if ( $valid->type == 'required' )
            {
                $subContainer = $containerControls[0];

                if ( $containerControls[0]->inner && is_object($containerControls[0]->inner) )
                {
                    $containerControls[0]->inner->setClass('mCaptionRequired');
                }
            }
        }
        else
        {
            $valid = self::getValidator($this->item, $field->name ? $field->name : $field->id);

            if ( $valid->type == 'required' )
            {
                $field->validator->type = 'required';
            }
        }

        return $field;
    }

    public function getFields($itemForce, $elementId = null)
    {
        $fields = self::getSessionValue('fields', $itemForce);

        if ( is_array($fields) )
        {
            foreach ( $fields as $line => $info )
            {
                $field = unserialize($info);

                $result[$field->id] = $field;

                if ( $elementId && $elementId == $field->id )
                {
                    return $field;
                }
            }
        }
        if ( !$elementId )
        {
            return $result;
        }
    }

    /**
     * Define os campos que serão utilizados na classe
     *
     * @param array $fields array de objetos
     */
    public function setFields($fields)
    {
        if ( $fields )
        {
            $fields = $this->parseFields($fields);
            $this->setControls($fields);
            $fieldNames = $this->namesArray;
            self::setSessionValue('fieldNames', $fieldNames, $this->item);
            $classesNames = $this->classesArray;
            self::setSessionValue('classesNames', $classesNames, $this->item);
        }
    }

    /**
     * Define as colunas que serão criadas na Tabela
     *
     * @param array $columns array de objetos MGridColum
     */
    public function setColumns($columns)
    {
        /* if ( !$columns )
          {
          return;
          } */

        $tempColumns = null;
        if ( $columns && is_array($columns) )
        {
            //crias os títulos e as colunas na seção
            foreach ( $columns as $line => $info )
            {
                if ( $info->visible == true )
                {
                    $titles[] = $info->title;
                    $temp = new StdClass();
                    $temp->align = $info->align;
                    $temp->title = $info->title;
                    $temp->width = $info->width;
                    $temp->visible = $info->visible;
                    $temp->options = $this->item . '_' . $info->options; //concatena o nome do subdetail na coluna
                    $temp->order = $info->order;
                    $tempColumns[] = $temp;
                }
            }
        }

        self::setSessionValue('titles', $titles, $this->item);
        self::setSessionValue('columns', $tempColumns, $this->item);
    }

    /**
     * Return a array of columns
     * You can use in static or object way
     *
     * @param String $itemForce the id of the subDetail
     * @return Return a array of columns
     */
    public function getColumns($itemForce = null)
    {
        return self::getSessionValue('columns', $itemForce);
    }

    public function getColumn($columnId, $itemForce = null)
    {
        $columns = self::getColumns($itemForce);

        if ( is_array($columns) )
        {
            foreach ( $columns as $line => $info )
            {
                if ( $info->options == $columnId )
                {
                    return $info;
                }
            }
        }

        return false;
    }

    /**
     * addData a column to this table.
     *
     *
     * @param title   = inplemented
     * @param align   = inplemented
     * @param nowrap  = no
     * @param width   = inplemented
     * @param visible = inplemented
     * @param options = inplemented
     * @param order   = no
     * @param filter  = no
     *
     */
    private function addColumn($title, $align = 'left', $nowrap = 'notImplemented', $width = NULL, $visible = TRUE, $options = NULL, $order = 'notImplemented', $filter = 'notImplemented')
    {
        $titles = self::getSessionValue('titles', $this->item);
        $columns = self::getSessionValue('columns', $this->item);

        //Inserir na seção tambem
        $columns[] = new MGridColumn($title, $align, $nowrap, $width, $visible, $options, $order, $filter);

        if ( $visible == true )
        {
            $titles[] = $title;
        }

        self::setSessionValue('titles', $titles, $this->item);
        self::setSessionValue('columns', $columns, $this->item);
    }

    /**
     * Set the validators
     *
     * @param $validators
     */
    public function setValidators($validators = null)
    {
        //limpa a seção caso seja null
        if ( is_null($validators) )
        {
            self::setSessionValue('validators', '', $this->item);
            return;
        }

        //converte para array, caso venha um único objeto
        //previne erro de tela branca
        if ( is_object($validators) )
        {
            $validators = array( $validators );
        }

        if ( is_array($validators) )
        {
            foreach ( $validators as $line => $info )
            {
                $validators[$line] = $this->convertValidator($info);
            }
        }

        if ( is_array($validators) )
        {
            foreach ( $validators as $line => $info )
            {
                $validators[$line]->field = $this->item . '_' . $validators[$line]->field;
            }
        }

        if ( is_array($validators) )
        {
            self::setSessionValue('validators', $validators, $this->item);
        }
    }

    protected function convertValidator($validator)
    {
        $valid = new StdClass();
        $valid->id = $validator->id;
        $valid->field = $validator->field;
        $valid->min = $validator->min;
        $valid->max = $validator->max;
        $valid->type = $validator->type;
        $valid->chars = $validator->chars;
        $valid->mask = $validator->mask;
        $valid->checker = $validator->checker;
        $valid->msgerr = $validator->msgerr;
        $valid->html = $validator->html;
        $valid->label = $validator->label;
        $valid->value = $validator->value;
        $valid->hint = $validator->hint;
        $valid->regexp = $validator->regexp;
        $valid->expression = $validator->expression;
        $valid->class = get_class($validator);

        return $valid;
    }

    /**
     * Adiciona uma ação personalizada a tabela
     */
    public function addAction($phpFunction, $imgUrl)
    {
        $action = new StdClass();
        $action->event = "AddTableResult" . $this->item;
        $action->jsFunction = "AddTableResult" . $this->item;
        $action->phpFunction = $phpFunction;
        $action->img = $imgUrl;
        $actions = self::getSessionValue('actions', $this->item);
        $actions[$phpFunction] = $action;
        self::setSessionValue('actions', $actions, $this->item);
    }

    /**
     * Gera uma a string de uma ação
     *
     * @return o html correspondente a uma ação
     */
    protected function generateActionString($i, $itemForce = NULL)
    {
        $MIOLO = MIOLO::getInstance();

        if ( $this )
        {
            $item = $this->item;
        }
        if ( $itemForce )
        {
            $item = $itemForce;
        }

        $actions = self::getSessionValue('actions', $item);

        $tempString = null;
        $result = null;

        $actionColumWidth = 0;

        if ( $actions )
        {
            foreach ( $actions as $line => $info )
            {
                $link = null;
                $linkImg = null;
                $link = 'javascript:' . MUtil::getAjaxAction($info->phpFunction, array( 'mSubDetail' => $item, "arrayItemTemp{$item}" => $i ));
                $linkImg = new MImageLink("link$i", null, $link, $info->img);
                $result .= $linkImg->generate();
                $actionColumWidth += self::IMGWIDTH;
            }
        }
        $div = new MDiv('action' . rand(), $result);
        $div->addStyle('width', $actionColumWidth . 'px');

        return $div->generate();
    }

    /**
     * Trata os dados de acordo com a situação retirando ou adicionando o id do componente a frente dos dados.
     *
     * @param Object $data dados a serem parseado
     * @param String $itemForce id do subdetail
     * @param Boolean $remove se é para remover (caso contrário é adição)
     * @return Object
     */
    public function parseData($data, $itemForce, $remove = false)
    {
        if ( is_array($data) )
        {
            foreach ( $data as $line => $info )
            {
                $newData = array( );
                $infoArray = (array) $info;

                foreach ( $infoArray as $l => $i )
                {

                    if ( $l == 'dataStatus' )
                    {
                        $newData[$l] = $i;
                    }

                    $pos_underline = stripos($l, '_');

                    if ( strrpos($l, "\0") > 0 )
                    {
                        //FIXME: POG feita para mapear campos private da classe
                        //http://br.php.net/manual/en/language.types.array.php
                        $aux = explode("\0", $l);
                        $l = $aux[2];
                    }

                    if ( !is_object($i) )
                    {
                        //se esta adicinando
                        if ( !$remove )
                        {
                            if ( $pos_underline > 0 )
                            {
                                if ( method_exists(get_class($info), "obter$l") )
                                {
                                    $function = 'obter' . $l;
                                    $newData[$l] = $info->$function();
                                }
                                else
                                {
                                    $newData[$l] = $i;
                                    $newData[$itemForce . '_' . $l] = $i; // isto foi colocado em função do MLookupContainer dentro do MSubDetail, para funcionar o campo _lookDescription
                                }
                            }
                            else
                            {
                                if ( method_exists(get_class($info), "obter$l") )
                                {
                                    $function = 'obter' . $l;
                                    $newData[$itemForce . '_' . $l] = $info->$function();
                                }
                                else
                                {
                                    $newData[$itemForce . '_' . $l] = $i;
                                }
                            }
                        }
                        else //se esta tirando
                        {
                            if ( $pos_underline > 0 )
                            {
                                $temp = explode('_', $l);

                                if ( $temp[2] ) //isto é feito pois o lookup tem um _ no seu nome, mas o MSubdetail precisa pegar o nome inteiro
                                {
                                    $temp[1] .= '_' . $temp[2];
                                }

                                $index = $temp[1] ? $temp[1] : $temp[0];

                                if ( method_exists(get_class($info), "obter$index") )
                                {
                                    $function = 'obter' . $index;
                                    $newData[$index] = $info->$function();
                                }
                                else
                                {
                                    if ( !is_array($newData[$index]) )
                                    {
                                        $newData[$index] = $i;
                                    }
                                }
                            }
                            else
                            {
                                if ( method_exists(get_class($info), "obter$l") )
                                {
                                    $function = 'obter' . $l;
                                    $newData[$l] = $info->$function();
                                }
                                else
                                {
                                    // segurança para não sobrescrever dados ja existentes,
                                    // útil no caso no subdetail interno, em outros caso não interfere
                                    if ( !is_array($newData[$l]) )
                                    {
                                        $newData[$l] = $i;
                                    }
                                }
                            }
                        }
                    }
                }

                $result[] = (object) $newData;
            }
        }

        if ( !$remove )
        {
            return self::parseFieldData($result, $itemForce);
        }

        return $result;
    }

    /**
     * Trabalha os dados para reconhecer automaticamente o campoDescription dentre outros
     *
     * Os dados ja devem vir trabalhados com o nome do subdetail na frente, por isso $result.
     *
     * @param $result
     * @return unknown_type
     */
    public function parseFieldData($result, $itemForce)
    {
        if ( is_array($result) )
        {
            $fieldNamesD = self::getSessionValue('fieldNames', $itemForce);
            $classesNamesD = self::getSessionValue('classesNames', $itemForce);

            if ( $fieldNamesD )
            {
                foreach ( $fieldNamesD as $line => $info )
                {
                    $classesData[$info] = $classesNamesD[$line];
                }
            }

            foreach ( $result as $line => $info )
            {
                $array = (array) $info;
                $newData = null;

                foreach ( $fieldNamesD as $index => $fieldId )
                {
                    if ( !isset($array[$fieldId]) )
                    {
                        $array[$fieldId] = null; //FIXME adicionaria os campos faltantes nos dados, faria funcionar o 'Não' no caso do checkbox, mas não esta funcionando, esta limpando os campos no setData
                    }
                }

                foreach ( $array as $fieldId => $value )
                {
                    $valueDescription = null; //Reinicia a variável dentro do foreach
                    $field = self::getFields($itemForce, $fieldId);
                    $phpClasse = $classesData[$fieldId];
                    $fieldIdDescription = $fieldId . 'Description';

                    if ( $phpClasse == 'MCheckBox' )
                    {
                        $newData[$fieldIdDescription] = _M('No');

                        if ( isset($value) )
                        {
                            $newData[$fieldIdDescription] = _M('Yes');

                            if ( is_string($value) && $value == DB_FALSE )
                            {
                                $newData[$fieldIdDescription] = _M('No');
                            }
                        }
                    }

                    //para o caso do MSelection
                    if ( $field->options )
                    {
                        $options = $field->options;

                        //procura string de descrição do dado atual na grid
                        if ( $value )
                        {
                            //se as opções forem  array multi dimensional
                            if ( is_array($options[0]) )
                            {
                                foreach ( $options as $index => $option )
                                {
                                    if ( $option[0] == $value )
                                    {
                                        $valueDescription = $option[1];
                                    }
                                }
                            }
                            else //se for array simples
                            {
                                $valueDescription = $field->options[$value];
                            }

                            //somente define os dados de descrição, caso já não existam
                            if ( !$newData[$fieldIdDescription] )
                            {
                                $newData[$fieldIdDescription] = $valueDescription;
                            }
                        }
                    }


                    // se for um campo de descrição e não contiver dados limpa a descrição evitando assim aparecer o '--Select--' no tabela de dados
                    if ( strpos($fieldId, 'Description') && !strpos($fieldId, 'lookup') )
                    {
                        $realFieldId = str_replace('Description', '', $fieldId);

                        if ( !$result[$line]->$realFieldId )
                        {
                            $newData[$fieldId] = null;
                        }
                        else
                        {
                            $newData[$fieldId] = $value;
                        }
                    }
                    else
                    {
                        //define o dado padrão
                        $newData[$fieldId] = $value;
                    }
                }

                $result[$line] = (object) $newData;
            }
        }

        return $result;
    }

    /**
     * Custom generate to this class it implements some MGridColumn function:
     * List:
      $   title   = inplemented
      $   align   = inplemented
      $   nowrap  = no
      $   width   = inplemented
      $   visible = inplemented
      $   options = inplemented
      $   order   = no
      $   filter  = no
     */
    public function generate()
    {
        $module = MIOLO::getCurrentModule();
        $MIOLO = MIOLO::getInstance();

        $item = $this->item;
        //pega o nome da classe do form atual (que é pai do subdetail )
        self::setSessionValue('form', get_class($this->parent), $item);

        $sendArray['mSubDetail'] = $item;

        //function called only first time that component is rendered
        //mount buttons
        if ( $this->readonly )
        {
            $this->setControls(null);
        }

        if ( $this->showButtons && !$this->readonly )
        {
            $addImg = $MIOLO->getUI()->getImage(NULL, 'button_add.png');
            $updImg = $MIOLO->getUI()->getImage(NULL, 'button_apply.png');
            $clearImg = $MIOLO->getUI()->getImage(NULL, 'button_clear.png');

            $span = new MSpan('label_clearData', _M('Clear'));
            $buttons[0] = new MButton('clearData' . $item, $span->generate(), MUtil::getAjaxAction('clearTableFields', $sendArray), $clearImg);

            if ( $this->updateButton )
            {
                $span = new MSpan('label_addData', _M('Add'));
                $buttons[1] = new MButton('addData' . $item, $span->generate(), MUtil::getAjaxAction('forceAddToTable', $sendArray), $addImg);
                $span = new MSpan('label_updateData', _M('Update'));
                $buttons[2] = new MButton('updateData' . $item, $span->generate(), MUtil::getAjaxAction('addToTable', $sendArray), $updImg);
            }
            else
            {
                $span = new MSpan('label_addData', _M('Add'));
                $buttons[1] = new MButton('addData' . $item, $span->generate(), MUtil::getAjaxAction('addToTable', $sendArray), $addImg);
            }

            $divButtons = new MDiv('divButtonsSubdetail', $buttons, 'mSubDetailButtons', 'style=');
            $this->addControl($divButtons);
        }

        $div = new MDiv('div' . $item, self::getTable($item, $this->readonly));

        if ( $this->overflowWidth )
        {
            $div->addStyle('overflow-x', $this->overflowType);
            $div->addStyle('width', $this->overflowWidth . 'px');
        }
        else
        {
            $div->addStyle('width', '100%');
        }

        if ( $this->overflowHeight )
        {
            $div->addStyle('overflow-y', $this->overflowType);
            $div->addStyle('height', $this->overflowHeight . 'px');
        }

        $this->controls->add($div);

        $index = new MTextField('mSubdetail[]', $item, '');
        $index->addStyle('display', 'none');
        $this->controls->add($index);

        if ( !$this->readonly )
        {
            $this->caption = $this->title . ' (' . _M('Insert mode') . ')';
        }
        else
        {
            $this->caption = $this->title . ' (' . _M('Read only mode') . ')';
        }

        return parent::generate();
    }

    public function order($columnName, $orderType = null, $itemForce = null)
    {
        //tentar pegar o id do this caso não passe o itemForce
        if ( $this && !$itemForce )
        {
            $itemForce = $this->item;
        }

        //pega os dados do subdetail
        $itensData = self::getData($itemForce, false);

        //se não tiver tipo de ordenação faz ordenação automática, a qual inverte a ordenação atual sempre
        if ( !$orderType & !is_array($columnName) )
        {
            $orderType = self::getSessionValue('orderType_' . $columnName, $itemForce);

            if ( !$orderType )
            {
                $orderType = 'desc';
            }

            $orderType = $orderType == 'asc' ? 'desc' : 'asc';

            //define dados na sessão o orderType
            self::setSessionValue('orderType_' . $columnName, $orderType, $itemForce);
        }

        //define na seção as colunas ordenadas
        self::setSessionValue('orderColumnName', $columnName, $itemForce);

        //transforma columnName em array caso não seja
        if ( !is_array($columnName) )
        {
            $columnName = array( $columnName );
        }

        if ( !is_array($orderType) )
        {
            $orderType = array( $orderType );
        }

        //adiciona o nome do subdetail na coluna caso seja necessário
        foreach ( $columnName as $line => $info )
        {
            if ( stripos($info, '_') === false )
            {
                $columnName[$line] = $itemForce . '_' . $info;
            }
        }

        $orderArray = array( );

        //monta arrays de ordenação
        if ( is_array($itensData) )
        {
            foreach ( $itensData as $line => $info )
            {
                foreach ( $columnName as $index => $name )
                {
                    //pega pela descrição, caso de selections, checkbox e radios ...
                    $key = $info->{$name . 'Description'};

                    //  se não achou pela descrição pega o dado normal do campo
                    if ( !$key )
                    {
                        $key = $info->$name;
                    }

                    $key = strtoupper($key); // colaca tudo em maisculas para não interferir na ordenação
                    $orderArray[$name][] = $key;
                }

                $newItensData[] = $info; //adiciona ao novo array
            }
        }

        foreach ( $columnName as $line => $name )
        {
            $funcArgs[] = &$orderArray[$name];
            $funcArgs[] = strtolower($orderType[$line]) == 'desc' ? SORT_DESC : SORT_ASC;
            ;
        }

        $funcArgs[] = &$newItensData;

        call_user_func_array('array_multisort', &$funcArgs);

        $itensData = array( );

        //remonta o array original com índices com numeração
        if ( is_array($newItensData) )
        {
            $i = 0;
            foreach ( $newItensData as $line => $info )
            {
                $itensData[$i] = $info;
                $i++;
            }
        }

        //pega e define imagens a serem utilizadas
        $MIOLO = MIOLO::getInstance();
        $ui = $MIOLO->getUI();
        $upImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'down.png');
        $downImg = $ui->getImageTheme($MIOLO->getTheme()->getId(), 'up.png');

        //adiciona por javascript as setas de ordenação

        foreach ( $columnName as $line => $info )
        {
            $img = $orderType[$line] == 'asc' ? $upImg : $downImg;

            $MIOLO->page->onload("
			    newImg = document.createElement('img');
			    newImg.src= '$img';
			    newImg.id = 'order_{$columnName[$line]}_img';
			    newImg.style.marginLeft = '10px';

			    if (element = document.getElementById('order_{$columnName[$line]}'))
			    {
			        element.parentNode.appendChild( newImg );
			    }
	        ");
        }

        //atualiza o subdetail por ajax
        self::update($itensData, $itemForce);
    }

    public function hasData($item = null)
    {
        if ( $item )
        {
            return count(self::getData($item)) > 0;
        }
        else
        {
            return count($this->getData()) > 0;
        }

        return false;
    }

    public function setValue($data)
    {
        if ( !self::isFirstAccess() || !$data )
        {
            return;
        }

        $data = self::parseData($data, $this->item);
        $controls = self::getFields($this->item);

        // Verifica atributo que indica se o autocomplete dos lookups deve ser disparado
        if ( self::$doLookupAutocomplete )
        {
            // Dispara o autocomplete dos lookups presentes na subdetail
            $data = self::lookupAutocomplete($data, $controls);
        }

        self::clearData($this->item);
        self::addData($data, $this->item);
        self::getData($this->item, false); // Chamado para reordenar os índices
    }

    public function getValue($value)
    {
        self::getData($this->item);
    }

    public static function getTable($item, $readOnly = false)
    {
        $titles = self::getSessionValue('titles', $item);
        $columns = self::getSessionValue('columns', $item);
        $sessionActions = self::getSessionValue('actions', $item);

        //transforma os títulos em links
        if ( self::getOrder($item) && !$readOnly )
        {
            foreach ( $titles as $line => $info )
            {
                if ( $columns[$line]->order )
                {
                    $link = MUtil::getAjaxAction('order', array( 'mSubDetail' => $item, 'mSubDetailOrderField' => $columns[$line]->options ));
                    $titles[$line] = new MLinkButton('order_' . $columns[$line]->options, $info, $link);
                }
            }
        }

        //adiciona coluna de ações caso não seja somente leitura e tenha ações
        if ( is_array($sessionActions) && !$readOnly )
        {
            $titles = array_merge(array( _M('Actions') ), $titles);

            $temp = new StdClass();
            $temp->align = 'left';
            $temp->title = _M('Actions');
            $temp->width = '';
            $temp->visible = true;
            $temp->options = '';

            $columns = array_filter($columns);
            $columns = array_merge(array( $temp ), $columns); //limpa
        }

        $table = new MTableRaw('', array( ), $titles);
        $table->setAlternate(true);
        $table->addAttribute('width', '100%');
        $table->addStyle('width', '100%');
        $table->addStyle('width', '100%');
        $table->addAttribute("Style", "width:100%");
        $table->setCellAttribute(0, 0, "width", '1%');

        $itens = self::getData($item, false);

        //monta os dados para a tabela
        if ( $itens )
        {
            foreach ( $itens as $i => $info )
            {
                if ( $info->dataStatus != self::STATUS_REMOVE )
                {
                    $encodedInfo = null;
                    foreach ( $info as $l => $il )
                    {
                        if ( is_string($il) )
                        {
                            $encodedInfo->$l = urlencode($i);
                            $encodedInfo->$l = str_replace("\n", '\n', $encodedInfo->$l);
                        }
                        else
                        {
                            $encodedInfo->$l = $il;
                        }
                    }
                    if ( !$readOnly )
                    {
                        $actions[$i] = self::generateActionString($i, $item);
                    }
                    $args = null;
                    if ( $sessionActions )
                    {
                        if ( !$readOnly )
                        {
                            $actions[$i] = self::generateActionString($i, $item);
                            $args[] = $actions[$info->arrayItem];
                        }
                    }
                    foreach ( $columns as $line => $column )
                    {
                        if ( $column->visible == true )
                        {
                            if ( $column->options )
                            {
                                //pega por descrição caso exista
                                $opt = $column->options;
                                $tempOpt = $opt . 'Description';
                                if ( $info->$tempOpt )
                                {
                                    $columnData = $info->$tempOpt;
                                }
                                else
                                {
                                    $columnData = $info->$opt;
                                }

                                if ( is_array($columnData) )
                                {
                                    $columnData = '<pre>' . print_r($columnData, 1) . '</pre>';
                                }

                                $args[] = $columnData;

                                $cellId = $column->options . '_' . $i;

                                $aplyCellAttributeId = $i;

                                if ( !$readOnly )
                                {
                                    $aplyCellAttributeId = $i + 1;
                                }

                                $table->setCellAttribute($aplyCellAttributeId, $line, 'id', $cellId);
                                //mSubDetailCellEditId
                                if ( $column->order && !$readOnly )
                                {
                                    $table->setCellAttribute($aplyCellAttributeId, $line, 'onclick', MUtil::getAjaxAction('editCell', array( 'mSubDetail' => $item, 'mSubDetailCellEditId' => $cellId )));
                                }

                                //alinhamento
                                if ( $column->align )
                                {
                                    $table->setCellAttribute($aplyCellAttributeId, $line, 'align', $column->align);
                                }
                                //tamanho
                                if ( $column->width )
                                {
                                    $table->setCellAttribute($aplyCellAttributeId, $line, 'width', $column->width);
                                }
                            }
                        }
                    }
                    $tableData[] = $args;
                }
            }
        }

        $table->array = $tableData; //seta os dados no array

        return $table;
    }

    /**
     * Update the visual Component with some data. Make a ajax response
     *
     * @param array $data array of object
     * @param string $itemForce the name of the table
     */
    public static function update($data = NULL, $name)
    {
        $MIOLO = MIOLO::getInstance();
        self::setData($data, $name);
        $MIOLO->ajax->setResponse(self::getTable($name), 'div' . $name);
    }

    public static function removeFromTable($args)
    {
        $arrayItem = 'arrayItemTemp' . $args->mSubDetail;
        $MIOLO = MIOLO::getInstance();
        $indexData = self::getData($args->mSubDetail, false); //dados da sessão
        $lineData = $indexData[$args->$arrayItem]; //dados na sessão da linha atual

        if ( $lineData->dataStatus == self::STATUS_ADD )
        {
            self::removeData($args->$arrayItem, $args->mSubDetail);
        }
        else
        {
            $itensData = self::getSessionValue('contentData', $args->mSubDetail);
            $itensData[$args->$arrayItem]->dataStatus = self::STATUS_REMOVE;
            self::setSessionValue('contentData', $itensData, $args->mSubDetail);
        }

        self::update(null, $args->mSubDetail);
    }

    public static function forceAddToTable($args)
    {
        unset($args->arrayItemTemp{$args->mSubDetail});
        self::addToTable($args);
    }

    public static function addToTable($data)
    {
        $subdetailId = $data->mSubDetail;
        $arrayItem = 'arrayItemTemp' . $data->mSubDetail;
        $childs = self::getChilds($subdetailId);

        //detecta os filhos e capta os dados deles
        if ( is_array($childs) )
        {
            foreach ( $childs as $line => $childId )
            {
                if ( $childId != $data->mSubDetail ) // se não for o mSubdetail atual para não ficar recursivo
                {
                    $innerData = self::getData($childId);
                    $data->$childId = $innerData; //pega os dados do mSubdetail filho e adiciona no pai
                    self::clearData($childId);
                    self::update(null, $childId); //limpa o filho e atualiza
                }
            }
        }

        $temp = null;
        $module = MIOLO::getCurrentModule();
        $validators = self::getValidators($subdetailId);
        $requireds = array();

        foreach ( $validators as $validator )
        {
            if ( $validator->type != 'required' )
            {
                continue;
            }

            if ( !MIOLO::_REQUEST($validator->field) )
            {
                new MMessageWarning(_M('Missing required fields!'));
                return;
            }
        }

        //se estiver editando
        if ( $data->$arrayItem || $data->$arrayItem === '0' )
        {
            //obtem o dado atual da sessão
            $sData = self::getDataItem($data->$arrayItem, $subdetailId);
            //e seta o dataStatus que esta na sessão para o registro atual
            $data->dataStatus = $sData->dataStatus;

            //caso o status seja diferente de adição, troca para editado
            if ( $data->dataStatus != self::STATUS_ADD )
            {
                $data->dataStatus = self::STATUS_EDIT;
            }
            //atualiza os dados específicos
            self::defineData($data->$arrayItem, $data, $data->mSubDetail);
        }
        //se estiver adicionando
        else
        {
            $data->dataStatus = self::STATUS_ADD;
            self::addData($data, $data->mSubDetail);
        }

        //limpa os campos e define o foco no primeiro
        self::clearFields($data->mSubDetail);

        self::update(null, $data->mSubDetail);
    }

    /**
     * Define o focus do primeiro campo do subdetail
     *
     * @return unknown_type
     */
    private static function setFocus($mSubDetailId)
    {
        $MIOLO = MIOLO::getInstance();
        $fieldNames = self::getSessionValue('fieldNames', $mSubDetailId);

        if ( $fieldNames[0] )
        {
            $fieldNameFocus = trim($fieldNames[0]);
            $MIOLO->page->onload("dojo.byId('$fieldNameFocus').focus();");
        }
    }

    private static function clearFields($name)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        $addImg = $MIOLO->getUI()->getImage(NULL, 'button_add.png');
        $clearImg = $MIOLO->getUI()->getImage(NULL, 'button_clear.png');
        $fieldNames = self::getSessionValue('fieldNames', $name);

        //passa pelos campos setando-os como vazio
        if ( is_array($fieldNames) )
        {
            foreach ( $fieldNames as $line => $info )
            {
                $info = str_replace('Description', '', $info); //evita que se limpe o conteúdo dos $valueDescription
                $temp .= "msubdetail.updateField('$info','');";
            }
        }

        $temp .= "msubdetail.updateButtons( '$name', '$addImg', '$clearImg');";
        $temp .= "dojo.byId('arrayItemTemp{$name}').value=''"; //limpa o arrayItem

        $MIOLO->page->onload($temp);

        self::setFocus($name);
    }

    /**
     * Limpa o valor prenchido nos campos
     *
     * @param object $args
     */
    public static function clearTableFields($args)
    {
        $childs = self::getChilds($args->mSubDetail);

        //detecta os filhos e os limpa também
        if ( is_array($childs) )
        {
            foreach ( $childs as $line => $info )
            {
                if ( $info != $args->mSubDetail ) // se não for o subdetail atual para não ficar recursivo
                {
                    self::clearData($info);
                    self::update(null, $info); //limpa o filho e atualiza
                }
            }
        }

        self::clearFields($args->mSubDetail);

        self::update(null, $args->mSubDetail);
    }

    public static function upFromTable($data)
    {
        $object = $data->mSubDetail;
        $data->sessionItem = $data->mSubDetail;
        $nivel = $data->arrayItem;

        if ( !$nivel )
        {
            $nivel = $data->arrayItemTemp{$args->mSubDetail};
        }
        if ( $nivel != 0 )
        {
            $tempObjAtual = self::getDataItem($nivel, $object);
            $tempObjSuperior = self::getDataItem($nivel - 1, $object);
            self::defineData($nivel - 1, $tempObjAtual, $object);
            self::defineData($nivel, $tempObjSuperior, $object);
        }

        self::update(null, $args->mSubDetail);
    }

    public static function downFromTable($data)
    {
        $object = $data->mSubDetail;
        $data->sessionItem = $data->mSubDetail;
        $nivel = $data->arrayItem;

        if ( !$nivel )
        {
            $nivel = $data->arrayItemTemp{$args->mSubDetail};
        }

        $item = self::getData($object, false);

        if ( $nivel < count($item) - 1 )
        {
            $tempObjAtual = self::getDataItem($nivel, $object);
            $tempObjInferior = self::getDataItem($nivel + 1, $object);
            self::defineData($nivel + 1, $tempObjAtual, $object);
            self::defineData($nivel, $tempObjInferior, $object);
        }

        self::update(null, $args->mSubDetail);
    }

    /**
     * Função chamada automaticamente ao apertar editar na tabela, define os valores dos campos
     *
     * @param object $data ajax miolo object
     */
    public static function editFromTable($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        self::moveDataToFields($args);
        $arrayItem = 'arrayItemTemp' . $args->mSubDetail;
        $updateButton = self::getUpdateButton($args->mSubDetail);

        if ( !$updateButton )
        {
            $imgAplicar = $MIOLO->getUI()->getImage(NULL, 'button_apply.png');
            $imgCancelar = $MIOLO->getUI()->getImage(NULL, 'button_cancel.png');
            $temp .= "msubdetail.updateButtons('$args->mSubDetail', '$imgAplicar', '$imgCancelar', 'aplicar');";
        }

        $temp .= "dojo.byId('{$arrayItem}').value='{$args->$arrayItem}'";

        //resposta falsa só para executar o js
        $MIOLO->page->onload($temp);
        $MIOLO->ajax->setResponse(null, 'divResponse');
    }

    public function moveDataToFields($args)
    {
        $MIOLO = MIOLO::getInstance();
        $childs = self::getChilds($args->mSubDetail);
        $arrayItem = 'arrayItemTemp' . $args->mSubDetail;

        //detecta filhos e seta os dados deles
        if ( is_array($childs) )
        {
            foreach ( $childs as $line => $childId )
            {
                if ( $childId != $args->mSubDetail ) // se não for o subdetail atual para não ficar recursivo
                {
                    $valuesSB = self::getDataItem($args->$arrayItem, $args->mSubDetail);
                    $subData = $valuesSB->$childId;

                    if ( !$subData )
                    {
                        $name = $args->mSubDetail . "_" . $childId;
                        $subData = $valuesSB->$name;
                    }

                    //se ainda não achou dados limpa o subdetail interno
                    if ( !$subData )
                    {
                        self::clearData($childId);
                    }

                    self::update($subData, $childId);
                }
            }
        }

        $module = MIOLO::getCurrentModule();
        $values = self::getDataItem($args->$arrayItem, $args->mSubDetail);
        $fieldNames = self::getSessionValue('fieldNames', $args->mSubDetail);

        if ( is_array($fieldNames) )
        {
            foreach ( $fieldNames as $line => $info )
            {
                $value = $values->$info;
                $value = str_replace("\n", '\n', $value);
                $value = str_replace("'", "\'", $value);
                $value = str_replace('"', "\"", $value);
                $temp .= "msubdetail.updateField('$info','$value');\n";
            }
        }

        $MIOLO->page->onload($temp);
    }

    public function duplicate($args)
    {
        $MIOLO = MIOLO::getInstance();
        self::moveDataToFields($args);
        $MIOLO->ajax->setResponse(null, 'divResponse');
    }

    /**
     * Evento chamado ao clicar em uma célula editável
     */
    public function editCell($args)
    {
        $MIOLO = MIOLO::getInstance();
        $cellId = $args->mSubDetailCellEditId;
        $id = explode('_', $args->mSubDetailCellEditId);
        $arrayItem = $id[2];
        $id = $id[0] . '_' . $id[1];
        $field = self::getFields($args->mSubDetail, $id);
        $itemData = self::getDataItem($arrayItem, $args->mSubDetail);
        $value = $itemData->$id;

        if ( is_object($field) )
        {

            $idEx = $field->id . '_ex';
            $field->id = $idEx;
            $field->name .= '_ex';

            if ( $field->class == 'mtextfield' )
            {
                $field = new MTextField($field->name, $value);
            }
            else
            if ( $field->class == 'MSelection' || $field->class == 'mselection' )
            {
                $field = new MSelection($field->name, $value, null, $field->options);
            }

            $field = self::parseFields($field, false, $args->mSubDetail, true);

            $field->addStyle('width', '100%');
            $field->addStyle('height', '100%');
        }

        //adiciona evento ao sair do campo, adiciona estilo e seta valor
        $ajaxAction = MUtil::getAjaxAction('editCellExit', array( 'mSubDetail' => $args->mSubDetail, 'mSubDetailCellEditId' => $cellId ));
        $js .= "
        innerElement = dojo.byId('$idEx');

        if (innerElement)
        {
            blur = dojo.connect(innerElement, 'onblur', function () { $ajaxAction ; dojo.disconnect(blur); });
            keyPress = dojo.connect(innerElement, 'onkeypress', function (e) {if (e.keyCode == dojo.keys.ENTER){ $ajaxAction ; dojo.disconnect( keyPress ); }  });
            innerElement.focus();
        }";

        $MIOLO->page->onload($js);
        $MIOLO->ajax->setResponse($field, $cellId);
    }

    /**
     * Evento executado ao sair de uma célula editável
     */
    public function editCellExit($args)
    {
        $cellId = $args->mSubDetailCellEditId;
        $id = explode('_', $args->mSubDetailCellEditId);
        $id[0] .= '_' . $id[1];
        $fieldId = $id[0] . '_ex';
        $fieldIdDesc = $id[0] . '_exDescription';
        $dataId = $id[0];
        $dataIdDesc = $id[0] . 'Description';
        $arrayItem = $id[2];

        $value = $args->$fieldIdDesc ? $args->$fieldIdDesc : $args->$fieldId;
        $value = addSlashes($value); //FIXME isso não funciona, precisa funcionar para aceitar aspas simples


        $itemData = self::getDataItem($arrayItem, $args->mSubDetail);
        $itemData->$dataId = $args->$fieldId;
        $itemData->$dataIdDesc = $args->$fieldIdDesc;
        self::defineData($arrayItem, $itemData, $args->mSubDetail);

        $MIOLO = MIOLO::getInstance();
        $MIOLO->ajax->setResponse($value, $cellId);
    }

    /**
     * Limpa o campo dataStatus de todos os dados do subdetail passado
     *
     * Função chamada após enviar o SubDetail para o banco de dados, mas continuando a sua utilização.
     *
     * Se for STATUS_REMOVE, quer dizer, é pra remover no banco, após a remoção, o registro é removido do subdetail
     * Se for STATUS_ADD o registro foi inserido no banco e pode continuar aqui, mas não precisa mais fazer nada, removemos então o dataStatus
     * O caso do STATUS_EDIT é o mesmo do STATUS_ADD, o registro foi editado e podemos remover o dataStatus
     *
     * @param $name
     * @return void
     */
    public static function clearDataStatus($name)
    {
        $data = self::getData($name);

        if ( is_array($data) )
        {
            foreach ( $data as $line => $info )
            {
                if ( $info->dataStatus == self::STATUS_REMOVE )
                {
                    unset($data[$line]);
                }
                else
                {
                    unset($data[$line]->dataStatus);
                }
            }
        }

        self::setData($data, $name);
    }

    public static function setSessionValue($var, $value, $item)
    {
        $MIOLO = MIOLO::getInstance();
        $session = $MIOLO->getSession();
        $object = $session->getValue(MIOLO::getCurrentAction() . ':' . $item);
        $object->$var = $value;
        //diferencia por handler
        $session->setValue(MIOLO::getCurrentAction() . ':' . $item, $object);
    }

    public static function getSessionValue($var, $item)
    {
        $MIOLO = MIOLO::getInstance();
        //diferencia por handler
        $object = $MIOLO->getSession()->getValue(MIOLO::getCurrentAction() . ':' . $item);
        return $object->$var;
    }

    /**
     * Get validators
     *
     * @return $validators (Array)
     */
    public static function getValidators($item)
    {
        return self::getSessionValue('validators', $item);
    }

    public static function getValidator($item, $id)
    {
        $validators = self::getValidators($item);

        if ( strpos($id, $item . '_') !== 0 )
        {
            $id = $item . '_' . $id;
        }

        if ( is_array($validators) )
        {
            foreach ( $validators as $line => $valid )
            {
                if ( $valid->field == $id )
                {
                    return $valid;
                }
            }
        }
    }

    /**
     * getData Item all itens of table/session, organized with arrayItem
     * Each time u call this function the session is cleanned and rewrited
     */
    public static function getData($itemForce = NULL, $final = true)
    {
        if ( $this )
        {
            $item = $this->item;
        }
        if ( $itemForce )
        {
            $item = $itemForce;
        }

        //get the required fields of the subdetail
        $fieldNames = self::getSessionValue('fieldNames', $item);
        $classesNames = self::getSessionValue('classesNames', $item);

        if ( $fieldNames )
        {
            foreach ( $fieldNames as $line => $info )
            {
                $info2 = str_replace('Description', '', $info);
                $fieldNames[] = $info2 . 'Description'; //adiciona suporte a campo campoDescription (usado no MSelection e MCheckBox)
                $classesData[$info2] = $classesNames[$line];

                $info3 = str_replace($itemForce . '_', '', $info); //tira o id do subdetail do nome
                $fieldNames[] = $info3;
                $classesData[$info3] = $classesNames[$line];
            }

            $fieldNames[] = 'arrayItem';
            $fieldNames[] = 'dataStatus';
        }

        $itensData = self::getSessionValue('contentData', $item);

        self::setSessionValue('contentData', null, $item); // clearData realy need??
        //rewrite the session with correct arrayItem values
        $tempData = array( );

        if ( $itensData )
        {
            $x = 0;
            foreach ( $itensData as $line )
            {
                $newLine = null;

                $line->arrayItem = $x;
                $x++;

                foreach ( $line as $k => $l )
                {
                    //se tiver nome de campo na sessão, ou seja, subdetail ja foi instanciado, filtra os campos
                    if ( !$fieldNames || in_array($k, $fieldNames) )
                    {
                        $classe = strtolower($classesData[$k]); // classe PHP do campo

                        if ( !$newLine->$k )
                        {
                            $newLine->$k = $l;
                        }
                    }
                }

                $tempData[] = $newLine;
            }
        }

        self::setSessionValue('contentData', $tempData, $item);

        if ( $final )
        {
            $tempData = self::parseData($tempData, $itemForce, true);
        }

        return $tempData;
    }

    /**
     * getDataItem one item from table
     *
     * @param arrayItem the index of the item you wanna take
     */
    public static function getDataItem($arrayItem, $item)
    {
        $itensData = self::getSessionValue('contentData', $item);

        if ( is_array($itensData) )
        {
            foreach ( $itensData as $line => $info )
            {
                if ( $info->arrayItem == $arrayItem )
                {
                    return $info;
                }
            }
        }
    }

    /**
     * Set one item to table
     *
     * @param arrayItem the index to be seted
     * @param $obj the object to put into table
     */
    public static function defineData($arrayItem, $data, $item)
    {
        $itensData = self::getSessionValue('contentData', $item);
        $parseData = array( $data );
        $data = self::parseFieldData($parseData, $item);
        $itensData[$arrayItem] = $data[0];
        self::setSessionValue('contentData', $itensData, $item);
    }

    /**
     * addData some item to session/Table (You can pass an array or one item)
     * It is a recursive function.
     */
    public static function addData($data, $item)
    {
        if ( $data )
        {
            if ( is_array($data) )
            {
                foreach ( $data as $line => $info )
                {
                    self::addData($info, $item);
                }
            }
            else
            {
                $itensData = self::getSessionValue('contentData', $item);
                $parseData = array( $data );
                $data = self::parseFieldData($parseData, $item);

                //quando for duplicar os dataStatus sempre devem ser add, para forçar uma adição no banco de dados
                if ( MIOLO::_REQUEST('duplicar') )
                {
                    $data[0]->dataStatus = self::STATUS_ADD;
                }

                $itensData[] = $data[0];

                self::setSessionValue('contentData', $itensData, $item);
            }
        }
    }

    /**
     * Define the Data of the field.
     * It will clearData e add the passed data
     *
     * @param (array) the array of objects with all data
     */
    public static function setData($data, $itemForce)
    {
        if ( !$data )
        {
            return;
        }

        $data = self::parseData($data, $itemForce);
        $controls = self::getFields($itemForce);

        if ( self::$doLookupAutocomplete )
        {
            // Dispara o autocomplete dos lookups presentes na subdetail
            $data = self::lookupAutocomplete($data, $controls);
        }

        self::clearData($itemForce);
        self::addData($data, $itemForce);
        self::getData($itemForce, false); // chamado para reordenar os indices
    }

    /**
     * Função estática que executa o autocomplete dos lookups presentes no array passado ($controls)
     *
     * @param array $data
     * @param array $controls
     */
    public static function lookupAutocomplete($data, $controls)
    {
        foreach ( $controls as $line => $control )
        {
            if ( $control->class != 'mlookuptextfield' )
            {
                continue;
            }

            $lookup = new MLookupContainer($control->name, $control->value, $control->label, $control->module, $control->item);
            $lookup->setContext($control->module, $control->item, $control->related, $control->filter, $control->autoComplete);

            $relatedArray = explode(',', $control->related);

            foreach ( $data as $key => $line )
            {
                if ( !$line->{$control->name} )
                {
                    continue;
                }

                $extraData = $lookup->doAutoComplete($line->{$control->name});

                // busca dados dos lookup para cada linha do subdetail
                foreach ( $relatedArray as $rKey => $related )
                {
                    if ( !$data[$key]->$related )
                    {
                        $data[$key]->$related = $extraData[$rKey];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * clearData all itens of the table session
     */
    public static function clearData($itemForce)
    {
        self::setSessionValue('contentData', null, $itemForce);
    }

    /**
     *  Remove Data some item from Table, you need an Id.
     *  This id can be found is $item->arrayItem
     */
    public static function removeData($arrayItem, $item)
    {
        $itensData = self::getSessionValue('contentData', $item);
        unset($itensData[$arrayItem]);
        self::setSessionValue('contentData', $itensData, $item);
    }

    public static function ajaxHandler()
    {
        $MIOLO = MIOLO::getInstance();
        $args = MUtil::getAjaxActionArgs();
        $event = MIOLO::_REQUEST("{$MIOLO->page->getFormId()}__EVENTTARGETVALUE");

        //lista de eventos possíveis
        $possibleEvents = array( 'removeFromTable', 'duplicate', 'addToTable', 'forceAddToTable', 'upFromTable', 'downFromTable', 'editFromTable', 'clearTableFields', 'editCell', 'editCellExit', 'removeFromTable' );

        //chama a função especifica
        if ( in_array($event, $possibleEvents) )
        {
            self::$event($args);
        }

        //"order" tem padrão diferenciado
        if ( $event == 'order' )
        {
            self::$event($args->mSubDetailOrderField, null, $args->mSubDetail);
        }
    }

    /**
     * Método para alterar o atributo doLookupAutocomplete
     * Esse atributo indica se o autocomplete do lookup deve ser disparado ao fazer o setValue
     *
     * Importante: No setData, o atributo não é levado em consideração, pois o método é estático,
     * disparando o autocomplete de cada lookup
     *
     * @param boolean $doLookupAutocomplete
     */
    public function setDoLookupAutocomplete($doLookupAutocomplete)
    {
        self::$doLookupAutocomplete = $doLookupAutocomplete;
    }

    public static function isFirstAccess($step = NULL)
    {
        $MIOLO = MIOLO::getInstance();

        if ( !$step )
        {
            $step = $this->step;
        }

        $stepsData = $this->getAllStepData();

        $data = $stepsData[$step];

        // if is set, returns true
        return !$data;
    }
}

MSubDetail::ajaxHandler();

?>
