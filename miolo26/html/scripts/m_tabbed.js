// xTabPanelGroup, Copyright 2005 Michael Foster (Cross-Browser.com)
// Part of X, a Cross-Browser Javascript Library, Distributed under the terms of the GNU LGPL

/* xTabPanelGroup(id, w, h, th, clsTP, clsTG, clsTD, clsTS)
     id - id string of tabPanelGroup element.
     w - overall width.
     h - overall height.
     th - tab height.
     clsTP - tabPanel css class
     clsTG - tabGroup css class
     clsTD - tabDefault css class
     clsTS - tabSelected css class
	 nCurrPage - current Page

   Assumes tabPanelGroup element (overall container) has a 1px border.
*/

function xTabPanelGroup(id, w, h, th, clsTP, clsTG, clsTD, clsTS, nCurrPage) // object prototype
{
  // Private Methods

  function onClick() //r7
  {
    paint(this);
    return false;
  }
  function onFocus() //r7
  {
    paint(this);
  }
  function paint(tab)
  {
    tab.className = clsTS;
    xZIndex(tab, highZ++);
    xDisplay(panels[tab.xTabIndex], 'block'); //r6
  
    if (selectedIndex != tab.xTabIndex) {
      xDisplay(panels[selectedIndex], 'none'); //r6
      tabs[selectedIndex].className = clsTD;
  
      selectedIndex = tab.xTabIndex;

      ele = MIOLO_GetElementById('frm_currpage_');
      ele.value = selectedIndex;
    }
  }

/*
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
*/

  // Public Methods

  this.select = function(n) //r7
  {
    if (n && n <= tabs.length) {
      var t = tabs[n-1];
      if (t.focus) t.focus();
      else t.onclick();
    }
  }

  this.onUnload = function()
  {
    if (xIE4Up) for (var i = 0; i < tabs.length; ++i) {tabs[i].onclick = null;}
  }

  // Constructor Code (note that all these vars are 'private')

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
  xMoveTo(tabGrp[0], 0, 0);
  w -= 4; // remove border widths
  var tw = w / tabs.length;
  for (i = 0; i < tabs.length; ++i) {
    xResizeTo(tabs[i], tw - 4, th); 
    xMoveTo(tabs[i], x, +3);
    x += tw;
    tabs[i].xTabIndex = i;
    tabs[i].onclick = onClick;
    tabs[i].onfocus = onFocus; //r7
    xDisplay(panels[i], 'none'); //r6
    xResizeTo(panels[i], w, h - th - 2); // -2 removes border widths
    xMoveTo(panels[i], 0, th+3);
  }
  highZ = i;
  tabs[nCurrPage].onclick();
}

function MIOLO_TabbedForm_GotoPage(pageNumber)
{
	ele = miolo.getElementById('frm_currpage_');
	if ( ele != null )
    {
        ele.value = pageNumber;
		miolo.submit();
        miolo.webForm.submit();
    }
    else
    {
        alert('MIOLO INTERNAL ERROR:\n\nForm ' + frmName + ' not found!');
    }
}

function _MIOLO_TabbedForm_GotoPage(pageName, pageNumber)
{
	ele = miolo.getElementById('frm_currpage_');
	if ( ele != null )
    {
        ele.value = pageNumber;
		miolo.submit();
        miolo.webForm.submit();
    }
    else
    {
        alert('MIOLO INTERNAL ERROR:\n\nForm ' + frmName + ' not found!');
    }
}