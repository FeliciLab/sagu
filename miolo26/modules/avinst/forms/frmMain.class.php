<?php

// form definition
class frmMain extends MForm
{
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();

        // call the parent constructor
        parent::__construct( 'Hello World!' );

        // set form width
        $this->setWidth( '470px' );

        // sets the form icon
        $this->setIcon( $this->manager->getUI()->getImage( 'helloworld', 'helloworld-16x16.png' ) );

        // array of fields
        $fields  = array( new MTextField( 'myMsg', '', _M('Message'), 30, _M('Your message to the world') ),
        new MTextField( 'myName', '', _M('Name'), 20, _M('Your name') )
        );
        // define buttons
        $url = $MIOLO->getActionURL( $MIOLO->getConf('options.common'), 'main');

        $buttons = array( new MButton( 'btnHello', _M('Click Me!') ),
        new MButton( 'btnReset', _M('Reset') ),
        new MLinkButton( 'btnBack', _M('Go Back!'), $url )
        );

        // insert the components on the form
        $this->setFields( $fields );
        $this->setButtons( $buttons );

        // connect onBtnHelloClick method to the button
        $this->btnHello->attachEventHandler( 'click', 'OnBtnHelloClick' );

        // call the event's handler
        $this->eventHandler();
    }

    // this method is called when the form is submitted
    public function onBtnHelloClick( $sender )
    {
        // set the visible attribute of the fields to false
        $this->setFieldAttr( 'myMsg', 'visible', false );
        $this->setFieldAttr( 'myName', 'visible', false );
        $this->setFieldAttr( 'btnHello', 'visible', false );

        // add fields (labels) to the form
        $this->addField( new MLabel( _M('Hello World!') ) );
        // this labels contains the text entered in the input fields
        $this->addField( new MLabel( $this->myMsg->getValue() ) );
        $this->addField( new MLabel( $this->getFieldValue('myName') ) );
    }
}

?>