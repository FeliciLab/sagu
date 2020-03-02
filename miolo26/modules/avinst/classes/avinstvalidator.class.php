<?php

/**
 * Validator base class.
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/08/02
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

class AvinstValidator extends MFormControl
{
    public $field;
    public $min;
    public $max;
    public $type = 'required';  // ex. required | optional | ignore/ readonly
    public $chars;
    public $mask;
    public $checker;
    public $msgerr;
    public $html;

    /**
     * @var string Validator last error.
     */
    protected $error = NULL;

    public function __construct()
    {
        parent::__construct('');
        $this->checker = '';
        $this->html = '';
        $this->page->addScript('m_validate.js');
        $this->page->addScript('avinstvalidator.js', 'avinst');
    }

    /**
     * Validate value according to validator rules.
     *
     * @param mixed $value Field value.
     * @return boolean Whether field value is valid.
     */
    public function validate($value)
    {
        $valid = true;

        // Check required
        if ( $this->type == 'required' )
        {
            // For single selection or single info fill
            if ( $value == NULL )
            {
                $this->error = _M('This field is required');
                $valid = false; 
            }
        }
        return $valid;
    }

    /**
     * @return string Return the error messages. If validation succeeded, 
     * the message is NULL.
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @deprecated Use generateJSValidators instead.
     */
    public static function generateValidators($validators)
    {
        self::generateJSValidators($validators);
    }

    /**
     * Generate JavaScript validators.
     *
     * @param array $validators MIOLO validators instances array.
     */
    public static function generateJSValidators($validators)
    {
        $MIOLO = MIOLO::getInstance();

        $formId = $MIOLO->getPage()->getFormId();
        $MIOLO->getPage()->onLoad("miolo.getForm('{$formId}').validators = new Miolo.validate();");
        $MIOLO->getPage()->onSubmit("miolo.getForm('{$formId}').validators.process()");

        foreach ( $validators as $v )
        {
            $v->generate();
        }
    }

    public static function ifProcess($command)
    {
        $MIOLO = Miolo::getInstance();
        $formId = $MIOLO->getPage()->getFormId();
        return "if (miolo.getForm('{$formId}').validators.process()) { " . $command . "};";
    }

    public function generate()
    {
        $v = "{id: '{$this->id}', form: '{$this->form}', field: '{$this->field}', label: '{$this->label}', min: '{$this->min}', max: '{$this->max}', type: '{$this->type}', chars: '{$this->chars}', mask: '{$this->mask}', msgerr: '{$this->msgerr}', {$this->html} checker: '{$this->checker}'}";
        $this->manager->getPage()->onLoad("miolo.getForm('{$this->formId}').validators.add({$v});");
    }
}

?>
