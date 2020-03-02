/* tw_debug.js */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//                                                                                                                    //
//                                                                                                                    //
// V e r s i o n       1.0                                                                                            //
//                                                                                                                    //
//                                                                                                                    //
//                                                                                                                    //
// History                                                                                                            //
// -------                                                                                                            //
// 1.0  31-03-2003  First Release of "tw_debug.js"                                                                    //
//                                                                                                                    //
//                                                                                                                    //
//                                                                                                                    //
// Copyright                                                                                                          //
// ---------                                                                                                          //
// written by Luke @ trueworld (©2003 by Luke) =>  http:   http://www.trueworld.ch                                    //
//                                                         E-Mail: luke@trueworld.ch                                  //
//                                                                                                                    //
//                                                                                                                    //
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                                                                                                                         

// ---------------------------------------------------------------------------------------------------------------------
// browser detection
//
// tw_ie  = internet explorer (all versions)
// tw_ns  = netscape          (all versions)
// tw_dom = DOM
//
// ---------------------------------------------------------------------------------------------------------------------

var tw_dom=false,tw_ie=false,tw_ns=false;
if(document.getElementById)tw_dom=true;
if(document.all)tw_ie=true;
if(navigator.appName=='Netscape')tw_ns=true;

// ---------------------------------------------------------------------------------------------------------------------
// following constant may be used by other scripts to check whether this script has been loaded previously
// ---------------------------------------------------------------------------------------------------------------------

var tw_base    = true;

// ---------------------------------------------------------------------------------------------------------------------
// create constants
// ---------------------------------------------------------------------------------------------------------------------

var TW_TOP     = 'TW_TOP';
var TW_BOTTOM  = 'TW_BOTTOM';
var TW_REPLACE = 'TW_REPLACE';

// =====================================================================================================================
// tw_add_function
// =====================================================================================================================
//
// add function to objects, functions or events. if already another function is assigned/exists, add new function as
// call into existing function (either at the end or at the beginning) or replace existing function.
//
// when functions are added with parameters, these parameters are added to the parents function-parameters 
// (if existing). Please design functions that way, that existence of parameters is checked. Already existing calls
// of the parent function may not now that there are additional parameters to be handed over.
//
// syntax       : <object> = tw_add_function(<object>,<function>,[TW_BOTTOM|TW_TOP|TW_REPLACE]
//
// parameters   : - object                         <object>
//                - function                       <string> ; name + parameters -> e.g my_function(param1,param2)
//                - [TW_TOP|TW_BOTTOM|TW_REPLACE]  <const > ; default = TW_BOTTOM
//
// return-value : new function to be assigned to object (see syntax)

function tw_add_function (tw_af_object,tw_af_function,tw_af_position){
  
  // if position-parameter was not passed, set to TW_BOTTOM

  if(typeof(tw_af_position)=='undefined')tw_af_position=TW_BOTTOM;

  // if function has no parameter and now brackets "()", add the brackets

  if (tw_af_function.indexOf("(")==-1)tw_af_function+="()"

  // if there is no function yet, or position is set to TW_REPLACE, jump to else path

  if(typeof(tw_af_object)!='undefined' && tw_af_object!=null && tw_af_position!=TW_REPLACE){

    // create string out of existing function and create an array out of the parameters from the existing function and
    // concatenate the parameters from the new function

    tw_af_string=tw_af_object.toString();
    tw_af_params=new Array(tw_af_string.substring(tw_af_string.indexOf("(")+1,tw_af_string.indexOf("{")-2));
    tw_af_params=tw_af_params.concat(new Array(tw_af_function.substring(tw_af_function.indexOf("(")+1,
                                                                        tw_af_function.lastIndexOf(")"))));
    
    // if last array-element is empty (happens, when new function has no parameters), delete this element
    
    if (tw_af_params[tw_af_params.length-1]=="")tw_af_params.length--;
    
    // put call of new function into the existing function either at the top or at the bottom
    
    if (typeof(tw_af_position)!='undefined' && tw_af_position==TW_TOP)
      tw_af_object=new Function (tw_af_params,tw_af_function+";"+tw_af_string.substring(tw_af_string.indexOf("{")+1,
                                 tw_af_string.lastIndexOf("}")));
    else
      tw_af_object=new Function (tw_af_params,
                                 tw_af_string.substring(tw_af_string.indexOf("{")+1,tw_af_string.lastIndexOf("}"))+
                                 ";"+tw_af_function+";");
  }else{
    
    // create an array out of the parameters from the new function.
    // if last array-element is empty (happens, when new function has no parameters), delete this element

    tw_af_params=new Array(tw_af_function.substring(tw_af_function.indexOf("(")+1,tw_af_function.lastIndexOf(")")));
    if (tw_af_params[tw_af_params.length-1]=="")tw_af_params.length--;

    // create new function
  
    tw_af_object = new Function(tw_af_params.join(","),tw_af_function);  
  }

  // return new function

  return tw_af_object;    
}

