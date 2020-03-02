<?php
class MIndexedForm extends MForm
{
    public $pages;

    public function __construct($title='',$action='')
    {
        parent::__construct($title,$action);
    }
    
    public function addPage($title, $fields)
    {
        $n = count($fields);
        
        //Add prefix 'frm_' which identifies form variables and 
        //distinguishes them from other possible variables
        for ( $i=0; $i<$n; $i++ )
        { 
            $fields[$i]->name  = 'frm_' . $fields[$i]->name;
            
            if ( $fields[$i]->value == '' )
                $fields[$i]->value = $GLOBALS[$fields[$i]->name]; 
        }
        
        $this->pages[$title] = $fields;
    }
    
    public function generateIndexPage()
    {
       foreach ( $this->pages as $page => $fields )
       {
           $href = "javascript:document.{$this->name}." .
                   "frm_currpage_.value='$page';" .
                   "document.{$this->name}.submit();";
               
           echo "<a class='fieldLabel' href=\"$href\">$page</a><br>\n";
       }
    }
    
    public function generateBody()
    { 
        
        $currpage = $GLOBALS['frm_currpage_'];
        
	if ( ! $currpage )
        {
            $this->generateIndexPage();
        }
        else
        {
            echo "<table width=\"100%\">\n";
            
            $fields = $this->pages[$currpage];
            
            foreach ( $fields as $f )
            {
                if ( ! $f->label )
                {
                    $hidden[] = $f;
                    continue;
                }
                
                echo "  <tr>\n";
                
                $label = $f->label;
                
                if ( $label != '&nbsp;' )
                    $label .= ':';
                
                echo("    <td class=\"fieldLabel\">&nbsp;$label&nbsp;</td>\n");
                echo("    <td>");
                
                $f->generate();
                
                echo("</td>\n");
                echo("  </tr>\n");
            }
            
            echo("  <tr>\n");
            echo("    <td colspan=\"2\"><hr size=\"1\" noshade></td>\n");
            echo("  </tr>\n");
            echo("  <tr>\n");
            echo("    <td colspan=\"2\">\n");
            
            foreach ( $this->buttons as $b )
            {
                if ($b->name == 'submit')
                {
                    $b->name='enviar';
                }
                $b->generate();
            }
            
            if ( $this->reset )
            {
                echo("      <input type=\"reset\">\n");
            }
            
            if ( $this->return )
            {
                echo("      <input name=\"return\"  type=\"button\" value=\"Retornar\" onclick=\"javascript:history.go(-1)\">\n");
            }
            
            if ( $hidden )
            {
                echo("      <!-- HIDDEN FIELDS -->\n");
                
                foreach ( $hidden as $f )
                {
                    echo("      ");
                    $f->generate();
                    echo("\n");
                }
            }
            
            foreach($this->pages as $page=>$fields)
            {
                if ($page != $currpage)
                {
                    foreach($fields as $f)
                    {
                        echo("      <input type='hidden' name='{$f->name}' value='{$f->value}'>\n");
                    }
                }
            }
            
            echo("    </td>\n");
            echo("  </tr>\n");
            echo("</table>\n");
        }
        
        echo("  <input type='hidden' name='frm_currpage_' value='$currpage'>\n");
        echo("<br>\n");
    }
  

}

?>