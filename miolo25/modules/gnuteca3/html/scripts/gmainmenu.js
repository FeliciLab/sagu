/*	JSCookMenu v2.0.2 (c) Copyright 2002-2006 by Heng Yuan - Modifications to Gnuteca by Eduardo Bonfandini (eduardo@solis.coop.br) */

// Globals
var _cmIDCount = 0;                 // contador de id de submenus
var _cmTimeOut = null;				// controla o tempo que o menu irá permancer, variável de timer
var _cmCurrentItem = null;			// o menu atual que está sendo selecionado
var _cmNoAction = new Object();	    // indicate that the item cannot be hovered.
var _cmNoClick = new Object();		// similar to _cmNoAction but does not respond to mouseup/mousedown events
var _cmSplit = new Object();		// indicate that the item is a menu split
var _cmItemList = new Array();		// a simple list of items
var _cmClicked = false;				// for onClick

// produz um novo id unico
function cmNewID()
{
	return 'cmSubMenuID' + (++_cmIDCount);
}

// Retorna a string e propriedades para montar um item do menu
function cmActionItem( item, isMain, idSub)
{
	_cmItemList[_cmItemList.length] = item;
	var index = _cmItemList.length - 1;
	idSub = (!idSub) ? 'null' : ('\'' + idSub + '\'');

	var param = 'this,' + isMain + ',' + idSub + ',0,' + index;

    return ' onmouseover="cmItemMouseOverOpenSub(' + param + ')"';
}

//monta separador
function cmSplitItem()
{
    return eval( [_cmNoClick, '<td class="ThemeOffice2003MenuItemLeft"></td><td colspan="2"><div class="ThemeOffice2003MenuSplit"></div></td>'] );
}

// draw the sub menu recursively
function cmDrawSubMenu( subMenu, id, zIndexStart )
{
	var str = '<div class="ThemeOffice2003SubMenu" id="' + id + '" style="z-index: ' + zIndexStart + ';position: absolute; top: 0px; left: 0px;" onmouseout="_cmTimeOut = window.setTimeout (\'closeMainMenu()\', 1000 );	window.defaultStatus = \'\';" onmouseover="clearTimeout (_cmTimeOut);">';
	str += '<table summary="sub menu" id="' + id + 'Table" cellspacing="0" class="SubMenuTable">';

	var strSub = '';
	var item;
	var idSub;
	var hasChild;
	var i;
	var classStr;

    //passa por todos itens do submenu
	for ( i = 5; i < subMenu.length; ++i )
	{
		item = subMenu[i];

		if ( !item ) //caso não exista o item pula
        {
			continue;
        }

		if ( item == _cmSplit ) //separador
        {
			item = cmSplitItem();
        }

		item.parentItem = subMenu;

		hasChild = (item.length > 5);
		idSub = hasChild ? cmNewID () : null;

		str += '<tr class="ThemeOffice2003MenuItem"';

		if (item[0] != _cmNoClick)
        {
			str += cmActionItem( item, 0, idSub );
        }
		else
        {
            //no click, caso de separador e sem ação
            _cmItemList[_cmItemList.length] = item;
            var index = _cmItemList.length - 1;
            idSub = (!idSub) ? 'null' : ('\'' + idSub + '\'');

            var param = 'this,false,' + idSub + ',' + '0,' + index;

			str += ' onmouseover="cmItemMouseOver (' + param + ')"';
        }

		str += '>'

        //caso não tenha ação ou não tenha clique fecha o tr com label
		if (item[0] == _cmNoAction || item[0] == _cmNoClick)
		{
			str += item[1]+'</tr>';
			continue;
		}

		classStr += hasChild ? 'ThemeOffice2003MenuFolder' : 'ThemeOffice2003MenuItem';

		if ( item[0] != null )
        {
			myStr = item[0];
        }
		else
        {
			myStr += '<img alt="" src="images/spacer.gif">';
        }
        
        if ( item[2].length > 1 ) //Se tiver url
        {
            str += '<td class="' + classStr + 'Left">' +myStr + '</td>';

            // caso tiver javascript na frente, executado
            if ( item[2].substr(0,11) == 'javascript:' )
            {
                mLink = item[2] +'; closeMainMenu(); return false;';
            }
            else
            {
                mLink = 'gnuteca.doLink(\'' + item[2]  +'\', \'__mainForm\'); closeMainMenu(); return false';
            }

            str += '<td style="cursor:pointer;" class="' + classStr + 'Text" onclick="'+mLink+'"><a href="' + item[2]  + '">' + item[1] + '</a></td>';
            str += '<td style="cursor:pointer;" class="' + classStr + 'Text" onclick="'+mLink+'" class="' + classStr + 'Right">';
        }
        else
        {
            str += '<td class="' + classStr + 'Left">' +myStr + '</td>';
            str += '<td class="' + classStr + 'Text">' + item[1] + '</td>';
            str += '<td class="' + classStr + 'Right">';
        }

		if ( hasChild )
		{
			str += '<img alt="" src="images/arrow.gif">';
			strSub += cmDrawSubMenu( item, idSub, zIndexStart + 5);
		}
		else
        {
			str += '<img alt="" src="images/blank.gif">';
        }
        
		str += '</td></tr>';
	}

	str += '</table>';
	str += '</div>' + strSub;
	return str;
}

