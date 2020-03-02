<?php

class MHtmlPainter extends MBasePainter
{
    public $BR = "<br>";
    private $control;

    private function validateControl()
    {
        try
        {
            if ( ! is_object($this->control) )
            {
                throw new Exception;
            }
        }
        catch ( Exception $e )
        {
            echo $e->getTraceAsString();
        }
    }

    private function getAttribute( $name, $value )
    {
        return " $name=\"$value\"" ;
    }

    private function getAttributeValue( $name, $value )
    {
        return $value ? " $name=\"$value\"" : "";
    }

    private function getId()
    {
        $this->validateControl();        
        return  ($id = $this->control->getId()) ? $this->getAttribute( 'id', $id ) : '';
    }


    private function getClass()
    {
        return ($class = $this->control->getClass()) ? $this->getAttribute( 'class', $class ) : '';
    }


    private function getName()
    {
        return $this->getAttribute( 'name', $this->control->getName() );
    }

    private function escapeValue($value)
    {
        if ( is_array($value) )
        {
            foreach ($value as $i=>$v)
            {
                $value[$i] = $this->escapeValue($v);
            }
        }
        else
        {
            $value = str_replace('\"','&quot;',$value);
            $value = str_replace('"','&quot;',$value);
        }
        return $value;
    }

    private function getValue()
    {
        return $this->getAttribute( 'value', $this->escapeValue($this->control->getValue()) );
    }

    public function div( $control )
    {
        $this->control = $control;
        $this->validateControl(); 

        return "\n<div" . $this->getId() . $this->getClass() . $control->getAttributes() . ">" . $control->getInnerToString() . "</div>";
    }


    public function span( $control )
    {
        $this->control = $control;

        return "<span" . $this->getId() . $this->getClass() . $control->getAttributes() . ">" . $control->getInnerToString() . "</span>";
    }


    public function text( $control )
    {
        $this->control = $control;

        return "\n<span" . $this->getId() . $this->getClass() . $control->getAttributes() . ">" . $control->value . "</span>";
    }


    public function inputHidden( $control )
    {
        $this->control = $control;

        return "\n<input type=\"hidden\"" . $this->getId() . $this->getName() . $this->getValue() . ">";
    }


    public function inputButton( $control )
    {
        $this->control = $control;

        return "\n<input type=\"button\"" . $this->getId() . $this->getClass() . $this->getName() . $this->getValue() .  $this->getAttributeValue('onclick',$control->onclick) . $control->getAttributes() . ">";
    }


    public function inputText( $control )
    {
        $this->control = $control;

        return "\n<input " . $this->getAttribute('type', $control->type) . $this->getId() . $this->getClass() . $this->getName() . $this->getValue() .  $this->getAttribute('size',$control->size) . $control->getAttributes() . ">";
    }


    public function inputCheck( $control )
    {
        $this->control = $control;

        return "\n<div " . $this->getClass() . "><input " . $this->getAttribute('type',$control->type) . $this->getId() . $this->getName() . $this->getValue() . (($control->checked != '') ? " checked " : "") . $control->getAttributes() . ">". ($control->text != '' ? "<label " . $this->getAttribute('for',$control->id)  .  ">" . $control->text . "</label>" : ''). '</div>';
    }


    public function inputTextArea( $control )
    {
        $this->control = $control;

        return "\n<textarea " . $this->getId() . $this->getClass() . $this->getName() . $this->getAttribute('rows',$control->rows) . $this->getAttribute('cols',$control->cols) . $control->getAttributes() . ">" . $control->getValue() . "</textarea>";
    }


    public function button( $control )
    {
        $this->control = $control;
        $html  = "\n<button " . $this->getId() . $this->getAttribute('type',$control->type) . $this->getClass() . $this->getName() . $this->getValue() . $control->getAttributes() . ">";
        $image = (($control->image != '') ? "<img src=\"{$control->image}\" alt=\"\">&nbsp;&nbsp;" : "");
        $text  = (($control->text != '') ? "{$control->text}" : (($control->label != '') ? "{$control->label}" : "{$control->value}"));
        $html .= $image . $text;
        $html .= "</button>";

        return $html;
    }


    public function label( $control )
    {
        $this->control = $control;

        return "\n<label " . $this->getClass() . $this->getAttribute('for',$control->getId()) . $control->getAttributes() . ">" . $control->getValue() . "</label>";
    }


    public function fieldSet( $control )
    {
        $this->control = $control;
        $legend = (($control->caption != '') ? "<legend>{$control->caption}</legend>" : "");

        return "\n<fieldset " . $this->getId() . $this->getClass() . $control->getAttributes() . ">" . $legend . $control->getInnerToString() . "</fieldset>";
    }


