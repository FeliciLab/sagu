<?php
//if (($MIOLO->checkAccess(DB_TRANSACTION_ADMIN, DB_RIGHT_ADMIN)) || ($MIOLO->checkAccess(DB_TRANSACTION_ROOT, DB_RIGHT_ROOT)))
{
    $theme->clearContent();
    $ui = $MIOLO->getUI();
    $navbar->addOption('Formulário', $module, $action);

    switch ( MIOLO::_REQUEST( 'function' ) )
    {
        case 'insert':
        case 'edit':
            $steps = array( 1 => 'frmStepByStepFormulario1', 2=>'frmStepByStepFormulario2', 3=>'frmStepByStepFormulario3');
            $stepsDescription = array( 1 => _M('Dados gerais', $module), 2=>_M('Blocos', $module), 3=>_M('Questões', $module));
            $step = MStepByStepForm::getCurrentStep() ? MStepByStepForm::getCurrentStep() : 1 ;
            $form = $MIOLO->getUI()->getForm($module, $steps[$step], $stepsDescription);
            break;
        case 'search':
        default :
            $form = $ui->getForm($module, 'frmSearchAvaFormulario');
    }
    $theme->insertContent($form);
}
?>
