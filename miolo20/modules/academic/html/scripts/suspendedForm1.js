/*  
 * Suppose to have a file description here. Maybe later
 */
 var is_ie = ( /msie/i.test(navigator.userAgent) &&
		   !/opera/i.test(navigator.userAgent) );

 function createSuspendedForm (  e , buttonAction)
 {
    //var parent = document.getElementsByTagName("body")[0];
    
    var div;
    
    div = document.getElementById('suspendedForm');
    
    if ( div == null )
    {
        div = document.createElement("div");
        div.id = 'suspendedForm';
    
        div.className = "suspendedForm";
    }
    //div.style.position = "absolute";
    //div.style.display = "none";
    

    var textField = document.createElement("input");
    textField.setAttribute("type", "TEXT");
    
    var submitButton = document.createElement("input");
    submitButton.setAttribute("type", "SUBMIT");
    submitButton.setAttribute("value", "Continue");
   
/*    var form = document.createElement('form');
    form.action = document.forms[0].action;    
    form.appendChild(textField);
    form.appendChild(submitButton);
    div.appendChild(form);*/

    div.innerHTML = '<form action="' + buttonAction + '" method="POST" >' +
                    '<table align="left" border=0>' +
                    '<tr><th align="left">' + 
                    '<font face="arial" size=2>Dias Letivos:</font>' + 
                    '</th><th><input type="text" id = "textFieldParam1" name="textFieldParam1"></th></tr>' +
                    '<tr><th align="left">' + 
                    '<font face="arial" size=2>Data de apuração<br>final dos rendimentos:</font>' + 
                    '</th><th><input type="text" name="textFieldParam2"></th></tr>' +
                    '<tr><th colspan="2"><input type="submit" value="Continuar"></th></tr></table><br>';
    
    //div.style.visibility = "visible";
    //div.style.display    = "block";
    div.style.position   = 'absolute';
    
    var pos = getMousePos( e );
    div.style.top        = pos[1]+'px';
    div.style.left       = (pos[0]+40)+'px';
    div.style.zIndec     = 10;
        
    document.body.appendChild(div);
    focusTextField = document.getElementById("textFieldParam1");
    //focusTextField.focus();
    //alert(buttonAction);
 }

function getMousePos( ev )
{
    var posX;
	var posY;
	if ( is_ie ) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posY = ev.clientY + window.scrollY;
		posX = ev.clientX + window.scrollX;
	}
	
	return new Array( posX, posY);
}
