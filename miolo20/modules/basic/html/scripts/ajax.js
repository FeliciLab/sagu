/**
 * 
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Fabiano Tomasini [fabiano@solis.coop.br]
 *
 * @since
 * Creation date 2009/10/01
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2009 Solis - Cooperativa de Soluções Livres \n
 *
 */

function saguDoAjax(phpFunction, responseElement, validate, args)
{
    var alright = true;

    // if fields are to be validated, simulate a form submit
    if ( validate )
    {
        SAGURemoveUnusedValidators();
        alright = miolo_onSubmit();
    }

    // if everything gone right up to this point, execute AJAX call
    if ( alright )
    {
        SFormResponseDiv = 
        args += getFieldValuesInURIFormat();
        // declare the name of the function to be called by the ajaxCallBack callback function
        args += '&phpFunction=' + escape(phpFunction);
        // declare the name of the div where results will be put
        args += '&SFormResponseDivId=' + escape(responseElement);
        MIOLO_ajaxCall(document.URL, 'POST', 'ajaxCallBack', args, saguResponse, 'TEXT');
    }
}

function getFieldValues()
{
    var temp ='';
    for (i=0; i<document.forms[0].elements.length; i++)
    {
        var field = document.forms[0].elements[i].id;
        var value = escape(document.forms[0].elements[i].value);
        temp+= field + '|' + value + '#';
    }
    return temp;
}

function getFieldValuesInURIFormat()
{
    var uri = '';
    for (i=0; i<document.forms[0].elements.length; i++)
    {
        var add = true;
        var field = document.forms[0].elements[i].id;
        var value = '';

        if ( document.forms[0].elements[i].type == 'checkbox' )
        {
            // values from unchecked checkboxes are not returned
            add = document.forms[0].elements[i].checked != "";
        }
        else if ( document.getElementById(field).tagName == 'FIELDSET' ) //MRadioButton check
        {
            for (x=0; fieldsetElement = document.forms[0].elements[i].childNodes[x]; x++)
            {
                if (fieldsetElement.tagName == 'DIV')
                {
                    for (z=0; element = fieldsetElement.childNodes[z]; z++)
                    {
                        if ((element.type == 'radio') && (element.checked)) //If radio input is checked, define the value to id father
                        {
                            value = element.value;
                        }
                    }
                }
            }
        }

        value = escape( value ? value : document.forms[0].elements[i].value );
        
        if ( add )
        {
            uri += '&' + field + '=' + value;
        }
    }
    return uri;
}

/**
 * This function is only compatible with SForm.
 * 
 * @param result
 * @return
 */
function saguResponse( result )
{
    if ( result != null )
    {
        MIOLO_parseAjaxJavascript(result);

        var divSaguMessages = document.getElementById('divSaguMessages');
        var divSaguMessagesContent = document.getElementById('divSaguMessagesContent');
        if ( divSaguMessages != null && divSaguMessagesContent != null )
        {
            var messages = SAGUParseResult(result, '<!-- ### SAGU_MESSAGES ### -->', '<!-- ### SAGU_MESSAGES_END ### -->');
            result = SAGUStripContent(result, '<!-- ### SAGU_MESSAGES ### -->', '<!-- ### SAGU_MESSAGES_END ### -->');
            if ( messages.length > 0 )
            {
                divSaguMessagesContent.innerHTML = messages;
                divSaguMessages.style.display = 'block';
            }
        }

        if ( result != '' )
        {
            var divResponseId = SAGUParseResult(result, '<!-- ### SFORM_RESPONSE_DIV ### -->', '<!-- ### SFORM_RESPONSE_DIV_END ### -->');
            result = SAGUStripContent(result, '<!-- ### SFORM_RESPONSE_DIV ### -->', '<!-- ### SFORM_RESPONSE_DIV_END ### -->');
            var divResponse = document.getElementById(divResponseId);
            
            // divResponse contents will only be overwritten when some content
            // exists, i.e. it won't be cleared.
            if ( divResponse != null && result.length > 0 )
            {
                divResponse.innerHTML = result;
            }
            // this is in case of some $MIOLO->error() or other content that was
            // not expected. It will be thrown into divSaguMessages
            else if ( result.length > 0 )
            {
                divSaguMessagesContent.innerHTML = result;
                divSaguMessages.style.display = 'block';
            }
        }

        //ajusta tamanho do frame para o Joomla
        inSite();        
 
    }
}

/**
 * Hides the message div i.e. when the user clicks ok.
 * @return nothing
 */
function SAGUHideMessagesDiv()
{
    var divSaguMessages = document.getElementById('divSaguMessages'); 
    if ( divSaguMessages != null )
    {
        divSaguMessages.style.display = 'none';
    }
}

/**
 * Return the content under openTag and closeTag.
 * 
 * @param content Content to be parsed
 * @param openTag Open tag
 * @param closeTag Close tag
 * @return parsed content or '' if one of the tags was not found
 */
function SAGUParseResult(content, openTag, closeTag)
{
    var retContent = '';
    
    var start = content.indexOf(openTag);
    var end = content.indexOf(closeTag);
    
    if ( start != -1 && end != -1 )
    {
        retContent = content.substring(start + openTag.length, end);
    }
    
    return retContent;
}

/**
 * Cut off the content under the specified tags (and the tags themselves)
 * 
 * @param content Content to be parsed
 * @param openTag Open tag
 * @param closeTag Close tag
 * @return parsed content or the content itself if openTag or closeTag not found.
 */
function SAGUStripContent(content, openTag, closeTag)
{
    var start = content.indexOf(openTag);
    var end = content.indexOf(closeTag);
    
    if ( start != -1 && end != -1 )
    {
        content = content.substring(0, start) + content.substring(end + closeTag.length);
    }
    
    return content;
}

// Iterate through all validators, deleting the ones that do not reference a valid (existing) field
function SAGURemoveUnusedValidators()
{
    for ( var key in window.MIOLO_validators )
    {
        if ( document.getElementById(window.MIOLO_validators[key].field) == null )
        {
            delete window.MIOLO_validators[key];
        }
    }
}
