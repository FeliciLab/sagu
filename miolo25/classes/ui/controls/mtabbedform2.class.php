<?php
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# @title
#   Tabbed Form 2
#
# @description
#   
#   
#
# @topics   form, tabbedform
#
# @created
#   2003/06/11
#
# @organisation
#   MIOLO - Miolo Development Team - SOLIS/UNIVATES
#
# @legal
#   CopyLeft (L) 2003 SOLIS - Cooperativa de Solucoes Livres
#   Licensed under GPL (see COPYING.TXT or FSF at www.fsf.org for
#   further details)
#
# @contributors
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
# 
# @maintainers
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#
# @history
#   See history in CVS repository:
#   http://codigolivre.org.br/cvs/?group_id=70
#
# @Id $id: tabbedform2.class,v 1.1 2003/06/17 20:04:57 vgartner Exp $
#---------------------------------------------------------------------

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MTabbedForm2 extends MForm
{
    // member variables
/**
 * Attribute Description.
 */
    public $pages;

/**
 * Attribute Description.
 */
    public $css='tab';


/**
 * Brief Class Description.
 * Complete Class Description.
 */
    // class constructor
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $title' (tipo) desc
 * @param $action='' (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function __construct($title='',$action='')
    {
        parent::__construct($title,$action);
        $this->css = 'tab';
    }
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $file' (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setCSS($file='')
    {
        if ( ! $file )
        {
            $file = 'tab';
        }
        
        $this->css = $file;
    }
    
    
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $title (tipo) desc
 * @param $fields (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function addPage($title, $fields)
    {
        $n = count($fields);
        
        //Add prefix 'frm_' which identifies form variables and 
        //distinguishes them from other possible variables
        for ( $i=0; $i<$n; $i++ )
        { 
            if ( $fields[$i]->name )
            {
                $fields[$i]->name  = 'frm_' . $fields[$i]->name;
                
                if ( is_subclass_of($fields[$i],'radiobutton') || is_subclass_of($fields[$i],'checkbox') )
                {
                    $fields[$i]->checked = ( MIOLO::_REQUEST($fields[$i]->name) == "{$fields[$i]->value}" );
                }
                else if ( ! $fields[$i]->value )
                {
                    $fields[$i]->value = MIOLO::_REQUEST($fields[$i]->name);
                }
                
                $fields[$i]->value = $this->escapeValue($fields[$i]->value);
            }
        }
        
        $this->pages[$title] = $fields;
    }
    
    
    //
    // returns a plain list of all fields contained in the form
    //
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getFieldList()
    {
        $fields = array();
        
        foreach($this->pages as $page)
        {
            $fields = array_merge($fields,$this->_GetFieldList($page));
        }
        
        return $fields;
    }
    
    
    //
    // Generate form body
    //
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function generateBody()
    {
        // optionally generate errors
        if ( $this->hasErrors() )
        {
            $this->generateErrors();
        }
        
        $hidden = null;
        
        echo '<script type="text/javascript" src="scripts/tabpane.js"></script>';
        echo "<link type=\"text/css\" rel=\"StyleSheet\" href=\"/theme/miolo/{$this->css}.css\" />";
        
        echo "<form name=\"{$this->name}\" method=\"{$this->method}\" action=\"{$this->action}\"" .
             " onSubmit=\"return {$this->name}_onSubmit();\">\n";
        
        echo "<div class=\"tab-pane\" id=\"tab-pane-1\">\n";
        $n = 0;
        
        foreach($this->pages as $page)
        {
            $title = array_keys($this->pages);
            
            echo "<div class=\"tab-page\">\n";
            echo "<h2 class=\"tab\">". $title[$n] ."</h2>\n";
            
            //$this->layoutFormFields($page,&$hidden);
            $this->generateLayoutFields(&$hidden);
            
            echo "</div>\n";
            $n += 1;
        }
        
        echo '<script type="text/javascript">';
        echo '    setupAllTabs();';
        echo '</script>';
        
        if ( $this->buttons )
        {
            foreach ( $this->buttons as $b )
            {
                $b->generate();
            }
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
            echo "      <!-- START OF HIDDEN FIELDS -->\n";
            
            foreach ( $hidden as $h )
            {
                echo "      ";
                $h->generate();
                echo "\n";
            }
            
            echo "      <!-- END OF HIDDEN FIELDS -->\n";
        }
        
        echo "</form>\n";
        
        $this->generateScript();

    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
   public function generate()
   {
       $MIOLO = MIOLO::getInstance();
        
      if ( !isset($this->buttons) )
	  {
         if ($this->defaultButton)
         {
	         $this->buttons[] = new FormButton( FORM_SUBMIT_BTN_NAME, _M('Send'),'SUBMIT');
         }
	  }
      //$title  = HtmlPainter::generateToString($this->generateTitle());
      $body   = HtmlPainter::generateToString($this->generateBody());
      $footer = HtmlPainter::generateToString($this->generateFooter());
 
      $f = new Div('', array($title,$body,$footer), 'formBox');
      HtmlPainter::generateElements($f);
   }


}

?>