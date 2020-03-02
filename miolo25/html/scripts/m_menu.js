dojo.require("dijit.Menu");

dojo.provide("dijit.MStaticMenu");
dojo.declare("dijit.MStaticMenu", dijit.Menu, {
    indexZebraPopupMenuItem: 0,
    indexZebraMenuItem: 0,

    setSubMenu: function()
    {
        this._onBlur = function()
        {
            this.inherited('_onBlur', arguments);
            dijit.popup.close(this);
        }

        this.onExecute = function()
        {
            dijit.popup.close(this);
        }
    },

    addCustomCSS: function(css, cssClass)
    {
        if ( document.getElementById('customCSS-' + cssClass) )
        {
            document.getElementById('customCSS-' + cssClass).innerHTML = css;
        }
        else
        {
            element = document.createElement('style');
            element.setAttribute('id','customCSS-' + cssClass);
            element.innerHTML = css;
            dojo.body().appendChild(element);
        }
    }
});
dojo.provide("dijit.MContextMenu");
dojo.declare("dijit.MContextMenu", dijit.MStaticMenu, {
    show: function(coords)
    {
        coords = coords.split(':');

        dijit.popup.open({
            popup: this,
            x: coords[0],
            y: coords[1],
            orient: 'BL',
            onExecute: this.close,
            onCancel: this.close
        });

        this._onBlur = function()
        {
            this.inherited('_onBlur', arguments);
            dijit.popup.close(this);
        }
    },

    close: function()
    {
        dijit.popup.close(this);
    },

    setGridTarget: function(gridId)
    {
        dojo.connect(dojo.byId(gridId), 'oncontextmenu', function(event) {
            var targetRow = event.target.parentNode.id.substring(3);
            if ( ! targetRow )
            {
                targetRow = event.target.parentNode.parentNode.id.substring(3);
            }
            var targetCheck = dojo.byId('select' + targetRow);

            if ( targetCheck)
            {
                if ( ! targetCheck.checked )
                {
                    var body = event.target.parentNode.parentNode;

                    if ( body.tagName != 'TBODY' )
                    {
                        body = event.target.parentNode.parentNode.parentNode;
                    }

                    var rowCount = 0;
                    dojo.query('tr', body).forEach( function () { rowCount = rowCount+1 } );
                    if (  targetRow )
                    {
                        var chkAll = dojo.byId('chkAll');
                        chkAll.checked = false;
                        miolo.grid.checkAll(chkAll, rowCount, gridId);
                    }

                    targetCheck.checked = true;
                    miolo.grid.check(targetCheck, targetRow);
                }
            }
        });
    }
});

dojo.provide("dijit.MMenuItem");
dojo.declare("dijit.MMenuItem", dijit.MenuItem, {
    postCreate: function(){
        dojo.addClass(this.domNode, 'dijitMenuZebra' + ((this.getParent().indexZebraMenuItem++)%2) );
    }
});

dojo.provide("dijit.MPopupMenuItem");
dojo.declare("dijit.MPopupMenuItem", dijit.PopupMenuItem, {
    postCreate: function(){
        dojo.addClass(this.domNode, 'dijitMenuZebra' + ((this.getParent().indexZebraPopupMenuItem++)%2) );
    }
});