    public function select( $control )
    {
        $this->control = $control;

        return "\n<select " . $this->getId() . $this->getClass() . $this->getName() . $control->getAttributes() . ">" . $this->generateToString($control->content) . "</select>";
    }


    public function optionGroup( $control )
    {
        $this->control = $control;

        return "\n<optgroup " . $this->getId() . $this->getClass() . $this->getAttribute('label',$control->label) .  ">" . $this->generateToString($control->content) . "</optgroup>";
    }


    public function option( $control )
    {
        $this->control = $control;
        $label = ( $control->showValues ? $control->value . ' - ' : '') . $control->label; 

        return "\n<option" . $this->getValue() . ( ($control->checked != '') ? " selected " : "" ) .  ">$label</option>";
    }


    public function anchor( $control )
    {
        $this->control = $control;

        return "\n<a " . $this->getId() . $this->getClass() . $this->getAttribute('href',$control->href) . $control->getAttributes() . ">" . $control->caption . "</a>";
    }


    public function comment( $control )
    {
        $this->control = $control;

        return "\n<!-- {$control->value} -->\n";
    }


    public function header( $control )
    {
        $this->control = $control;

        return "\n<h" . $control->level . $this->getClass() . $control->getAttributes() . ">" . $control->value . "</h" . $control->level . ">";
    }


    public function image( $control )
    {
        $this->control = $control;

        return "\n<img" . $this->getAttribute('src',$control->location) . $this->getId() . $this->getClass() . $this->getAttribute('alt',$control->label) . $control->getAttributes() . ">";
    }


    public function table( $control )
    {
        $this->control = $control;
        $body = $control->body;
        $html  = "\n<table " .  $this->getId() . $control->getAttributes() . $this->getClass() . ">";

        if ($control->caption != '')
        {
            $html .= "\n<caption> " .  $control->caption . "</caption>";
        }
        if ($n = count($control->colgroup))
        {
            for($i=0; $i<$n; $i++)
            {
                $html .= "\n<colgroup " . $control->colgroup[$i]['attr'] . ">" ;
                $k = count($control->colgroup[$i]['col']);
                for($j=0; $j<$k; $j++)
                {
                    $html .= "<col " . $control->colgroup[$i]['col'][$j] . ">";
                }
                $html .= "\n</colgroup>";
            } 
        }
        if ($n = count($control->head))
        {
            $html .= "\n<thead><tr>";
            for($i=0; $i<$n; $i++)
            {
                $html .= "\n<th " . $control->attr['head'][$i] . ">" . $control->head[$i] . "</th>";
            } 
            $html .= "\n</tr></thead>";
        }
        if ($n = count($control->foot))
        {
            $html .= "\n<tfoot><tr>";
            for($i=0; $i<$n; $i++)
            {
                $html .= "\n<td " . $control->attr['foot'][$i] . ">" . $control->foot[$i] . "</td>";
            } 
            $html .= "\n</tr></tfoot>";
        }
        $html .= "\n<tbody>";
        $n = count($body);
        for($i=0; $i<$n; $i++)
        {
           $html .= "\n<tr " . $control->attr['row'][$i] . ">";
           $k = count($body[$i]);

           for($j=0; $j<$k; $j++)
           {
               $html .= "<td " . $control->attr['cell'][$i][$j] . ">";
               $html .= $body[$i][$j];
               $html .= "</td>";
           }

           $html .= "\n</tr>";
        }
        $html .= "\n</tbody>";

        $html .= "\n</table>";

        return $html;
    }


    public function unorderedList( $control )
    {
        $this->control = $control;
        return $control->content ? "\n<ul " . $this->getId() . ">". $control->content . "\n</ul>" : "";
    }


    public function unorderedListItem( $control )
    {
        return ($type = $control->type) ?  "\n  <li" . (($type != 'circle') ? "type={$type}>" : ">") . $control->value . "</li>" : '';
    }


    public function orderedList( $control )
    {
        $this->control = $control;
        return $control->content ? "\n<ol " . $this->getId() . ">" . $control->content . "\n</ol>" : "";
    }


    public function orderedListItem( $control )
    {
        return ($type = $control->type) ?  "\n  <li>" . $control->value . "</li>" : '';
    }


    public function iFrame( $control )
    {
        return "\n<iframe name=\"{$control->name}\" src=\"{$control->src}\"" . $control->getAttributes(). ">\n</iframe>";
    }

    public function hr( $control )
    {
        return "\n<hr ". $control->getAttributes(). ">";
    }

