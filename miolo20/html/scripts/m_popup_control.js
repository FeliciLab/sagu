var mpopup =
{
    context: null,
    constructor: function()
    {
        window.onresize = function () {
            mpopup.show();
        };
    },

    scrollUp: function ()
    {
        window.scrollTo(0,0);
    },

    remove: function ()
    {
        // Undo prevent body scroll
        if (document.all && document.createAttribute && document.compatMode != 'BackCompat') {
            // IE6 (and above) in standards mode
            document.getElementsByTagName('html')[0].style.overflow = '';
        } 
        else {
            document.body.style.overflow = '';
        }

        var background = MIOLO_GetElementById('mPopupBackground');
        var popup = MIOLO_GetElementById('mPopup');
        var anchor = MIOLO_GetElementById('mPopupAnchor');

        if ( background && popup && anchor )
        {
            window.setTimeout(function() {
                background.parentNode.removeChild(background);
            }, 200);
            window.setTimeout(function() {
                popup.parentNode.removeChild(popup);
            }, 200);
            window.setTimeout(function() {
                anchor.parentNode.removeChild(anchor);
            }, 200);
        }
    },

    show: function(focusField)
    {
        // Prevent body scroll
        if (document.all && document.createAttribute && document.compatMode != 'BackCompat') {
            // IE6 (and above) in standards mode
            document.getElementsByTagName('html')[0].style.overflow = 'hidden'; 
        } else {
            document.body.style.overflow = 'hidden';
        }

        popup  = MIOLO_GetElementById('mPopup');

        if ( popup )
        {
            // centers the popup
            var viewportW = 630, viewportH = 460;
            if (document.body && document.body.offsetWidth)
            {
                viewportW = document.body.offsetWidth;
                viewportH = document.body.offsetHeight;
            }
            if (document.compatMode=='CSS1Compat' && document.documentElement && document.documentElement.offsetWidth )
            {
                viewportW = document.documentElement.offsetWidth;
                viewportH = document.documentElement.offsetHeight;
            }
            if (window.innerWidth && window.innerHeight)
            {
                viewportW = window.innerWidth;
                viewportH = window.innerHeight;
            }

            mbH = popup.offsetHeight;
            mbW = popup.offsetWidth;

            var left = Math.floor((viewportW - mbW) / 2);
            popup.style.left = left + 'px';

            var top = Math.floor((viewportH - mbH) / 2);

            if ( typeof dojo == 'undefined' )
            {
                if ( top < 0 )
                {
                    top = 30;
                }
            }
            else
            {
                if ( top < 100 )
                {
                    top = 100;
                }
            }

            popup.style.top = top + 'px';
            
            if ( focusField != null )
            {
                document.getElementById(focusField).focus();
            }
        }
    },

    configureClose: function()
    {
        // Caso o botão não tenha sido renderizado
        if( MIOLO_GetElementById('mPopupClose') )
        {
            MIOLO_GetElementById('mPopupClose').onclick = mpopup.remove;
        }
        
        document.onkeydown = function ( e )
        {
            if ( e.keyCode == 27 )
            {
                mpopup.remove();
            }
        };
    },

    response: function (result)
    {
        MIOLO_GetElementById('mPopupResponse').innerHTML = result;
        MIOLO_UpdateAJAXValidators('mPopupResponse');
        mpopup.configureClose();
        mpopup.show();

        var script = MIOLO_GetElementById('mPopupResponse').getElementsByTagName('script');
        if ( script.length > 0 )
        {
            for ( var i=0; i < script.length; i++ )
            {
                setTimeout(script[i].innerHTML, 0);
            }
        }
    },

    doAjax: function(url, method)
    {
        MIOLO_ajaxCall(url, "POST", method, '', this.response, "TEXT");
    }
}