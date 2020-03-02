<?php

/**
 * Base class for form controls.
 * This class implements basic funcionality of all forms (registering/rendering 
 * of fields/buttons/validators without a box and rendering of errors/info 
 * prompts).
 *
 * @author Vilson Cristiano Gärtner [vilson@solis.coop.br]
 * @author Ely Edison Matos [ely.matos@ufjf.edu.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2009/03/17
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2009-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

define ('FORM_SUBMIT_BTN_NAME', 'submit_button');

/**
 
 */
class MBaseForm extends MContainerControl
{
    protected $action;
    public $method;
    public $buttons;
    public $fields = array();
    public $return;
    public $reset;
    public $styles;
    public $width;
    public static $showHints = true;
    public $enctype;
    public $validations;
    public $defaultButton;
    public $errors;
    public $infos;

    /**
     * @var array Alert messages to be shown on form generation
     */
    public $alerts;

    static $fieldNum = 0;
    public $layout;
    public $zebra = false;
    public $labelWidth = NULL;
    public $focus = '';
    public $ajax;
    public $cp; // compatibility -> $cp = $ajax;
    public $cssForm = false;
    public $cssButtons;

    /**
     * @var boolean Define if form data validation must be done via JavaScript.
     */
    private $jsValidationEnabled = true;