// A função monta o menu principal da aplicação
// menu	o objeto do menu a desenhar
function cmDraw( menu )
{
	var obj = dojo.byId( 'gMainMenu' );
      
	var str = '<table summary="main menu" class="ThemeOffice2003Menu" cellspacing="0"><tr>';
	var strSub = '';

	var i;
	var item;
	var idSub;
	var hasChild;

	var classStr;

    //passa por todos itens do menu montando seu html
	for ( i = 0; i < menu.length; ++i )
	{
		item = menu[i];

		if (!item)
        {
			continue;
        }

		item.menu = menu;
		item.subMenuID = 'gMainMenu';

		str += '<td class="ThemeOffice2003MainItem"';

		hasChild = (item.length > 5);
		idSub = hasChild ? cmNewID () : null;

		str += cmActionItem( item, 1, idSub) + '>';

		if (item == _cmSplit) //separador
        {
			item = cmSplitItem();
        }

		if (item[0] == _cmNoAction || item[0] == _cmNoClick)
		{
			str += item[1]+'</td>';
			continue;
		}

		classStr = 'ThemeOffice2003Main' + (hasChild ? 'Folder' : 'Item');

		str +=  '<span class="' + classStr + 'Left">';
		str += (item[0] == null) ? '&nbsp;': item[0];
		str += '</span>';
		str += '<span  class="' + classStr + 'Text">'+item[1]+'</span><span class="' + classStr + 'Right">&nbsp</span></td>';

        //todos os submenus são adicionados no final do html do menu principal
		if ( hasChild )
        {
			strSub += cmDrawSubMenu( item, idSub, 1000, 0 );
        }
	}

    str += '</tr></table>' + strSub

	obj.innerHTML = str;
}

// get the DOM object associated with the item
function cmGetMenuItem(item)
{
	if (!item.subMenuID)
    {
		return null;
    }

	var subMenu = dojo.byId(item.subMenuID);
	// we are dealing with a main menu item
	if (item.menu)
	{
		var menu = item.menu;
		// skip over table, tbody, tr, reach td
		subMenu = subMenu.firstChild.firstChild.firstChild.firstChild;
		var i;
		for (i = 0; i < menu.length; ++i)
		{
			if (menu[i] == item)
				return subMenu;
			subMenu = subMenu.nextSibling;
		}
	}
	else if (item.parentItem) // sub menu item
	{
		var menu = item.parentItem;
		var table = dojo.byId( item.subMenuID + 'Table' );
		if (!table)
			return null;
		// skip over table, tbody, reach tr
		subMenu = table.firstChild.firstChild;
		var i;
		for (i = 5; i < menu.length; ++i)
		{
			if (menu[i] == item)
				return subMenu;
			subMenu = subMenu.nextSibling;
		}
	}
	return null;
}

