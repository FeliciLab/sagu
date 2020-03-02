/**
 * MULTITEXTFIELD2
 */

var _MIOLO_MultiTextField2_separator = '] [';

/**
 * Função que simplesmente seleciona todos os itens, para que
 * sejam incluidos ao enviar o formulário
 */
function _MIOLO_MultiTextField2_onSubmit(frmName, mtfName)
{
    var form = eval('document.' + frmName);
//    var list = form[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');


    if (list != null && list.options != null)
    {
        for (var i = 0; i < list.length; i++)
        {
            list.options[i].value = list.options[i].text;
            list.options[i].selected = true;
        }
    }

    return true;
}
/**
 *  
 */
function _MIOLO_MultiTextField2_Split(value)
{
    return value.substring(1, value.length - 1).split(_MIOLO_MultiTextField2_separator);
}
/**
 *  
 */
function _MIOLO_MultiTextField2_Join(fields)
{
    var value = '[';

    for (var i = 0; i < fields.length; i++)
    {
        if (i > 0)
        {
            value += _MIOLO_MultiTextField2_separator;
        }

        value += fields[i];
    }

    value += ']';
    return value;
}
/**
 *  
 */
function _MIOLO_MultiTextField2_onKeyDown(source, frmObj, mtfName, event, numFields)
{
    // IE and compatibles use 'keyCode', NS and compatibles 'which'
    var key = (document.all != null) ? event.keyCode : event.which;
    var name = mtfName + '_text';
    var len = name.length;

    if (source.name.substring(0, len) == name)
    {
        if (key == 13) // enter key
        {
            _MIOLO_MultiTextField2_add(frmObj, mtfName, numFields);
            return false;
        }
    }

    else if (source.name == mtfName + '[]')
    {
        // alert(key);

        if (key == 46) // delete key
        {
            _MIOLO_MultiTextField2_remove(frmObj, mtfName, numFields);
            return false;
        }
    }
}
/**
 *  
 */
function _MIOLO_MultiTextField2_onSelect(frmObj, mtfName, numFields)
{
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

    var i = list.selectedIndex;

    if (i != -1)
    {
        var a = _MIOLO_MultiTextField2_Split(list.options[i].text);

        for (var j = 1; j <= numFields; j++)
        {
            var tf = frmObj[mtfName + '_text' + j];

            if (tf != null)
            {
                tf.value = a[j - 1] ? a[j - 1] : '';
            }
            else
            {
                var op = frmObj[mtfName + '_options' + j];
                
                if (op != null)
                {
                    // preselect option based on value
                    for (var n = 0; n < op.options.length; n++)
                    {
                        //                        if ( op.options[n].value == a[j-1] )
                        if (op.options[n].text == a[j - 1] || ( a[j - 1] == "" && op.options[n].value == "") )
                        {
                            op.selectedIndex = n;
                            break;
                        }
                        else
                        {
                            op.selectedIndex = 0;
                        }
                    }
                }
            }
        }
    }
    else
    {
        for (var j = 1; j <= numFields; j++)
        {
            var tf = frmObj[mtfName + '_text' + j];

            if (tf != null)
            {
                tf.value = '';
            }
            else
            {
                var op = frmObj[mtfName + '_options' + j];

                if (op != null)
                {
                    op.selectedIndex = -1;
                }
            }
        }
    }
}
/**
 *  
 */
function _MIOLO_MultiTextField2_getInput(frmObj, mtfName, numFields)
{
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

    var fields = new Array(numFields);
    var value = '';

    for (var i = 1; i <= numFields; i++)
    {
        var tf = frmObj[mtfName + '_text' + i];

        fields[i - 1] = '';

        if (tf != null)
        {
            //            if ( i > 1 )
            //            {
            //                value += _MIOLO_MultiTextField2_separator;
            //            }

            fields[i - 1] = tf.value;

            //            tf.value = '';
        }
        else
        {
            var list = frmObj[mtfName + '_options' + i];

            if (list != null)
            {
                //                if ( i > 1 )
                //                {
                //                    value += _MIOLO_MultiTextField2_separator;
                //                }

                //                fields[i-1] = list.options[list.selectedIndex].value;
            fields[i - 1] = list.options[list.selectedIndex].text;
            }
        }
    }

    return _MIOLO_MultiTextField2_Join(fields);
}
/**
 *  
 */
function _MIOLO_MultiTextField2_add(frmObj, mtfName, numFields)
{
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

    var i = list.length;

    list.options[i] = new Option(_MIOLO_MultiTextField2_getInput(frmObj, mtfName, numFields));
    list.selectedIndex = i;
}
/**
 * Funcção que exclui o item atualmente selecionado
 */
function _MIOLO_MultiTextField2_remove(frmObj, mtfName, numFields)
{
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

	for (var i = 0; i < list.length; i++)
    {
        if (list.options[i].selected)
        {
            list.options[i] = null;

            if (i >= list.length)
            {
                i = list.length - 1;
            }

            if (i >= 0)
            {
                list.options[i].selected = true;
            }

            break;
        }
    }
}
/**
 * 
 */
function _MIOLO_MultiTextField2_modify(frmObj, mtfName, numFields)
    {
    var list = MIOLO_GetElementById(mtfName + '[]');

    var i = list.selectedIndex;

    if (i != -1)
    {
        list.options[i] = new Option(_MIOLO_MultiTextField2_getInput(frmObj, mtfName, numFields));
    }

    else
    {
        alert('É preciso selecionar o item a ser modificado!');
    }
}
/**
 * 
 */
function _MIOLO_MultiTextField2_moveUp(frmObj, mtfName, numFields)
{
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

    var i = list.selectedIndex;

    if (i != -1)
    {
        if (i > 0)
        {
            var u = list.options[i - 1].text;

            list.options[i - 1].text = list.options[i].text;
            list.options[i - 1].selected = true;

            list.options[i].text = u;
            list.options[i].selected = false;

            list.selectedIndex = i - 1;
        }
    }
    else
    {
        alert('É preciso selecionar o item a ser modificado!');
    }
}
/**
 * 
 */
function _MIOLO_MultiTextField2_moveDown(frmObj, mtfName, numFields)
{
//    var list = frmObj[mtfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

    var i = list.selectedIndex;

    if (i != -1)
    {
        if (i < list.options.length - 1)
        {
            var u = list.options[i + 1].text;

            list.options[i + 1].text = list.options[i].text;
            list.options[i + 1].selected = true;

            list.options[i].text = u;
            list.options[i].selected = false;

            list.selectedIndex = i + 1;
        }
    }
    else
    {
        alert('É preciso selecionar o item a ser modificado!');
    }
}

/**
 *  
 */
function _MIOLO_MultiSelectionField_add(frmObj, msfName, n)
{
//    var list = frmObj[msfName + '[]'];
    var list = MIOLO_GetElementById(mtfName + '[]');

    var selection = frmObj[msfName + '_options' + n];
    var n = list.length;
    var i = 0;
    var achou = false;
    var atext = selection.options[selection.selectedIndex].text;

    for (i = 0; i < n; i++)
    {
        if (list.options[i].text == atext)
            achou = true;
    }

    if (achou)
    {
        alert('Item já está na lista!');
    }

    else
    {
        list.options[n] = new Option(atext);
        list.selectedIndex = n;
    }
}
