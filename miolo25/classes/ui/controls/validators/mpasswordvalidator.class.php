<?php

/**
 * Password strength validator.
 * Based on Steve Moitozo [god@zilla.us] JavaScript validator.
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

class MPasswordValidator extends MValidator
{
    const STRENGTH_VERY_WEAK = 1;
    const STRENGTH_WEAK = 2;
    const STRENGTH_MEDIUM = 3;
    const STRENGTH_STRONG = 4;
    const STRENGTH_VERY_STRONG = 5;

    /**
     * @var integer Defines the minimum strength the password must have.
     */
    private $minStrength;

    public function __construct($field, $label='', $type='required', $msgerr='', $minStrength=self::STRENGTH_MEDIUM)
    {
        parent::__construct();
        $this->id = 'password';
        $this->field = $field;
        $this->label = $label;
        $this->type = $type;
        $this->min = 0;
        $this->max = 99;
        $this->chars = 'ALL';
        $this->mask = '';
        $this->checker = 'PASSWORD';
        $this->msgerr = $msgerr;
        $this->minStrength = $minStrength;
    }

    /**
     * Set the minimum strength the password must have.
     *
     * @param integer $minStrength Use STRENGTH_* constants.
     */
    public function setMinStrength($minStrength)
    {
        $this->minStrength = $minStrength;
    }

    /**
     * @return integer Get the minimum strength the password must have.
     */
    public function getMinStrength($minStrength)
    {
        $this->minStrength = $minStrength;
    }

    /**
     * Get the strength label.
     *
     * @param integer $strength Strength code.
     * @return string Strength label.
     */
    public function getStrengthLabel($strength)
    {
        $strengthLabels = array(
            self::STRENGTH_VERY_WEAK => _M('very weak'),
            self::STRENGTH_WEAK  => _M('weak'),
            self::STRENGTH_MEDIUM => _M('medium'),
            self::STRENGTH_STRONG => _M('strong'),
            self::STRENGTH_VERY_STRONG => _M('very strong')
        );

        return $strengthLabels[$strength];
    }

    /**
     * Get the strength label inside a html b tag, with different colors.
     *
     * @param integer $strength Strength code.
     * @return string Description.
     */
    public function getStrengthDescription($strength)
    {
        $description = '';

        switch ( $strength )
        {
            case self::STRENGTH_VERY_WEAK:
                $description = '<b style="color: #ff0000;">'. $this->getStrengthLabel($strength) .'</b>';
                break;
            case self::STRENGTH_WEAK:
                $description = '<b style="color: #bb0000;">'. $this->getStrengthLabel($strength) .'</b>';
                break;
            case self::STRENGTH_MEDIUM:
                $description = '<b style="color: #ff9900;">'. $this->getStrengthLabel($strength) .'</b>';
                break;
            case self::STRENGTH_STRONG:
                $description = '<b style="color: #00bb00;">'. $this->getStrengthLabel($strength) .'</b>';
                break;
            case self::STRENGTH_VERY_STRONG:
                $description = '<b style="color: #00ee00;">'. $this->getStrengthLabel($strength) .'</b>';
                break;
        }

        return $description;
    }
    
    /**
     * Validate value according to validator rules.
     *
     * @param mixed $value Field value.
     * @return boolean Whether field value is valid.
     */
    public function validate($passwd)
    {
        $valid = true;
        $intScore = 0;
        $strVeredict = 0;
        $passwdLength = strlen($passwd);
        $description = array();

        // Password length
        if ( $passwdLength == 0 || !$passwdLength )
        {
            $intScore = -1;
        }
        else if ( $passwdLength > 0 && $passwdLength < 5 )
        {
            $intScore += 3;
        }
        else if ( $passwdLength > 4 && $passwdLength < 8 )
        {
            $intScore += 6;
        }
        else if ( $passwdLength > 7 && $passwdLength < 12 )
        {
            $intScore += 12;
        }
        else if ( $passwdLength > 11 )
        {
            $intScore += 18;
        }

        if ( $passwdLength > 0 )
        {
            // Letters

            // At least one lower case letter
            if ( preg_match('/[a-z]/', $passwd) )
            {
                $intScore += 1;
            }

            // At least one upper case letter
            if ( preg_match('/[A-Z]/', $passwd) ) 
            {
                $intScore += 5;
            }

            // Numbers

            // At least one number
            if ( preg_match('/\d+/', $passwd) )
            {
                $intScore += 5;
            }

            // At least three numbers
            if ( preg_match('/(.*[0-9].*[0-9].*[0-9])/', $passwd) )
            {
                $intScore += 5;
            }

            // Special chars

            // At least one special character
            if ( preg_match('/.[!,@,#,$,%,^,&,*,?,_,~]/', $passwd) )
            {
                $intScore += 5;
            }

            // At least two special characters
            if ( preg_match('/(.*[!,@,#,$,%,^,&,*,?,_,~].*[!,@,#,$,%,^,&,*,?,_,~])/', $passwd) )
            {
                $intScore += 5;
            }

            // Combos

            // Both upper and lower case
            if ( preg_match('/([a-z].*[A-Z])|([A-Z].*[a-z])/', $passwd) )
            {
                $intScore += 2;
            }

            // Both letters and numbers
            if ( preg_match('/(\d+.*\D+)|(\D+.*\d+)/', $passwd) )
            {
                $intScore += 2;
            }

            // Letters, numbers and special characters
            if ( preg_match('/([a-zA-Z0-9].*[!,@,#,$,%,^,&,*,?,_,~])|([!,@,#,$,%,^,&,*,?,_,~].*[a-zA-Z0-9])/', $passwd) )
            {
                $intScore += 2;
            }
        }

        if ( $intScore > -1 && $intScore < 16 )
        {
            $strength = self::STRENGTH_VERY_WEAK;
        }
        elseif ( $intScore > 15 && $intScore < 25 )
        {
            $strength = self::STRENGTH_WEAK;
        }
        elseif ( $intScore > 24 && $intScore < 35 )
        {
            $strength = self::STRENGTH_MEDIUM;
        }
        elseif ( $intScore > 34 && $intScore < 45 )
        {
            $strength = self::STRENGTH_STRONG;
        }
        elseif ( $intScore != -1 )
        {
            $strength = self::STRENGTH_VERY_STRONG;
        }

        if ( $passwd != NULL && ( $strength < $this->minStrength ) )
        {
            $valid = false;
            $strengthDescription = $this->getStrengthDescription($strength);
            $minStrengthLabel = $this->getStrengthLabel($this->minStrength);
            $this->error = _M('The password strength is @1 and it should be at least @2', 'miolo', $strengthDescription, $minStrengthLabel);
        }

        return $valid ? parent::validate($passwd) : $valid;
    }
}

?>