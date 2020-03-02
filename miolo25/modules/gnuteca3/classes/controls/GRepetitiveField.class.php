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
 * GRepetitiveField (before it was MAjaxTableRaw)
 * This class implements the repetitive field using tableraw, session and ajax VERSION 3;
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 * */
class GRepetitiveField extends MBaseGroup
{

    private $item = 'item';
    private $tableItem = '';
    private $actions = '';
    private $table = NULL;
    private $fields = NULL;
    private $showButtons = true;
    private $overflowWidth = NULL;
    private $overflowType = NULL;
    public $cleanFields = true;
    public $cleanHiddenF = false;
    public $actionCelWidth = false;
    public $transaction = NULL;
    protected $updateButton = false;
    protected $includeButton = false;
    public $gValidator;
    private $namesArray;
    protected $defaultValues = array();

    const HIDDEN_FIELD_PREFIX_NAME = "GRepetitiveField";

    /**
     * Default constructor, need to pass Session Item.
     */
    public function __construct($item, $title, $columns = NULL, $fields = NULL, $opts = true, $align = 'vertical')
    {
        parent::__construct($item, $title, null, $align, 'css', MControl::FORM_MODE_SHOW_NBSP);
        $module = MIOLO::getCurrentModule();
        $this->item = $item;
        $this->gValidator = new GValidators();

        $this->setShowLabel(true);

        //adiciona coluna padrão
        $this->addColumn(_M('Ações', $module), 'left', true, '10%', true, '');

        if ($columns)
        {
            $this->setColumns($columns);
        }

        $this->setFields($fields);
        $this->setUpdateButton(true);

        $this->setSessionValue('actions', null); //limpa as ações
        //adiciona ações padrão
        if ($opts)
        {
            if ($opts === true)
            {
                $this->addAction('editFromTable', 'table-edit.png', $module);
                $this->addAction('removeFromTable', 'table-delete.png', $module);
            }
            else if (is_array($opts))
            {
                if (in_array('gravaTag', $opts))
                {
                    $this->addAction('gravaTag', 'accept-16x16.png', $module);
                }
                
                if (in_array('edit', $opts))
                {
                    $this->addAction('editFromTable', 'table-edit.png', $module);
                }

                if (in_array('remove', $opts))
                {
                    $this->addAction('removeFromTable', 'table-delete.png', $module);
                }

                if (in_array('up', $opts))
                {
                    $this->addAction('upFromTable', 'table-up.png', $module);
                }

                if (in_array('down', $opts))
                {
                    $this->addAction('downFromTable', 'table-down.png', $module);
                }

                if (in_array('download', $opts)) //used in GFileUploader
                {
                    $this->addAction('downloadFromTable', 'table-down.png', $module);
                }

                if (in_array('noButtons', $opts))
                {
                    $this->showButtons = false;
                }

                if (in_array('updateButton', $opts))
                {
                    $this->setUpdateButton(true);
                }

                if (in_array('noUpdateButton', $opts))
                {
                    $this->setUpdateButton(false);
                }

                if (in_array('includeButton', $opts))
                {
                    $this->setIncludeButton(true);
                }
            }
        }

        $this->setClass('repetitiveFieldBaseGroup'); //classe css
    }

    /**
     * Define um valor padrão para um campo qualquer
     *
     * @param string $fieldId id do campo
     * @param string $value valor a determinar como padrão
     */
    public function setDefaultValue($fieldId, $value)
    {
        $this->defaultValues[$fieldId] = $value;
    }

    /**
     * Define se é para mostrar ou não o botão de atualização
     *
     * @param boolean $show
     */
    public function setUpdateButton($show)
    {
        $this->updateButton = $show;
        $this->setSessionValue('updateButton', $show);
    }

    /**
     * Define se é para mostrar ou não o botão de inclusão
     *
     * @param boolean $show
     */
    public function setIncludeButton($show)
    {
        $this->includeButton = $show;
        $this->setSessionValue('includeButton', $show);
    }

