<?php

class MSpacer extends MDiv
{
    public function __construct($space = NULL)
    {
        parent::__construct('', '&nbsp;', 'mSpacer');

        if ( ! is_null($space) )
        {
            $this->addStyle('line-height',$space);
        } 
    }
}

?>