// ---------------------------------------------------------------------------------------------------------------------
// check debug-options. if not set, define them here
// ---------------------------------------------------------------------------------------------------------------------
               
if (typeof(tw_debug_on)        =='undefined')tw_debug_on=false;
if (typeof(tw_debug_float)     =='undefined')tw_debug_float=true;
if (typeof(tw_debug_ascending) =='undefined')tw_debug_ascending=false;

// ---------------------------------------------------------------------------------------------------------------------
// establish event-handlers
// ---------------------------------------------------------------------------------------------------------------------
               
if (tw_debug_on){
  window.onunload=tw_add_function(window.onunload,'tw_debug_onunload(event)');
}

// ---------------------------------------------------------------------------------------------------------------------
// call the debug init-function to establish debug functionality
// ---------------------------------------------------------------------------------------------------------------------
               
if (tw_debug_on){
  tw_debug_init();
}

// ---------------------------------------------------------------------------------------------------------------------
// add debug-functions to main-window
// ---------------------------------------------------------------------------------------------------------------------

window.debug    =tw_add_function(window.debug,    'tw_debug(tw_string)    ');
window.debug_var=tw_add_function(window.debug_var,'tw_debug_var(tw_string)');

// =====================================================================================================================
// tw_debug_onunload
// =====================================================================================================================
//
// this function is called when window is unloaded. close debug-window and reposition/resize main window if necessary. 
//
// syntax       : tw_debug_onunload()
//
// parameters   : - tw_event                 <event>
//
// return-value : none

function tw_debug_onunload(tw_event){
   tw_debug_close();
}     

// =====================================================================================================================
// tw_debug_init
// =====================================================================================================================
//
// this function is initializes the debug functionality, means: creating debug-window, creating debug-functions
//
// syntax       : tw_debug_init()
//
// parameters   : none
//
// return-value : none

