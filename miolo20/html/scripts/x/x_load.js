// xlib.js, X v3.15.2, Cross-Browser.com DHTML Library
// Copyright (c) 2004 Michael Foster, Licensed LGPL (gnu.org)
// global vars still duplicated in x_core.js - I still don't know what I'm going to do about this
var xVersion='3.15.2',xNN4,xOp7,xOp5or6,xIE4Up,xIE4,xIE5,xMac,xUA=navigator.userAgent.toLowerCase();
if (window.opera){
  xOp7=(xUA.indexOf('opera 7')!=-1 || xUA.indexOf('opera/7')!=-1);
  if (!xOp7) xOp5or6=(xUA.indexOf('opera 5')!=-1 || xUA.indexOf('opera/5')!=-1 || xUA.indexOf('opera 6')!=-1 || xUA.indexOf('opera/6')!=-1);
}
else if (document.all && xUA.indexOf('msie')!=-1) {
  xIE4Up=parseInt(navigator.appVersion)>=4;
  xIE4=xUA.indexOf('msie 4')!=-1;
  xIE5=xUA.indexOf('msie 5')!=-1;
}
else if (document.layers) {xNN4=true;}
xMac=xUA.indexOf('mac')!=-1;

if (!window.xIncludeList) {
  window.xIncludeList = new Array();
}  

// xInclude is very experimental!

function xInclude(url1, url2, etc)
{
  if (document.getElementById || document.all || document.layers) { // minimum dhtml support required
    var h, f, i, j, a, n, inc;

    for (var i=0; i<arguments.length; ++i) { // loop thru all the url arguments

      h = ''; // html (script or link element) to be written into the document
      f = arguments[i].toLowerCase(); // f is current url in lowercase
      inc = false; // if true the file has already been included

      // Extract the filename from the url

      // Should I extract the file name? What if there are two files with the same name 
      // but in different directories? If I don't extract it what about: '../foo.js' and '../../foo.js' ?

      a = f.split('/');
      n = a[a.length-1]; // the file name

      // loop thru the list to see if this file has already been included
      for (j = 0; j < xIncludeList.length; ++j) {
        if (n == xIncludeList[j]) { // should I use '==' or a string cmp func?
          inc = true;
          break;
        }
      }

      if (!inc) { // if the file has not yet been included

        xIncludeList[xIncludeList.length] = n; // add it to the list of included files

        // is it a .js file?
        if (f.indexOf('.js') != -1) {
          if (xNN4) { // if nn4 use nn4 versions of certain lib files
            var c='x_core', e='x_event', d='x_dom', n='_n4';
            if (f.indexOf(c) != -1) { f = f.replace(c, c+n); }
            else if (f.indexOf(e) != -1) { f = f.replace(e, e+n); }
            else if (f.indexOf(d) != -1) { f = f.replace(d, d+n); }
          }
          h = "<script type='text/javascript' src='" + f + "'></script>";
        }

        // else is it a .css file?
        else if (f.indexOf('.css') != -1) { // CSS file
          h = "<link rel='stylesheet' type='text/css' href='" + f + "'>";
        }    
        
        // write the link or script element into the document
        if (h.length) { document.writeln(h); }

      } // end if (!inc)
    } // end outer for
    return true;
  } // end if (min dhtml support)
  return false;
}
