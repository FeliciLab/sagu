<?php
/**
 * MCaptchaField
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2010/07/13
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b CopyRight: \n
 * Copyright (c) 2010 SOLIS - Cooperativa de Soluções Livres \n
 *
 */

global $MIOLO;
$MIOLO->uses('../html/extensions/securimage/securimage.php');

class MCaptchaField extends MContainer
{
    public function __construct( $name, $label = '', $hint = '', $size = 10 )
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( $label )
        {
            $controls['label'] = new MLabel($label.':');
        }
        
        $uniqueId = md5( uniqid( time() ) );
        $controls[] = new MImage("{$name}Image", NULL, "extensions/securimage/securimage_show.php?sid=$uniqueId");

        $controls['button'] = new MButton("{$name}Button", ' ', $this->getRefreshCode());
        $controls['button']->setClass('mButton mCaptchaButton');

        $controls['code'] = new MTextField($name, NULL, NULL, $size);
        $controls['code']->setClass('mTextField mCaptchaCode');
        $controls['code']->addAttribute('maxlength', '4');
        
        if ( $hint )
        {
            $controls[] = new MSpan("{$name}Hint", $hint, 'mSpan mHint');
        }

        parent::__construct("{$name}Container", $controls, 'horizontal', MFormControl::FORM_MODE_SHOW_SIDE);
    }

    /**
     * Returns a javascript code to change the captcha image
     * 
     * @return JavaScript code
     */
    public function getRefreshCode()
    {
        $code = "captchaImage = dojo.byId('{$this->name}Image');";
        $code .= "if (captchaImage) { captchaImage.src = 'extensions/securimage/securimage_show.php?sid=' + Math.random() };";

        return $code;
    }

    /**
     * Checks if the given code is the same as the one showed on the image
     * 
     * @param string $code Code entered by the user
     * @return boolean Returns true if have validated
     */
    public static function validate( $code )
    {
        $img = new Securimage();
        return $img->check( $code );
    }
}

?>