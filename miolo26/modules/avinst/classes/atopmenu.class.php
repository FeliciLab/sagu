<?php

/*
 * Arquivo que contém a classe controladora dos itens no menu da faixa de cima do sistema, ao lado do banner
 */
define('avinst', 'avinst');
$MIOLO->Uses('classes/atype.class.php', 'avinst');
$MIOLO->Uses('classes/adatabase.class.php', 'avinst');

class ATopMenu extends MForm
{

    //
    // Classe construtora, cria o elemento e seta com eventHandler
    //
    public function __construct()
    {
        parent::__construct($title);
        $this->eventHandler();
    }

    //
    // Classe de construção dos campos
    //
    public function createFields()
    {
        $MIOLO = MIOLO::getInstance();
        parent::createFields();
        // Como a função MIOLO::getCurrentModule() não funciona neste momento (antes de chegar no handler),
        // o module tem que ser fixado, portanto, se for mudado o nome do módulo,
        // deve-se fazer uma busca textual e mudar manualmente nos arquivos o nome do módulo
        $module = 'avinst';
        $url = $js = $MIOLO->getActionURL($module, 'scripts:avinst.js');
        $MIOLO->page->addScriptURL($url);
        $MIOLO->uses('types/avaAvaliacao.class.php', $module);
        $typeAvaliacao = new avaAvaliacao();

        // TODO: Checar se o usuário é administrador
        if ( $MIOLO->getSession()->getValue('login') instanceof MLogin )
        {
            if ( $MIOLO->getSession()->getValue('login')->rights['avinstAdmin'] == true )
            {
                //
                // TODO: Colocar controle para liberar ou não a opção de criar nova avaliação conforme tipo de perfil
                //
        
                // Obtém as avaliações cadastradas
                $avaliacoes = $typeAvaliacao->search();
                $selectionData = array_merge($avaliacoes, array( array( 0, 'Criar nova avaliação' ) ));
                $selection = new MSelection('adminSelection_evaluationId', $evaluationId, null, $selectionData, false, '');
                $selection->addEvent('change', MUtil::getAjaxAction('changeActionTopMenu_click'));

                //
                // TODO: Colocar controle conforme tipo de perfil
                //
                $button = new MButton('editButton', 'Editar');
                $button->addEvent('click', MUtil::getAjaxAction('editTopMenu_click'));
                $button->setClass('mTopButton', false);
                //
                // Se não for as opções "Nova avaliação" e "Selecione", então habilita o botão "Editar"
                //
                if ( !in_array($evaluationId, array( "", "0" )) )
                {
                    $button->setEnabled(false);
                }

                $elements[] = $selection;
                $elements[] = $button;
            }
            
            $buttonBackFields[] = new MImage(null,null,$MIOLO->getUI()->getImageTheme('avinst', 'botao_anterior.png'));
            $buttonBackFields[] = new MSpan(null,'Retornar ao portal');
            $buttonBack = new MDiv('backTopMenuButton', $buttonBackFields, 'mTopButtonBack');
            $buttonBack->addAttribute('onClick', MUtil::getAjaxAction('backTopMenuButton_click', ''));
            
            if ( $_SESSION['loginFrom'] != 'portal' )
            {
                $buttonBack->addBoxStyle('display', 'none');
            }
            
            $buttonOutFields[] = new MImage(null,null,$MIOLO->getUI()->getImageTheme('avinst', 'botao_sair.png'));
            $buttonOutFields[] = new MSpan(null,'Sair');
            $buttonOut = new MDiv('logoutTopMenuButton', $buttonOutFields, 'mTopButton');
            $buttonOut->addAttribute('onClick', MUtil::getAjaxAction('logoutTopMenuButton_click', ''));
            
            $elements[] = new MHContainer('divBtns', array($buttonBack, $buttonOut));

            $hElements = new MHContainer('hTopContainer', $elements);
        }
        else
        {
            $hElements[] = new MSpan();
        }
        $this->setFields($hElements);
        $this->setShowPostButton(false);
    }

    //
    // Classe chamada ao mudar de item no selection das avaliações do form
    //
    public function changeActionTopMenu_click()
    {
        $this->setResponse('Teste', 'mDivTopSystem');
    }

    //
    // Classe chamada ao clicar no botão "Editar"
    //
    public function editTopMenu_click()
    {
        $this->setResponse('Teste', 'mDivTopSystem');
    }

    //
    // Classe chamada ao clicar em "Sair"
    //
    public function logoutTopMenuButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->redirect($MIOLO->getActionURL('avinst', 'logout', null, array('return_to'=>$MIOLO->getActionURL('avinst', 'main'))));
    }
    
    public function backTopMenuButton_click()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->page->redirect($MIOLO->getActionURL('avinst', 'logout', null, array('do_logout'=>'n')));
    }
}

?>