function tw_debug_init(){

  // open debug-window
  
  if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
    tw_debug_win.close();
  }

  tw_debug_win_height=screen.availHeight-31;
  tw_debug_win_left  =screen.availWidth-311;
  if (tw_ns){
    if (tw_dom){
      tw_debug_win_height+=2;
      tw_debug_win_left  +=3;
    }else{
      tw_debug_win_height+=3;
    }
  }
  if (navigator.userAgent.indexOf("Windows NT 5")!=-1){
    if (tw_ns && !tw_dom) tw_debug_win_height-=32;
    else tw_debug_win_height-=7;
  }

  tw_debug_win_properties ='height='+tw_debug_win_height;
  tw_debug_win_properties+=',width=300';
  tw_debug_win_properties+=',top=0';
  tw_debug_win_properties+=',left='+tw_debug_win_left;
  tw_debug_win_properties+=',dependent=yes';
  tw_debug_win_properties+=',scrollbars=no';
  tw_debug_win_properties+=',resizable=yes';
  tw_debug_win=window.open("about:blank","debug_window",tw_debug_win_properties)

  // create layout of debug-window

  with (tw_debug_win.document) {
    clear();
    writeln('<html><head><title>trueworld - debug console</title>');
    writeln('</head><body bgColor="#DDDDDD"><form method="get" action="about:_self" onSubmit="opener.tw_debug_eval();return false;">');
    writeln('<table cellspacing="0" cellpadding="0" border="0"><tr><td><p style="font: 8pt MS Sans Serif;font-weight:bold;">Debug-Log:</p></td><td><p style="font: 8pt MS Sans Serif;">&nbsp;&nbsp;');      
    if (tw_debug_ascending) writeln('(highest is oldest)');
    else                    writeln('(highest is newest)');
    writeln('</p></td></tr></table>');
    writeln('<table cellspacing="0" cellpadding="0" border="0" height="5px"><tr><td></td></tr></table>');      
    if (tw_ns && !tw_dom)writeln('<font face="MS Sans Serif" size="1"><textarea name="debug" cols="29" rows="15"></textarea></font>');
    else writeln('<textarea name="debug" cols="52" rows="17" style="font: 8pt MS Sans Serif;"></textarea>');      
    writeln('<br>');      
    writeln('<br>');      
    writeln('<table cellspacing="0" cellpadding="0" border="0"><tr><td><p style="font: 8pt MS Sans Serif;font-weight:bold;">Debug-Input:</p></td></tr></table>');      
    writeln('<table cellspacing="0" cellpadding="0" border="0" height="5px"><tr><td></td></tr></table>');      
    if (!tw_ns)writeln('<input name="console" type="text" size=54 style="font: 8pt MS Sans Serif;">');
    else if(tw_dom)writeln('<input name="console" type="text" size=55 style="font: 8pt MS Sans Serif;">');
    else writeln('<input name="console" type="text" size=30 style="font: 8pt MS Sans Serif;"><br>');
    writeln('<table cellspacing="0" cellpadding="0" border="0" height="5px"><tr><td></td></tr></table>');      
    writeln('<input type="submit" value="enter">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="clear" onClick="opener.tw_debug_clear_debug()">');
    writeln('<br>');      
    writeln('<br>');      
    writeln('<table cellspacing="0" cellpadding="0" border="0"><tr><td><p style="font: 8pt MS Sans Serif;font-weight:bold;">Variable Monitoring:</p></td></tr></table>');      
    writeln('<table cellspacing="0" cellpadding="0" border="0" height="5px"><tr><td></td></tr></table>');      
    if (tw_ns && !tw_dom)writeln('<font face="MS Sans Serif" size="1"><textarea name="monitor" cols="29" rows="15"></textarea></font>');
    else writeln('<textarea name="monitor" cols="52" rows="17" style="font: 8pt MS Sans Serif;"></textarea>');      
    writeln('<table cellspacing="0" cellpadding="0" border="0" height="5px"><tr><td></td></tr></table>');      
    writeln('<input type="button" value="start" onClick="opener.tw_debug_start()">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="stop" onClick="opener.tw_debug_stop()">&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="clear" onClick="opener.tw_debug_clear_monitor()">');
    writeln('<table cellspacing="0" cellpadding="0" border="0" height="5px"><tr><td></td></tr></table>');      
    writeln('<input type="button" value="close debug console" onClick="opener.tw_debug_close()">');
    writeln('</form></body></html>');
    close();
  }
  if (!tw_debug_float){
    self.moveTo(0,0);
    self.resizeTo(tw_debug_win_left,screen.availHeight);
  }
  tw_debug_win.focus();
}

// =====================================================================================================================
// tw_debug
// =====================================================================================================================
//
// this function writed new entries to the debug-log
//
// syntax       : tw_debug(<entry>)
//
// parameters   : - entry       <string>
//
// return-value : none

function tw_debug(tw_string) {
  if (tw_debug_on){
    if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
      if (tw_debug_ascending)tw_debug_win.document.forms[0].debug.value+=tw_string+"\n";
      else                   tw_debug_win.document.forms[0].debug.value=tw_string+"\n"+tw_debug_win.document.forms[0].debug.value;
    }
  }
}