// action should be taken for mouse moving in to the menu item
// Here we just do things concerning this menu item, w/o opening sub menus.
function cmItemMouseOver(obj, isMain, idSub, menuID, index, calledByOpenSub)
{
	if ( ( !calledByOpenSub && _cmClicked ) )
	{
		cmItemMouseOverOpenSub(obj, isMain, idSub, 0, index);
		return;
	}

    if ( isMain )
	{
        cmItemMouseDownOpenSub(obj,1,'cmSubMenuID1',0,0);
		return;
	}

	clearTimeout (_cmTimeOut);

	if (!obj.cmMenuID)
	{
		obj.cmMenuID = 0;
		obj.cmIsMain = isMain;
	}

	var thisMenu = cmGetThisMenu(obj);

	// insert obj into cmItems if cmItems doesn't have obj
	if (!thisMenu.cmItems)
		thisMenu.cmItems = new Array ();
	var i;

	for (i = 0; i < thisMenu.cmItems.length; ++i)
	{
		if (thisMenu.cmItems[i] == obj)
			break;
	}

	if (i == thisMenu.cmItems.length)
	{
		//thisMenu.cmItems.push (obj);
		thisMenu.cmItems[i] = obj;
	}

	// hide the previous submenu that is not this branch
	if (_cmCurrentItem)
	{
		// occationally, we get this case when user
		// move the mouse slowly to the border
		if (_cmCurrentItem == obj || _cmCurrentItem == thisMenu)
		{
			var item = _cmItemList[index];
			return;
		}

		var thatMenu = cmGetThisMenu (_cmCurrentItem);

		if (thatMenu != thisMenu.cmParentMenu)
		{
			if (_cmCurrentItem.cmIsMain)
            {
				_cmCurrentItem.className = 'ThemeOffice2003MainItem';
            }
			else
            {
				_cmCurrentItem.className = 'ThemeOffice2003MenuItem';
            }
			if (thatMenu.id != idSub)
            {
				cmHideMenu( thatMenu, thisMenu ) ;
            }
		}
	}

	// okay, set the current menu to this obj
	_cmCurrentItem = obj;

	// just in case, reset all items in this menu to MenuItem
	cmResetMenu( thisMenu );
}

// ação deve acontecer quando o mouse é movido sobre o menu, também abre um submenu, caso exista
function cmItemMouseOverOpenSub(obj, isMain, idSub, menuID, index)
{
    //solução temporária para funcionar no google chrome
    if ( dojo.isChrome > 0 && ( document.activeElement.tagName == 'SELECT' ) )
    {
        return ;
    }

	clearTimeout (_cmTimeOut);
	cmItemMouseOver(obj, isMain, idSub, 0, index, true);

	if (idSub)
	{
		cmShowSubMenu( obj, isMain, dojo.byId( idSub ));
	}
        
        if ( index == 0 )
        {
            var interval = setInterval(function() { cmItemMouseDownOpenSub(obj, isMain, idSub, menuID, index); }, 500);
            setTimeout(function() {
                clearInterval(interval);
            }, 2000);
        }
}

// action should be taken for mouse button down at a menu item
// this is one also opens submenu if needed
function cmItemMouseDownOpenSub(obj, isMain, idSub, menuID, index)
{
	_cmClicked = true;

	if (idSub)
	{
		cmShowSubMenu(obj, isMain, dojo.byId( idSub ) );
	}
}

// move submenu to the appropriate location
function cmMoveSubMenu( obj, isMain, subMenu )
{
	var orient = 'hbr';

	var offsetAdjust = [0, 0];

	if (!isMain && orient.charAt (0) == 'h')
    {
		orient = 'v' + orient.charAt (1) + orient.charAt (2);
    }

	var mode = String (orient);
	var p = subMenu.offsetParent;
	var subMenuWidth = cmGetWidth (subMenu);
	var horiz = cmGetHorizontalAlign (obj, mode, p, subMenuWidth);

	if (mode.charAt (0) == 'h')
	{
		if (mode.charAt (1) == 'b')
			subMenu.style.top = (cmGetYAt (obj, p) + cmGetHeight (obj) + offsetAdjust[1]) + 'px';
		else
			subMenu.style.top = (cmGetYAt (obj, p) - cmGetHeight (subMenu) - offsetAdjust[1]) + 'px';
		if (horiz == 'r')
			subMenu.style.left = (cmGetXAt (obj, p) + offsetAdjust[0]) + 'px';
		else
			subMenu.style.left = (cmGetXAt (obj, p) + cmGetWidth (obj) - subMenuWidth - offsetAdjust[0]) + 'px';
	}
	else
	{
		if (horiz == 'r')
			subMenu.style.left = (cmGetXAt (obj, p) + cmGetWidth (obj) + offsetAdjust[0]) + 'px';
		else
			subMenu.style.left = (cmGetXAt (obj, p) - subMenuWidth - offsetAdjust[0]) + 'px';
		if (mode.charAt (1) == 'b')
			subMenu.style.top = (cmGetYAt (obj, p) + offsetAdjust[1]) + 'px';
		else
			subMenu.style.top = (cmGetYAt (obj, p) + cmGetHeight (obj) - cmGetHeight (subMenu) + offsetAdjust[1]) + 'px';
	}

	if (horiz != orient.charAt (2))
    {
		orient = orient.charAt (0) + orient.charAt (1) + horiz;
    }

	return orient;
}

