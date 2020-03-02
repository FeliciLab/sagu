var enterPressed = false;
var f10Pressed = false;
var eventTriggered = false;

function myCustomKeyHandler(e)
{
    if ( window.event ) // IE
    {
            keycode = e.keyCode;
    }
    else if ( e.which ) // Netscape/Firefox/Opera
    {
            keycode = e.which;
    }

    if( document.getElementById('m_ctnButtons') )
    {
        if ( document.getElementById('m_ctnButtons').style.display != 'none' )
        {
            // evita que seja pressionado rapidamente as teclas, fazendo com que abra multiplas popups ao mesmo tempo (#28945)
            if ( eventTriggered )
            {
                return false;
            }
            
            if( !document.getElementById('mPopup') )
            {
                if  ( keycode == 113 ) //F2
                {
                    document.getElementById('btnInvoice').onclick();
                    e.preventDefault();
                    eventTriggered = true;
                    return false;
                }
                
                if  ( keycode == 114 ) //F3
                {
                    document.getElementById('btnMulta').onclick();            
                    e.preventDefault();
                    eventTriggered = true;
                    return false;
                }
                
                if  ( keycode == 115 ) //F4
                {
                    document.getElementById('btnRequests').onclick();
                    e.preventDefault();
                    eventTriggered = true;
                    return false;
                }
            }
        }
    }
    if  ( keycode == 116 ) //F5
    {
        //Busca Titulos
        if( document.getElementById('btnSearchInvoice') )
        {
            document.getElementById('btnSearchInvoice').onclick();
        }
        //Busca Multas
        if( document.getElementById('btnSearchFine') )
        {
            document.getElementById('btnSearchFine').onclick();
        }
        //Busca Solicitações
        if( document.getElementById('btnSearchRequests') )
        {
            document.getElementById('btnSearchRequests').onclick();
        }
        e.preventDefault();
        return false;
    }
    if  ( keycode == 13 && !enterPressed ) //ENTER
    {
        //Adiciona Multa
        if( document.getElementById('btnAddFine') )
        {
            enterPressed = true;
            document.getElementById('btnAddFine').click();
        }
        
        //Adiciona Título
        if( document.getElementById('btnAddInvoice') )
        {
            enterPressed = true;
            document.getElementById('btnAddInvoice').click();            
        }        
        
        //Adiciona solicitaçao
        if( document.getElementById('btnAddRequests') )
        {
            enterPressed = true;
            document.getElementById('btnAddRequests').click();
        }
        
        if ( document.activeElement.tagName == 'BUTTON' )
        {
            return true;
        }
        else
        {
            e.preventDefault();
            return false;
        }
    }
    else if ( keycode == 13 )
    {
        e.preventDefault();
        return false;
    }
    
    if  ( keycode == 121 ) //F10
    {
        if( !document.getElementById('mPopup') )
        {
            if( document.getElementById('btnFinalizarPagamento') !== null && !f10Pressed )
            {
                f10Pressed = true;
                document.getElementById('btnFinalizarPagamento').click();
            }
            else if( document.getElementById('btnPreFinalizarPagamento') !== null )
            {
                document.getElementById('btnPreFinalizarPagamento').click();
            }
            else if( document.getElementById('divPayments').style.display != 'none' && !f10Pressed )
            {
                f10Pressed = true;
                document.getElementById('btnFinalize').click();
                
                if( document.getElementById('specieId') )
                {
                    document.getElementById('specieId').focus();
                }                
            }
            else
            {
                if( document.getElementById('btnFinalizePre') )
                {
                    document.getElementById('btnFinalizePre').onclick();
                }        
            }
        }        
        e.preventDefault();
        return false;
    }

    if  ( keycode == 27 ) //ESC
    {
        
        if( document.getElementById('divSaguMessages').style.display != 'none' )
        {
            SAGUHideMessagesDiv();
        }
        else if ( document.getElementById('mPopup') && !document.getElementById('m_popupOpenCounter') && !document.getElementById('m_popupOpenCounter') )
        {
            mpopup.remove();
        }
        else
        {
            document.getElementById('btnCancel').click();
        }       
        e.preventDefault();
        return false;
    }
    
    if  ( keycode === 117 ) //F6
    {
        if( document.getElementById('btnMostrarSaldoCaixasBancos') !== null )
        {
            document.getElementById('btnMostrarSaldoCaixasBancos').click();
        }
        else if( document.getElementById('btnVerSaldos').parentNode.style.display !== 'none' )
        {
            document.getElementById('btnVerSaldos').click();
        }
        
        e.preventDefault();
        
        return false;
    }
    
    //console.log(keycode); //Debug das teclas de atalho	    	                
    return true;
}