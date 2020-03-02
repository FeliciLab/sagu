dojo.declare ("MSpecialGrid", null,
{
    x:null,
    context: null ,
    constructor: function()
    {
    },

    showHideColumn: function (id, index)
    {
        var gridBody = dojo.query('.mGridBody', dojo.byId(id))[0];
        var headColumns  = dojo.query('th',dojo.query('tr', dojo.query('thead', gridBody)[0])[0]);
        var tableIndex = index;
        dojo.forEach ( headColumns, function (element, key) {
            if ( element.className == 'btn' || element.className == 'select' ) //pula colunas do miolo
            {
                tableIndex++;
            }
        });

        var head = headColumns[tableIndex];

        if ( head.style.display == '' )
        {
            head.style.display = 'none';
            type = 'none';

            dojo.byId( id + '_invisibleColumns' ).value += index + ',';
        }
        else
        {
            head.style.display = '';
            type = '';

            dojo.byId( id + '_invisibleColumns' ).value = dojo.byId( id + '_invisibleColumns' ).value.replace( index + ',', '' );
        }

        var lines = dojo.query('tr', dojo.query('tbody', gridBody)[0]);

        dojo.forEach(lines, function (element) {
            dojo.query('td', element)[tableIndex].style.display = type;
        }
        );

        var item = 
            dojo.query('img', 
                dojo.query('td', 
                    dojo.query('tr', 
                        dojo.query('tbody', 
                            dijit.byId(id + '_columnsMenu').domNode
                        )[0]
                    )[index]
                )[0]
            )[0];

        if ( type == '' )
        {
            item.className = 'dijitMenuItemIcon mGridVisibleColumnsChecked';
        }
        else
        {
            item.className = 'dijitMenuItemIcon mGridVisibleColumnsUnchecked';
        }
    },

    uncheckAll: function ( gridId )
    {
        var grid = dojo.byId( gridId );
        var body = dojo.query( '.mGridBody tbody', grid )[0];

        var rowCount = 0;
        dojo.query('tr', body).forEach( function () {
            rowCount = rowCount+1
        } );
        var chkAll = dojo.byId('chkAll');
        chkAll.checked = false;
        miolo.grid.checkAll(chkAll, rowCount, gridId);
    },

    boldColumn: function (gridId, index)
    {
        var tr = dojo.byId('row' + gridId + '[' + index + ']');
        tr.className += ' mspecialgridTrBold';
    },

    selectKeys: function (event)
    {
        var targetRow = event.target.parentNode.id.substring(3);
        if ( ! targetRow )
        {
            targetRow = event.target.parentNode.parentNode.id.substring(3);
        }

        var targetCheck = dojo.byId('select' + targetRow);
        indexRow = targetRow.substring(targetRow.indexOf('['),targetRow.indexOf(']')).replace('[','');

        if ( event.shiftKey )
        {
            // Clear the text selection
            window.getSelection().removeAllRanges();

            element = targetRow.substring(0,targetRow.indexOf('['));

            if(mspecialgrid.x)
            {
                if(mspecialgrid.x<indexRow)
                {
                    for(var i=indexRow; i>=mspecialgrid.x; i--)
                    {
                        if(dojo.byId('select'+element+'['+i+']').checked)
                        {
                            break;
                        }
                        else
                        {
                            if(dojo.byId('select'+element+'['+i+']'))
                            {
                                dojo.byId('select'+element+'['+i+']').checked = true;
                                miolo.grid.check(dojo.byId('select'+element+'['+i+']'), element+'['+i+']' );
                            }
                        }
                    }
                }
                else
                {
                    for ( i=indexRow; i<=mspecialgrid.x; i++ )
                    {
                        if ( dojo.byId('select'+element+'['+i+']').checked )
                        {
                            break;
                        }
                        else
                        {
                            if ( dojo.byId('select'+element+'['+i+']') )
                            {
                                dojo.byId('select'+element+'['+i+']').checked = true;
                                miolo.grid.check(dojo.byId('select'+element+'['+i+']'), element+'['+i+']' );
                            }
                        }
                    }
                }
            }
            return false;
        }
        else
        {
            if (targetRow && targetCheck)
            {
                if ( ! event.ctrlKey )
                {
                    mspecialgrid.uncheckAll(targetRow.substring(0,targetRow.indexOf('[')));
                }

                if ( targetCheck.checked == true )
                {
                    targetCheck.checked = false;
                }
                else
                {
                    targetCheck.checked = true;
                }

                miolo.grid.check(targetCheck, targetRow);
            }

        }

        mspecialgrid.x = indexRow;
    }
}
);

mspecialgrid = new MSpecialGrid;


