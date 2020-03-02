// x_tpg.js, part of X, a Cross-Browser.com Javascript Library
// Copyright (C) 2001,2002,2003,2004,2005 Michael Foster - Distributed under the terms of the GNU LGPL - OSI Certified
// File Rev: 5

/* xTabPanelGroup(id, w, h, th, clsTP, clsTG, clsTD, clsTS)
     id - id string of tabPanelGroup element.
     w - overall width.
     h - overall height.
     th - tab height.
     clsTP - tabPanel css class
     clsTG - tabGroup css class
     clsTD - tabDefault css class
     clsTS - tabSelected css class

   Assumes tabPanelGroup element (overall container) has a 1px border.
*/

function xTabPanelGroup(id, w, h, th, clsTP, clsTG, clsTD, clsTS, nCurrPage) // object prototype
{
  var panelGrp = xGetElementById(id);
  if (!panelGrp) { return null; }
  var panels = xGetElementsByClassName(clsTP, panelGrp);
  var tabs = xGetElementsByClassName(clsTD, panelGrp);
  var tabGrp = xGetElementsByClassName(clsTG, panelGrp);
  if (!panels || !tabs || !tabGrp || panels.length != tabs.length || tabGrp.length != 1) { return null; }

  var selectedIndex = 0, highZ, x = 0, i;

  xResizeTo(panelGrp, w, h+10);
  xMoveTo(panelGrp, 0, 0);

  xResizeTo(tabGrp[0], w, th+3);
  xMoveTo(tabGrp[0], 0, 3);
  
  w -= 4; // remove border widths
  var tw = w / tabs.length;

  for (i = 0; i < tabs.length; ++i) {
    xResizeTo(tabs[i], tw, th); 
    xMoveTo(tabs[i], x, +3);
    x += tw;
    tabs[i].xTabIndex = i;
    tabs[i].onclick = tabOnClick;
    xResizeTo(panels[i], w, h - th - 2); // -2 removes border widths
    xMoveTo(panels[i], 0, th + 6);
  }
  highZ = i;
  tabs[nCurrPage].onclick();
  
  function tabOnClick()
  {
    tabs[selectedIndex].className = clsTD;
    xResizeTo(tabs[selectedIndex], tw, th); 
    xMoveTo(tabs[selectedIndex], (tw*selectedIndex), +3);
    this.className = clsTS;
    xZIndex(this, highZ++);
    xZIndex(panels[this.xTabIndex], highZ++);
    selectedIndex = this.xTabIndex;
    xResizeTo(tabs[selectedIndex], tw, th+3); 
    xMoveTo(tabs[selectedIndex], (tw*selectedIndex), 0);

	ele = MIOLO_GetElementById('frm_currpage_');
    ele.value = selectedIndex;
    // patch to handler "select tag" problem

    svn=xGetElementsByTagName("SELECT");
    for (a=0;a<svn.length;a++){
	    xHide(svn[a]);
    }
    svn=xGetElementsByTagName("SELECT",panels[selectedIndex]);
    for (a=0;a<svn.length;a++){
	   xShow(svn[a]);
    }

  }

  this.onUnload = function()
  {
    for (var i = 0; i < tabs.length; ++i) {tabs[i].onclick = null;}
  }
}

function _MIOLO_TabbedForm_GotoPage(frmName,pageName)
{
	ele = xGetElementById('frm_currpage_');
	if ( ele != null )
    {
        ele.value = pageName;
		form = xGetElementById(frmName);
		miolo_onSubmit();
		form.submit();
    }
    else
    {
        alert('MIOLO INTERNAL ERROR:\n\nForm ' + frmName + ' not found!');
    }
}
