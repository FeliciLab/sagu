<?php

/**
 * MLookupContainer
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/08/04
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2010 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 * \b History: \n
 * See history in CVS repository: http://www.miolo.org.br
 *
 */
class MLookupContainer extends MContainer
{
    /**
     * @var string Default value.
     */
    public $value;

    /**
     * @var integer Description field size.
     */
    public $descriptionSize;

    /**
     * @var string Label.
     */
    public $label;

    /**
     * @var string Lookup module.
     */
    public $module;

    /**
     * @var string Lookup item.
     */
    public $item;

    /**
     * @var string Hint.
     */
    public $hint;

    /**
     * @var MLookupTextField Lookup field.
     */
    private $lookupField;

    /**
     * @var MTextField Description field.
     */
    private $descriptionField;

    /**
     * MLookupContainer constructor.
     *
     * @param string $name Input name.
     * @param string $value Default value.
     * @param string $label Label.
     * @param string $module Lookup module.
     * @param string $item Lookup item.
     * @param boolean $readOnly Whether field is read only.
     * @param string $hint Hint.
     * @param integer $descriptionSize Description field size.
     * @param string $descriptionId Description field id.
     */
    public function __construct($name, $value='', $label='', $module=NULL, $item='', $readOnly=FALSE, $hint='', $descriptionSize=50, $descriptionId='')
    {
        if ( !$descriptionId )
        {
            $descriptionId = $name . '_lookupDescription';
        }

        $this->lookupField = new MLookupTextField($name, $value, NULL, 10);
        $this->descriptionField = new MTextField($descriptionId, NULL, NULL, $descriptionSize, $hint, NULL, true);

        $this->label = $label;
        $this->module = $module ? $module : MIOLO::getCurrentModule();
        $this->item = $item;

        $this->lookupField->setContext($this->module, $this->module, $this->item, 'filler', "$name,$descriptionId", NULL, true);
        $this->lookupField->setReadOnly($readOnly);

        $controls = array( $this->lookupField, $this->descriptionField );

        parent::__construct($name . '_container', $controls, 'horizontal', MFormControl::FORM_MODE_SHOW_NBSP);
    }

    /**
     * @return MLookupTextField Get the lookup field.
     */
    public function getLookupField()
    {
        return $this->lookupField;
    }

    /**
     * @param MLookupTextField $lookupField Set the lookup field.
     */
    public function setLookupField($lookupField)
    {
        $this->lookupField = $lookupField;
    }

    /**
     * @return MTextField Get the description field.
     */
    public function getDescriptionField()
    {
        return $this->descriptionField;
    }

    /**
     * @param MTextField $descriptionField Set the description field.
     */
    public function setDescriptionField($descriptionField)
    {
        $this->descriptionField = $descriptionField;
    }

    /**
     * Set lookup context.
     *
     * @param string $module Lookup module.
     * @param string $item Lookup item.
     * @param string $related Related field ids.
     * @param array $filter Filters.
     * @param boolean $autoComplete Whether it has auto complete feature.
     */
    public function setContext($module, $item, $related, $filter, $autoComplete = true)
    {
        $this->lookupField->setContext($module, $module, $item, 'filler', $related, $filter, $autoComplete);
    }

    /**
     * @return string Get related field ids. 
     */
    public function getRelated()
    {
        return $this->lookupField->related;
    }

    /**
     * @param boolean $isInteger Sets if the lookup field must be rendered as a MIntegerField.
     */
    public function setInteger($isInteger)
    {
        $this->lookupField->setInteger($isInteger);
    }

    /**
     * Execute the auto complete action.
     *
     * @param string $value Value.
     * @return array Data to put on the lookup fields.
     */
    public function doAutoComplete($value = null)
    {
        $_POST['filter'] = $value ? $value : $this->lookupField->value;

        if ( $this->lookupField->value || $value )
        {
            $bussinesPath = $this->manager->getConf('namespace.business'); //  <business>/classes</business>

            $this->manager->uses( $bussinesPath.'/lookup.class.php', $this->lookupField->baseModule );

            $lookupName = "Business{$this->lookupField->baseModule}Lookup";
            $autoCompleteName = "AutoComplete{$this->item}";

            $lookup = new $lookupName();
            $mlookup = new MLookup($this->lookupField->baseModule);

            if ( method_exists( $lookup, $autoCompleteName ) )
            {
                $result = $lookup->$autoCompleteName($mlookup);
            }

            if ( !$value )
            {
                $related = explode(',', $this->lookupField->related );

                if ( is_array( $related ) && is_array( $result ) )
                {
                    foreach ( $related as $line => $id )
                    {
                        $this->page->setElementValue($id, $result[$line]);
                    }
                }
            }

            return $result;
        }
    }

    /**
     * Get context attribute.
     *
     * @param string $attribute Attribute name.
     * @return mixed Attribute value.
     */
    public function getContextAttribute($attribute)
    {
        return $this->lookupField->$attribute;
    }

    /**
     * @param boolean $readOnly Set lookup as read only.
     */
    public function setReadOnly($readOnly)
    {
        $this->lookupField->setReadOnly($readOnly);
    }

    /**
     * @return string Generate field label.
     */
    public function generateLabel()
    {
        $label = '';
        if ( $this->label != '' )
        {
            $span  = new MSpan( '', $this->label . ':', 'label' );

            $r = $this->attrs->items['required'] || ($this->lookupField->validator && $this->lookupField->validator->type == 'required');

            if ( $r && trim(MUtil::removeSpaceChars($this->label)) )
            {
                $span->setClass('mCaptionRequired');
            }

            $label = $this->painter->span( $span );
        }

        return $label;
    }

    /**
     * @return string Generate component.
     */
    public function generate()
    {
        $span = new MSpan(NULL, parent::generate(), 'field');

        return $this->generateLabel() . $span->generate();
    }

    /**
     * Add an attribute to the lookup field.
     *
     * @param string $attr Attribute name.
     * @param string $value Attribute value.
     */
    public function addAttribute($attr, $value)
    {
        $this->lookupField->addAttribute($attr, $value);
    }

    /**
     * Add an event to the lookup field.
     *
     * @param string $event Event name.
     * @param string $value Event action.
     * @param boolean $preventDefault Whether to prevent the default action.
     */
    public function addEvent($event, $value, $preventDefault = true)
    {
        if ( substr($value, 0, 1) == ':' )
        {
            $value = substr($value, 1);
        }

        $this->lookupField->lookupEvent = $value;
    }

    /**
     * Set lookup field event.
     *
     * @param string $action Event action.
     */
    public function setEvent($action)
    {
        if ( substr($action, 0, 1) == ':' )
        {
            $action = substr($action, 1);
        }

        $this->lookupField->lookupEvent = $action;
    }

    /**
     * @param string $value Set the value of the lookup field.
     */
    public function setValue($value)
    {
        $this->lookupField->setValue($this->value);
    }

    /**
     * @return string Get the lookup value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param boolean $autocomplete Set whether it supports auto complete.
     */
    public function setAutoComplete($autocomplete=true)
    {
        $this->lookupField->setAutoComplete($autocomplete);
    }
}
?>
