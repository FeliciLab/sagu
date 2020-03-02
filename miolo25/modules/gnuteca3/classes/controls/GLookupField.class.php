<?php
class GLookupField extends GContainer
{
    public $lookupTextField;
    public $label;
    public $myRelated;

    public function  __construct( $name = '', $value = '', $label = '', $item = '',  $controls = null, $autocomplete = true )
    {
        $module = 'gnuteca3';
        $event = 'filler';
        $filter = '';
        $hint = '';
        $validator = null;
        $size = FIELD_LOOKUPFIELD_SIZE;

        //FIXME: não há como setar o filter e related pelo construtor
        $this->lookupTextField = new GLookupTextField( $name, $value, null, $size, $hint, $validator, $related, $module, $item, $event, $filter, $autocomplete );
        $this->lookupTextField->baseModule ='gnuteca3';
        $this->label = new MLabel( $label );
        $this->myRelated[] = $name;

        parent::__construct( $name.'Cont', array( $this->label ,$this->lookupTextField ) );

        if (  $controls )
        {
            //converte para array caso não seja
            if ( !is_array( $controls ) )
            {
                $controls = array( $controls );
            }
            
            $this->addControls( $controls );
        }
    }

    public function setRelated( $related )
    {
        $this->lookupTextField->related = $related;
    }

    public function getRelated()
    {
        return $this->lookupTextField->related ;
    }

    public function addControls($controls)
    {
        foreach ( $controls as $control )
        {
            if ( $control->name )
            {
                $this->myRelated[] = $control->name;
            }
            
            $this->addControl( $control );
        }

        if ( $this->myRelated )
        {
            $this->setRelated( implode(',' , $this->myRelated) );
        }
    }
}

class GLookupTextField extends MLookupTextField
{
    public function generateInner()
    {
        parent::generateInner();

        $myDiv = $this->getInner()->getControls();
        $myDiv = $myDiv[1];

        //retira um espaçamento desnecessário no campo
        if ( $myDiv instanceof MDiv )
        {
            $inner = $myDiv->getInner();
            $inner = str_replace('class="mCaption"','', $inner);
            $inner = str_replace('&nbsp;','', $inner);
            $myDiv->setInner($inner);
        }
        
        $field = $this->inner->controls->items[0];

        if ( $field && $this->autocomplete )
        {
            //tira ovento padrão do miolo isso faz funcionar corretamente o onblur do campo
            unset( $field->event['change'][0] ) ;
            $field->addAttribute('onchange',"setTimeout('{$this->lookup_name}.start(true);',100);");
        }
    }
}
?>