dojo.declare ("MDualList", null,
{
    add: function(leftListId, rightListId, mainListId)
    {
        var leftList = dojo.byId(leftListId);
        var rightList = dojo.byId(rightListId);

        this.toggleSelectedElements(leftList, rightList);
        this.updateMainList(mainListId, rightListId);
    },

    remove: function(leftListId, rightListId, mainListId)
    {
        var leftList = dojo.byId(leftListId);
        var rightList = dojo.byId(rightListId);

        this.toggleSelectedElements(rightList, leftList);
        this.updateMainList(mainListId, rightListId);
    },

    toggleSelectedElements: function(srcList, targetList)
    {
        for ( i=srcList.options.length-1; i>=0; i-- )
        {
            item = srcList.options.item(i);
            if ( item.selected )
            {
                targetList.options.add(dojo.clone(item));
                srcList.options.remove(i);
            }
        }

        var srcCounter = dojo.byId(srcList.id + '_counter');
        if ( srcCounter )
        {
            var targetCounter = dojo.byId(targetList.id + '_counter');

            srcCounter.innerHTML = srcList.options.length;
            targetCounter.innerHTML = targetList.options.length;
        }
    },

    moveUp: function(listId, mainListId)
    {
        var list = dojo.byId(listId);

        for ( i=list.options.length-1; i>=0; i-- )
        {
            item = list.options.item(i);
            if ( item.selected && i != 0 )
            {
                upperItem = dojo.clone(list.options.item(i-1));
                list.options[i-1] = dojo.clone(item);
                list.options[i-1].selected = true;
                list.options[i] = upperItem;
                break;
            }
        }

        this.updateMainList(mainListId, listId);
    },

    moveDown: function(listId, mainListId)
    {
        var list = dojo.byId(listId);

        for ( i=0; i<list.options.length; i++ )
        {
            item = list.options.item(i);
            if ( item.selected && i != list.options.length-1 )
            {
                downerItem = dojo.clone(list.options.item(i+1));
                list.options[i+1] = dojo.clone(item);
                list.options[i+1].selected = true;
                list.options[i] = downerItem;
                break;
            }
        }

        this.updateMainList(mainListId, listId);
    },

    updateMainList: function(mainListId, rightListId)
    {
        var rightList = dojo.byId(rightListId);

        var newList = dojo.clone(rightList);
        newList.id = mainListId;
        newList.name = mainListId + '[]';
        this.selectAll(newList);

        dojo.byId(mainListId + '_div').innerHTML = '';
        dojo.byId(mainListId + '_div').appendChild(newList);
    },

    selectAll: function(list)
    {
        for ( i=list.options.length-1; i>=0; i-- )
        {
            list.options.item(i).selected = true;
        }
    }
});

mduallist = new MDualList();