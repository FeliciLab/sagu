<?php

class prtTableRaw extends MTableRaw
{
    
    
    public function addCellAttributes($row, $column, $attributes)
    {
        if ( is_array($attributes) )
        {
            $attr = '';
            foreach($attributes as $key => $attribute)
            {
                $attr .= " $key=\"$attribute\" ";
            }
            
            $this->attributes['cell'][$row][$column] = $attr;
        }
        else
        {
            $this->attributes['cell'][$row][$column] = $attributes;
        }
    }
    
}

?>
