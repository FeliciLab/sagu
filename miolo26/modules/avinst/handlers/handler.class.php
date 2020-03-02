<?php

// this class is called by getHandler (in miolo.class)
// this file/class must exist, otherwise you'll get a error
class HandlerAvinst extends MHandler
{
    public function init()
    {
        parent::init();
        $MIOLO = MIOLO::getInstance();
        $MIOLO->Uses('classes/adatabase.class.php','avinst');
        $MIOLO->Uses('classes/atype.class.php','avinst');
        $MIOLO->uses('classes/defines.class.php','avinst');
        $MIOLO->uses('classes/avinst.class.php','avinst');
        $MIOLO->Uses('classes/aform.class.php','avinst');
        $MIOLO->Uses('classes/adynamicform.class.php', 'avinst');
        $MIOLO->Uses('classes/agrid.class.php','avinst');
        $MIOLO->Uses('classes/asearchform.class.php','avinst');
        $MIOLO->Uses('classes/amanagementform.class.php','avinst');
        $MIOLO->Uses('classes/aprocessform.class.php','avinst');
        $MIOLO->Uses('classes/astepbystepform.class.php','avinst');
        $MIOLO->Uses('classes/aradiobuttongroup.class.php','avinst');
        $MIOLO->Uses('classes/abuttonselectgroup.class.php','avinst');
        $MIOLO->Uses('classes/amultilinefield.class.php','avinst');
        $MIOLO->Uses('classes/acheckboxgroup.class.php','avinst');
        $MIOLO->Uses('classes/aoption.class.php','avinst');
        $MIOLO->Uses('classes/acolorpicker.class.php','avinst');
        $MIOLO->Uses('classes/adynamicfields.class.php','avinst');
        $MIOLO->Uses('classes/middleware/middleware.class.php', 'avinst');
        $this->manager->trace('HandlerAvinst:init');
    }
}

?>
