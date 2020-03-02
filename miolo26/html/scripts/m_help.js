var ie = document.all;
var nn6 = document.getElementById &&! document.all;

var isdrag = false;
var x, y;
var dobj;


function createRequestObject() 
{
    var ro;
    var browser = navigator.appName;

    if(browser == "Microsoft Internet Explorer")
    {
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }
    else
    {
        ro = new XMLHttpRequest();
    }
    return ro;
}

var http = createRequestObject();

function requestHelp(action)
{
    //updateImageStatus('block');

    http.open('get', action);
    http.onreadystatechange = checkResponse;
    http.send(null);

    return http.responseText;
}

var varDoc   = '';

function checkResponse()
{

    if( http.readyState == 4 )
    {
        var response = http.responseText;
        var update = new Array();

        processResult(response);
    }
}

var className  = '';
var moduleName = '';
var actionName = '';

function MIOLO_getHelp( class, module, actionN)
{
    className  = class;
    moduleName = module;
    actionName = actionN;

    var url = '/help.php?module=' + moduleName +
              '&action=' + actionName +
              '&class=' +className;

    requestHelp(url);
}


//var mousePosition  = getMousePosition(eventObj)
//var newXCoordinate = mousePosition[0]-125;
//var newYCoordinate = mousePosition[1]-56;

function showStructDiv()
{
    var divId = document.getElementById('xmlStruct')

    if ( divId != null )
    {
        divId.style.display = (divId.style.display == 'none') ? 'block':'none';
    }
}

function processResult(result)
{

    newDiv = document.createElement('div');
    newDiv.id = 'help_window';
    newDiv.name = 'help_window';

    //newDiv.style.backgroundColor = 'transparent';
    //newDiv.style.border  = '0';
    //newDiv.style.display = 'none';
    //newDiv.style.height  = '50%';
    //newDiv.style.width = '58%';
    //newDiv.style.padding  = '0';
    //newDiv.style.position = 'absolute';
    //newDiv.style.left = '50px';
    //newDiv.style.top  = '50px';

    var rs = result.split('|');

    // No help found?
    if ( rs[0] == 'NONE' )
    {
        var actionName1 = actionName.replace(/:/g, "_");

        result = '<div onClick="showStructDiv();">No help found for class:' + className + "</div>\n<br\>"+
                 '<div id="xmlStruct" style="display: none">File: ' + rs[1] + "<br\>";

        var formFields = new Array();
        var j = 0;

        result = result + '<pre>' +
                 '&lt;?xml version="1.0" encoding="ISO-8859-1"?&gt; <br>' +
                 '&lt;help><br>' +
                 '  &lt;action>'+actionName+'&lt;/action&gt;<br>' +
                 '  &lt;name>'+className+'&lt;/name&gt;<br>' +
                 '  &lt;description> ???? &lt;/description&gt;<br>' +
                 '  &lt;attributes&gt;';

        for ( i = 0; i < document.forms[0].elements.length-1; i++)
        {
            fieldName = document.forms[0].elements[i].name;

            try
            {
                if ( fieldName.substring(0,2) != '__' )
                {
                        formFields[j++] = fieldName;
                        result = result +
                                '<br/>    &lt;attribute&gt;<br>' +
                                '      &lt;name&gt;'+fieldName+'&lt;/name&gt;<br>' +
                                '      &lt;label&gt;'+fieldName+'&lt;/label&gt;<br>' +
                                '      &lt;type&gt;varchar(10)&lt;/type&gt;<br>' +
                                '      &lt;description&gt; ???? &lt;/description&gt<br>' +
                                '    &lt;/attribute&gt;';
                }
            }
            catch(err)
            {}
        }

        result = result +
                 '<br/>  &lt;/attributes&gt;<br/>' +
                 '  &lt;image&gt;&lt;/image&gt;<br/>' +
                 '&lt;/help&gt;<br> </pre></div>';
    }

    content = " <div id='header' onmousedown='selectmouse(event);'>" +
              "  <div id='left_header'><img src='images/help_top_left.png'></div>" +
              "  <div id='right_header'><a href='javascript:MIOLO_helpWindowClose();'><img src='images/help_close.png' border='0'></a></div>" +
              "  <div id='center_header'>Help On-Line</div>" +
              " </div>"+
              " <div id='content'>" + result + "</div>";


    newDiv.innerHTML = content;
//alert(result);
    document.getElementById('m-container').appendChild(newDiv);
}


function movemouse( e ) {
  if( isdrag ) {
    dobj.style.left = nn6 ? tx + e.clientX - x : tx + event.clientX - x;
    dobj.style.top  = nn6 ? ty + e.clientY - y : ty + event.clientY - y;
    return false;
  }
}

function selectmouse( e ) {
  var fobj       = nn6 ? e.target : event.srcElement;
  var topelement = nn6 ? "HTML" : "BODY";

  if (fobj.id=="header") {
    isdrag = true;
    dobj = document.getElementById("help_window");
    //alert(dobj.id);
    tx = parseInt(dobj.style.left+0);
    ty = parseInt(dobj.style.top+0);
    x = nn6 ? e.clientX : event.clientX;
    y = nn6 ? e.clientY : event.clientY;
    document.onmousemove=movemouse;
    return false;
  }
}

function MIOLO_helpWindowClose() {
  document.getElementById("help_window").style.display = "none";
}

document.onmouseup=new Function("isdrag=false");
