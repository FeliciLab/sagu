<?php
//
// Classe utilizada para retornar um conjunto de elementos para gerar 
// uma mensagem informativa para o formulário dinâmico
//
class adynamicformmessage extends MDiv
{
    //
    // Construção do formulário
    //
    public function __construct($name, $message, $setButton = true)
    {
        $MIOLO = MIOLO::getInstance();
        $fields[] = new MDiv(null, $message, 'adynamicformmessage');
        $url = $MIOLO->getActionURL($module, 'main');
        if ($setButton == true)
        {
            $button['button'] = new MButton('buttonBack', 'Retornar à pagina inicial da avaliação', $url);
            $button['button']->setClass('avinstMessageEvaluationButton');
            $div = new MDiv('divButtonBack', $button);
            $div->addAttribute('align', 'center');
            $fields[] = $div;
        }
        parent::__construct($name, $fields, 'adynamicformmessageMain');
        self::addStyle('width', '100%');
    }
}
?>