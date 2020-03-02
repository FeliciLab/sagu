function OnlyNumbers2(e)
{
    if (window.event) //IE
    {
        tecla = e.keyCode;
    }
    else if (e.which) //FF
    {
        tecla = e.which;
    }
    if ( (tecla >= 48 && tecla <= 57)||(tecla == 8 ) )
    {
        return true;
    }
    else
    {
        return false;
    }
}