    public function setSessionValue($var, $value, $itemForce = null)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        $session = new MSession('CLASSNAME');
        $object = $session->getValue($item);
        $object->$var = $value;
        $session->setValue($item, $object);
    }

    public function getSessionValue($var, $itemForce = null)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        $session = new MSession('CLASSNAME');
        $object = $session->getValue($item);
        return $object->$var;
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
        return $this->showButtons();
    }

    /**
     * Set the transaction
     *
     * @param String $transaction
     */
    //TODO tirar daqui
    public function setTransaction($transaction = null)
    {
        $this->transaction = $transaction;
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
     * Passa pelo campo detectando se existe campos internos ou não
     * Função usada para detectar relação de campos para javascripts
     *
     * @param object $field o campo a detectar, pode ser um array de campos tambem.
     */
    protected function processNamesArray($field)
    {
        if (is_array($field))
        {
            foreach ($field as $value)
            {
                $this->processNamesArray($value);
            }
        }
        else if ($field instanceof MContainer)
        {
            $this->processNamesArray($field->getControls());
        }
        else if ($field instanceof MDiv)
        {
            $this->processNamesArray($field->getInner());
        }
        else if ($field->name)
        {
            $controls = $this->getSessionValue('controls', $this->item);
            $controlsData = new stdClass();
            $controlsData->name = $field->name;
            $controlsData->class = get_class($field);

            //adiciona : caso não tenha sido passado
            /* if ( $field->label )
              {
              $lastChara = $field->label[strlen($field->label)-1];

              if ( $lastChara != ':')
              {
              $field->label = $field->label . ':';
              }
              } */

            if ($field instanceof MLookupTextField)
            {
                $controlsData->baseModule = $field->baseModule;
                $controlsData->module = $field->module;
                $controlsData->item = $field->item;
                $controlsData->event = $field->event;
                $controlsData->related = $field->related;
                $controlsData->filter = $field->filter;
                $controlsData->title = $field->title;
            }

            $controls[$field->name] = $controlsData;
            $this->setSessionValue('controls', $controls, $this->item);

            //para suportar o enter no campos do RepetitiveField, adiciona evento
            $field->addAttribute('onPressEnter', 'dojo.byId(\'addData' . $this->name . '\').onclick();');
            //acessibilidade
            if ($field->label)
            {
                $field->addAttribute('alt', $field->label);
                $field->addAttribute('title', $field->label);
            }

            //FIXME acredito que isso não seja mais necessário, testar
            if (ereg("_ro", $field->name))
            {
                return;
            }

            $this->namesArray[] = $field->name;
        }

        return $this->namesArray;
    }

    /**
     * Define os campos que serão utilizados na classe
     *
     * @param array $fields array de objetos
     */
    public function setFields($fields)
    {
        $MIOLO = MIOLO::getInstance();

        if ($fields)
        {
            $this->setControls($fields);
            $fieldNames = $this->processNamesArray($fields);
            $this->setSessionValue('fieldNames', $fieldNames);
        }
    }

    /**
     * Define as colunas que serão criadas na Tabela
     *
     * @param array $columns array de objetos MGridColum
     */
    public function setColumns($columns)
    {
        //TODO passar por addColumn
        //TODO usar MDataGridCOlumn
        $tempColumns = null;
        if ($columns && is_array($columns))
        {
            //crias os títulos e as colunas na seção
            foreach ($columns as $line => $info)
            {
                if ($info->visible == true)
                {
                    $titles[] = $info->title;
                    $temp = new StdClass();
                    $temp->align = $info->align;
                    $temp->title = $info->title;
                    $temp->width = $info->width;
                    $temp->visible = $info->visible;
                    $temp->options = $info->options;
                    $tempColumns[] = $temp;
                }
            }
        }
        $this->setSessionValue('titles', $titles);
        $this->setSessionValue('columns', $tempColumns);
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
        //Inserir na seção tambem
        $this->columns[] = new MGridColumn($title, $align, $nowrap, $width, $visible, $options, $order, $filter);

        if ($visible == true)
        {
            $this->titles[] = $title;
        }
    }

    /**
     * Set the validators
     *
     * @param $validators
     */
    public function setValidators($validators = null)
    {
        if (is_null($validators))
        {
            $this->setSessionValue('validators', '', $this->item);
            return;
        }

        $this->gValidator->setValidators($validators);
        $validators = $this->gValidator->getValidators();

        if (is_array($validators))
        {
            //seta label no GnutecaUniqueValidator
            foreach ($validators as $i => $validator)
            {
                if ($validator->checker == 'unique')
                {
                    if (strlen($validator->label) == 0)
                    {
                        $validator->label = $this->caption;
                    }
                }
            }

            $this->setSessionValue('validators', $validators, $this->item);
        }
    }

    /**
     * Get validators
     *
     * @return $validators (Array)
     */
    public function getValidators($itemForce = null)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }
        return self::getSessionValue('validators', $item);
    }

    /**
     * Adiciona uma ação personalizada a tabela
     * //TODO testar e melhorar
     */
    public function addAction($phpFunction, $img, $imgModule)
    {
        if ($this)
        {
            $action->event = "AddTableResult" . $this->item;
            $action->jsFunction = "AddTableResult" . $this->item;
            $action->phpFunction = $phpFunction;
            $action->img = $img;
            $action->imgModule = $imgModule;
            $actions = $this->getSessionValue('actions');
            $actions[$phpFunction] = $action;
            $this->setSessionValue('actions', $actions);
        }
    }

    /**
     * Gera uma a string d euma ação
     *
     * @return o html correspondente a uma ação
     */
    protected function generateActionString($i, $itemForce = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $url = str_replace('&amp;', '&', $MIOLO->getCurrentURL());

        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        $actions = self::getSessionValue('actions', $item);

        unset($result);

        if ($actions)
        {
            foreach ($actions as $line => $info)
            {
                $linkImg = new MImageLink("link$i", null, "javascript:gnuteca.repetitiveFieldAction('{$item}','{$i}','{$info->phpFunction}'); ", GUtil::getImageTheme($info->img));
                $result .= $linkImg->generate();
            }
        }

        return $result;
    }

    /**
     * getDataItem all itens of table/session, organized with arrayItem
     * Each time u call this function the session is cleanned and rewrited
     * @param id da repetitive
     * @param flag para dizer se o getData é final, por padrão é final
     */
    public function getData($itemForce = NULL, $generate = false)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        $itens = $_SESSION['GRepetitiveField'][$item];

        if (!$itens)
        {
            $itens = $_SESSION['GRepetitiveField'][$item][0]; //bug??/
        }
        unset($_SESSION['GRepetitiveField'][$item]); //clearData
        //rewrite the session with correct arrayItem values
        if ($itens)
        {
            $x = 0;
            //$controls = GRepetitiveField::getSessionValue('controls', $item);

            foreach ($itens as $line)
            {
                $line->arrayItem = $x;
                $x++;
                $_SESSION['GRepetitiveField'][$item][] = $line;
            }
        }
        
        return $_SESSION['GRepetitiveField'][$item];
    }

    /**
     * Método que faz getData somente dos próprios controls
     * 
     * @param (String) nome da repetitive
     * @param (boolean) $generate
     * @return (array) com dados 
     */
    public static function getDataOnlySelfControls($itemForce = NULL, $generate = false)
    {
        $data = self::getData($itemForce, $generate);
        $controls = GRepetitiveField::getSessionValue('controls', $itemForce);

        $newData = array();
        if (is_array($controls))
        {
            foreach ($data as $i => $value)
            {
                foreach ($controls as $key => $control)
                {
                    if ( strlen($value->$key) > 0 )
                    {
                        $newData[$i]->$key = $value->$key;
                    }
                }

                //obtém o removeData
                if ($value->removeData)
                {
                    $newData[$i]->removeData = $value->removeData;
                }

                //obtém insertData
                if ($value->insertData)
                {
                    $newData[$i]->insertData = $value->insertData;
                }

                //obtém arrayItem
                $newData[$i]->arrayItem = $value->arrayItem;
            }
        }

        return $newData;
    }

    /**
     * getDataItem one item from table
     *
     * @param arrayItem the index of the item you wanna take
     */
    public function getDataItem($arrayItem, $itemForce = NULL)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        if ($itens = $_SESSION['GRepetitiveField'][$item])
        {
            foreach ($itens as $line => $info)
            {
                if ($info->arrayItem == $arrayItem)
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
    public function defineData($arrayItem, $obj, $itemForce = NULL)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        $_SESSION['GRepetitiveField'][$item][$arrayItem] = $obj;
    }

    /**
     * addData some item to session/Table (You can pass an array or one item)
     * It is a recursive function.
     */
    public function addData($data, $itemForce = NULL)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        if ($data)
        {
            if (is_array($data))
            {
                foreach ($data as $line => $info)
                {
                    GRepetitiveField::addData($info, $item);
                }
            }
            else
            {
                //converte objetos GFILE para stdClass, pois o miolo não está permitindo enviar para sessão
                $_SESSION['GRepetitiveField'][$item][] = (object) ( (array) $data);
            }
        }
    }

    /**
     * Define the Data of the field.
     * It will clearData e add the passed data
     *
     * @param (array) the array of objects with all data
     */
    public /* static */ function setData($data, $itemForce = NULL)
    {
        if ($itemForce)
        {
            GRepetitiveField::clearData($itemForce);
            GRepetitiveField::addData($data, $itemForce);
            GRepetitiveField::getData($itemForce, true);
        }
        else
        {
            $this->clearData();
            $this->addData($data);
            $this->getData(null, true);
        }
    }

    /**
     * clearData all itens of the table session
     */
    public /* static */ function clearData($itemForce = NULL)
    {
        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        unset($_SESSION['GRepetitiveField'][$item]);
    }

    /**
     *  removeData some item from Table, you need an Id.
     *  This id can be found is $item->arrayItem
     */
    public function removeData($arrayItem)
    {
        unset($_SESSION['GRepetitiveField'][$this->item][$arrayItem]);
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
    public function generate($doNothing = false, $itemForce = NULL)
    {
        $module = MIOLO::getCurrentModule();
        $MIOLO = MIOLO::getInstance();
        $url = str_replace('&amp;', '&', $MIOLO->getCurrentURL());

        if ($itemForce)
        {
            $item = $itemForce;
        }
        else
        {
            $item = $this->item;
        }

        if (!$item)
        {
            return '';
        }

        //function called only first time that component is rendered , mount buttons
        if ($this)
        {
            if ($this->readonly)
            {
                $readonly = $this->readonly;
                $this->setControls(array());
                $this->caption .= ' - ' . _M('somente leitura', $this->module);
            }

            if ($this->showButtons && !$this->readonly)
            {
                $js = "document.getElementById( 'GRepetitiveField' ).value = '$item'; ";

                //include
                if ($this->includeButton)
                {

                    $imgAdd = GUtil::getImageTheme('include-8x8.png');
                    $addTitle = _M('Incluir', $module);
                    $js .= GUtil::getPostBack('forceIncludeToTable');
                }
                else //add
                {
                    $imgAdd = GUtil::getImageTheme('add-8x8.png');
                    $addTitle = _M('Adicionar', $module);

                    if ($this->updateButton)
                    {
                        $js .= GUtil::getPostBack('forceAddToTable');
                    }
                    else
                    {
                        $js .= GUtil::getPostBack('addToTable') . " document.getElementById('addData$item').innerHTML = '$addTitle';";
                    }
                }

                //update
                if ($this->updateButton)
                {
                    $imgEdit = GUtil::getImageTheme('edit-8x8.png');
                    $buttons[] = new MButton('addData' . $item, $addTitle, $js, $imgAdd);
                    $js = "document.getElementById( 'GRepetitiveField' ).value = '$item';" . GUtil::getPostBack('addToTable');
                    $buttons[] = new MButton('updateData' . $item, _M('Atualizar', $this->module), $js, $imgEdit);
                }
                else
                {
                    $buttons[] = new MButton('addData' . $item, $addTitle, $js, $imgAdd);
                }

                //clear
                $imgClear = GUtil::getImageTheme('clear-8x8.png');
                $js = "document.getElementById('GRepetitiveField').value = '$item'; document.getElementById('addData$item').innerHTML = '$addTitle';" . GUtil::getPostBack('clearTableFields');
                $buttons[] = new MButton('clearData' . $item, _M('Limpar', $module), $js, $imgClear);
                $divButtons = new MDIV('divButtons', $buttons);

                $this->addControl($divButtons);
            }

            //valores padrão, para preencher automaticamente em novos registros
            if (is_array($this->defaultValues))
            {
                foreach ($this->defaultValues as $line => $defaultValue)
                {
                    $field = new MTextField($line . '_defaultValue', $defaultValue);
                    $field->addStyle('display', 'none');
                    $this->addControl($field);
                }
            }
        }

        $temp = self::getTable($item, $readonly)->generate(); #criação da tabela
        $tempData = self::getData($item, true);
        $columns = GRepetitiveField::getSessionValue('columns', $item);

        if (!$itemForce)
        {
            $div = new MDiv('div' . $item, $temp, 'repetitiveField');

            if ($this->overflowWidth)
            {
                $div->addStyle('overflow-x', $this->overflowType);
                $div->addStyle('width', $this->overflowWidth . 'px');
            }
            else
            {
                $div->addStyle('width', '100%');
            }
            $this->controls->add($div);

            //adiciona * nos campos requeridos
            $validators = $this->getValidators($this->item);

            if (is_array($validators))
            {
                foreach ($validators as $valid)
                {
                    $field = $this->getControlById($valid->field);

                    if ($valid->type == 'required')
                    {
                        $field = $this->getControlById($valid->field);

                        if ($field instanceof MCalendarField)
                        {
                            $field->validator = '';
                            $field->validator->checker = 'DATEDMY';
                        }
                        if (is_object($field))
                        {

                            $field->validator->type = 'required';
                        }
                    }
                }
            }

            return parent::generate();
        }
        else
        {
            return $temp;
        }
    }

    /**
     * Retorna o objeto MTableRaw utilizado no campo repetivo
     *
     * @param string $item nome do campo repetivie
     * @param boolean $readonly
     * @return MTableRaw
     */
    public function getTable($item, $readonly = false)
    {
        $actions = GRepetitiveField::getSessionValue('actions', $item);
        $columns = GRepetitiveField::getSessionValue('columns', $item);
        $itens = self::getData($item, true);
        $titles = GRepetitiveField::getSessionValue('titles', $item);

        //adiciona a coluna actions ao título caso existam ações
        if ($actions && !$readonly)
        {
            $titles = array_merge(array(_M('Ações', $module)), $titles);

            unset($temp);
            $temp->align = 'left';
            $temp->title = _M('Ações', $module);
            $temp->width = '10%';
            $temp->visible = true;
            $temp->options = '';

            $columns = array_filter($columns);
            $columns = array_merge(array($temp), $columns); //limpa
        }

        $table = new MTableRaw('', array(), $titles);
        $table->setAlternate(true);
        $table->addStyle('width', '100%');
        $table->addAttribute("cellspacing", "0");

        //adiciona atributos ao cabeçalho
        foreach ($columns as $line => $column)
        {
            if ($column->visible == true)
            {
                if ($column->align)
                {
                    $table->setHeadAttribute($line, 'align', $column->align);
                    ;
                }

                if ($column->width && $column->width !== true)
                {
                    $table->setHeadAttribute($line, 'width', $column->width);
                    ;
                }
            }
        }

        //monta os dados para a tabela
        if ($itens)
        {
            foreach ($itens as $i => $info)
            {
                if (!$info->removeData)
                {
                    unset($encodedInfo);

                    //trata os dados conforme necessário
                    foreach ($info as $l => $i)
                    {
                        if (is_string($i))
                        {
                            $encodedInfo->$l = urlencode($i);
                            $encodedInfo->$l = str_replace("\n", '\n', $encodedInfo->$l);
                        }
                        else
                        {
                            $encodedInfo->$l = $i;
                        }
                    }

                    unset($args);

                    if ($actions && !$readonly)
                    {
                        $actions[$i] = self::generateActionString($i, $item);
                        $args[] = $actions[$info->arrayItem];
                    }

                    foreach ($columns as $line => $column)
                    {
                        if ($column->visible == true)
                        {
                            if ($column->options)
                            {
                                $opt = $column->options;
                                //FIXME método reescrito para substituir </style > por </style>. Ticket #9332
                                $args[] = str_replace('</style>', '</style >', $info->$opt);

                                if ($column->align)
                                {
                                    $table->setCellAttribute($i, $line, 'align', $column->align);
                                }
                            }
                        }
                    }

                    //seta atributo na célula de ações para não quebrar linha
                    $table->setCellAttribute($i, 0, 'style', 'white-space:nowrap');

                    $tableData[] = $args;
                }
            }
        }

	// Inverte array. O código rsort($tableData, SORT_NUMERIC) não funcionou.
        if ( is_array($tableData) )
        {  
            $newTableData = array();
            $count = 0;

            for ( $i = (count($tableData) -1 ); $i >= 0; $i-- )
            {
                $newTableData[$count] = $tableData[$i];
                $count++;
            }
       
            $tableData = $newTableData;
        }
        
        $table->array = $tableData; //seta os dados no array

        return $table;
    }

    public function getControlById($id)
    {
        $fieldId = $this->controlsId->get($id);

        if (!$fieldId)
        {
            $controls = $this->getControls();

            $fieldId = $this->_getControlById($controls, $id);
        }

        return $fieldId;
    }

    private function _getControlById($controls, $id)
    {
        if (is_array($controls))
        {
            foreach ($controls as $line => $info)
            {
                if (!$fieldId)
                {
                    if ($info->id == $id)
                    {

                        $fieldId = $id;
                    }
                    else
                    if ($info->controls instanceof MObjectList)
                    {
                        $fieldId = $info->getControlById($id);
                    }
                }
            }
        }

        return $fieldId;
    }

    /**
     * Update the visual Component with some data. Make a ajax response
     *
     * @param array $data array of object
     * @param string $itemForce the name of the table
     */
    public function update($data = NULL, $itemForce)
    {
        $MIOLO = MIOLO::getInstance();
        GRepetitiveField::setData($data, $itemForce);
        $generate = GRepetitiveField::generate(false, $itemForce);
        $MIOLO->ajax->setResponse($generate, 'div' . $itemForce);
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $field
     * @param unknown_type $line
     * @return boolean
     */
    public function checkRemovedItem($field, $line)
    {
        return (isset($_SESSION['GRepetitiveField'][$field][$line]->removeData) && $_SESSION['GRepetitiveField'][$field][$line]->removeData == 1);
    }

    /**
     * Verifica se tem componente de envio de arquivo
     *
     * @param string $repetitive
     * @return boolean
     */
    function hasFileUpload($repetitive)
    {
        $controls = GRepetitiveField::getSessionValue('controls', $repetitive);

        if (is_array($controls))
        {
            foreach ($controls as $line => $controls)
            {
                if (strtolower($controls->class) == strtolower(MFileField))
                {
                    return true;
                }
            }
        }

        return false;
    }
     
    /**
     * Procede com o upload do arquivo, chamada dentro do autoAddAction
     *
     * @param string $repetitive
     * @return stdClass dados resultantes
     */
        
    public static function fileUpload($repetitive)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        $MIOLO = MIOLO::getInstance();
        $uploadInfo = mFileField::getUploadInfo();
        
        if (!is_array($uploadInfo))
        {
            throw new Exception(_M("É necessário selecionar um arquivo para efetuar o envio.\n Tamanho máximo do arquivo deve ser ".ini_get('upload_max_filesize'), 'gnuteca3'));
        }

        $MIOLO->getClass('gnuteca3', 'GBusiness');
        $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');

        $uploadedFiles = array();

        foreach ($uploadInfo as $fileInfo)
        {
            list($fileName, $tmpFile) = explode(';', $fileInfo);

            $targetPath = $busFile->getAbsoluteServerPath(true) . '/tmp/' . basename($tmpFile);
            $tmpFile = $MIOLO->getConf('home.html') . "/files/tmp/$tmpFile";

            if (copy($tmpFile, $targetPath))
            {
                unlink($tmpFile);
                $uploadFile = new stdClass();
                //retira o acento do nome do arquivo
                $resp = GString::remAcento($fileName);
                $uploadFile->basename = $resp;
                $uploadFile->type = filetype($targetPath);
                $uploadFile->tmp_name = $targetPath;
                $uploadFile->size = filesize($targetPath);
                $uploadFile->mimeContent = mime_content_type($targetPath);
                $uploadedFiles[] = $uploadFile;
                
            }
            else
            {
                throw new Exception(_M('Sem permissão para mover o arquivo para a pasta temporária', 'gnuteca3'));
            }
        }

        if ($uploadedFiles[0]->basename)
        {
            $uploadedFile = $uploadedFiles[0];
        }
        else
        {
            throw new Exception(_M('Falha no envio de arquivo.', 'gnuteca3'));
        }

        $limit = GFileUploader::getLimit($repetitive);
        $deny = GFileUploader::getDenyExtensions($repetitive);
        $allow = GFileUploader::getAllowedExtensions($repetitive);
        $explode = explode('.', $uploadedFile->tmp_name);
        $extension = strtolower($explode[count($explode) - 1]);

        //deny
        if ($deny && in_array($extension, $deny))
        {
            $msg = _M('Extensão não permitida: ', 'gnuteca3') . $extension;
            unlink($uploadedFile->tmp_name); //remove arquivo
            throw new Exception($msg);
        }

        //allow
        if ($allow && !in_array($extension, $allow))
        {
            $msg = _M('Extensão não permitida : ', 'gnuteca3') . $extension .
                    $msg .= '<br/>' . _M('Somente são permitidas as extensões: ', 'gnuteca3') . implode(',', $allow);
            unlink($uploadedFile->tmp_name); //remove arquivo
            throw new Exception($msg);
        }

        //limit
        $data = GRepetitiveField::getData($repetitive);
        $count = 0;

        //calcula a quantidade certa
        if (is_array($data))
        {
            foreach ($data as $line => $info)
            {
                if (!$info->removeData)
                {
                    $count++;
                }
            }
        }

        if (!$limit || $count < $limit)
        {
            return $uploadedFile;
        }
        else
        {
            unlink($uploadedFile->tmp_name); //remove arquivo
            throw new Exception(_M('Excede a quantidade de arquivos permitidos.', 'gnuteca3'));
        }
    }

    public static function downloadFromTable($args)
    {
        $item = GRepetitiveField::getDataItem($args->arrayItem, $args->GRepetitiveField);

        if ($item->tmp_name)
        {
            throw new Exception(_M('Impossível visualizar prévia, pois ainda é um arquivo temporário.', 'gnuteca3'));
        }

        if (!$item->dirname || !$item->basename)
        {
            throw new Exception(_M('Impossível visualizar prévia: url não encontrada.', 'gnuteca3'));
        }

        GFileUploader::downloadFile($item->dirname . '/' . $item->basename);
    }
}