// =====================================================================================================================
// tw_debug_var
// =====================================================================================================================
//
// this function adds new variables to the list of variables to monitor
//
// syntax       : tw_debug_var(<variable>)
//
// parameters   : variable      <string> which contains the variable name
//
// return-value : none

var tw_debug_var_array=new Array();
var tw_debug_var_int;
function tw_debug_var(tw_string) {
  if (tw_debug_on){
    clearInterval(tw_debug_var_int);
    if (tw_debug_var_array.join(",").indexOf(tw_string)==-1)tw_debug_var_array[tw_debug_var_array.length]=tw_string;
    tw_debug_var_int=setInterval("tw_debug_var_monitor()",100);
  }
}

// =====================================================================================================================
// tw_debug_clear_debug
// =====================================================================================================================
//
// this function clears the debug-log
//
// syntax       : tw_debug_clear_debug()
//
// parameters   : none
//
// return-value : none

function tw_debug_clear_debug() {
  if (tw_debug_on){
    if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
      tw_debug_win.document.forms[0].debug.value="";
    }
  }
}

// =====================================================================================================================
// tw_debug_clear_monitor
// =====================================================================================================================
//
// this function clears the monitor-field
//
// syntax       : tw_debug_clear_monitor()
//
// parameters   : none
//
// return-value : none

function tw_debug_clear_monitor() {
  if (tw_debug_on){
    if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
      tw_debug_win.document.forms[0].monitor.value="";
    }
  }
}

// =====================================================================================================================
// tw_debug_stop
// =====================================================================================================================
//
// this function stops the monitoring of variables
//
// syntax       : tw_debug_stop()
//
// parameters   : none
//
// return-value : none

function tw_debug_stop() {
  if (tw_debug_on)clearInterval(tw_debug_var_int);
  if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
  tw_debug_win.document.forms[0].monitor.value="==> currently not running\n\n"+tw_debug_win.document.forms[0].monitor.value;
  }
}

// =====================================================================================================================
// tw_debug_start
// =====================================================================================================================
//
// this function starts variable monitoring
//
// syntax       : tw_debug_start()
//
// parameters   : none
//
// return-value : none

function tw_debug_start() {
  if (tw_debug_on){
    tw_debug_var_int=setInterval("tw_debug_var_monitor()",100);
  }
}

// =====================================================================================================================
// tw_debug_var_monitor
// =====================================================================================================================
//
// this function updates the content of the monitor-field. Therefore it builds a string which contains for each
// variable a line with the variable-name and the variable-content. This string is then put into the monitor-field.
//
// syntax       : tw_debug_var_monitor()
//
// parameters   : none
//
// return-value : none

function tw_debug_var_monitor() {
  if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
    tw_string='';
    for (var tw_i=0;tw_i<tw_debug_var_array.length;tw_i++){
      if(typeof(tw_debug_var_array[tw_i])!='undefined')tw_string+=tw_debug_var_array[tw_i]+" => "+eval(tw_debug_var_array[tw_i])+'\n';
    }
    tw_debug_win.document.forms[0].monitor.value=tw_string;
  }
}

// =====================================================================================================================
// tw_debug_eval
// =====================================================================================================================
//
// this function echoes the user-input and calls the parse-function to evaluate and debug the input-value
//
// syntax       : tw_debug_eval()
//
// parameters   : none
//
// return-value : none

function tw_debug_eval(){
  debug("==> Console Entry: "+tw_debug_win.document.forms[0].console.value);
  if (tw_debug_win.document.forms[0].console.value.length>0){
      if (tw_debug_eval_parse()){
        if (typeof(eval(tw_debug_win.document.forms[0].console.value))!='undefined')debug(eval(tw_debug_win.document.forms[0].console.value));
        tw_debug_win.document.forms[0].console.value="";
      }
  }
  tw_debug_win.focus();
  
}