    /**
     * This is the constructor of the MBaseForm class. It takes an 
     * action url as optional parameter. The action URL is typically 
     * obtained by calling the <code>MIOLO->getActionURL()</code> method.
     *
     * @param $action (string) the URL for the forms <code>ACTION</code>
     *                attribute.
     */
    public function __construct($action='')
    {
        parent::__construct();
        $this->setId('frm' . uniqid()); 
        $this->method = 'post';
        $this->return = false;
        $this->width  = '95%';
        $this->defaultButton = true;
        $this->fields = array();
        $this->validations = array();
        $this->ajax = $this->manager->ajax;
        $this->cp = $this->ajax;
        $this->createFields();
        if ($this->page->isPostBack)
        { 
            $this->getFormFields(); // set the fields array with form post/get values
        }
        $this->onLoad();
    }
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $name (tipo) desc
 * @param $value (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __set($name, $value)
    {
        if ($name == 'form') return;
        $this->addControl($value);
        $this->fields[$name] = $value;
    }

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $name (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __get($name)
    {
        return $this->fields[$name];
    }

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function onLoad()
    {
    }

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function createFields()
    {
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function isAjaxCall()
    {
		return ($this->manager->getIsAjaxCall());
    }
    
/**
 * Deprecated; only for compatibility with olf MFormAjax.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function registerMethod()
    {
    }
    

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $validator (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function addValidator($validator)
    {
        $field = $this->{$validator->field};
        $name = '_validator_' . $this->name . '_' . $validator->id . '_' . $validator->field;
        $validator->name  = $name;
        $validator->form  = $this->name;
        $validator->label = ($validator->label == '') ? $field->label : $validator->label;
        $validator->max = $validator->max ? $validator->max : $field->size;
        $this->validations[] = $validator;

        if ( !$field->validator )
        {
            $field->validator = $validator;
        }
        elseif ( ($validator->type == 'required') && ($field->validator instanceof MValidator) )
        {
            $field->validator->type = $validator->type;
        }

        return $name;
    }

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $validators (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setValidators($validators)
    {
       if (is_array($validators))
       {
          foreach($validators as $v)
          {
             $this->addValidator($v);
          }
       }
       elseif (is_subclass_of($validators,'validator'))
       {
             $this->addValidator($validators);
       }
    }
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $validators (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setAjaxFields($fields, $responseControl)
    {
        foreach ($fields as $key => $f)
        {
            $validator = $this->getFieldValidator($f->id);
            if ( $validator )
            {
                 $f->validator = $validator;
            }
            
            $fields[$key] = $this->generateLayoutField($f);
        }
        $this->setResponse($fields, $responseControl);
    }

    
    /**
     * Detects if a form has been submitted.
     * 
     * @return (boolean) true if the form has been submitted otherwise false
     */
    public function isSubmitted()
    {   
        $isSubmitted = $this->defaultButton && MForm::getFormValue(FORM_SUBMIT_BTN_NAME);
        if (isset($this->buttons))
        {
            foreach($this->buttons as $b)
            {
                $isSubmitted = $isSubmitted || MForm::getFormValue($b->name);
            }
        }
        return $isSubmitted;
    }
    
    
    /**
     * Detects if a form has been submitted.
     * 
     * @return (boolean) true if the form has been submitted otherwise false
     */
    public function setAlternate($color0, $color1)
    {
        $this->zebra = array($color0, $color1);
    }

    
    public function setFocus($fieldName)
    {
        $this->focus = $fieldName;
    }
    
    /**
     * Obtains a submitted form fields's values and sets the array fields
     * Uses $page->request
     */
    public function getFormFields()
    {   
       $this->_getFormFieldValue($this->fields);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $field (tipo) desc    
     *
     * @returns (tipo) desc
     *
     */
    protected function _getFormFieldValue($field)
    {   
        if ( is_array($field) )
        {
            foreach($field as $f)
            {
                $this->_getFormFieldValue($f);
            }
        }
        else
        {
            if ($field instanceof MFormControl)
            {
                if ( $field->name )
                {
                    $field->setSubmittedValue();
                }
            }
        }
    }
    
    /**
     * Obtains a submitted form field's value. This is a static function.
     *
     * @param (string) $name
     * @param (string) $value
     * @return (mixed) value of field contained in <code>$HTTP_POST_VARS</code>
     */
    public function getFormValue($name, $value = NULL)
    { 
        $result = '';
        if ( ($name != '') && ((strpos($name,'[')) === false))
        {
           $result = $_REQUEST[$name];
        }

        if (! isset($result))
        {
            $result = $value;
        }
        return $result;
    }

       
    /**
     * Sets the content of a form field to the specified value. The 
     * function does this by setting both, the field's value member and the
     * global <code>$_POST</code> to remain consistency between the values.
     *
     * @param (string) $name Field name 
     * @param (string) $value Value to set to the field
     */
    public function setFormValue($name, $value)
    {   
        $value = MForm::escapeValue($value);
        if (isset($this->$name))
        {
            $this->$name->setValue($value);
        }
        
        // If the fields 
        if ($this->fields[$name])
        {
            $this->fields[$name]= $value;        
        }
        
        $_REQUEST[$name] = $value;
    }
    
    
    /**
     * Used to escapes special characters contained in a form field's value
     * Currently, only simple and double quote characters are subsituted
     * with their corresponding HTML entities.
     *
     * This function is used internally by some of the form's methods.
     *
     * @param (string) $value 
     * @return 
     */
    private function /* PRIVATE */ escapeValue($value)
    {
        if ( is_array($value) )
        {
            for ( $i=0, $n = count($value); $i < $n; $i++ )
            {
                $value[$i] = $this->escapeValue($value[$i]);
            }
        }
        else
        {
            $value = str_replace('\"','&quot;',$value);
            $value = str_replace('"','&quot;',$value);
        }
        return $value;
    }

    
    /**
     * Adds JavaScript code which is to be executed, when the form is submitted.
     * When the form is generated, and any JS code has been registered using
     * this function, an <code>OnSubmit</code> handler is dynamically generated 
     * where the code is placed.
     * 
     * The generated code looks like the following where stmt stands for the
     * registered statments
     *
     * @param (string) $jscode Javascript code
     */
    public function onSubmit($jscode)
    {
        $this->page->onSubmit($jscode);
    }

    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $jscode (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function addJsCode($jscode)
    {
        $this->page->addJsCode($jscode);
    }
    
    
    /**
     * Sets the action URL for this form. This is the URL to which the
     * form data will be submitted. Usually, the URL is obtained by the
     * GetActionURL of the MIOLO class.
     *
     * @param (string) $action URL of the action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
    
   
    /**
     * Obtain the list of form fields.
     *
     * @deprecated this function will be changed in the near future, so
     * don't use it anymore and keep in touch with the development team
     * to figure out, what will be the replacement. 
     *
     * @return (array) the list of form fields 
     */
    private function /*PRIVATE*/ getFields()
    {
        return $this->fields;
    }
    
    
    /**
     * This function is used to set an array of fields for the form.
     *
     * @param (array) $fields Fields array
     */
    public function setFields($fields)
    {
        $this->fields = array();
        if (!is_array($fields))
        {
            $fields = array($fields);
        }
        $this->layout = $fields;
        $this->_registerField($fields);
    }
    
    
    /**
     * This function is used internally of the form framework to 
     * <i>prepare</i> the form fields for the usage within the form
     * framework.
     *
     * The function basically renames all fields to 'frm_' + name and
     * assigns the value from the global array <code>$HTTP_POST_VARS</code>
     * if the field has no value (null) assigned.
     *
     * @param $field (reference) to a single field or an array of fields
     *               If an array of fields is passed, the function is called
     *               recursively for each of the contained fields.
     * 
     * @return (nothing)
     */
    private function /*PRIVATE*/ _registerField($field)
    {   
        if ( is_array($field) )
        {
            foreach ( $field as $i => $f )
            {
                $this->_RegisterField($f);
            }
        }
        else
        {
            if ($field instanceof MFormControl)
            {
                $field->form = $this;
            }
            $className = $field->className;
            if ($field instanceof MFileField)
            {
                $this->enctype='multipart/form-data';
                $this->page->setEnctype($this->enctype);

                if ( MUtil::isAjaxEvent() )
                {
                    $this->page->onload("miolo.getForm('{$this->page->getFormId()}').setEnctype('$this->enctype');");
                }
            }
            $namefield = ($field->name == $field->id) ? $field->name : $field->id;
    	    if ($namefield)
	        {
                $this->manager->assert(!isset($this->$namefield), _M("Err: property [$namefield] already in use in the form [$this->title]! Choose another name to [$namefield]."));
                $this->$namefield = $field;
	        }
            $value = $this->page->request($field->name);
            if ($field instanceof MFormControl)
            {
                if ( ($field instanceof MCheckBox) || ($field instanceof MRadioButton) )
                {
                    // set checked flag of checkbox or radiobutton if the value matches
                    $field->checked = $this->page->isPostBack() ? (isset($value) ? ($value == $field->value) : $field->checked) : $field->checked;
                }
                elseif ( ($field instanceof MIndexedControl) )
                {
                    $this->addFields($field->controls);
                    $field->setValue($value);
                }
                elseif (($field instanceof MInputGrid))
                {
                    $field->setValue($value);
                }
//                else
//                {
//                    $field->setValue($this->escapeValue($field->value));
//                }
                elseif ($field instanceof MInputControl) 
                {
                    if( $field->value == '' )
                    {
                        $field->setValue($value);
                    }
                }
//                else
//                {
//                    $field->setValue($field->value);
//                }
            }
            elseif ($field instanceof MContainer)
            {
                $this->_registerField($field->getControls());
                if ($field instanceof MRadioButtonGroup)
                {
                    $field->setValue($value);
                }
        
            }
            elseif ($field instanceof MDiv)
            {
                $this->_registerField($field->getInner());
            }
        }
    }
    
    /**
     * Adds a single field to the list of form fields and optionally adds
     * a hint text for the field.
     * 
     * @param (object) $field Form field object
     * @param (string) $hint Optional hinto for the form field
     */
    public function addField($field,$hint=false)
    {
        if ( $hint )
        {
            $field->setHint($hint);
        }
        $this->_RegisterField($field);
        $this->layout[] = $field;
    }


    public function addFields($fields)
    {
        if ( is_array($fields) )
        {
           foreach($fields as $f)
           {
              $this->addField($f);
           }
        }
    }
    
    
    /**
     * Add button to the form.
     * This method adds a button to the form. Existing buttons will remaing unchanged.
     *
     * @see setButtons()
     * @see MButton
     * 
     * @param (MButton) $btn Button object
     */
    public function addButton($button)
    {
        if (strtoupper($button->action == 'REPORT'))
        {
           $this->page->hasReport = true;
        }
        $button->form = $this;
        $this->buttons[] = $this->{$button->getId()} = $button;
    }

    
    /**
     * Sets the form buttons.
     * This method adds buttons to the form, but first removes existing ones.
     *
     * @see addButton() 
     * 
     * @param (mixed) $buttons MButton object or array of MButtons
     */
    public function setButtons($buttons)
    {
        $this->clearButtons();
        
        if ( is_array($buttons) )
        {
            for ( $i=0, $n = count($buttons); $i < $n; $i++ )
            {
                $this->addButton($buttons[$i]);
            }
        }
        else
        {
           $this->addButton($buttons);
        }
    }

    /**
     * Set the buttons labels.
     * This function is mainly used, to change the labels of the form's
     * default buttons for submit and return.
     *
     * @param (integer) $index The 0 based index of the button
     * @param (string) $label The new button label
     */
    public function setButtonLabel( $index, $label )
    {
        $this->buttons[$index]->label = $label;
    }

    /**
     * @deprecated This method is deprecated, use setShowReturnButton instead.
     */
    public function showReturn( $state )
    {
        $this->setShowReturnButton( $state );
    }

    /**
     * Return button visibility.
     * Use this function to set the visibility of the form's return button.
     *
     * @param (boolean) $state True to show, false to not show.
     */
    public function setShowReturnButton( $state )
    {
        $this->return = $state;
    }

    /**
     * Post button visibility.
     * Use this method to set the visibility of the Post Button.
     *
     * @param (boolean) $state The visible state of the Post Button
     */
    public function setShowPostButton( $state )
    {
        $this->defaultButton = $state;
    }

    /**
     * @deprecated This method is deprecated, use setShowResetButton instead.
     */
    public function showReset( $state )
    {
        $this->setShowResetButton( $state );
    }

    /**
    * Reset button visibility.
    * This function can be used to show or hide the form's reset button.
    *
    * @param (boolean) $state The visible state of the reset button
    */
    public function setShowResetButton( $state )
    {
        $this->reset = $state;
    }

    /**
     * Form's hints visibility.
     * This function returns the visibility of the form's hint texts.
     *
     * @see ShowHints
     */
    public function getShowHints()
    {
        return self::$showHints;
    }

    /**
     * Set form's hints visibility.
     * This function can be used to show or hide the form's hint texts.
     * Each form element may have a hint text associated with it. Using
     * this method, one can enable/disable the display of the texts. This
     * may be useful for implementing kind of an beginner/expert mode.
     *
     * @param (boolean) $state The visible state of the hint texts
     */
    public function setShowHints( $state )
    {
        self::$showHints = $state;
    }

    /**
     * @deprecated This method is deprecated, use setShowHints instead.
     */
    public function showHints( $state )
    {
        $this->setShowHints ( $state );
    }


    /**
     * Returns form fields list.
     * This is a placeholder function to bu the form's field list. It
     * is excpected, that the form returns a scalar list of all defined 
     * fields which carry a form field value. Thus, form elements of 
     * decorative purpose only should be omitted.
     * <br><br>
     * Derived classes such as <code>TabbedForm</code> override this
     * function to provide the list of fields independently of the form's
     * layout.
     *
     * @returns (array) a scalar array of form fields
     */
    public function getFieldList()
    {
        return $this->_getFieldList($this->fields);
    }

    /**
     * Returns field list.
     * Internal function which takes a list of form elements possibly
     * consisting of single fields as well as arrays and returns a scalar
     * the list of fields filtering out some known decorative form fields.
     *
     * @param  (array) $allfields An array of form fields
     * @return (array) A scalar array of form fields
     */
    private function _getFieldList($allfields)
    {
        $fields = array();
        foreach ($allfields as $f )
        {
            if ( is_array($f) )
            {
                foreach ( $f as $a )
                {
                    if (is_a($a,'MBaseLabel')) continue;
                    $fields[] = $a;
                }
            }
            else
            {
                if ( is_a($f,'MBaseLabel') || is_null($f->value) ) continue;
                $fields[] = $f;
            }
        }
        return $fields;
    }

    public function clearFields()
    {
        $this->fields = NULL;
        $this->layout = NULL;
    }

    public function clearField($name)
    {
        $f = $this->fields[$name];
        $f->addStyle('display','none');
    }


    /**
     * Remove existing buttons on the form.
     */
    public function clearButtons()
    {
        $this->buttons = NULL;
        $this->defaultButton = false;
    }

    /**
     * Validates the form input.
     * Check if form data is valid according to validator components added to 
     * form.
     *
     * @return boolean Return whether data is valid.
     */
    public function validate()
    {
        $MIOLO = MIOLO::getInstance();
        $this->errors = array();

        $data = $this->getAjaxData();

        foreach ( $this->validations as $validator )
        {
            eval("\$value = \$data->{$validator->field};");

            if ( !$validator->validate($value) )
            {
                $this->errors[$validator->field] = $validator->getError();
            }
        }

        $js = 'mvalidator.removeAllErrors();';

        if ( count($this->errors) > 0 )
        {
            $formId = $MIOLO->getPage()->getFormId();

            foreach ( $this->errors as $field => $error )
            {
                // Add the error message
                $error = str_replace("\n", '\n', $error); //troca linha nova do php para javascript
                $error = str_replace("'", "\'", $error); // retira ' para evitar erros de sintaxe js
                $js .= "mvalidator.addErrorToField('$error', '$field');";
            }
        }

        $MIOLO->page->onload($js);

        return count($this->errors) == 0;
    }

    /**
     * Regiter the error
     * Registers the error related to the form
     *
     * @param $err (tipo) desc
     * @return (tipo) desc
     */
    public function error( $err )
    {   
        $this->manager->logMessage(_M("[DEPRECATED] Call method Form::error() is deprecated -- use Form::addError() instead!"));
        $this->addError( $err );
    }

    /**
     * Adds the related form error
     *
     * @param (mixed) $err Error message string or array of messages
     */
    public function addError($err)
    {
        if ( $err )
        {
            if ( is_array($err) )
            {
                if ( $this->errors )
                {
                    $this->errors = array_merge($this->errors,$err);
                }
                
                else
                {
                    $this->errors = $err;
                }
            }
            else
            {
                $this->errors[] = $err;
            }
        }
    }

    
    /**
     *  Returns the number of error messages or 0 if no errors exist
     *
     * @return (integer) Error count
     */
    public function hasErrors()
    {
        return count($this->errors);
    }
    
    
    /**
     * Register an information related to the form
     *
     * @param (mixed) $info Information message string or array of messages
     */
    public function addInfo($info)
    {
        if ( $info )
        {
            if ( is_array($info) )
            {
                if ( $this->infos )
                {
                    $this->infos = array_merge($this->infos,$info);
                }
                
                else
                {
                    $this->infos = $info;
                }
            }
            else
            {
                $this->infos[] = $info;
            }
        }
    }

    
    /**
     * Returns the number of info messages or 0, if no info exist
     *
     * @return (integer) Information messages count
     */
    public function hasInfos()
    {
        return count($this->infos);
    }


    /**
     * Register an alert related to the form
     *
     * @param (mixed) $alert Message string or array of messages
     */
    public function addAlert($alert)
    {
        if ( $alert )
        {
            if ( is_array($alert) )
            {
                if ( $this->alerts )
                {
                    $this->alerts = array_merge($this->alerts,$alert);
                }
                else
                {
                    $this->alerts = $alert;
                }
            }
            else
            {
                $this->alerts[] = $alert;
            }
        }
    }


    /**
     * Returns the number of alert messages
     *
     * @return (integer) Alert messages count
     */
    public function hasAlerts()
    {
        return count($this->alerts);
    }


    /**
     * Get form data and put it into the classmembers
     */
    public function collectInput($data)
    {
        foreach ( $this->getFieldList() as $f )
        {
            $field = $f->name;
            if ( $field != '' )
            {
                // Remove [] from the end of the name
                if ( substr($field, strlen($field) - 2) == '[]' )
                {
                    $field = substr($field, 0, strlen($field) - 2);
                }

                $value = $this->getFormValue($field);

                $data->$field = $value;
            }
        }
        return $data;
    }

    /**
     * Obtains form fields in a FormData object.
     *
     * @return (Object) Form fields
     */
    public function getData()
    {
        return $this->collectInput( new FormData() );
    }

    /**
     * Set data on the form fields.
     * Set form fields values. A subclassed form will likely override this
     * method, in order to provide a specialized processing for the passed
     * data object. <br><br>
     * This method simply calls the <code>_setData()</code> method of the
     * <code>Form</code> class.
     * @example
     * $data = new FormData();
     * $form->setData( $data );
     *
     * @param $data (MBusiness Object) object containing the field values
     */
    public function setData( MBusiness $data)
    {
        $this->_setData($data);
    }
    
    
    /**
     * This method sets the form field values in way, that it iterates
     * thru the list of fields and sets the values of all fields matching
     * the data object attribute names.
     * 
     * In short it does the following<br>
     * @example 
     * <code>$frm_name = $data->name;</code>
     * 
     * This implies that the form field names must be identical the data
     * member names.
     * 
	 * @param $data (array) Data to be assigned to formfields
 	 *
 	 * @return (void)
 	 */
    private function _setData($data)
    {
        foreach ( $this->fields as $field=>$name)
        {
            $name = $this->fields[$field]->name;
            if ( $name )
            {
                if ( ($this->fields[$field] instanceof MRadioButton) || 
                     ($this->fields[$field] instanceof MCheckBox) )
                {
                    $this->fields[$field]->checked = ( $data->$name == $this->fields[$field]->value );
                }
                else
                { 
                    $value = $data->$name;
                    
                    if ( ($this->fields[$field] instanceof MFormControl) &&
                         (isset($value)) )
                    {
                        $this->fields[$field]->setValue($value);
                        $_POST[$name] = $value;
                    }
                }
            }
        }
    }
    
    
    /**
     * Obtains a form field's value
     */
    public function getFieldValue($name,$value=false)
    {   
        $field = $this->fields[$name];
        return ($field ? $field->getValue() : NULL);
    }
    
    
    /**
     * Set a form field's value
     */
    public function setFieldValue($name,$value)
    {   
        $field = $this->fields[$name];
        $field->setValue($value);
    }
    
    
    /**
     * Set a form field's validator
     */
    public function setFieldValidator($name,$value)
    {   
        for ( $i=0, $n = count($field); $i < $n; $i++ )
        {
            if ( $name == $this->fields[$i]->name )
            {
                $this->fields[$i]->validator = $value;
                break;
            }
        }
    }
    
    
    /**
     * Get a reference for a form field
     */
    public function & getField($name)
    {
        return $this->fields[$name];
    }
    
    
    /**
     * Get a reference for a button
     */
    public function & getButton($name)
    {
        for ( $i=0, $n = count($this->buttons); $i < $n; $i++ )
        {
            if ( $name == $this->buttons[$i]->name )
            {
                return $this->buttons[$i];
            }
        }
    }
    
    
    /**
     * Get a reference for page
     */
    public function & getPage()
    {
        return $this->page;
    }
    
    
    /**
     * Set reference for page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
    
    
    /**
     * Set a form field's attribute a value
     */
    public function setFieldAttr($name,$attr,$value)
    {   
        $this->fields[$name]->$attr = $value; 
    }
    
    
    /**
     * Get a form field's attribute value
     */
    public function getFieldAttr($name,$attr, $index=NULL)
    {   
        $field = $this->fields[$name];
        if ( is_array($field->$attr) )
        {
            $a = $field->$attr;
            $value = $a[$index];
        }
        else
        {
          $value = $field->$attr;
        }
        return $value;
    }
    
    
    /**
     * Set a form field's attribute a value
     */
    public function setButtonAttr($name,$attr,$value)
    {   
        $button = &$this->getButton($name);
        $button->$attr = $value;
    }
    
    /**
     * Set a Ajax response
     */
    public function setResponse($controls, $element='', $generateFormLayout=false)
    { 
        if ($element == '')
        {
            $element = $this->manager->_request('__FORMSUBMIT') . '_content';
        }
        $this->manager->ajax->setResponseControls($controls, $element, $generateFormLayout);
        $this->page->onload("dojo.parser.parse('$element');");
    }


    public function getCloseWindow()
    {
        return $this->manager->getUI()->closeWindow();
    }

   /**
    * Renderize
    *
    * @param (integer) $width
    */
   public function setLabelWidth($width)
   {
        if ( (strpos($width, '%') === false) && (strpos($width, 'px') === false) )
        {
            $width = "{$value}%";
        }
        $this->labelWidth = $width; 
   } 

   /**
    * Set all fields as read-only.
    *
    * @param boolean $readOnly Whether is read-only.
    */
    public function setFieldsReadOnly($readOnly=true)
    {
        foreach ( (array) $this->fields as $field )
        {
            if ( $field instanceof MTabbedBaseGroup )
            {
                foreach ( (array) $field->getTabs() as $tab )
                {
                    foreach ( (array) $tab->getControls() as $control )
                    {
                        $this->setFieldReadOnly($control, $readOnly);
                    }
                }
            }
            elseif ( $field instanceof MContainer )
            {
                foreach ( (array) $field->getControls() as $control )
                {
                    $this->setFieldReadOnly($control, $readOnly);
                }
            }
            elseif ( $field instanceof MDiv )
            {
                foreach ( (array) $field->getInner() as $control )
                {
                    $this->setFieldReadOnly($control, $readOnly);
                }
            }
            else
            {
                $this->setFieldReadOnly($field, $readOnly);
            }
        }
    }

    /**
     * Set a field as read-only.
     *
     * @param object $field MControl instance.
     * @param boolean $readOnly Whether is read-only.
     */
    public function setFieldReadOnly($field, $readOnly=true)
    {
        if ( is_object($field) && method_exists($field, 'setReadOnly') )
        {
            $field->setReadOnly($readOnly);
        }
        elseif ( is_object($field) )
        {
            $field->readonly = $readOnly;
        }
    }

   public function submit_button()
   {
        $this->setResponse(new stdClass,'');
   }
   
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
   public function generateErrors()
   {    
        $prompt = MPrompt::error($this->errors,'NONE',_M('Errors'));
        return $prompt;
   }
   
   
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
   public function generateInfos()
   {    
        $prompt = MPrompt::information($this->infos,'NONE',_M('Information'));
        return $prompt;
   }


   /**
    * Generate a MPrompt object with all the alerts added to $this->alerts
    *
    * @returns (MPrompt) Alert MPrompt object
    */
    public function generateAlerts()
    {
        return MPrompt::alert($this->alerts, 'NONE', _M('Alerts'));
    }


/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
   public function generateBody()
   {  
        $row = 0;
        $t = array();

        // optionally generate errors
        if ( $this->hasErrors() )
        {
            $t[] = $this->generateErrors();
        }
        if ( $this->hasInfos() )
        {
            $t[] = $this->generateInfos();
        }
        if ( $this->hasAlerts() )
        {
            $t[] = $this->generateAlerts();
        }
        $hidden = null;
        $t = array_merge($t, $this->generateLayoutFields($hidden));
        
        if( method_exists($this->page,'getLayout') )
        {
            $layout = $this->page->theme->getLayout();
        }
        else
        {
            $layout = $this->manager->theme->getLayout();
        }
        
        if ( $layout != 'print')
        {
            $buttons = $this->generateButtons();
            if ($buttons)
            {
                $t = array_merge($t,array($buttons));
            }
        }
        if ($this->action == '')
        {
           $this->action = $this->page->action;
        } 
        $hidden[] = new MHiddenField($this->getId() . '_action',$this->action);

        if ( $hidden )
        {
           $t = array_merge($t,$this->generateHiddenFields($hidden));
        }
        $t = array_merge($t,$this->generateScript());
        $body = new MDiv('',$t);
        return $body;
   }
   
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
   public function generateFooter()
   {
   }
   
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param &$hidden (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
   public function generateLayoutFields(&$hidden)
   {  
       $line = 0;  
       $zebra = is_array($this->zebra);
       $t = array();
       if (is_array($this->layout))
       {
           foreach ( $this->layout as $f )
           {
               if ( $f->validator != NULL)
               {
                    if ($f->validator instanceof MValidator)
                    {
                        $this->addValidator($f->validator);
                    }
               }
               else
               {
                    foreach($this->validations as $validator)
                    {
                        if($validator->field == $f->name)
                        {
                            $f->validator = $validator;
                        }
                    }
               }
               $row = $t[] = $this->generateLayoutField($f, $hidden);
               if ($zebra)
               {
                   $row->addStyle('background-color', $this->zebra[($line++) % 2]);
               }
           }
       }
       return $t;
   }

    public function getFieldValidator($name)
    {
        foreach($this->validations as $validator)
        {
            if( $validator && $validator->field == $name )
            {
                return $validator;
            }
        }
        return false;
    }
   
    /**
     * Generate field according to form layout.
     *
     * @param mixed $f Field or array of fields.
     * @param array $hidden Array of hidden fields.
     * @return MDiv Div containing the field(s) with the class mFormRow.
     */
    public function generateLayoutField($f, &$hidden=array())
    {  
        if ( is_array($f) )
        {
            $c = array();
            foreach($f as $fld)
            {
                if ($fld->visible)
                {
                    $c[] = $fld;
                }
            }
            $f = new MHContainer('',$c);
            $f->showLabel = true;
        }

        if ( ! $f->visible )
        {
            return;
        }

        $rowContent = NULL;
        $label = $f->label;
        if ( ( ( ($f->className == 'textfield') || ($f->className == 'mtextfield'))  && ($f->size==0) ) || ($f instanceof  MHiddenField) )
        {
            $hidden[] = $f;
            return;
        }

        if ( ( $f->readonly || $f->maintainstate) )
        {
            $hidden[] = $f;
        }

        if ( $f->cssp )
        {
            return $f;
        }
   
        if (($f->formMode != MFormControl::FORM_MODE_SHOW_SIDE) || 
           (($f->formMode == MFormControl::FORM_MODE_SHOW_SIDE) && (! $label)))
        {
            $rowContent[] = $f;
        }
        else
        {

            if ( $label != '' && $label != '&nbsp;' )
            {
                $label .= ':';
            }
            $tf = array();
            if ($label != '') 
            {  
                if ($f->id != '')
                { 
                    $lbl = new MFieldLabel($f->id,$label);

                    if($f->validator && $f->validator->type == 'required')
                    {
                        $lbl->setClass('mCaptionRequired');
                    }
                    else
                    {
                        $lbl->setClass('mCaption');
                    }
                }
                else
                {
                    $lbl = new MSpan('',$label,'mCaption');
                }

                $slbl = $rowContent[] = new MSpan('',$lbl,'label');
                if ($this->labelWidth != NULL)
                {
                    $slbl->addStyle('width',$this->labelWidth);
                }
            }

            $rowContent[] = new MSpan('',$f,'field');

/*
            if ($this->labelWidth != NULL)
            {
                $sfld->addStyle('width', (95 - $this->labelWidth).'%');
            }
*/

        }
//        $rowContent[] = new MSpacer();
        $rowContent[] = new MDiv('','','clear');
        return new MDiv('',$rowContent, "mFormRow");
    }

    /**
     * @param boolean $jsValidationEnabled Define whether the form must validate data via JavaScript.
     */
    public function setJsValidationEnabled($jsValidationEnabled)
    {
        $this->jsValidationEnabled = $jsValidationEnabled;
    }

    /**
     * @return boolean Return whether the form must validate data via JavaScript.
     */
    public function getJsValidationEnabled()
    {
        return $this->jsValidationEnabled;
    }

    public function generateButtons()
    {
        $ul = new MUnorderedList();
        if (isset($this->buttons) )
        {
            $c[] = new MHr;
            foreach ( $this->buttons as $b )
            {
                if ($b->visible) $buttons[] = $b;
            }
        }
        if ( $this->reset )
        {
            $buttons[] = new MButton('_reset',_M("Clear"),'RESET');
        }
        if ( $this->return )
        {
            $buttons[] = new MButton('_return',_M("Back"),'RETURN');
        }
        $ul->addOption(new MDiv('',$buttons));
        $d = (count($ul->options) ? new MDiv('',array($c,$ul),'mFormButtonBox') : NULL);
        return ($d ? new MDiv('',$d,'mFormRow') : NULL);
    }


    public function generateHiddenFields($hidden)
    {
        $f[] = "\n<!-- START OF HIDDEN FIELDS -->";
        foreach ( $hidden as $h )
        {
            $f[] = new MHiddenField($h->name,$h->value);
        }
        $f[] = "\n<!-- END OF HIDDEN FIELDS -->";
        return $f;
    }

    
    /**
     * Generate form specific script code
     */
    public function generateScript()
    {
        if ($this->focus != '')
        {
            $jsCode = " element = miolo.getElementById('{$this->focus}');
                        if ( element )
                        {
                            element.focus();
                        } ";
            $this->page->onLoad($jsCode);
        }
        $f = array();
        if ( $this->validations && $this->jsValidationEnabled )
        {
            MValidator::generateJSValidators($this->validations);
        }
        return $f;
    }

    public function generateInner()
    {
        if (!isset($this->buttons))
        {
            if ($this->defaultButton)
            {
                $this->buttons[] = new MButton(FORM_SUBMIT_BTN_NAME, _M("Send"), 'SUBMIT');
            }
        }
        if ($this->cssForm)
        {
            $body = $this->generateBody();
            $this->setControls(array($body));
            $this->setClass('mFormCSS');
            return $this->generate();
        }
        else
        {
            $body = new MDiv('',$this->generateBody(),'mFormBody');
            if (!is_null($this->bgColor))
            {
                $body->addStyle('background-color',$this->bgColor);
            }

            $this->inner = $body;
        }
    }
}

class FormData
{
}

?>
