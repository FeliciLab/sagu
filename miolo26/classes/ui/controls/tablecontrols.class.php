<?php

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Table
#    Class for html tables
#    - Rows, Cols and Content are 0-based
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

class MTable extends MControl
{
    public $body;
    public $head;
    public $foot;
    public $colgroup;
    public $attr;

    public function __construct($body, $tableAttr, $attr, $head=NULL, $foot=NULL, $colgroup=NULL)
    {
        parent::__construct();
        $this->body = $body;
        $this->head = $head;
        $this->foot = $foot;
        $this->colgroup = $colgroup;
        $this->setAttributes($tableAttr);
        $this->attr = $attr;
    }
}

class MSimpleTable extends MControl
{
   public $head;
   public $foot;
   public $cell;
   public $colgroup;
   public $attributes;

   public function __construct($name='', $attrs='', $row=1, $cell=1)
   {
      parent::__construct($name);
      if ($attrs == '')
         $attrs = "cellspacing=0 cellpadding=0 border=0 width=100%";
      else
         $attrs = str_replace("\"",'',$attrs);
      $this->setAttributes($attrs); 
      $this->setClass($this->attrs->items['class']);
      for($i=0; $i < $row; $i++)
      {
         $this->attributes['row'][$i] = '';
         for($j=0; $j < $cell; $j++)
           $this->attributes['cell'][$i][$j] = '';
      }
   }

   private function setTableAttribute($area, $i, $j=NULL,$name, $attr)
   {
       $at = ($attr != '') ? " $name=\"$attr\" " : " $name ";
       if (is_null($j))
       {
           $this->attributes[$area][$i] .= $at;
       }
       else
       {
           $this->attributes[$area][$i][$j] .= $at;
       }
   }	  

   private function setTableClass($area, $i,$j=NULL,$class)
   {
       if (is_null($j))
       {
           $this->attributes[$area][$i] .= " class=\"$class\" ";
       }
       else
       {
           $this->attributes[$area][$i][$j] .= " class=\"$class\" ";
       }
   }	  

   public function setRowAttribute($i, $name, $attr)
   {
       $this->setTableAttribute('row',$i,NULL,$name, $attr);
   }

   public function setCellAttribute($i, $j, $name, $attr='')
   {
       $this->setTableAttribute('cell',$i,$j,$name, $attr);
   }

   public function setHeadAttribute($i, $name, $attr='')
   {
       $this->setTableAttribute('head',$i,NULL,$name, $attr);
   }

   public function setFootAttribute($i, $name, $attr='')
   {
       $this->setTableAttribute('foot',$i,NULL,$name, $attr);
   }

   public function setRowClass($i,$class)
   {
       $this->setTableClass('row',$i,NULL,$class);
   }

   public function setCellClass($i,$j,$class)
   {
       $this->setTableClass('cell',$i,$j,$class);
   }

   public function setHeadClass($i,$class)
   {
       $this->setTableClass('head',$i,NULL,$class);
   }

   public function setFootClass($i,$class)
   {
       $this->setTableClass('foot',$i,NULL,$class);
   }

   public function setCell($i,$j,$content,$attrs='')
   {
       $this->cell[$i][$j] = $content;
       if ($attrs != '')
       {
          $this->attributes['cell'][$i][$j] .= $attrs;
       }
   }

   public function setHead($i,$content,$attrs='')
   {
       $this->head[$i] = $content;
       if ($attrs != '')
       {
          $this->attributes['head'][$i] .= $attrs;
       }
   }

   public function setFoot($i,$content,$attrs='')
   {
       $this->foot[$i] = $content;
       if ($attrs != '')
       {
          $this->attributes['foot'][$i] .= $attrs;
       }
   }

   public function setColGroup($i,$attrs='')
   {
       $this->colgroup[$i]['attr'] = $attrs;
   }

   public function setColGroupCol($i,$j,$attrs='')
   {
       $this->colgroup[$i]['col'][$j] = $attrs;
   }

   public function generate()
   {
      $n = count($this->head);
      for($i=0; $i<$n; $i++)
      {
          $head[$i] = $this->painter->generateToString($this->head[$i]);
      }
      $n = count($this->foot);
      for($i=0; $i<$n; $i++)
      {
          $foot[$i] = $this->painter->generateToString($this->foot[$i]);
      }
      $n = count($this->cell);
      for($i=0; $i<$n; $i++)
      {
         $k = count($this->cell[$i]);
         for($j=0; $j<$k; $j++)
         {
            $body[$i][$j] = $this->painter->generateToString($this->cell[$i][$j]);
         }
      }
      $t = new MTable($body, $this->attributes(), $this->attributes, $head, $foot, $this->colgroup);
      $t->setCaption($this->caption);
      $t->setId($this->getId()); 
      return $t->getRender('table');
   }

}

