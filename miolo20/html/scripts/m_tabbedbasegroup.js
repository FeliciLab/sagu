var mtabbedbasegroup =
{
    toggleTabState: function ( tabId, tabbedBaseGroupId, state )
    {
        var button = MIOLO_GetElementById(tabId + 'Button');
        if ( !button )
        {
            return;
        }

        switch ( state )
        {
            case 'enabled':
                button.className = 'm-tab m-tab-idle';
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
                currentTabIndex = this.getCurrentTabIndex(tabbedBaseGroupId);
                button.className = 'm-tab m-tab-disabled';
                button.setAttribute('onclick', 'return false;');
                div = MIOLO_GetElementById('m_'+tabId);
                div.style.display = 'none';

                if ( this.getTabIndex(tabId, tabbedBaseGroupId) == currentTabIndex )
                {
                    this.changeToClosestTab(currentTabIndex, tabbedBaseGroupId);
                }
                break;

            case 'removed':
                currentTabIndex = this.getCurrentTabIndex(tabbedBaseGroupId);
                button.className = 'm-tab-removed';
                button.innerHTML = '';
                button.setAttribute('onclick', null);

                var tab = MIOLO_GetElementById(tabId);

                if ( tab )
                {
                    tab.innerHTML = '';
                }

                if ( this.getTabIndex(tabId, tabbedBaseGroupId) == currentTabIndex )
                {
                    this.changeToClosestTab(currentTabIndex, tabbedBaseGroupId);
                }
                break;

            case 'active':
                button.className = 'm-tab m-tab-active';

                div = MIOLO_GetElementById('m_'+tabId);
                oldDisplay = div.style.display;
                div.style.display = 'block';

                break;

            case 'idle':
                button.className = 'm-tab m-tab-idle';

                div = MIOLO_GetElementById('m_'+tabId);
                div.style.display = 'none';
                break;
        }
    },
    enableTab: function ( tabId, tabbedBaseGroupId )
    {
        this.toggleTabState(tabId, tabbedBaseGroupId, 'enabled');
    },
    disableTab: function ( tabId, tabbedBaseGroupId )
    {
        this.toggleTabState(tabId, tabbedBaseGroupId, 'disabled');
    },
    removeTab: function ( tabId, tabbedBaseGroupId  )
    {
        this.toggleTabState(tabId, tabbedBaseGroupId, 'removed');
    },
    changeTab: function ( tabId , tabbedBaseGroupId)
    {
        var tabArray = eval(tabbedBaseGroupId+'Tabs');

        // Deactivates all tabs and activates the selected one
        for (i = 0; i < tabArray.length; i++)
        {
            var currentTab = tabArray[i];

            if ( tabId == currentTab )
            {
                this.toggleTabState(currentTab, tabbedBaseGroupId, 'active');
            }
            else
            {
                if ( this.tabIsSelectable(currentTab) )
                {
                    this.toggleTabState(currentTab, tabbedBaseGroupId, 'idle');
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
            if ( this.tabIsSelectable(tabs[i]) )
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
                if ( this.tabIsSelectable(tabs[i]) )
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
                this.toggleTabState(currentTab, tabbedBaseGroupId, 'active');
            }
            else
            {
                if ( this.tabIsSelectable(currentTab) )
                {
                    this.toggleTabState(currentTab, tabbedBaseGroupId, 'idle');
                }
            }
        }

        return false;
    },
    firstTab: function ( tabbedBaseGroupId )
    {
        var tabArray = eval(tabbedBaseGroupId + 'Tabs');
        this.changeTab(tabArray[0] , tabbedBaseGroupId);
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
        var button = MIOLO_GetElementById(tabId + 'Button');
        return ( !button || ( ( button.className != 'm-tab m-tab-disabled' ) && ( button.className != 'm-tab-removed' ) ) );
    },
    tabIsActive: function ( tabId )
    {
        var button = MIOLO_GetElementById(tabId + 'Button');
        return ( !button || ( button.className == 'm-tab m-tab-active' ) );
    },
    getCurrentTabIndex: function (tabbedBaseGroupId)
    {
        var index = false;
        var tabs = eval(tabbedBaseGroupId + 'Tabs');

        for ( i = 0; i < tabs.length; i++ )
        {
            if ( this.tabIsActive(tabs[i]) )
            {
                index = i;
                break;
            }
        }

        return index;
    }
};
