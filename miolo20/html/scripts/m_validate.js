// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro Universitário  |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 2001,2002  UNIVATES, Lajeado/RS - Brasil           |
// +-----------------------------------------------------------------+
// | Licensed under GPL: see COPYING.TXT or FSF at www.fsf.org for   |
// |                     further details                             |
// |                                                                 |
// | Site: http://miolo.codigolivre.org.br                           |
// | E-mail: vgartner@univates.br                                    |
// |         ts@interact2000.com.br                                  |
// +-----------------------------------------------------------------+
// | Abstract: This file contains the javascript functions           |
// |                                                                 |
// | Created: 2001/08/14 Vilson Cristiano Gärtner [vg]               |
// |                     Thomas Spriestersbach    [ts]               |
// |                                                                 |
// | History: Initial Revision                                       |
// |          2001/12/14 [ts] Added MultiTextField support functions |
// +-----------------------------------------------------------------+

/**
 * MIOLO Form Validation Handler
 */

var __MIOLO_Validate_Errors = '';

function objValidate(field, mask)
{
    this.field = field;
    this.mask = mask;
}

function MIOLO_Validator()
{
}

function MIOLO_Validate_Input(validations)
{
    var error = '';
    var count = 0;
    var field = null;

    validations = window.MIOLO_validators;

    for ( var key in validations )
    {
        var validator = validations[key];
        var e = MIOLO_Validate(validator);

        if ( e != null && e != '' )
        {
            if ( error != '' )
            {
                error += '\n';
            }
            error += '- ' + e;
            if ( field == null )
            {
                field = MIOLO_GetElementById(validator.field);
            }
            count++;
        }
    }

    if ( error != '' )
    {
        if ( count > 1 )
        {
            error = 'Os seguintes erros foram detectados:\n' + error;
        }
        else
        {
            error = 'O seguinte erro foi detectado:\n' + error;
        }

        alert(error);

        if ( field != null )
        {
            field.focus();
        }
    }

    return (error == '');
}

function MIOLO_Validate(validator)
{
    if ( validator.type == 'ignore')
    {
        return null;
    }
    var req      = '';
    var field = MIOLO_GetElementById(validator.field);

    if ( typeof(field) == 'undefined' || field == null)
    {
        console.error('Field ' + validator.field + ' doesn\'t exists!');
        return null;
    }

    var value    = field.value;
    var error    = null;
    var param    = '';
    var label    = validator.label == "" ? validator.field : validator.label;

    if ( typeof(value) == 'undefined' || value == null )
    {
        error = 'Field ' + validator.field + ' duplicated!';
        return error;
    }
    
    if ( validator.type == 'required' )
    {
        req = 'yes';
    }

    if ( (req != '' && value.length == 0) )
    {
        error = 'O campo "' + label + '" deve ser informado ' ;
    }

    if ( (req != '' || value.length > 0) && value.length < validator.min )
    {
        error = 'O campo "' + label + '" deve conter no mínimo ' +
        validator.min + ' caracteres';
    }
    
    if ( validator.max > 0 && value.length > validator.max )
    {
        error = 'O campo "' + label + '" deve conter no máximo ' +
        validator.max + ' caracteres';
    }
    
    if ( validator.chars != 'ALL')
    {
        for ( var i=0; i<value.length; i++ )
        {
            var c = value.charAt(i);
            
            if ( validator.chars.indexOf(c) == -1 )
            {
                error = 'O carater "' + c + '" é inválido para o campo "' + label + '"';
            }
        }
    }
    
    if ( (value.length > 0 || req != '') && validator.mask != '' )
    {
        if ( validator.max == 0 && validator.min == 0 && value.length != validator.mask.length )
        {
            error = 'O campo "' + label + '" deve conter ' +
            validator.mask.length + ' caracteres';
        }
        else
        {
            for ( var i=0; i<value.length; i++ )
            {
                var m = validator.mask.charAt(i);
                var c = value.charAt(i);
                
                if ( m == '9' )
                {
                    if ( c < '0' || c > '9' )
                    {
                        error = 'O campo "' + label + '" deve conter um dígito numérico na posição ' + (i+1);
                    }
                }
                else if ( m != 'a' )
                {
                    if ( m != c )
                    {
                        error = 'O campo "' + label + '" deve conter o caractere "' + m + '" na posição ' + (i+1);
                    }
                }
            }
        }
    }
    
    if ( (value.length > 0 ||  req != '') && error == null && validator.checker != null )
    {
        param = '(value)';
        if ( validator.id == 'password')
        {
            param = '(value)';
        }
        if ( validator.id == 'compare')
        {
            param = '(value, validator.operator, validator.value, validator.datatype)';
        }
        if ( validator.id == 'range')
        {
            param = '(value, validator.minvalue, validator.maxvalue, validator.datatype)';
        }
        if ( validator.id == 'regexp')
        {
            param = '(value, validator.regexp)';
        }
        if ( ! eval(validator.checker + param) )
        {
            error = 'O conteúdo do campo "' + label + '" está inválido!';
        }
    }

    if ((error != null) && (validator.msgerr != ''))
    {
        error = validator.msgerr; 
    }
    
    return error;
}

