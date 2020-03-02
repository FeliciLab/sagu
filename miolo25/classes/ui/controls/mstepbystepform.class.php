<?php

/**
 * MStepByStep Class
 * Form with a step by step logic
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/11/08
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

class MStepByStepForm extends MForm
{
    public $steps;
    public $step;
    public $nextStep;
    public $stepName;
    public $prevStep;
    public $data;

    /**
     * @var array MStep instances.
     */
    public $stepButtons = array();

    /**
     * @var array Buttons to navigate between steps and close.
     */
    public $controlButtons;

    private static $defaultButtons = true;
    private static $showImageOnButtons = false;

    const BUTTONS_DIV_ID = 'stepButtonsDiv';
    const BUTTONS_DIV_STYLE = 'mStepbystepButtons';
    const BUTTONS_DIV_IMG_STYLE = 'mStepbystepButtonsImg';

    /**
     * Ids for buttons.
     */
    const CANCEL_BUTTON_ID = 'cancelButton';
    const PREVIOUS_STEP_BUTTON_ID = 'previousStepButton';
    const NEXT_STEP_BUTTON_ID = 'nextStepButton';
    const FINALIZE_BUTTON_ID = 'finalizeButton';
    const CLOSE_BUTTON_ID = 'closeButton';

    /**
     * MStepByStepForm construct method
     *
     * @param string $title Form title
     * @param array $steps Form steps
     * @param integer $step Current step
     * @param integer $nextStep Next step
     */
    public function __construct($title, $steps = NULL, $step = NULL, $nextStep = NULL)
    {
        $MIOLO = MIOLO::getInstance();

        $this->steps = $steps;
        $this->stepName = MIOLO::getCurrentAction();

        if ( !isset($_REQUEST['step']) && MUtil::isFirstAccessToForm() )
        {
            $MIOLO->getSession()->setValue($this->stepName . '_history', array());
        }

        parent::__construct($title);
        $this->setJsValidationEnabled(FALSE);
        $this->eventHandler();
        
        $this->buttons = array();
        $this->step = $step;
        $this->nextStep = $nextStep;
    }

    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        parent::createFields();

        if ( !self::getCurrentStep() )
        {
            $this->cleanStepData();
        }

        $this->step = $this->step ? $this->step : self::getCurrentStep() ? self::getCurrentStep() : 1;
        $this->nextStep = $this->nextStep ? $this->nextStep : $this->step+1;

        $stepImageUrl = $MIOLO->getUI()->getImageTheme($MIOLO->getTheme()->getId(), 'button_steps.png');

        foreach ( $this->steps as $k => $stp )
        {
            if ( $this->step == $k )
            {
                $type = MStep::TYPE_CURRENT;
            }
            elseif ( $this->step < $k )
            {
                $type = MStep::TYPE_NEXT;
            }
            elseif ( $this->step > $k )
            {
                $type = MStep::TYPE_PREVIOUS;
            }

            $this->stepButtons[$k] = $stepsDiv[$k] = new MStep($k, $stp, $type);
        }

        /*
         * If it has more than five steps shows the first, the previous,
         * the current, the next and the last one.
         */
        if ( count($this->steps) > 5 )
        {
            $asteps[] = $stepsDiv[1];
            if ( $this->step <= 3 )
            {
                $asteps[] = $stepsDiv[2];
                $asteps[] = $stepsDiv[3];
                $asteps[] = $stepsDiv[4];
                $asteps[] = new MDiv(NULL, '...', MStep::HIDE_STYLE);
                $asteps[] = $stepsDiv[count($this->steps)];
            }
            elseif ( $this->step >= (count($this->steps) - 2) )
            {
                $step = count($this->steps) - 2;
                $asteps[] = new MDiv(NULL, '...', MStep::HIDE_STYLE);
                $asteps[] = $stepsDiv[$step - 1];
                $asteps[] = $stepsDiv[$step];
                $asteps[] = $stepsDiv[$step + 1];
                $asteps[] = $stepsDiv[count($this->steps)];
            }
            else
            {
                $asteps[] = new MDiv(NULL, '...', MStep::HIDE_STYLE);
                $asteps[] = $stepsDiv[$this->step - 1];
                $asteps[] = $stepsDiv[$this->step];
                $asteps[] = $stepsDiv[$this->step + 1];
                $asteps[] = new MDiv('', '...', MStep::HIDE_STYLE);
                $asteps[] = $stepsDiv[count($this->steps)];
            }
        }
        else
        {
            $asteps = $stepsDiv;
        }

        $fields[] = new MBaseGroup(NULL, NULL, $asteps);
        $fields[] = MMessage::getMessageContainer();

        $this->addFields($fields);

        $this->loadButtons();
    }

    /**
     * Saves the step data and redirects to the next step
     *
     */
    public function nextStepButton_click()
    {
        if ( !$this->getJsValidationEnabled() && !$this->validate() )
        {
            new MMessageWarning(_M('Check input data.'));
            return;
        }

        if ( !$this->nextStep )
        {
            $this->nextStep = $this->step ? $this->step + 1 : 2;
        }

        $this->prepareData($this->getData());
        self::setCurrentStep($this->nextStep);

        $this->redirect(MIOLO::getCurrentModule(), MIOLO::getCurrentAction(), $_GET);
    }

    /**
     * Saves the step data and redirects to the previous step
     *
     */
    public function previousStepButton_click()
    {
        if ( !$this->prevStep )
        {
            $this->prevStep = $this->step - 1;
        }

        if ( $this->prevStep < 1 )
        {
            $this->prevStep = 1;
        }

        $this->prepareData($this->getData());
        self::setCurrentStep($this->prevStep);

        $this->redirect(MIOLO::getCurrentModule(), MIOLO::getCurrentAction(), $_GET);
    }

    /**
     * Redirects the user to the given step
     *
     * @param integer $step Step number
     */
    public function gotoStep($step)
    {
        self::setCurrentStep($step);
        $this->redirect(MIOLO::getCurrentModule(), MIOLO::getCurrentAction(), $_GET);
    }

    /**
     * Converts an object to array
     *
     * @param object Object
     * @return array Array
     */
    public function objectToArray($object)
    {
        if ( count($object) > 1 )
        {
            $arr = array( );
            for ( $i = 0; $i < count($object); $i++ )
            {
                $arr[] = get_object_vars($object[$i]);
            }
            return $arr;
        }
        else
        {
            return get_object_vars($object);
        }
    }

    /**
     * @return string Returns the step name
     */
    public function getStepName()
    {
        return $this->stepName;
    }

    /**
     * @param string $stepName Sets the step name
     */
    public function setStepName($stepName)
    {
        $this->stepName = $stepName;
    }

    /**
     * Sets data on the given step
     *
     * @param object $data Data object
     * @param integer $step Step number
     */
    public function setStepData($data, $step = null)
    {
        $data = (object) array_merge($this->getStepData($step, false), (array) $data);
        $this->prepareData($data, $step);
    }

    /**
     * Returns the given step data, or all steps data, if $step is null
     *
     * @param integer $step Step number
     * @param boolean $returnAsObject Sets the return type
     * @return unknown Returns an object if $returnAsObject is true and an array if false
     */
    public function getStepData($step = null, $returnAsObject = true)
    {
        $stepsData = $this->getAllStepData();

        // gets the last step data because it's not on session yet
        if ( $this->isLastStep() )
        {
            $data = self::objectToArray($this->getData());
            $lastStep = array_pop(array_keys($this->steps));
            $stepsData[$lastStep] = $data;
        }

        // if a step is given, returns only the specific step data
        if ( $step )
        {
            $data = $returnAsObject ? (object) $stepsData[$step] : $stepsData[$step];
        }
        else
        {
            if ( $returnAsObject )
            {
                $returnData = array();
                foreach ( $stepsData as $key => $data )
                {
                    $returnData = array_merge($returnData, $data);
                }

                $data = (object) $returnData;
            }
            else
            {
                $data = $stepsData;
            }
        }

        return $data;
    }

    /**
     * Clean the data of one step or of all steps
     *
     * @param integer $step Step number
     */
    public function cleanStepData($step=null)
    {
        if ( $step )
        {
            $stepsData = $this->getAllStepData();
            $stepsData[$step] = NULL;

            $this->setAllStepData($stepsData);
        }
        else
        {
            $this->setAllStepData(NULL);
        }
    }

    /**
     * Prepares the step data (current step, if none is informed) to put them on session
     *
     * @param unknown $data Array or object
     * @param integer $step Step number
     */
    public function prepareData($data = null, $step = null)
    {
        if ( !$step )
        {
            // the first time on the first step, the step is still undefined
            $step = $this->step ? $this->step : 1;
        }

        if ( is_object($data) )
        {
            $data = self::objectToArray($data);
        }

        // verifies if there is already data on session of this step by step
        $stepData = $this->getStepData(null, false);

        // adds the step data array to be saved on session
        $stepData[$step] = $data;

        $this->setAllStepData($stepData);
    }

    /**
     * Saves data from all steps on session
     *
     * @param array $stepData Data from all the steps
     */
    public function setAllStepData($stepData)
    {
        // serializes the data and saves it
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getSession()->setValue($this->getStepName(), serialize($stepData));
    }

    /**
     * Gets steps data from session and unserialize it
     *
     * @return array Unserialized data array
     */
    public function getAllStepData()
    {
        $MIOLO = MIOLO::getInstance();
        $data = unserialize($MIOLO->getSession()->getValue($this->getStepName()));
        return $data;
    }

    /**
     * @return integer Returns the current step
     */
    public static function getCurrentStep()
    {
        return MIOLO::_REQUEST('step');
    }

    /**
     * @param integer $step Sets the current step
     */
    public static function setCurrentStep($step)
    {
        $_GET['step'] = $step;
    }

    /**
     * @return boolean Returns if it is set to show image on buttons or not
     */
    public static function getShowImageOnButtons()
    {
        return self::$showImageOnButtons;
    }

    /**
     * @param boolean $showImageOnButtons Sets to show image on buttons or not
     */
    public static function setShowImageOnButtons($showImageOnButtons)
    {
        self::$showImageOnButtons = $showImageOnButtons;
    }

    /**
     * Create control buttons based on the current step
     */
    public function loadButtons()
    {
        if ( $this->isFirstStep() )
        {
            $buttons = $this->firstStepButtons();
        }
        elseif ( $this->isLastStep() )
        {
            $buttons = $this->lastStepButtons();
        }
        else
        {
            $buttons = $this->innerStepButtons();
        }

        $this->controlButtons = $buttons;
    }

    /**
     * Finalizes the step by step, by changing the buttons with the specified
     * ones (or with a default close button) and disabling the step buttons
     *
     * @param array $buttons Array with buttons (MButton) to be displayed
     * @param string $closeAction Close button action.
     */
    public function finalizeStepByStep($buttons=NULL, $closeAction=NULL)
    {
        $label = _M('Finished step');

        $jsCode = "
            miolo.getElementById('stepImage_{$this->step}').className = '".MStep::PREVIOUS_ICON_STYLE."';
            miolo.getElementById('stepDescription_{$this->step}').innerHTML = '$label'; ";

        foreach ( $this->steps as $step => $stepName )
        {
            $jsCode .= "
                var element = miolo.getElementById('divStep_$step');
                if ( element )
                {
                   element.className = '".MStep::DISABLED_STYLE."';
                   element.onclick = '';
                }";
        }

        $this->page->onLoad($jsCode);

        if ( $buttons )
        {
            $this->manager->ajax->setResponse($buttons, self::BUTTONS_DIV_ID);
        }
        else
        {
            $this->manager->ajax->setResponse($this->closeButton($closeAction), self::BUTTONS_DIV_ID);
        }
    }

    /**
     * @param boolean $defaultButtons Sets if the default buttons must be shown
     */
    public static function setDefaultButtons($defaultButtons)
    {
        self::$defaultButtons = $defaultButtons;
    }

    /**
     * @return boolean Returns if the default buttons must be shown
     */
    public static function getDefaultButtons()
    {
        return self::$defaultButtons;
    }
    
    /**
     * Loads the data automatically
     * It is called after createFields
     */
    public function onLoad()
    {
        if ( !$this->isFirstAccess($this->step) )
        {
            $this->setData($this->getStepData($this->step));
        }

        // Initialize history for isFirstAccess method
        $stepsData = $this->getAllStepData();
        $data = $stepsData[1];

        if ( !isset($data) )
        {
            $history = array(1);
        }
        else
        {
            $history = (array) $this->manager->getSession()->getValue($this->stepName . '_history');

            if ( !in_array($this->step, $history) )
            {
                $history[] = $this->step;
            }
        }

        $this->manager->getSession()->setValue($this->stepName . '_history', $history);
    }

    /**
     * @return boolean Returns true if current step is the first step
     */
    public function isFirstStep()
    {
        if ( self::getCurrentStep() == 1 || !self::getCurrentStep() || $this->step == 1 )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * @return boolean Returns true if the current step is the last step
     */
    public function isLastStep()
    {
        $lastStep = array_pop(array_keys($this->steps));
        return $lastStep == $this->step;
    }

    /**
     * Checks on session if the data of the given step was already set
     *
     * @param $step The step number. If not informed, it uses the current step
     * @return boolean True if data was already set
     */
    public function isFirstAccess($step = NULL)
    {
        if ( !$step )
        {
            $step = $this->step;
        }

        $history = (array) $this->manager->getSession()->getValue($this->stepName . '_history');

        return !in_array($step, $history);
    }

    /**
     * Method called on cancel button action
     *
     */
    public function cancelButton_click($args)
    {
        $this->redirect(MIOLO::getCurrentModule(), MIOLO::getCurrentAction());
    }

    /**
     * Method called on finalize button action
     * This is an example method. You must override it to save the form data
     * 
     */
    public function finalizeButton_click($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $url = $MIOLO->getActionUrl($module, 'main');
        $buttons[] = $this->closeButton($url);
        $this->finalizeStepByStep($buttons);
    }

    /**
     * Redirects the page to the given action of the given module, with the
     * given arguments
     *
     * @param string $module Module
     * @param string $action Action
     * @param array $args Arguments
     */
    public function redirect($module, $action, $args = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getActionURL($module, $action, '', $args);
        $MIOLO->page->redirect($url);
    }

    /**
     * @return MButton Default cancel button
     */
    public function cancelButton($action=NULL)
    {
        if ( !$action )
        {
            $action = $this->manager->getActionUrl(MIOLO::getCurrentModule(), $this->manager->context->getPreviousAction());
        }

        if ( self::$showImageOnButtons )
        {
            $image = $this->manager->ui->getImageTheme(NULL, 'button_cancel.png');
        }

        return new MButton(self::CANCEL_BUTTON_ID, _M('Cancel'), $action, $image);
    }

    /**
     * @return MButton Default next step button
     */
    public function nextStepButton()
    {
        if ( self::$showImageOnButtons )
        {
            $image = $this->manager->ui->getImageTheme(NULL, 'button_next.png');
        }
        return new MButton(self::NEXT_STEP_BUTTON_ID, _M('Next step'), ':nextStepButton_click', $image);
    }

    /**
     * @return MButton Default previous step button
     */
    public function previousStepButton()
    {
        if ( self::$showImageOnButtons )
        {
            $image = $this->manager->ui->getImageTheme(NULL, 'button_previous.png');
        }
        return new MButton(self::PREVIOUS_STEP_BUTTON_ID, _M('Previous step'), NULL, $image);
    }

    /**
     * @return MButton Default finalize button
     */
    public function finalizeButton()
    {
        if ( self::$showImageOnButtons )
        {
            $image = $this->manager->ui->getImageTheme(NULL, 'button_finalize.png');
        }
        return new MButton(self::FINALIZE_BUTTON_ID, _M('Finalize'), NULL, $image);
    }

    /**
     * @return MButton Default close button.
     */
    public function closeButton($url)
    {
        if ( self::$showImageOnButtons )
        {
            $image = $this->manager->ui->getImageTheme(NULL, 'button_close.png');
        }
        return new MButton(self::CLOSE_BUTTON_ID, _M('Close'), $url, $image);
    }

    /**
     * @return array Default buttons for the first step
     */
    public function firstStepButtons()
    {
        return array(
            self::CANCEL_BUTTON_ID => $this->cancelButton(), 
            self::NEXT_STEP_BUTTON_ID => $this->nextStepButton()
        );
    }

    /**
     * @return array Default buttons for steps between the first and the last
     */
    public function innerStepButtons()
    {
        return array(
            self::CANCEL_BUTTON_ID => $this->cancelButton(),
            self::PREVIOUS_STEP_BUTTON_ID => $this->previousStepButton(),
            self::NEXT_STEP_BUTTON_ID => $this->nextStepButton()
        );
    }

    /**
     * @return array Default buttons for the last step
     */
    public function lastStepButtons()
    {
        return array(
            self::CANCEL_BUTTON_ID => $this->cancelButton(),
            self::PREVIOUS_STEP_BUTTON_ID => $this->previousStepButton(),
            self::FINALIZE_BUTTON_ID => $this->finalizeButton()
        );
    }

    /**
     * @param array $buttons MButton array
     * @return MDiv Default buttons div
     */
    public function buttonsDiv($buttons)
    {
        $class = self::$showImageOnButtons ? self::BUTTONS_DIV_IMG_STYLE : self::BUTTONS_DIV_STYLE;
        return new MDiv(self::BUTTONS_DIV_ID, $buttons, $class, 'align="center"');
    }

    public function generate()
    {
        if ( self::$defaultButtons )
        {
            $this->addFields(array( $this->buttonsDiv($this->controlButtons) ));
        }

        return parent::generate();
    }
}
?>
