<?php  
        
    include_once('installer.class');

    $formAction = $MIOLO->getConf('home.url') . '/' . $MIOLO->getConf('options.dispatch');
    $form = new MForm('Install MIOLO');
    $form->defaultButton = true;


        $form->setTitle( _M('Installation Requisites') );

        //include_once('environment.inc');

        // Is dir writable?
        $installDir = Form::getFormValue('txtDestination');
        Requisites::addRequisite("Is <b>$installDir</b> writable?", // label
                                    "is_writable(\"$installDir\")", // expression
                                    ' True ' , // expected label
                                    'In order to install MIOLO, <br/>this dir MUST be writable!',
                                    true);

        Requisites::processRequisites( );

        $fields = array( Requisites::$content, new HiddenField('step3',true), new HiddenField('txtDestination',Form::getFormValue('txtDestination')), new HiddenField('txtAddress',Form::getFormValue('txtAddress')), new HiddenField('txtUserName',Form::getFormValue('txtUserName')), new HiddenField('txtPassword',Form::getFormValue('txtPassword')));



    $form->setFields( $fields );

    $theme->appendContent( $form );

?>
