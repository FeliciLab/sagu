<?php
class grdLog extends MGrid
{
    public $MIOLO;

    /**
     * Class constructor
     **/
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        
        $columns = array ( new MGridColumn( null ),
                           new MGridColumn(_M('IP'),       'right',    null, '15%', true, null, true),
                           new MGridColumn(_M('Date'),     'center',   null, '15%', true, null, true),
                           new MGridColumn(_M('Time'),     'right',    null, '15%', true, null, true),
                           new MGridColumn(_M('Module'),   'right',    null, '20%', true, null, true),
                           new MGridColumn(_M('User'),     'right',    null, '20%', true, null, true),
                           new MGridColumn(_M('SQL type'), 'left',     null, '20%', true, null, true)
                         );

        parent::__construct( null, $columns, $this->MIOLO->getCurrentURL(), '20' ); 
//        $args = array('pointer' => '%0%',
//                      'mod'     => '%4%');
//        $href = $this->MIOLO->getActionURL($this->MIOLO->getCurrentModule(), $this->MIOLO->getCurrentAction(), null, $args);
        $href = "javascript:doInfo(%0%,\'%4%\');";
        $this->addActionText(_M('info'), _M('Info'), $href);
    }

}
?>
