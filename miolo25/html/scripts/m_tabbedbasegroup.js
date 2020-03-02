dojo.declare("MTabbedBaseGroup", null, {

    animate: true,
    doEffect: true,
    ajaxTabs: {},

    createTab: function ( tabId, tabbedBaseGroupId, button, content, ajaxAction )
    {
        miolo.getElementById('buttons' + tabbedBaseGroupId).innerHTML += button;

        var t = miolo.getElementById(tabbedBaseGroupId);
        var d = document.createElement('div');

        d.innerHTML = content;
        delete t.childNodes[t.length-1];
        t.appendChild(d);

        eval(tabbedBaseGroupId + 'Tabs[' + tabbedBaseGroupId + "Tabs.length] = '" + tabId + "';");
        
        if ( ajaxAction )
        {
            eval('this.ajaxTabs.' + tabId + ' = true;');
        }

        mtabbedbasegroup.changeTab(tabId, tabbedBaseGroupId);
    },
    toggleTabState: function ( tabId, tabbedBaseGroupId, state )
    {
        var button = miolo.getElementById(tabId + 'Button');
        if ( !button )
        {
            return;
        }

        switch ( state )
        {
            case 'enabled':
                button.className = 'mTab mTabIdle';
                url = button.getAttribute('onclick');
                if ( url )
                {
                    var re = new RegExp(/^return\sfalse;/);
                    url = url.replace(re, ' ') + "return mtabbedbasegroup.changeTab('"+tabId+"','"+tabbedBaseGroupId+"');";
                }
                else
                {
                    url = "return mtabbedbasegroup.changeTab('"+tabId+"','"+tabbedBaseGroupId+"');";
                }

                button.setAttribute('onclick', url);
                button.onclick();
                break;

            case 'disabled':
                currentTabIndex = mtabbedbasegroup.getCurrentTabIndex(tabbedBaseGroupId);
                button.className = 'mTab mTabDisabled';
                button.setAttribute('onclick', 'return false;');
                div = miolo.getElementById(tabId);
                div.style.display = 'none';

                if ( mtabbedbasegroup.getTabIndex(tabId, tabbedBaseGroupId) == currentTabIndex )
                {
                    mtabbedbasegroup.changeToClosestTab(currentTabIndex, tabbedBaseGroupId);
                }
                break;

            case 'removed':
                currentTabIndex = mtabbedbasegroup.getCurrentTabIndex(tabbedBaseGroupId);
                button.className = 'mTabRemoved';
                button.innerHTML = '';
                button.setAttribute('onclick', null);

                var tab = miolo.getElementById(tabId);

                if ( tab )
                {
                    tab.innerHTML = '';
                }

                if ( mtabbedbasegroup.getTabIndex(tabId, tabbedBaseGroupId) == currentTabIndex )
                {
                    mtabbedbasegroup.changeToClosestTab(currentTabIndex, tabbedBaseGroupId);
                }
                break;

            case 'active':
                button.className = 'mTab mTabActive';

                div = miolo.getElementById(tabId);
                oldDisplay = div.style.display;
                div.style.display = 'block';

                // Dojo animation happens only if it was hidden
                if ( oldDisplay == 'none' && this.doEffect && this.animate )
                {
                    dojo.fx.wipeOut( { node: tabId, duration: 0 } ).play();
                    dojo.fx.wipeIn( { node: tabId, duration: 500 } ).play();
                }
                break;

            case 'idle':
                button.className = 'mTab mTabIdle';

                div = miolo.getElementById(tabId);
                div.style.display = 'none';
                break;
        }
    },
    enableTab: function ( tabId, tabbedBaseGroupId )
    {
        mtabbedbasegroup.toggleTabState(tabId, tabbedBaseGroupId, 'enabled');
    },
    disableTab: function ( tabId, tabbedBaseGroupId )
    {
        mtabbedbasegroup.toggleTabState(tabId, tabbedBaseGroupId, 'disabled');
    },
    removeTab: function ( tabId, tabbedBaseGroupId  )
    {
        mtabbedbasegroup.toggleTabState(tabId, tabbedBaseGroupId, 'removed');
    },
    changeTab: function ( tabId , tabbedBaseGroupId)
    {
        if ( !this.tabIsSelectable(tabId) )
        {
            return;
        }
        
        document.mtabbedbasegroup_lastTab = tabId;
        var tabArray = eval(tabbedBaseGroupId+'Tabs');

        // Deactivates all tabs and activates the selected one
        for (i = 0; i < tabArray.length; i++)
        {
            var currentTab = tabArray[i];

            if ( tabId == currentTab )
            {
                mtabbedbasegroup.toggleTabState(currentTab, tabbedBaseGroupId, 'active');
            }
            else
            {
                if ( mtabbedbasegroup.tabIsSelectable(currentTab) )
                {
                    mtabbedbasegroup.toggleTabState(currentTab, tabbedBaseGroupId, 'idle');
                }
            }
        }

        return false;
    },
    changeToClosestTab: function ( index, tabbedBaseGroupId)
    {
        var tabs = eval(tabbedBaseGroupId + 'Tabs');
        var selectId = -1;

        // Checks selectable tabs on the left first
        for ( i = index - 1; i >= 0; i-- )
        {
            if ( mtabbedbasegroup.tabIsSelectable(tabs[i]) )
            {
                selectId = i;
                break;
            }
        }

        // If no selectable tab was found, checks on the right
        if ( selectId == -1 )
        {
            for ( i = index + 1; i < tabs.length; i++ )
            {
                if ( mtabbedbasegroup.tabIsSelectable(tabs[i]) )
                {
                    selectId = i;
                    break;
                }
            }
        }

        // Does NOT allow to have no tab selected
        if ( selectId == -1 )
        {
            selectId = index;
        }

        // Deactivates all tabs and activate the selected one
        for ( i = 0; i < tabs.length; i++ )
        {
            var currentTab = tabs[i];

            if ( selectId == i )
            {
                mtabbedbasegroup.toggleTabState(currentTab, tabbedBaseGroupId, 'active');
                document.mtabbedbasegroup_lastTab = currentTab;
            }
            else
            {
                if ( mtabbedbasegroup.tabIsSelectable(currentTab) )
                {
                    mtabbedbasegroup.toggleTabState(currentTab, tabbedBaseGroupId, 'idle');
                }
            }
        }

        return false;
    },
    firstTab: function ( tabbedBaseGroupId )
    {
        var tabArray = eval(tabbedBaseGroupId + 'Tabs');
        mtabbedbasegroup.changeTab(tabArray[0] , tabbedBaseGroupId);
    },
    getTabIndex: function ( tabId, tabbedBaseGroupId )
    {
        var index = false;
        var tabs = eval(tabbedBaseGroupId + 'Tabs');

        for ( i = 0; i < tabs.length; i++ )
        {
            if ( tabs[i] == tabId )
            {
                index = i;
                break;
            }
        }

        return index;
    },
    tabIsSelectable: function ( tabId )
    {
        var button = miolo.getElementById(tabId + 'Button');
        return ( !button || ( ( button.className != 'mTab mTabDisabled' ) && ( button.className != 'mTabRemoved' ) ) );
    },
    tabIsActive: function ( tabId )
    {
        var button = miolo.getElementById(tabId + 'Button');
        return ( !button || ( button.className == 'mTab mTabActive' ) );
    },
    getCurrentTabIndex: function (tabbedBaseGroupId)
    {
        var index = false;
        var tabs = eval(tabbedBaseGroupId + 'Tabs');

        for ( i = 0; i < tabs.length; i++ )
        {
            if ( mtabbedbasegroup.tabIsActive(tabs[i]) )
            {
                index = i;
                break;
            }
        }

        return index;
    },
    changeToLastTab: function (tabId, tabbedBaseGroupId)
    {
        if ( !tabId )
        {
            return;
        }
        mtabbedbasegroup.doEffect = false;

        if ( tabId in this.ajaxTabs )
        {
            miolo.getElementById(tabId + 'Button').onclick();
        }
        else
        {
            mtabbedbasegroup.changeTab(tabId, tabbedBaseGroupId);
        }

        mtabbedbasegroup.doEffect = true;
    }
});

mtabbedbasegroup = new MTabbedBaseGroup;