/**
 *  INICIO DAS FUNÇÕES NECESSARIAS PARA O REPETITIVE FIELD COM AJAX
 */
function autoRemoveAction($data)
{
    $MIOLO = MIOLO::getInstance();
    $object = $data->GRepetitiveField;
    $insertData = $_SESSION['GRepetitiveField'][$object][$data->arrayItemTemp]->insertData;

    //caso o registro não venha da base, remove da sessão definitivamente
    if ($insertData)
    {
        $itemData = $_SESSION['GRepetitiveField'][$object][$data->arrayItemTemp];

        //caso tenha um upload de arquivo remove-o
        if ($itemData->tmp_name)
        {
            unlink($itemData->tmp_name);
        }

        unset($_SESSION['GRepetitiveField'][$object][$data->arrayItemTemp]);
    }
    else
    {
        $_SESSION['GRepetitiveField'][$object][$data->arrayItemTemp]->removeData = true;
    }

    $MIOLO->ajax->setResponse(GRepetitiveField::generate(false, $object), 'div' . $object);
}

function autoForceAddAction($args, $object = null, $errors = null)
{
    $args->arrayItemTemp = null;
    autoAddAction($args, $object, $errors);
}

function autoAddAction($data, $object = NULL, $errors = NULL, $setFocus = true)
{
    $repetitive = $data->GRepetitiveField;
    
    //faz upload caso tenha campo de upload
    if (GRepetitiveField::hasFileUpload($repetitive))
    {
        if (( $repetitive == 'generalUploader' ) || ( $repetitive != 'generalUploader' && MFileField::getUploadInfo() ))
        {
            $uploadData = GRepetitiveField::fileUpload($repetitive);
            $data = (Object) array_merge((array) $data, (Array) $uploadData);
        }
        else
        {
            throw new Exception(_M('Não foi possível realizar o upload do arquivo. Verifique o tamanho máximo.', 'gnuteca3'));
        }
    }

    $controls = GRepetitiveField::getSessionValue('controls', $repetitive);
    $temp = null;
    $MIOLO = MIOLO::getInstance();
    $module = MIOLO::getCurrentModule();
    $validators = GRepetitiveField::getValidators($repetitive);
    //tira mensagens de validação
    $MIOLO->page->onload("gnuteca.cleanValidatorsMessage();");

    $gValidator = new GValidators();
    $_errors = $gValidator->validate($data, $validators);

    if ($_errors)
    {
        $errors = ($errors) ? array_merge($errors, $_errors) : $_errors;
    }

    //modifica o formulário
    $js = "dojo.byId('isModified').value = 't';";

    if (is_array($errors))
    {
        foreach ($errors as $fieldid => $msg)
        {
            $js .= "gnuteca.addValidatorMessage('$fieldid','$msg');";
        }

        $MIOLO->page->onload($js);

        GForm::error(implode('<br>', $errors));
    }
    else
    {
        /**
         * Este campo contem funções que devem ser executadas ao adicionar um valor.
         * //FIXME descobrir para que serve esse código e fazer da forma certa //MEEEDO
         */
        if (isset($data->Gnuteca3RepetitiveAddFunction_ro) && is_array($data->Gnuteca3RepetitiveAddFunction_ro))
        {
            foreach ($data->Gnuteca3RepetitiveAddFunction_ro as $funtion)
            {
                @eval("$funtion");
            }
        }

        if ($data->arrayItemTemp || $data->arrayItemTemp === '0')
        {
            $data->updateData = true;
            GRepetitiveField::defineData($data->arrayItemTemp, $data, $repetitive);
        }
        else
        {
            $data->insertData = true;
            GRepetitiveField::addData($data, $repetitive);
        }

        $fieldNames = GRepetitiveField::getSessionValue('fieldNames', $repetitive);

        //busca dados do lookup
        $controls = GRepetitiveField::getSessionValue('controls', $repetitive);

        if (is_array($controls))
        {
            foreach ($controls as $control)
            {
                if ($control->class == 'MLookupTextField')
                {
                    $lookupData[$control->name] = $control;
                }
            }
        }

        //instancia lookup
        $dbLookup = $MIOLO->getBusiness($module, 'lookup');
        $dbLookup = new BusinessGnuteca3Lookup();
        $dbLookup->setForRepetitiveField(true); //seta função lookup para repetitive field

        foreach ($fieldNames as $index => $value)
        {
            $lookup = $lookupData[$value];

            if ($lookup) //se esse id tiver lookup
            {
                $function = 'autoComplete' . $lookup->item; //monta função de autocomplete

                if (method_exists($dbLookup, $function)) //verifica se função existe
                {
                    $_REQUEST['filter'] = $data->{$lookup->filter}; //seta o filter com o dado do data
                    $dbLookup->$function(); //chama a função do lookup
                    $resultLookup = $dbLookup->result;

                    if (is_array($resultLookup))
                    {
                        $related = explode(',', $lookup->related);

                        foreach ($resultLookup as $l => $i)
                        {
                            $relatedData[$related[$l]] = $i;

                            if ($related[$l])
                            {
                                $data->$related[$l] = $i;
                            }
                        }
                    }
                }
            }

            if ((!$index) && ($setFocus))
            {
                GForm::jsSetFocus($value); //utiliza o GForm para gerenciamento de foco automático
            }

            //limpa o campo, definindo seu valor para vazio
            $js .= "gnuteca.setValueForRepetitive('{$value}','');";
        }

        $js .= "dojo.byId('arrayItemTemp').value=''";
    }

    $MIOLO = MIOLO::getInstance();
    $MIOLO->page->onload($js);
    $temp .= GRepetitiveField::generate(false, $repetitive);
    $MIOLO->ajax->setResponse($temp, 'div' . $repetitive);
}

