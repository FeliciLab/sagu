miolo.linkButton = function(url, event, param, formId)
{
    if ( formId != null )
    {
        if ( eval('miolo.onSubmit()') )
        {
            form.action = url;
            miolo.doPostBack(event, param);
            form.submit();
        }
    }
    else
    {
        alert('MIOLO INTERNAL ERROR: LinkButton\n\nForm ' + formId + ' not found!');
    }
}