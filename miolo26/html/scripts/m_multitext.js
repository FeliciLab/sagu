// +-----------------------------------------------------------------+
// | MIOLO - Miolo Development Team - UNIVATES Centro UniversitÃ¡rio  |
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
// | Created: 2001/08/14 Vilson Cristiano GÃ¤rtner [vg]               |
// |                     Thomas Spriestersbach    [ts]               |
// |                                                                 |
// | History: Initial Revision                                       |
// |          2001/12/14 [ts] Added MultiTextField support functions |
// +-----------------------------------------------------------------+

/**
 * MULTITEXTFIELD
 */
/**
 * FunÃ§Ã£o que simplesmente seleciona todos os itens, para que
 * serÃ£o incluidos ao enviar o formulÃ¡rio
 */
function _MIOLO_MultiTextField_onSubmit(frmName,mtfName)
{
    var form = eval('document.'+frmName);
    var list = form[mtfName+'[]'];
    if ( list != null  && list.options != null )
    {
        for ( var i=0; i<list.length; i++ )
        {
            list.options[i].value = list.options[i].text;
            list.options[i].selected = true;
        }
    }
    return true;
}

/**
 * FunÃ§Ã£o que intercepta a tecla Enter, para que o conteÃºdo do
 * campo de texto Ã© adicionado a lista.
 */
function _MIOLO_MultiTextField_onKeyDown(source,frmObj,mtfName,event)
{
    // IE and compatibles use 'keyCode', NS and compatibles 'which'
    var key = ( document.all != null ) ? event.keyCode : event.which;
    
    if ( source.name == mtfName + '_text' )
    {
        if ( key == 13 ) // enter key
        {
            _MIOLO_MultiTextField_add(frmObj,mtfName);
            return false;
        }
    }
    
    else if ( source.name == mtfName + '[]' )
    {
        // alert(key);
        
        if ( key == 46 ) // delete key
        {
            _MIOLO_MultiTextField_remove(frmObj,mtfName);
            return false;
        }
    }
}

/**
 * FuncÃ§Ã£o que adiciona o conteÃºdo do campo de texto a lista.
 */
function _MIOLO_MultiTextField_add(frmObj,mtfName)
{
    var list = frmObj[mtfName+'[]'];
    var tf   = frmObj[mtfName+'_text'];
    if ( tf.value != '' )
    {
        var i = list.length;
        list.options[i] = new Option(tf.value);
        for ( var j=0; j<=i; j++ )
        {
            list.options[i].selected = (j==i);
        }
        tf.value = '';
    }
}

/**
 * FuncÃ§Ã£o que exclui o item atualmente selecionado
 */
function _MIOLO_MultiTextField_remove(frmObj,mtfName)
{
    var list = frmObj[mtfName+'[]'];
    
    for ( var i=0; i<list.length; i++ )
    {
        if ( list.options[i].selected )
        {
            list.options[i] = null;
            
            if ( i >= list.length )
            {
                i = list.length - 1;
            }
            
            if ( i >= 0 )
            {
                list.options[i].selected = true;
            }
            
            break;
        }
    }
}

var _MIOLO_MultiTextField2_separator = '] [';

/**
 *  
 */
function _MIOLO_MultiTextField2_Split(value)
{
    return value.substring(1,value.length-1).split(_MIOLO_MultiTextField2_separator);
}

/**
 *  
 */
