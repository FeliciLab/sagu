/**
 * MULTITEXTFIELD3
 */

var _MIOLO_MultiTextField3_separator = '] [';

/**
 * Função que simplesmente seleciona todos os itens, para que
 * sejam incluidos ao enviar o formulário
 */
function _MIOLO_MultiTextField3_onSubmit(frmName, mtfName)
    {
    return _MIOLO_MultiTextField2_onSubmit(frmName, mtfName);
    }
/**
 *  
 */
function _MIOLO_MultiTextField3_Split(value)
    {
    return _MIOLO_MultiTextField2_Split(value);
    }
/**
 *  
 */
function _MIOLO_MultiTextField3_Join(fields)
    {
    return _MIOLO_MultiTextField2_Join(fields);
    }
/**
 *  
 */
function _MIOLO_MultiTextField3_onKeyDown(source, frmObj, mtfName, event, numFields)
    {
    return _MIOLO_MultiTextField2_onKeyDown(source, frmObj, mtfName, event, numFields);
    }
/**
 *  
 */
function _MIOLO_MultiTextField3_onSelect(frmObj, mtfName, sFields)
    {
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');
    var aFields = sFields.split(',');

    var i = list.selectedIndex;

    if (i != -1)
        {
        var a = _MIOLO_MultiTextField3_Split(list.options[i].text);

        for (var i = 0; i < aFields.length; i++)
            {
            var field = document.getElementById(aFields[i]);

            if (field.options != null) // selection
                {
                for (var n = 0; n < field.options.length; n++)
                    {
                    if (field.options[n].text == a[i])
                        {
                        field.selectedIndex = n;
                        break;
                        }
                    }
                }

            else // text
                {
                field.value = a[i];
                }
            }
        }

    else
        {
        for (var i = 0; i < aFields.length; i++)
            {
            var field = document.getElementById(aFields[i]);

            if (field.options != null) // selection
                {
                field.selectedIndex = -1;
                }

            else // text
                {
                field.value = '';
                }
            }
        }
    }
/**
 *  
 */
function _MIOLO_MultiTextField3_getInput(frmObj, mtfName, sFields)
    {
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');
    var value = '';
    var aFields = sFields.split(',');
    var fields = new Array(aFields.length);

    for (var i = 0; i < aFields.length; i++)
        {
        var field = document.getElementById(aFields[i]);

        if (field.options != null) // selection
            {
            fields[i] = field.options[field.selectedIndex].text;
            }

        else // text
            {
            fields[i] = field.value;
            }
        }

    return _MIOLO_MultiTextField3_Join(fields);
    }
/**
 *  
 */
function _MIOLO_MultiTextField3_add(frmObj, mtfName, sFields)
    {
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');
    var i = list.length;

    list.options[i] = new Option(_MIOLO_MultiTextField3_getInput(frmObj, mtfName, sFields));
    list.selectedIndex = i;
    }
/**
 * Função que exclui o item atualmente selecionado
 */
function _MIOLO_MultiTextField3_remove(frmObj, mtfName, sFields)
    {
    var aFields = sFields.split(',');
    var numFields = aFields.length;
    _MIOLO_MultiTextField2_remove(frmObj, mtfName, numFields);
    }
/**
 * 
 */
function _MIOLO_MultiTextField3_modify(frmObj, mtfName, sFields)
    {
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');
    var i = list.selectedIndex;

    if (i != -1)
        {
        list.options[i].text = _MIOLO_MultiTextField3_getInput(frmObj, mtfName, sFields);
        }

    else
        {
        alert('É preciso selecionar o item a ser modificado!');
        }
    }
/**
 * 
 */
function _MIOLO_MultiTextField3_moveUp(frmObj, mtfName, sFields)
    {
    var aFields = sFields.split(',');
    var numFields = aFields.length;
    _MIOLO_MultiTextField2_moveUp(frmObj, mtfName, numFields);
    }
/**
 * 
 */
function _MIOLO_MultiTextField3_moveDown(frmObj, mtfName, sFields)
    {
    var aFields = sFields.split(',');
    var numFields = aFields.length;
    _MIOLO_MultiTextField2_moveDown(frmObj, mtfName, numFields);
    }
