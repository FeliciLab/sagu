<?php
class MAttributes
{
    public $attrs;

    public function __construct()
    {
        $this->attrs = new MStringList();
    }

    public function addAttribute( $name, $value = '' )
    {
        if ($name != '')
        {
            $this->attrs->addValue( $name, ( $value != '' ) ? "\"$value\"" : '' );
        }
    }

    public function getAttribute( $name )
    {
        $items = $this->attrs->getItems();
        $a = $items[strtolower($name)];
        return substr($a,1,strlen($a)-1);
    }

    /* TODO: tokenizer */
    public function setAttributes($attr)
    {
        if ( $attr != NULL )
        {
            if ( is_array($attr) )
            {
                foreach( $attr as $ak => $av )
                {
                    $this->addAttribute($ak, $av);
                }
            }
            else if ( is_string($attr) )
            {
                $attr = str_replace( "\"", '', trim($attr) );

                foreach ( explode(' ', $attr) as $a )
                {
                    $a = explode('=', $a);
                    $this->addAttribute($a[0], $a[1]);
                }
            }
        }
    }

    public function attributes( $mergeDuplicates=false )
    {
        if ( $mergeDuplicates )
        {
            $items = $this->attrs->getItems();
            $items_new = array( );
            foreach( $items as $id=>$item )
            {
                if ( $items_new[ strtolower($id) ] )
                {
                    $items_new[ strtolower($id) ] = substr($items_new[ strtolower($id) ], 0, -1) .';' . substr($item, 1);
                }
                else
                {
                    $items_new[ strtolower($id) ] = $item;
                }
            }
            $this->attrs->setItems( $items_new );
        }
        return $this->attrs->hasItems() ? ' ' . $this->attrs->getText("=", " ") : '';
    }

    public function getAttributes( $mergeDuplicates=false )
    {
        return $this->attributes( $mergeDuplicates );
    }
}
?>