function MIOLO_Is_Common_Key(e)
{
    var key = e.which ? e.which : e.keyCode;

    switch ( key )
    {
        case 8:  // Backspace
        case 9:  // Tab
        case 13: // Enter
        case 27: // Esc
        case 35: // End
        case 36: // Home
        case 37: // Left
        case 38: // Up
        case 39: // Right
        case 40: // Down
        case 45: // Insert
        case 46: // Delete
            return true;
    }

    return false;
}

function MIOLO_Validate_Mask(validator, e)
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
    var mask = validator.mask;

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


function MIOLO_Apply_Mask(validator, e)
{
    if ( !e )
    {
        e = window.event;
    }
    var key = e.which ? e.which : e.keyCode;
    var field = MIOLO_GetElementById(validator.field);
    var value = field.value;
    var mask  = validator.mask;
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

function isDigit(chr)
{
    return "0123456789".indexOf(chr) != -1;
}

function returnNumbers(str)
{
    var rs='';
    
    for ( var i=0; i<str.length; i++)
    {
        var chr = str.charAt(i);
        if ( isDigit(chr) )
        {
            rs += chr;
        }
    }
    
    return rs;
}

function MIOLO_Validate_Check_PASSWORD(value)
{
	return (value == value.replace(' ',''));
}

function MIOLO_Validate_Check_COMPARE(value, operator, basevalue, datatype)
{
	var exp = '';

	if (datatype == 'i')
	{
		exp = parseInt(value) + ' ' + operator + ' ' + basevalue;
	}
	else
	{
		exp = "'" + value + "' " + operator + " '" + basevalue + "'";
	}
	return eval(exp) ;
}

function MIOLO_Validate_Check_RANGE(value, min, max, datatype)
{
	var exp = 'false';
	var pos1, pos2, sD1, sD2, sD3, sM1, sM2, sM3, sY1, sY2, sY3;

	if (datatype == 'i')
	{
		exp = '(' + parseInt(value) + ' >= ' + min + ') && (' + parseInt(value) + ' <= ' + max + ')';
	}
	else if (datatype == 'f')
	{
        value = parseFloat( value.replace(',', '.') );
        min   = parseFloat( min.replace(',', '.') );
        max   = parseFloat( max.replace(',', '.') );
		exp = '(' + value + ' >= ' + min + ') && (' + value + ' <= ' + max + ')';
	}
	else if (datatype == 's')
	{
		exp = "('" + value + "' >= '" + min + "') && ('" + value + "' <= '" + max + "')";
	}
	else if (datatype == 'd')
	{
       pos1 = value.indexOf('/');
	   pos2 = value.indexOf('/', pos1+1);
       sD1 = value.substring(0,pos1);
	   sM1 = value.substring(pos1+1,pos2);
	   sY1 = value.substring(pos2+1);
       if (isDate( sM1 + '/' + sD1 + '/' + sY1 ))
       {
          pos1 = min.indexOf('/');
	      pos2 = min.indexOf('/', pos1+1);
          sD2 = min.substring(0,pos1);
	      sM2 = min.substring(pos1+1,pos2);
	      sY2 = min.substring(pos2+1);
          if (isDate( sM2 + '/' + sD2 + '/' + sY2 ))
          {
             pos1 = max.indexOf('/');
	         pos2 = max.indexOf('/', pos1+1);
             sD3 = max.substring(0,pos1);
	         sM3 = max.substring(pos1+1,pos2);
	         sY3 = max.substring(pos2+1);
             if (isDate( sM3 + '/' + sD3 + '/' + sY3 ))
             {
				 var dt1 = new Date(sY1,sM1-1,sD1);
				 var dt2 = new Date(sY2,sM2-1,sD2);
				 var dt3 = new Date(sY3,sM3-1,sD3);
				 exp = (dt1 >= dt2) && (dt1 <= dt3);
		     }
          }

       }
	}
	return eval(exp) ;
}

function MIOLO_Validate_Check_REGEXP(value, regexp)
{
	return (value.search(regexp) >= 0);
}

/*
** Validador CNPJ
** Baseado no script original no CodigoLivre
** http://codigolivre.org.br/snippet/detail.php?type=snippet&id=22
*/
function MIOLO_Validate_Check_CNPJ(CNPJ)
{
    CNPJ = returnNumbers(CNPJ);
    
    if ( CNPJ.length == 14 && CNPJ != '00000000000000' )
    {
        var g = CNPJ.length - 2;
        
        if ( MIOLO_Validate_Verify_CNPJ(CNPJ,g) == 1 )
        {
            g = CNPJ.length - 1;
            
            if( MIOLO_Validate_Verify_CNPJ(CNPJ,g) == 1 )
            {	
                return true;
            }
        }
    }
    
    return false;
}

function MIOLO_Validate_Verify_CNPJ(CNPJ,g)
{
    var VerCNPJ=0;
    var ind=2;
    var tam;
    
    for( f = g; f > 0; f-- )
    {
        VerCNPJ += parseInt(CNPJ.charAt(f-1)) * ind;
        if(ind>8)
        {
            ind=2;
        }
        else
        {
            ind++;
        }
    }
    
    VerCNPJ%=11;
    
    if( VerCNPJ==0 || VerCNPJ==1 )
    {
        VerCNPJ=0;
    }
    else
    {
        VerCNPJ=11-VerCNPJ;
    }
    if( VerCNPJ!=parseInt(CNPJ.charAt(g)) )
    {
        return(0);
    }
    else
    {
        return(1);
    }
}    
    

function MIOLO_Validate_Check_CPF(value)
{
    var i;
    var c;
    
    var x = 0;
    var soma = 0;
    var dig1 = 0;
    var dig2 = 0;
    var texto = "";
    var numcpf1="";
    var numcpf = "";
    
    var numcpf = returnNumbers(value);

    if ( ( numcpf == '00000000000') ||
         ( numcpf == '11111111111') ||
         ( numcpf == '22222222222') ||
         ( numcpf == '33333333333') ||
         ( numcpf == '44444444444') ||
         ( numcpf == '55555555555') ||
         ( numcpf == '66666666666') ||
         ( numcpf == '77777777777') ||
         ( numcpf == '88888888888') ||
         ( numcpf == '99999999999')  )
    {
        return false;
    }
    
/*    for (i = 0; i < value.length; i++) 
    {
        c = value.substring(i,i+1);
        if ( isDigit(c) )
        {
            numcpf = numcpf + c;
        }
    }
*/    
    if ( numcpf.length != 11 ) 
    {
        return false;
    }
    
    len = numcpf.length;x = len -1;
    
    for ( var i=0; i <= len - 3; i++ ) 
    {
        y     = numcpf.substring(i,i+1);
        soma  = soma + ( y * x);
        x     = x - 1;
        texto = texto + y;
    }
    
    dig1 = 11 - (soma % 11);
    if (dig1 == 10) 
    {
        dig1 = 0 ;
    }
    
    if (dig1 == 11) 
    {
        dig1 = 0 ;
    }
    
    numcpf1 = numcpf.substring(0,len - 2) + dig1 ;
    x = 11;soma = 0;
    for (var i=0; i <= len - 2; i++) 
    {
        soma = soma + (numcpf1.substring(i,i+1) * x);
        x = x - 1;
    }
    
    dig2 = 11 - (soma % 11);
    
    if (dig2 == 10)
    {
        dig2 = 0;
    }
    if (dig2 == 11) 
    {
        dig2 = 0;
    }
    if ( (dig1 + "" + dig2) == numcpf.substring(len,len-2) ) 
    {
        return true;
    }
    
    return false;
}


function MIOLO_Validate_Check_EMAIL(email)
{
    var re = /^(([a-zA-Z0-9/=?^_`{|}~!#$%&'*+-]+(\.[a-zA-Z0-9/=?^_`{|}~!#$%&'*+-]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return email.match(re);
}

function MIOLO_Validate_Check_DATEDMY(date)
{
    var pos1 = date.indexOf('/');
	var pos2 = date.indexOf('/', pos1+1);

	var strD = date.substring(0,pos1);
	var strM = date.substring(pos1+1,pos2);
	var strY = date.substring(pos2+1);
    
    return ( isDate( strM + '/' + strD + '/' + strY ) == true);
}

function MIOLO_Validate_Check_DATETimeDMY(date)
{
    var aux  = date.split(' ');
    date = aux[0];
    time = aux[1];

    return (MIOLO_Validate_Check_DATEDMY(date) && MIOLO_Validate_Check_TIME(time));
}


function MIOLO_Validate_Check_DATEYMD(date)
{
    var pos1 = date.indexOf('/');
	var pos2 = date.indexOf('/', pos1+1);

	var strY = date.substring(0,pos1);
	var strM = date.substring(pos1+1,pos2);
	var strD = date.substring(pos2+1);
    
    return ( isDate( strM + '/' + strD + '/' + strY ) == true );
}


function MIOLO_Validate_Check_TIME(time)
{
    var h = parseInt( time.substring(0,2) );
    var m = parseInt( time.substring(3,5) );

    return (time.length == 5) && (h >= 0 && h < 24) && (m >= 0 && m < 60);
}

 /*
 ** DHTML date validation script. 
 ** Courtesy of SmartWebby.com (http://www.smartwebby.com/dhtml/)
 */
 function isInteger(s)
 {
	var i;
    for ( i = 0; i < s.length; i++ )
    {   
        // Check that current character is number.
        var c = s.charAt(i);
        if (((c < "0") || (c > "9")))
        {
            return false;
        }
    }
    // All characters are numbers.
    return true;
}


function stripCharsInBag(s, bag)
{
	var i;
    var returnString = "";
    // Search through string's characters one by one.
    // If character is not in bag, append to returnString.
    for (i = 0; i < s.length; i++)
    {   
        var c = s.charAt(i);
        if (bag.indexOf(c) == -1) returnString += c;
    }
    return returnString;
}

function daysInFebruary (year)
{
	// February has 29 days in any year evenly divisible by four,
    // EXCEPT for centurial years which are not also divisible by 400.
    return (((year % 4 == 0) && ( (!(year % 100 == 0)) || (year % 400 == 0))) ? 29 : 28 );
}

function DaysArray(n)
{
	for ( var i = 1; i <= n; i++ ) 
    {
		this[i] = 31;
		if ( i==4 || i==6 || i==9 || i==11 ) 
        {
            this[i] = 30;
        }
		if ( i == 2 )
        {
            this[i] = 29;
        }
   } 
   return this;
}

function isDate(dtStr)
{
    dtCh= "/";
    minYear=1900;
    maxYear=2100;

	var daysInMonth = DaysArray(12);
	var pos1        = dtStr.indexOf(dtCh);
	var pos2        = dtStr.indexOf(dtCh,pos1+1);
	var strMonth    = dtStr.substring(0,pos1);
	var strDay      = dtStr.substring(pos1+1,pos2);
	var strYear     = dtStr.substring(pos2+1);
	var strYr       = strYear;
    
	if ( strDay.charAt(0) == "0" && strDay.length>1 ) 
    {
        strDay=strDay.substring(1);
    }
	
    if ( strMonth.charAt(0) == "0" && strMonth.length>1 ) 
    {
        strMonth=strMonth.substring(1);
    }
    
	for ( var i = 1; i <= 3; i++ )
    {
		if ( strYr.charAt(0) == "0" && strYr.length>1 ) 
        {
            strYr=strYr.substring(1);
        }
	}
	
    var month = parseInt(strMonth);
	var day   = parseInt(strDay);
	var year  = parseInt(strYr);
    
	if ( pos1==-1 || pos2==-1 )
    {
		return "The date format should be : mm/dd/yyyy";
	}
    
  	if ( strDay.length < 1 || day < 1 || day > 31 || (month==2 && day>daysInFebruary(year)) || day > daysInMonth[month] )
    {
		return "O Dia informado é inválido. \n(Please enter a valid day.)";
	}
    
	if ( strMonth.length < 1 || month < 1 || month > 12 )
    {
		return "O Mês informado é inválido. \n(Please enter a valid month.)";
	}
    
	if ( strYear.length != 4 || year==0 || year<minYear || year>maxYear )
    {
		return "O Ano deve conter 4 dígitos e estar entre "+minYear+" e "+maxYear+"\n(Please enter a valid 4 digit year between "+minYear+" and "+maxYear+")";
	}
    
	if ( dtStr.indexOf(dtCh,pos2+1)!=-1 || isInteger(stripCharsInBag(dtStr, dtCh))==false )
    {
		return "Informe uma data válida.";
	}
    
    return true;
}

function MIOLO_Validate_Check_Required(value)
{
	if ( value.length > 0 )
    {
        return true;
    }
    return false;
}

/**
 * Get the script tag content of the element and execute it
 *
 * @param string elementId Id of the element
 */
function MIOLO_UpdateAJAXValidators(elementId)
{
    var script = MIOLO_GetElementById(elementId).getElementsByTagName('script');
    if ( script.length > 0 )
    {
        eval(script[0].innerHTML);
    }
}