// automatically re-adjust the menu position based on available screen size.
function cmGetHorizontalAlign (obj, mode, p, subMenuWidth)
{
	var horiz = mode.charAt (2);
	if (!(document.body))
		return horiz;
	var body = document.body;
	var browserLeft;
	var browserRight;
	if (window.innerWidth)
	{
		// DOM window attributes
		browserLeft = window.pageXOffset;
		browserRight = window.innerWidth + browserLeft;
	}
	else if (body.clientWidth)
	{
		// IE attributes
		browserLeft = body.clientLeft;
		browserRight = body.clientWidth + browserLeft;
	}
	else
		return horiz;
	if (mode.charAt (0) == 'h')
	{
		if (horiz == 'r' && (cmGetXAt (obj) + subMenuWidth) > browserRight)
			horiz = 'l';
		if (horiz == 'l' && (cmGetXAt (obj) + cmGetWidth (obj) - subMenuWidth) < browserLeft)
			horiz = 'r';
		return horiz;
	}
	else
	{
		if (horiz == 'r' && (cmGetXAt (obj, p) + cmGetWidth (obj) + subMenuWidth) > browserRight)
			horiz = 'l';
		if (horiz == 'l' && (cmGetXAt (obj, p) - subMenuWidth) < browserLeft)
			horiz = 'r';
		return horiz;
	}
}

// show the subMenu w/ specified orientation also move it to the correct coordinates
function cmShowSubMenu( obj, isMain, subMenu)
{
	if (!subMenu.cmParentMenu)
	{
		// establish the tree w/ back edge
		var thisMenu = cmGetThisMenu(obj );
		subMenu.cmParentMenu = thisMenu;

		if (!thisMenu.cmSubMenu)
        {
			thisMenu.cmSubMenu = new Array ();
        }

		thisMenu.cmSubMenu[thisMenu.cmSubMenu.length] = subMenu;
	}

    // position the sub menu only if we are not already showing the submenu
    var orient = cmMoveSubMenu( obj, isMain, subMenu );
    subMenu.cmOrient = orient;
    subMenu.style.visibility = 'visible';
}

// reset all the menu items to class MenuItem in thisMenu
function cmResetMenu( thisMenu )
{
	if (thisMenu.cmItems)
	{
		var i;
		var str;
		var items = thisMenu.cmItems;

		for (i = 0; i < items.length; ++i)
		{
			if (items[i].cmIsMain)
			{
				if (items[i].className == 'ThemeOffice2003MainItemDisabled')
					continue;
			}
			else
			{
				if (items[i].className == 'ThemeOffice2003MenuItemDisabled')
					continue;
			}
			if (items[i].cmIsMain)
            {
				str = 'ThemeOffice2003MainItem';
            }
			else
            {
				str = 'ThemeOffice2003MenuItem';
            }
			if (items[i].className != str)
            {
				items[i].className = str;
            }
		}
	}
}

// Chamada pelo timer para enconder o menu principal
function closeMainMenu()
{
	_cmClicked = false; //remove o clique

	if (_cmCurrentItem)
	{
		cmHideMenu( cmGetThisMenu(_cmCurrentItem ) );
		_cmCurrentItem = null; //remove o atual
	}
}

// esconde o menu especifico
function cmHideThisMenu( thisMenu )
{
    thisMenu.style.visibility = 'hidden';
    thisMenu.style.top = '0px';
    thisMenu.style.left = '0px';
    thisMenu.cmOrient = null;

	if ( thisMenu .cmOverlap )
	{
		var i;

		for (i = 0; i < thisMenu .cmOverlap.length; ++i)
        {
			thisMenu .cmOverlap[i].style.visibility = "";
        }
	}

	thisMenu.cmOverlap = null;
	thisMenu.cmItems = null;
}