    public function form( $control )
    {
        $html = "\n<form id=\"$control->name\" name=\"$control->name\" method=\"post\" action=\"$control->action\" " . ($control->enctype != '' ? " enctype=\"$control->enctype\"" : '') . ($control->onsubmit != '' ? " onSubmit=\"$control->onsubmit\"" : '') . " >";
        $html .= $this->generateToString($control->content);
        $html .= "\n</form>"; 

        return $html;   
    }

/*
    public function page( $page )
    {
        $id = $page->name;
//        $page->onLoad("miolo.setForm('{$page->name}');");
        $compliant = $page->compliant ? "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" : "";
        $styles    = $page->getStyles()->getTextByTemplate("<link rel=\"stylesheet\" type=\"text/css\" href=\"/:v/\">\n");
        $styleCode = $page->getStyleCode()->getTextByTemplate("<style>/:v/</style>\n");
        $strOnUnload = ($onunload = $page->getOnUnload()->getValueText('',chr(13))) ? "window.onunload = function () {\n{$onunload}\n}" : '';
        $strOnFocus = ($onfocus = $page->getOnFocus()->getValueText('',chr(13))) ? "window.onfocus = function () {\n{$onfocus}\n}" : '';
        $scripts   = $page->getScripts()->getTextByTemplate("<script type=\"text/javascript\" src=\"/:v/\"></script>\n");
        $dojoRequire  = $page->getDojoRequire()->getTextByTemplate("<script type=\"text/javascript\"> dojo.require(\"/:v/\")</script>\n");
        $customScripts = $page->getCustomScripts()->getTextByTemplate("<script type=\"text/javascript\" src=\"/:v/\"></script>\n");
        $metas     = $page->getMetas()->getValueText('',chr(13));
        $title     = $page->getTitle();
        $form      = $page->form->generate();
        $jscode = $page->getJsCode()->getValueText('',chr(13));

        $html = 
    <<< HERE
$compliant
<html>
<head>
<title>$title</title>
$styles
$styleCode
$metas
<script type="text/javascript"> djConfig={isDebug:true, usePlainJson:true,  parseOnLoad:true}</script>
$scripts
$dojoRequire
<script type="text/javascript">
$jscode
//-->
</script>
$customScripts
</head>
<body class="m-theme-body">
<!-- begin of $id -->
<div id="$id">
<!-- begin of form __mainForm -->
<div id="__mainForm">
$form
</div>
<!-- end of form __mainForm -->
</div>
<!-- end of $id -->
</body>
</html>
HERE;

        return $html;
    }
*/

    public function page( $page )
    {
        $id = $page->name;
        $compliant = $page->compliant ? "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" : "";
        $styles    = $page->getStyles()->getTextByTemplate("<link rel=\"stylesheet\" type=\"text/css\" href=\"/:v/\">\n");
        $styleCode = $page->getStyleCode()->getTextByTemplate("<style>/:v/</style>\n");
        $scripts   = $page->getScripts()->getTextByTemplate("<script type=\"text/javascript\" src=\"/:v/\"></script>\n");
//        $dojoRequire  = $page->getDojoRequire()->getTextByTemplate("<script type=\"text/javascript\"> dojo.require(\"/:v/\")</script>\n");
        $customScripts = $page->getCustomScripts()->getTextByTemplate("<script type=\"text/javascript\" src=\"/:v/\"></script>\n");
        $metas     = $page->getMetas()->getValueText('',chr(13));
        $title     = $page->getTitle();
        $jscode = $page->getJsCode()->getValueText('',chr(13));

        $html = 
    <<< HERE
$compliant
<html>
<head>
<title>$title</title>
$styles
$styleCode
$metas
<script type="text/javascript"> djConfig={usePlainJson:true,  parseOnLoad:true}</script>
$scripts
<script type="text/javascript">
$jscode
//-->
</script>    
$customScripts
</head>
<body class="m-theme-body">
<div id="stdout"></div>
<!-- begin of $id -->
<div id="$id">
<!-- begin of form __mainForm -->
<div id="__mainForm">
<script type="text/javascript">
miolo.doHandler('$page->action','__mainForm');
//-->
</script>
</div>
<!-- end of form __mainForm -->
</div>
<!-- end of $id -->
</body>
</html>
HERE;

        return $html;
    }

    public function dompdf( $page )
    {
        $compliant = $page->compliant ? "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">" : "";
        $theme     = $page->theme->generate();
        $styles    = $page->getStyles()->getTextByTemplate("<link rel=\"stylesheet\" type=\"text/css\" href=\"/:v/\">\n");
        $styleCode = $page->getStyleCode()->getTextByTemplate("<style>/:v/</style>\n");
        $jscode = $page->getJsCode()->getValueText('',chr(13));
        $html = 
    <<< HERE
$compliant
<html>
<head>
<title>$title</title>
$styles
$styleCode
</head>
<body class="m-theme-body">
$theme
</body>
</html>
HERE;

        return $html;
    }

}
?>