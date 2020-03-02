<?php

/**
 * MStep Class
 * Represents the step button used by MStepByStepForm
 *
 * @author Armando Taffarel Neto [taffarel@solis.coop.br]
 * @author Daniel Hartmann [daniel@solis.coop.br]
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

class MStep extends MDiv
{
    const TYPE_PREVIOUS = 'previous';
    const TYPE_CURRENT = 'current';
    const TYPE_NEXT = 'next';

    // CSS style classes
    const CURRENT_ICON_STYLE = 'mStepIcon mStepIconCurrent';
    const NEXT_ICON_STYLE = 'mStepIcon mStepIconNext';
    const PREVIOUS_ICON_STYLE = 'mStepIcon mStepIconPrevious';
    const LABEL_STYLE = 'mStepLabel';
    const CURRENT_LABEL_STYLE = 'mStepLabel mStepCurrentLabel';
    const CURRENT_STYLE = 'mStepCurrent';
    const ENABLED_STYLE = 'mStep mStepEnabled';
    const DISABLED_STYLE = 'mStep mStepDisabled';
    const STATUS_LABEL_STYLE = 'mStepStatusLabel';
    const BUTTON_STYLE = 'mStepButton';
    const NUMBER_STYLE = 'mStepNum';
    const HIDE_STYLE = 'mStepHide';
    const INFO_STYLE = 'mStepInfo';

    public function __construct($stepNumber, $stepName, $stepType = self::TYPE_CURRENT)
    {
        $MIOLO = MIOLO::getInstance();

        $_GET['step'] = $stepNumber;

        $completedStep = false;

        if ( $stepType == self::TYPE_CURRENT )
        {
            $stepLabel = new MDiv('stepLabel_' . $stepNumber, $stepName, self::CURRENT_LABEL_STYLE);
            $stepImage = new MDiv('stepImage_' . $stepNumber, '', self::CURRENT_ICON_STYLE);
            $stepDescription = new MDiv('stepDescription_' . $stepNumber, _M('Current step'), self::STATUS_LABEL_STYLE);
        }
        elseif ( $stepType == self::TYPE_NEXT )
        {
            $stepLabel = new MDiv('stepLabel_' . $stepNumber, $stepName, self::LABEL_STYLE);
            $stepImage = new MDiv('stepImage_' . $stepNumber, '', self::NEXT_ICON_STYLE);
            $stepDescription = new MDiv('stepDescription_' . $stepNumber, _M('Pending step'), self::STATUS_LABEL_STYLE);
        }
        elseif ( $stepType == self::TYPE_PREVIOUS )
        {
            $stepLabel = new MDiv('stepLabel_' . $stepNumber, $stepName, self::LABEL_STYLE);
            $stepImage = new MDiv('stepImage_' . $stepNumber, '', self::PREVIOUS_ICON_STYLE);
            $stepDescription = new MDiv('stepDescription_' . $stepNumber, _M('Finished step'), self::STATUS_LABEL_STYLE);
            $completedStep = true;
        }

        $divLeft = new MDiv('divLeft_' . $stepNumber, $stepImage, self::BUTTON_STYLE);
        $divRight = new MDiv('divRight_' . $stepNumber, $stepNumber, self::NUMBER_STYLE);
        $div = new MDiv('divStepInner_' . $stepNumber, array( $divRight, $divLeft, $stepDescription ), self::INFO_STYLE);

        parent::__construct('divStep_' . $stepNumber, array( $div, $stepLabel ));

        if ( $completedStep )
        {
            $style = self::ENABLED_STYLE;

            $onclick = MUtil::getAjaxAction('gotoStep', $stepNumber);
            $this->setAttributes(array( 'onclick' => $onclick ));
        }
        else
        {
            $style = self::DISABLED_STYLE;
        }

        if ( $stepType == self::TYPE_CURRENT )
        {
            $style .= ' ' . self::CURRENT_STYLE;
        }

        $this->setClass($style);
    }

    /**
     * Disable button. 
     */
    public function disable()
    {
        $this->setAttribute('onclick', NULL);
        $this->setClass(self::DISABLED_STYLE, FALSE);
    }
}
?>
