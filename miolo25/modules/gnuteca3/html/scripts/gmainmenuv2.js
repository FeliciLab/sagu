dojo.require("dijit.Menu");

var GMainMenu = dojo.declare(dijit.Menu, {
    targetId: null,
    itens: [],
    subMenus: [],
    popupDelay: 0,
    
    setup: function(targetId)
    {
        this.targetId = targetId;
        this.targetNodeIds = [targetId];
        
        dojo.byId(targetId).addEventListener("mouseover", function()
        {
            gMainMenu.show();
            
        });
        
    },

    show: function()
    {
        var menu;
        var element;
        
        menu = this;
        element = dojo.byId(this.targetId);
        
        dijit.popup.open( {
            popup: menu,
            around: element,
            
            onExecute: function() { 
                dijit.popup.close(menu);
            },
            onCancel: function() {
                dijit.popup.close(menu);
            }
        });

        menu._onBlur = function() {

            menu.inherited('_onBlur', arguments);
            dijit.popup.close(menu);
            
        };

        menu.focus();
        
    },

    addItem: function(id, titulo, url, iconeCSS, disabled, parent)
    {
        var acao = null;
        
        if( !url )
        {
            acao = function() {};
        }
        else
        {
            // Normaliza a URL
            url = url.replace(/&amp;/g,"&");
            
            acao = function(evt)
            {
                // Se for o botão do meio
                if( evt.which === 2 )
                {
                    evt.preventDefault();
                    window.open(url, "_blank");
                    
                }
                else
                {
                    window.open(url, "_self");
                }
                
            };
            
        }
        
        // Cria o objeto
        var item = new dijit.MenuItem({
            label: titulo,
            onClick: function() {
                // Casos especias em que o dojo apresentava erro. Ticket #33692.
                if ( id == 'trocaUnidade' || id == 'sobre' )
                {
                    window.open(url, "_self");
                }
                else
                {
                    gnuteca.doLink(url, '__mainForm');
                }
            },
            href: acao,
            iconClass: iconeCSS,
            disabled: disabled
            
        });
        
        if ( this.subMenus[parent] )
        {
            this.subMenus[parent].addChild(item);
        }
        else
        {
            this.addChild(item);

        }
        
        this.itens[id] = {
            label: titulo,
            action: url
        };
        
    },

    addSubMenu: function(id, titulo, iconeCSS, disabled, parent)
    {
        this.subMenus[id] = new dijit.Menu({
            popupDelay: 0
        });
        
        var item = new dijit.PopupMenuItem({
            label: titulo,
            popup: this.subMenus[id],
            iconClass: iconeCSS,
            disabled: disabled            
        });

        if( this.subMenus[parent] )
        {
            this.subMenus[parent].addChild(item);
            
        }
        else
        {
            this.addChild(item);
        }
        
    },
    
    removeAccentsFromString: function(strAccents) {
        strAccents = strAccents.split('');
        strAccentsOut = new Array();
        strAccentsLen = strAccents.length;
        
        var accents = 'ÀÁÂÃÄàáâãäÒÓÔÕÕÖòóôõöÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûü';
        var accentsOut = ['A','A','A','A','A','a','a','a','a','a','O','O','O','O','O','O','o','o','o','o','o','E','E','E','E','e','e','e','e','C','c','I','I','I','I','i','i','i','i','U','U','U','U','u','u','u','u'];
        
        for ( var y = 0; y < strAccentsLen; y++ )
        {
            if ( accents.indexOf( strAccents[y] ) != -1 )
            {
                strAccentsOut[y] = accentsOut[accents.indexOf( strAccents[y] )];
            }
            else
            {
                strAccentsOut[y] = strAccents[y];
            }
        }
        
        strAccentsOut = strAccentsOut.join('');
        
        return strAccentsOut;
    }
    
});