/**
 * Limpa o valor prenchido nos campos
 *
 * @param object $args
 */
function autoClearAction($args)
{
    $fieldNames = GRepetitiveField::getSessionValue('fieldNames', $args->GRepetitiveField);

    //passa pelos campos setando os como vazio
    if (is_array($fieldNames))
    {
        foreach ($fieldNames as $line => $info)
        {
            $temp.= "gnuteca.setValueForRepetitive('{$info}','');";
        }
    }

    $temp .= "dojo.byId('arrayItemTemp').value=''";

    $MIOLO = MIOLO::getInstance();
    $MIOLO->page->onload($temp);
    $MIOLO->ajax->setResponse(GRepetitiveField::generate(false, $args->GRepetitiveField), 'div' . $args->GRepetitiveField);
}

function autoUpAction($data)
{
    $object = $data->GRepetitiveField;
    $data->sessionItem = $data->GRepetitiveField;
    $nivel = $data->arrayItem;
    if (!$nivel)
    {
        $nivel = $data->arrayItemTemp;
    }
    $item = GRepetitiveField::getData($object, true);
    if ($nivel < count($item) - 1)
    {
        $tempObjAtual = GRepetitiveField::getDataItem($nivel, $object);
        $tempObjInferior = GRepetitiveField::getDataItem($nivel + 1, $object);
        GRepetitiveField::defineData($nivel + 1, $tempObjAtual, $object);
        GRepetitiveField::defineData($nivel, $tempObjInferior, $object);
    }
    $MIOLO = MIOLO::getInstance();
    $MIOLO->ajax->setResponse(GRepetitiveField::generate(false, $object), 'div' . $data->GRepetitiveField);
}

