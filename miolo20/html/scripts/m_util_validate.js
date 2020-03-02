
function MUtil_Validate_Mask(mask, e)
{
    if ( !e )
    {
        e = window.event;
    }

    if ( MIOLO_Is_Common_Key(e) )
    {
        return true;
    }

    if ( e.ctrlKey || e.altKey || e.metaKey || e.shiftKey )
    {
        return true;
    }

    var key = e.which ? e.which : e.keyCode;
    var chr = String.fromCharCode(key);

    // numeric keypad - fix for firefox 3.6
    if ( key >= 96 && key <= 105 )
    {
        chr = (key - 96).toString();
    }

    var charIsValid = false;

    // check if the pressed char is present on mask
    for ( var i=0; i < mask.length; i++ )
    {
        var currentMaskChar = mask.charAt(i);

        if ( currentMaskChar == 'a' && chr.match(/\w/) )
        {
            charIsValid = true;
            break;
        }
        else if ( currentMaskChar == '9' && chr.match(/\d/) )
        {
            charIsValid = true;
            break;
        }
        else if ( chr == currentMaskChar )
        {
            charIsValid = true;
            break;
        }
    }

    return charIsValid;
}


function MUtil_Apply_Mask(fieldId, mask, e)
{
    if ( !e )
    {
        e = window.event;
    }
    var key = e.which ? e.which : e.keyCode;
    var field = MIOLO_GetElementById(fieldId);
    var value = field.value;
    var maskedValue = '';
    var maskPos = 0;

    if ( MIOLO_Is_Common_Key(e) )
    {
        return true;
    }

    // CTRL+A - prevent annoying behavior on google chrome
    if ( e.ctrlKey && key == 65 )
    {
        return true;
    }

    // remove exceeded chars
    if ( mask.length > 0 && value.length > mask.length )
    {
        value = value.substring(0, mask.length);
    }

    for ( var i=0; i < value.length; i++ )
    {
        fieldChar = value.charAt(i).toString();
        maskChar = mask.charAt(maskPos).toString();

        // change symbols to those which are expected by the mask
        if ( maskChar != '9' && maskChar != 'a' )
        {
            re = new RegExp('[^' + maskChar + ']');
            if ( fieldChar.match(re) )
            {
                maskedValue += maskChar;
                maskedValue += fieldChar;
                maskPos++;
                continue;
            }
        }

        // skip unexpected chars
        if ( maskChar == 'a' && fieldChar.match(/\W/) )
        {
            continue;
        }
        if ( maskChar == '9' && fieldChar.match(/\D/) )
        {
            continue;
        }

        maskedValue += fieldChar;
        maskPos++;
    }

    if ( maskedValue != field.value )
    {
        field.value = maskedValue;
    }
}