class MTableRaw extends MSimpleTable
{
    public $title;
    public $array;
    public $colTitle;
    public $zebra = false;
    public $table;

    public function __construct($title='', $array, $colTitle=null, $name='')
    {
        parent::__construct($name);
        $this->title = $title;
        $this->array = $array;
        $this->colTitle = $colTitle;
        $this->setClass("m-tableraw");
        $this->addAttribute('cellspacing','1');
        $this->addAttribute('width','');
        $this->addAttribute('cellpadding','3');
    }

    public function setData($data)
    {
        $this->array = $data;
    }
    
    public function setAlternate($zebra=false)
    {
        $this->zebra = $zebra;
    }

   public function generate()
   {
      $title = $this->title;
      $array = $this->array;
      $colTitle = $this->colTitle;
      $t = $this;
      $k = 0;
      if ($title)
      {
         $ncols = count($array[0]);
         $t->setCell($k++,0,$title," class=\"m-tableraw-title\" colspan=$ncols ");
      }
      if (is_array($colTitle))
      {
         $n = count($colTitle);
         for($i=0;$i<$n;$i++)
            $t->setCell($k,$i,$colTitle[$i]," class=\"m-tableraw-column-title\" ");
         $k++;
      }
      if (is_array($array))
      {
         $nrows = count($array);
         for($i=0; $i<$nrows; $i++)
         {
            $rowClass = "m-tableraw-row" . ($this->zebra ? '-'.($i%2) : '');
            $t->setRowClass($k, $rowClass);
            if (is_array($array[$i]))
            {
               $ncols = count($array[$i]);
               for($j=0; $j<$ncols; $j++)
               { 
                   $attr = $this->attributes['cell'][$k][$j];
                   if ($attr == '') $attr = "width=0 align=\"left\" valign=\"top\"";
                   $t->setCell($k,$j,$array[$i][$j],$attr);

               }
            }
            else 
            {
               $attr = $this->attributes['cell'][$k][0];
               if ($attr == '') $attr = "width=0 align=\"left\" valign=\"top\"";
               $t->setCell($k,0,$array[$i], $attr);
            }
            $k++; 
         }
      } 
      return parent::generate();
   }
}


class MTableXml extends MTableRaw
{
    public function __construct($title='', $file, $colTitle=null)
    {
        $xmlTree = new MXMLTree($file);
        $array = $xmlTree->getXMLTreeAsArray();
        parent::__construct($title, $array, $colTitle);
    }
}

class TableCell extends MControl
{
    public $content;
    public $attributes;
    public $separator;
    public $contentCount;

    public function __construct()
    {
       parent::__construct();
       $this->attributes = array(); 
       $this->content = array(); 
       $this->contentCount = 0;  
       $this->cssclass = 'ThemeTableCell';
       $this->separator = '<br>';
    }

    public function setAttributes($attr)
    {
       foreach($attr as $a=>$v) $this->attributes[$a] = $v;
    }

    public function setAttribute($attr,$value)
    {
       $this->attributes[$attr] = $value;
    }

    public function setContent($content)
    {
       $this->content = array($content);
       $this->contentCount = 1;
    }

    public function getContent($pos=0)
    {
       if ($pos < $this->contentCount) {
          return $this->content[$pos];
       }
       return null;
    }

    public function clearContent()
    {
       $this->content = array();
       $this->contentCount = 0;
    }

    public function addContent($content)
    {
       $this->content[] = $content;
       $this->contentCount++;
    }
}

class TableRow extends MControl
{
    public $cell;  // array of Cell object
    public $attributes;
    public $cellCount;

    public function __construct()
    {
       parent::__construct();
       $this->cell = array();
       $this->attributes = array (
          'align'=>'',
          'valign'=>''
       );
       $this->cssclass = 'ThemeTableRow';
       $this->cellCount = 0;
    }
  
    public function setAttributes($attr)
    {
       foreach($attr as $a=>$v) $this->attributes[$a] = $v;
    }

    public function setAttribute($attr,$value)
    {
       $this->attributes[$attr] = $value;
    }

    public function addCell()
    {
       $this->cell[$c = $this->cellCount++] = new TableCell();
       return $c;
    }

    public function removeCell($cell)
    {
       if (($this->cellCount > 0) && ($this->cellCount > $cell))
       {
          array_splice($this->cell,$cell, 1);
          $this->cellCount--;
       }
    }

    public function getCell($cell)
    {
       if ($this->ncells > $cell)
       {
          return $this->cell[$cell];
       }
       return null;
    }
}

?>