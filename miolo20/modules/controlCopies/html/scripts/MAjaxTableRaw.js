// get all data from form (the data is cleaned with getAjaxFields from class MTableRawSession)
// ir uses escape, that convert characters to html entities
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

// Converts + => " " and make unescape (to receive data from server...
function decode(str)
{
    if (str)
    {
        return unescape(str.replace(/\+/g, " "));
    }
    else
    {
        return '';
    }
}