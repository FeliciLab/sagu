<?php
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# @title
#   Autocompletion
#
# @description
#   Handler for autocompleting form field values.
#
# @see      miolo/ui/form.class,
#           miolo/common.js
#
# @topics   form, ui
#
# @created
#   2001/08/14
#
# @organisation
#   MIOLO - Miolo Development Team - UNIVATES Centro Universitario
#
# @legal
#   CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil
#   Licensed under GPL (see COPYING.TXT or FSF at www.fsf.org for
#   further details)
#
# @contributors
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
# 
# @maintainers
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#
# @history
#   See CVS history
#
# @id $id: autocomplete.php,v 1.5.2.1 2004/10/14 22:34:00 vgartner Exp $
#---------------------------------------------------------------------

$MIOLO->history->pop();

$module  = MIOLO::_Request('lmodule');
$item    = MIOLO::_Request('item');
$related = MIOLO::_Request('related');
$form    = MIOLO::_Request('form');
$field   = MIOLO::_Request('field');
$value   = MIOLO::_Request('value');

$MIOLO = MIOLO::getInstance();
$MIOLO->conf->loadConf($module);
$autocomplete = new MAutoComplete($module,$item,$value,$defaults);

$page->addScript('m_lookup.js');

$info = $autocomplete->getResult();
if(is_array($info))
{
    $inf = 'var info = new Array();';
    foreach($info as $n=>$i)
    {
        $inf .= "\ninfo[$n] = '".addslashes($i)."';";
    }
    $info = implode(',',$info);
}
else
{
    $inf = 'var info = \''.addslashes($info).'\';';
}

header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");                          // HTTP/1.0

$content =  "<html>\n";
$content .=  "<body>\n";
$content .=  "        Module=$module; Item=$item; Value=$value; Info=" . $info . "<br>\n";
$content .=  "<script language=\"JavaScript\">;\n";
$content .=  "doc  = top.frames['content'] ? top.frames['content'].document : top.document;";
$content .=  "form = doc.getElementsByName('$form')[0]; ";
$content .=  " var debugMessage=''; ";
$msg = "[autocomplete]: Field @1 not found!<br />";

    //$content .=  "\nif(form['{$r}_sel'])\n\tform['{$r}_sel'].value = '{$info[$i]}';";
    //$content .=  "\nif(form['$r'])\n\tform['$r'].value = '{$info[$i++]}';";
if(MUtil::getBooleanValue($MIOLO->getConf('options.debug')) && $MIOLO->getConf('logs.handler') == 'screen')
{
    $content .=  " \ndebugMessage = '"._M($msg,$module)."';";
}
$content .=  $inf;
$content .=  "MIOLO_AutoCompleteDeliver(doc, form, debugMessage, '$related', info);";
$content .=  "</script>\n";
$content .=  "</body>\n";
$content .=  "</html>";
$page->generate();
echo $content;
exit;

?>