// hide thisMenu, children of thisMenu, as well as the ancestor
// of thisMenu until currentMenu is encountered.  currentMenu will not be hidden
function cmHideMenu( thisMenu, currentMenu )
{
	var str = 'ThemeOffice2003SubMenu';

	// hide the down stream menus
	if (thisMenu.cmSubMenu)
	{
		var i;
		for (i = 0; i < thisMenu.cmSubMenu.length; ++i)
		{
			cmHideSubMenu( thisMenu.cmSubMenu[i] );
		}
	}

	// hide the upstream menus
	while (thisMenu && thisMenu != currentMenu)
	{
		cmResetMenu( thisMenu );

		if (thisMenu.className == str)
		{
			cmHideThisMenu( thisMenu );
		}
		else
        {
			break;
        }

		thisMenu = cmGetThisMenu( thisMenu.cmParentMenu );
	}
}

// hide thisMenu as well as its sub menus if thisMenu is not already hidden
function cmHideSubMenu( thisMenu )
{
	if (thisMenu.style.visibility == 'hidden')
    {
		return;
    }

	if (thisMenu.cmSubMenu)
	{
		var i;

		for (i = 0; i < thisMenu.cmSubMenu.length; ++i)
		{
			cmHideSubMenu(thisMenu.cmSubMenu[i]);
		}
	}

	cmResetMenu( thisMenu );
	cmHideThisMenu( thisMenu );
}

// returns the main menu or the submenu table where this obj (menu item) is in
function cmGetThisMenu( obj )
{
	while (obj)
	{
		if (obj.className == 'ThemeOffice2003SubMenu' || obj.className == 'ThemeOffice2003Menu' )
        {
			return obj;
        }

		obj = obj.parentNode;
	}

	return null;
}

// functions that obtain the width of an HTML element.
function cmGetWidth (obj)
{
	var width = obj.offsetWidth;
	if (width > 0 || !cmIsTRNode (obj))
		return width;
	if (!obj.firstChild)
		return 0;
	// use TABLE's length can cause an extra pixel gap
	//return obj.parentNode.parentNode.offsetWidth;

	// use the left and right child instead
	return obj.lastChild.offsetLeft - obj.firstChild.offsetLeft + cmGetWidth (obj.lastChild);
}

// functions that obtain the height of an HTML element.
function cmGetHeight (obj)
{
	var height = obj.offsetHeight;
	if (height > 0 || !cmIsTRNode (obj))
		return height;
	if (!obj.firstChild)
		return 0;
	// use the first child's height
	return obj.firstChild.offsetHeight;
}

// functions that obtain the coordinates of an HTML element
function cmGetX (obj)
{
	if (!obj)
		return 0;
	var x = 0;

	do
	{
		x += obj.offsetLeft;
		obj = obj.offsetParent;
	}
	while (obj);
	return x;
}

function cmGetXAt (obj, elm)
{
	var x = 0;

	while (obj && obj != elm)
	{
		x += obj.offsetLeft;
		obj = obj.offsetParent;
	}

	if (obj == elm)
    {
		return x;
    }

	return x - cmGetX (elm);
}

function cmGetY(obj)
{
	if (!obj)
		return 0;
	var y = 0;
	do
	{
		y += obj.offsetTop;
		obj = obj.offsetParent;
	}
	while (obj);
	return y;
}

function cmIsTRNode(obj)
{
	var tagName = obj.tagName;
	return tagName == "TR" || tagName == "tr" || tagName == "Tr" || tagName == "tR";
}

// get the Y position of the object.  In case of TR element though, we attempt to adjust the value.
function cmGetYAt (obj, elm)
{
	var y = 0;

	if (!obj.offsetHeight && cmIsTRNode (obj))
	{
		var firstTR = obj.parentNode.firstChild;
		obj = obj.firstChild;
		y -= firstTR.firstChild.offsetTop;
	}

	while (obj && obj != elm)
	{
		y += obj.offsetTop;
		obj = obj.offsetParent;
	}

	if (obj == elm)
    {
		return y;
    }

	return y - cmGetY (elm);
}