// =====================================================================================================================
// tw_debug_eval_parse
// =====================================================================================================================
//
// this function parses and evaluates the user-input and debugs the evaluated value.
//
// syntax       : tw_debug_eval_parse()
//
// parameters   : none
//
// return-value : - true    if user-input is valid
//                - false   if user-input is not valid

var tw_debug_dummy=true;

function tw_debug_eval_parse(){

  // create a working-variable from the user-input that does not contain any strings (parse-function is able
  // to return a variable that contains the user input with any strings replaced by a dummy-variable)

  tw_debug_str=tw_debug_eval_parse_doit(tw_debug_win.document.forms[0].console.value);
  
  // if the parse function identified wrong usage of ' or " (odd number of quotes), than process is stopped and
  // error message is displayed via debug-function
 
  if (tw_debug_str==false){
    debug("==> invalid usage of >'< or >\"<");
    return false;
  }else{
  
    // now replace any operaters, spaces etc. by commas
    //
    // at the end, the user-input is transformed into an array that only contains variables and no operaters or other
    // special-characters. This allows us to check all the variables before evaluating the user-input. This is necessary
    // because some browsers abort when evaluating user-input that contains undefined variables.

    tw_debug_str=tw_debug_str.replace(/\s/g,",");
    tw_debug_str=tw_debug_str.replace(/\+/g,",");
    tw_debug_str=tw_debug_str.replace(/\-/g,",");
    tw_debug_str=tw_debug_str.replace(/\=/g,",");
    tw_debug_str=tw_debug_str.replace(/\!/g,",");
    tw_debug_str=tw_debug_str.replace(/\&/g,",");
    tw_debug_str=tw_debug_str.replace(/\|/g,",");
    tw_debug_str=tw_debug_str.replace(/\*/g,",");
    tw_debug_str=tw_debug_str.replace(/\//g,",");
    tw_debug_str=tw_debug_str.replace(/\</g,",");
    tw_debug_str=tw_debug_str.replace(/\>/g,",");
    tw_debug_str=tw_debug_str.replace(/\(/g,",");
    tw_debug_str=tw_debug_str.replace(/\)/g,",");
    tw_debug_str=tw_debug_str.replace(/\d.+\B/,",");

    // create now the array from the transformed user input
    
    tw_debug_vars=tw_debug_str.split(",");

    // initialize some variables

    tw_debug_flag=true;
    tw_debug_pos=0;

    // now check all the variables from the user-input whether they are valid or not (define new function, that does a 
    // typeof-check of that variable/object - this is the only way to "evaluate" the object which is actually a 
    // string).
    // (the strings within the user-input have been replaced by a dummy-variable which has been defined under the
    // window-object.

    for (var tw_i=0;tw_i<tw_debug_vars.length;tw_i++){
      if (tw_debug_vars[tw_i].length>0){
        tw_debug_flag2=true;
        if (tw_debug_vars[tw_i].indexOf(".")!=-1){
          tw_debug_prevars=tw_debug_vars[tw_i].split(".");
          tw_j=0;
          tw_debug_prestr=tw_debug_prevars[tw_j];
          while (tw_debug_flag2 && tw_j<tw_debug_prevars.length-1){
            tw_debug_test_function=new Function("return typeof("+tw_debug_prestr+")");
            if (tw_debug_test_function()=='undefined'){
              tw_debug_flag2=false;
            }else{
              tw_j++;
              tw_debug_prestr+="."+tw_debug_prevars[tw_j];
            }
            delete tw_debug_test_function;
          }
        }
        if (tw_debug_flag2){
          tw_debug_test_function=new Function("return typeof("+tw_debug_vars[tw_i]+")");
          if (tw_debug_test_function()=='undefined'){
            tw_debug_flag=false;
            tw_debug_pos=tw_i;
            tw_i=tw_debug_vars.length;
          }
          delete tw_debug_test_function;
        }else{
          tw_debug_flag=false;
          tw_debug_pos=tw_i;
          tw_i=tw_debug_vars.length;
        }
      }
    }

    // if all the variables are defined, then evaluate the user-input. if user input called any function, then do 
    // not debug the value. If no function was called from within the user input, then debug the evaluated value.

    if (tw_debug_flag){
      return true;
    }else{

      // if undefined variabled have been detected, debug the variable within an error-message

      debug("==> invalid expression : "+tw_debug_vars[tw_debug_pos]);
      return false;
    }
  }
}

// =====================================================================================================================
// tw_debug_eval_parse_doit
// =====================================================================================================================
//
// this function is able to parse strings. this means, it looks for quotes (" or ') and replaces all strings by some
// dummy-variable. All possible combinations of quotes can be recogized, e.g.:  "test", 'test', "test's", "test\"s",
// etc.
// If invalid combination of quotes is recognized, FALSE is returned, otherways a string with all strings replaces by
// the dummy-variable "tw_debug_dummy" is returned.
//
// syntax       : tw_debug_eval_parse_doit(<string>)
//
// parameters   : - <string>    string to parse
//
// return-value : - false       if invalid combination of quotes is recognized
//                - <string>    string where all quoted strings are replaces by dummy-variable "tw_debug_dummy"

function tw_debug_eval_parse_doit(tw_debug_str){
  
  // locate first ' or "
  
  tw_debug_pos1=tw_debug_str.indexOf('"');
  tw_debug_pos2=tw_debug_str.indexOf("'");

  // identify which quote is first (' or ") and remember position

  if (((tw_debug_pos2<tw_debug_pos1)&&(tw_debug_pos2!=-1))||(tw_debug_pos1==-1)) tw_debug_pos1=tw_debug_pos2;

  // if some quote could be found, try to find the ending-quote, otherways return the unchanged string.

  if (tw_debug_pos1!=-1){
    
    // identify which quote was found first and create working-string out of original string, that contains only the
    // part after the first quote
    
    tw_debug_chr=tw_debug_str.substr(tw_debug_pos1,1);
    tw_debug_str2=tw_debug_str.substring(tw_debug_pos1+1,tw_debug_str.length);
    tw_debug_flag=true;

    // iterate until the ending quote could be found

    while (tw_debug_flag){
      
      // take the working-string and locate the next quote (same quote as frist one) and remember the position

      tw_debug_pos3=tw_debug_str2.indexOf(tw_debug_chr);

      // if the right quote has been found (and it has no esc-character in front), or no quote could be found:
      // end the iteration - otherways create new working-string, that only contains the part after this quote

      if ((tw_debug_pos3==-1)||(tw_debug_pos3==0)||(tw_debug_str2.substr(tw_debug_pos3-1,1).search(/\\/)==-1))tw_debug_flag=false;
      else tw_debug_str2=tw_debug_str2.substring(tw_debug_pos3+1,tw_debug_str2.length);
    }
    
    // if end-quote could not be found (remembered position = -1), then return false
    
    if (tw_debug_pos3==-1) return false;

    // if end-quote could be found, then replace found string (incl. quotes) by dummy-variable and call this function 
    // recursive to replace other strings...

    else return(tw_debug_eval_parse_doit(tw_debug_str.substr(0,tw_debug_pos1)+",tw_debug_dummy,"+tw_debug_str2.substring(tw_debug_pos3+1,tw_debug_str2.length)));
  }else{

    // no quote could be found so return unchanged string

    return tw_debug_str;
  }
}

// =====================================================================================================================
// tw_debug_close
// =====================================================================================================================
//
// this function closes the debug-window
//
// syntax       : tw_debug_close()
//
// parameters   : none
//
// return-value : none

function tw_debug_close(){
  if (typeof(tw_debug_win)!='undefined' && tw_debug_win.closed==false){
    clearInterval(tw_debug_var_int);
    tw_debug_win.close();
    if (!tw_debug_float){
      self.moveTo(0,0);      
      self.resizeTo(screen.availWidth,screen.availHeight);
    }
  }
}