dojo.declare ("MPopup", null,
{
    context: null ,
    constructor: function()
    {
        dojo.require('dojo.dnd.move');
        dojo.connect(window, 'onresize', function () {
            mpopup.show();
        } )
    },

    scrollUp: function ()
    {
        window.scrollTo(0,0);
    },

    remove: function ()
    {
        background = dojo.byId('mPopupBackground');
        popup      = dojo.byId('mPopup');

        if ( background && popup )
        {
            dojo.fadeOut({
                node: background ,
                duration: 300
            }).play();
            dojo.fadeOut({
                node: popup ,
                duration: 300
            }).play();

            window.setTimeout(function() {
                if ( dojo.byId('mPopupBackground') )
                {
                    dojo.byId('mPopupBackground').parentNode.removeChild(background);
                }
            }, 400);
            window.setTimeout(function() {
                if ( dojo.byId('mPopup') )
                {
                    dojo.byId('mPopup').parentNode.removeChild(popup);
                }
            }, 400);
        }
    },

    show: function()
    {
        popup  = dojo.byId('mPopup');

        if ( popup )
        {
            new dojo.dnd.Moveable(popup, {
                handle: 'popupTitle'
            });

            // centers the popup
            viewport = dijit.getViewport(); //tamanho da tela
            mb= dojo.marginBox(popup); //tamanho do popup

            // workaround para funcionar no webkit. Versão nova do dojo deve resolver o problema
            if ( mb.w == viewport.w)
            {
                mb.w = popup.offsetWidth;
            }

            left = Math.floor((viewport.w - mb.w) / 2);
            popup.style.left = left + 'px';

            dojo.fadeIn({
                node: popup,
                duration: 300
            }).play();
            this.scrollUp();
        }
    },

    configureClose: function()
    {
        handle = dojo.connect( document, "onkeydown", function ( e )
        {
            if ( e.keyCode == dojo.keys.ESCAPE )
            {
                mpopup.remove();
            }
            dojo.disconnect(handle);
        });
    }
}
);

mpopup = new MPopup;