function autoDownAction($data)
{
    $object = $data->GRepetitiveField;
    $data->sessionItem = $data->GRepetitiveField;
    $nivel = $data->arrayItem;
    if (!$nivel)
    {
        $nivel = $data->arrayItemTemp;
    }
    if ($nivel != 0)
    {
        $tempObjAtual = GRepetitiveField::getDataItem($nivel, $object);
        $tempObjSuperior = GRepetitiveField::getDataItem($nivel - 1, $object);
        GRepetitiveField::defineData($nivel - 1, $tempObjAtual, $object);
        GRepetitiveField::defineData($nivel, $tempObjSuperior, $object);
    }
    $MIOLO = MIOLO::getInstance();
    $MIOLO->ajax->setResponse(GRepetitiveField::generate(false, $object), 'div' . $data->GRepetitiveField);
}


//Gravar cookies para utilizar na integração

    function autoGravaTagAction($args)
    {
        //Pega o numero de exemplar
        $values = GRepetitiveField::getDataItem($args->arrayItemTemp, $args->GRepetitiveField);
        $tag = $values->spreeadsheetField_949_a;

        $test = RFID::writeTag($tag);

        if(is_array($test))
        {
            GPrompt::error("Problema ao gravar etiqueta #$tag. <br>
                                    $test[0] <br>");
        }else
        {
            GFORM::Information("Gravada etiqueta #$tag ");
        }
    }


/**
 * Função chamada automaticamente ao apertar editar na tabela
 *
 * @param object $data ajax miolo object
 */
function autoEditAction($args)
{
    $MIOLO = MIOLO::getInstance();
    $module = MIOLO::getCurrentModule();
    $values = GRepetitiveField::getDataItem($args->arrayItemTemp, $args->GRepetitiveField);
    $updateButton = GRepetitiveField::getUpdateButton($args->GRepetitiveField);
    $fieldNames = GRepetitiveField::getSessionValue('fieldNames', $args->GRepetitiveField);
    
    if (is_array($fieldNames))
    {
        //define o foco no primeiro campo visível.
        GForm::setFocusInFirstInput($fieldNames);
        
        foreach ($fieldNames as $line => $info)
        {
            $value = $values->$info;
            $value = addslashes($value);
            $value = str_replace("\n", '\n', $value);
            $value = str_replace("\r", '\r', $value);
            
            $temp.= "gnuteca.setValueForRepetitive('{$info}','{$value}');";

            if (!$updateButton)
            {
                GForm::jsSetInner('addData' . $args->GRepetitiveField, _M('Atualizar', $module));
            }
        }
    }

    $MIOLO->page->onload($temp);
    $MIOLO->ajax->setResponse(GRepetitiveField::generate(false, $args->GRepetitiveField), 'div' . $args->GRepetitiveField);
}

?>
