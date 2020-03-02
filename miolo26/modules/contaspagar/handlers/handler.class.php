<?php
class HandlerContaspagar extends Handler
{
    public function init()
    {
        parent::init();
        $this->manager->trace(__METHOD__);

        $MIOLO = MIOLO::getInstance();
        
        $MIOLO->uses('tipos/capconfiguracao.class.php', 'contaspagar');
        $MIOLO->uses('classes/capform.class.php', 'contaspagar');
    }
}
?>
