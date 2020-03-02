dojo.declare ("MSubDetail", null,
    {
        context: null ,
        constructor: function(){},

        updateField : function ( id , value)
        {
            element = dojo.byId( id );
            extraElement = dojo.byId( id + '_defaultValue' );

            if ( extraElement && !value )
            {
                value = extraElement.value;
            }

            if ( element)
            {
                if ( element.type == 'checkbox' )
                {
                    if ( value != '' && value != 'f' )
                    {
                        element.checked = 'checked';
                    }
                    else
                    {
                        element.checked = false;
                    }
                }
                else if ( element && element.className == 'mBaseGroup' )
                {
                    // procura por radiobuttongroup tenta achar
                    var radiosArray = Array();
                    var index	= 0;

                    dojo.query('input[type=radio]', element).forEach(
                        function(node, i, arr){
                            radiosArray[index] = node.id;
                            index++;
                        }
                    );

                    // se encontrou define como marcado ou não
                    if ( radiosArray.length > 0 )
                    {
                        for ( i = 0 ; i< radiosArray.length ; i++)
                        {
                            element = dojo.byId(radiosArray[i]);
                            element.checked = element.value == value;
                        }
                    }
                }
                else
                {
                    //pega o elemento pelo nome para o MCalendarField
                    elementName = document.getElementsByName( id ); //retornar array

                    if ( elementName )
                    {
                        elementName = elementName[0]; //tem que pegar o zero, pois retornar um array

                        if ( elementName )
                        {
                            //caso o elemento seja igual ao pego pelo id, não interessa
                            if ( elementName == element )
                            {
                                elementName = null;
                            }
                            else
                            {
                                //seta o valor do elementName
                                elementName.value = value;
                            }
                        }
                    }

                    //definição padrão
                    element.value = value;
                }

                // para funcionar MCombo
                if ( element = dojo.byId( id + '_sel' ) )
                {
                    element.value = value ;
                }

                // para lookup
                if ( element =  dojo.byId( id + '_lookupDescription' ) )
                {
                    element.value = value ;
                }

                // ativa os eventos onchange e onblur do campo, caso eles existam
                this.triggerEvent(id, 'change');
                this.triggerEvent(id, 'blur');
            }

        },

        triggerEvent: function( element, event )
        {
            event = event && event.slice(0, 2) == "on" ? event.slice(2) : event;

            if(dojo.doc.createEvent)
            {
                var evObj = dojo.doc.createEvent("HTMLEvents");
                evObj.initEvent( event , true, true);
                dojo.byId(element).dispatchEvent(evObj);
            }
            else if (dojo.doc.createEventObject) //IE
            {
                dojo.byId(element).fireEvent("on" + event);
            }
        },

        addJsField : function(id, value)
        {

            exists = dojo.byId( id);

            if (!exists)
            {
                element = document.createElement('input');
                element.type = 'hidden';
                element.id = id;
                element.name = id;

                if (value)
                {
                    element.value = value;
                }

                var content  = dojo.byId('content');

                if (!content)
                {
                    content = dojo.byId('extContent');
                }

                content.appendChild(element);
            }
        },

        updateButtons : function(name, addImg, clearImg, type)
        {
            if (!type || !type == 'adicionar')
            {
                labelA = 'Adicionar';
                labelB = 'Limpar';
                labelC = 'edição';
                labelD = 'inserção';
            }
            else
            {
                labelA = 'Atualizar';
                labelB = 'Cancelar';
                labelC = 'inserção';
                labelD = 'edição';
            }

            addData   = 'addData' + name;
            clearData = 'clearData' + name;

            var span = dojo.query('span', dojo.byId(addData))[0];
            if ( span ) span.innerHTML = labelA;

            dojo.query('img', dojo.byId(addData) )[0].src = addImg;

            span = dojo.query('span', dojo.byId(clearData))[0];
            if ( span ) span.innerHTML = labelB;

            dojo.query('img', dojo.byId(clearData) )[0].src = clearImg;

            var legend = dojo.query('legend', dojo.byId(name))[0];
            if ( legend )
            {
                legend.innerHTML = legend.innerHTML.replace(labelC, labelD);
            }
        },

        hideOnEdit : function(id)
        {
            var event   = dojo.byId('__mainForm__EVENTTARGETVALUE').value;
            var element = dojo.byId(id);

            switch ( event )
            {
                case 'addToTable':
                case 'clearTableFields':

                    if ( dojo.byId('label' + id) )
                    {
                        element.parentNode.removeChild(dojo.byId('label' + id));
                    }

                    element.style.display = 'block';
                    break;

                case 'editFromTable':

                    // remove o label criado anteriormente
                    if ( dojo.byId('label' + id) )
                    {
                        element.parentNode.removeChild(dojo.byId('label' + id));
                    }

                    var newElement = document.createElement('span');
                    newElement.id = 'label' + id;
                    newElement.innerHTML = element.options[element.selectedIndex].text;

                    var pElement = element.parentNode;
                    pElement.appendChild(newElement);
                    element.style.display = 'none';
                    break;
            }
        }
    }
    );
	    
var msubdetail = new MSubDetail();