function _MIOLO_MultiTextField2_Join(fields)
{
    var value = '[';
    
    for ( var i=0; i<fields.length; i++ )
    {
        if ( i > 0 )
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
function _MIOLO_MultiTextField2_onSubmit(frmName,mtfName)
{
  return _MIOLO_MultiTextField_onSubmit(frmName,mtfName);
}

/**
 *  
 */
function _MIOLO_MultiTextField2_onKeyDown(source,frmObj,mtfName,event,numFields)
{
  // IE and compatibles use 'keyCode', NS and compatibles 'which'
  var key  = ( document.all != null ) ? event.keyCode : event.which;
  var name = mtfName + '_text';
  var len  = name.length;
  
  if ( source.name.substring(0,len) == name )
  {
    if ( key == 13 ) // enter key
    {
      _MIOLO_MultiTextField2_add(frmObj,mtfName,numFields);
      return false;
    }
  }

  else if ( source.name == mtfName + '[]' )
  {
    // alert(key);

    if ( key == 46 ) // delete key
    {
      _MIOLO_MultiTextField2_remove(frmObj,mtfName,numFields);
      return false;
    }
  }
}

/**
 *  
 */
function _MIOLO_MultiTextField2_onSelect(frmObj,mtfName,numFields)
{
    var list = frmObj[mtfName+'[]'];
    
    var i = list.selectedIndex;
    
    if ( i != -1 )
    {
        var a = _MIOLO_MultiTextField2_Split(list.options[i].text);
        
        for ( var j=1; j<=numFields; j++ )
        {
            var tf = frmObj[mtfName+'_text'+j];
            
            if ( tf != null )
            {
                tf.value = a[j-1];
            }
            
            else
            {
                var op = frmObj[mtfName+'_options'+j];
                
                if ( op != null )
                {
                    // preselect option based on value
                    for ( var n=0; n<op.options.length; n++ )
                    {
//                        if ( op.options[n].value == a[j-1] )
                        if ( op.options[n].text == a[j-1] )
                        {
                            op.selectedIndex = n;
                            break;
                        }
                    }
                }
            }
        }
    }
    
    else
    {
        for ( var j=1; j<=numFields; j++ )
        {
            var tf = frmObj[mtfName+'_text'+j];
            
            if ( tf != null )
            {
                tf.value = '';
            }
            
            else
            {
                var op = frmObj[mtfName+'_options'+j];
                
                if ( op != null )
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
function _MIOLO_MultiTextField2_getInput(frmObj,mtfName,numFields)
{
    var list   = frmObj[mtfName+'[]'];
    var fields = new Array(numFields);
	var value='';  
    
    for ( var i=1; i<=numFields; i++ )
    {
        var tf = frmObj[mtfName+'_text'+i];
        
        fields[i-1] = '';
        
        if ( tf != null )
        {
//            if ( i > 1 )
//            {
//                value += _MIOLO_MultiTextField2_separator;
//            }
            
            fields[i-1] = tf.value;
            
            tf.value = '';
        }
        
        else 
        {
            var list = frmObj[mtfName+'_options'+i];
            
            if ( list != null )
            {
//                if ( i > 1 )
//                {
//                    value += _MIOLO_MultiTextField2_separator;
//                }
                
//                fields[i-1] = list.options[list.selectedIndex].value;
                fields[i-1] = list.options[list.selectedIndex].text;
            }
        }
    }

    return _MIOLO_MultiTextField2_Join(fields); 
}
/**
 *  
 */
function _MIOLO_MultiTextField2_add(frmObj,mtfName,numFields)
{
    var list  = frmObj[mtfName+'[]'];
    var i     = list.length;
    
	list.options[i] = new Option(_MIOLO_MultiTextField2_getInput(frmObj,mtfName,numFields));
    
//    for ( var j=0; j<=i; j++ )
//    {
//        list.options[i].selected = (j==i);
//    }
    list.selectedIndex = i;
    
    
}
/**
 *  
 */
function _MIOLO_MultiSelectionField_add(frmObj,msfName,n)
{
    var list  = frmObj[msfName+'[]'];
	var selection = frmObj[msfName+'_options'+n];
    var n     = list.length;
	var i = 0;
    var achou = false;
	var atext = selection.options[selection.selectedIndex].text;
    for( i=0; i<n; i++ )
    {
       if (list.options[i].text == atext) achou = true;
    }
	if (achou)
	{
		alert('Item jÃ¡ estÃ¡ na lista!');
	} 
	else
	{
	    list.options[n] = new Option(atext);
        list.selectedIndex = n;
    }
}

/**
 * FuncÃ§Ã£o que exclui o item atualmente selecionado
 */
function _MIOLO_MultiTextField2_remove(frmObj,mtfName,numFields)
{
    _MIOLO_MultiTextField_remove(frmObj,mtfName);	
}

/**
 * 
 */
function _MIOLO_MultiTextField2_modify(frmObj,mtfName,numFields)
{
    var list  = frmObj[mtfName+'[]'];
    
    var i = list.selectedIndex;
    
    if ( i != -1 )
    {
        list.options[i].text = _MIOLO_MultiTextField2_getInput(frmObj,mtfName,numFields);
	}
    else
    {
        alert('Ã preciso selecionar o item a ser modificado!');
    }
}

/**
 * 
 */
function _MIOLO_MultiTextField2_moveUp(frmObj,mtfName,numFields)
{
    var list  = frmObj[mtfName+'[]'];
    
    var i = list.selectedIndex;
    
    if ( i != -1 )
    {
	if ( i > 0 )
	{
	    var u = list.options[i-1].text;
	    
            list.options[i-1].text = list.options[i].text;
	    list.options[i-1].selected = true;
	    
	    list.options[i].text = u;
	    list.options[i].selected = false;
	    
	    list.selectedIndex = i - 1;
	}
    }
    
    else
    {
        alert('Ã preciso selecionar o item a ser modificado!');
    }
}

/**
 * 
 */
function _MIOLO_MultiTextField2_moveDown(frmObj,mtfName,numFields)
{
    var list  = frmObj[mtfName+'[]'];
    
    var i = list.selectedIndex;
    
    if ( i != -1 )
    {
	if ( i < list.options.length - 1 )
	{
	    var u = list.options[i+1].text;
	    
            list.options[i+1].text = list.options[i].text;
	    list.options[i+1].selected = true;
	    
	    list.options[i].text = u;
	    list.options[i].selected = false;
	    
	    list.selectedIndex = i + 1;
	}
    }
    
    else
    {
        alert('Ã preciso selecionar o item a ser modificado!');
    }
}