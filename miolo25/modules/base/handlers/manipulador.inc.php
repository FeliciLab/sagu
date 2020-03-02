<?php

/**
 * <--- Copyright 2011-2012 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Base.
 *
 * O Fermilab é um software livre; você pode redistribuí-lo e/ou modificá-lo
 * dentro dos termos da Licença Pública Geral GNU como publicada pela Fundação
 * do Software Livre (FSF); na versão 2 da Licença.
 *
 * Este programa é distribuído na esperança que possa ser útil, mas SEM
 * NENHUMA GARANTIA; sem uma garantia implícita de ADEQUAÇÃO a qualquer MERCADO
 * ou APLICAÇÃO EM PARTICULAR. Veja a Licença Pública Geral GNU/GPL em
 * português para maiores detalhes.
 *
 * Você deve ter recebido uma cópia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Manipulador de formulários
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 22/06/2012
 */

MUtil::clog('1');

// Obtém a chave da ação.
$chave = MIOLO::_REQUEST('chave');

// Obtém instância do Miolo, módulo e ação.
$MIOLO = MIOLO::getInstance();
$modulo = MIOLO::getCurrentModule();
$acao = MIOLO::getCurrentAction();

// Faz a manipulação de formulários caso exista uma chave.
if ( $chave )
{
    MUtil::clog(2);
    // Obtém o caminho completo para o formulário de gerênciamento padrão.
    $formulario = $this->manager->getModulePath($modulo, 'forms/frm' . ucfirst($chave) . '.class.php');

    // Se nao existir formulário padrão, é passo-a-passo.
    if ( !file_exists($formulario) )
    {
        MUtil::clog('2,5');
        // Chama o handler que controla o passo-a-passo.
        $MIOLO->invokeHandler($modulo, $chave);
    }
    else
    {
        MUtil::clog('3');
        // Faz a chamada dos formulários.
        bManipular($chave);
    }
}

// Chama handler do lookup, logout ou login.
if ( in_array($acao, array('lookup', 'logout', 'login') ) )
{
    $MIOLO->invokeHandler($modulo, $acao);
}

/**
 * Função para manipular formulários.
 * 
 * @param string $chave Chave do formulário.
 */
function bManipular($chave)
{
    $MIOLO = MIOLO::getInstance();
    $modulo = MIOLO::getCurrentModule();
    
    $funcao = MIOLO::_REQUEST('funcao');
    $ui = $MIOLO->getUI();
    
    // Descrição da chave
    $descricaoChave = ucfirst($chave);
    
    $parametros = array(
        'modulo' => $modulo,
        'funcao' => $funcao,
        'tipo' => $chave
    );
    
    switch ($funcao)
    {
        case FUNCAO_INSERIR:
        case FUNCAO_EDITAR:
            $conteudo = $ui->getForm($modulo, 'frm' . $descricaoChave, $parametros);
            break;
        
        case FUNCAO_BUSCAR:
        default:
            MUtil::clog('entrou'); 
            $conteudo =  $ui->getForm($modulo, 'frm' . $descricaoChave . 'Busca', $parametros);
            break;
    }
        
    // FIXME: adicionar checkAccess.
    $theme = $MIOLO->getTheme();
    $theme->clearContent();
    $theme->setContent($conteudo);
}

/**
 * Função para manipular formulários de passos.
 * 
 * @todo Implementar essa função.
 * 
 * @param string $chave Chave do formulário.
 * @param array $passos Nomes dos formulários.
 * @param array $passosDescricao Títulos dos formulários.
 * @param string Título do processo de passos.
 */
function bManipularPassos($chave, $passos, $passosDescricao, $titulo)
{
    $MIOLO = MIOLO::getInstance();
    $modulo = MIOLO::getCurrentModule();
    
    $funcao = MIOLO::_REQUEST('funcao');
    $ui = $MIOLO->getUI();
    
    // Descrição da chave
    $descricaoChave = ucfirst($chave);
    
    $theme = $MIOLO->getTheme();
    
    $formBusca = $MIOLO->getConf('home.modules') . "/$modulo/forms/frm{$descricaoChave}Busca.class.php";

    if ( !file_exists($formBusca) )
    {
        $funcao = FUNCAO_INSERIR;
    }

    switch ( $funcao )
    {
        case FUNCAO_INSERIR:
        case FUNCAO_EDITAR:
            
            // Adiciona a migalha.
            $barraDeNavegacao = $theme->getElement('navigation');
            $barraDeNavegacao->addOption($titulo, $modulo, 'main');
            
            $passo = MStepByStepForm::getCurrentStep();
            $formulario = $passo ? $passos[$passo] : array_shift($passos);

            MStepByStepForm::setShowImageOnButtons(true);
            $conteudo = $ui->getForm($modulo, $formulario, $passosDescricao);
            
            $action = $MIOLO->getActionURL($modulo, 'main', NULL, array( 'chave' => $chave ));
            $conteudo->controlButtons[MStepByStepForm::CANCEL_BUTTON_ID] = $conteudo->cancelButton($action);
                
            // Desativa validação via Javascript.
            $conteudo->setJsValidationEnabled(false);
            break;
        
        case FUNCAO_BUSCAR:
        default:
            $parametros = array(
                'modulo' => $modulo,
                'funcao' => $funcao,
                'tipo' => $chave
            );
            $conteudo =  $ui->getForm($modulo, 'frm' . $descricaoChave . 'Busca', $parametros);
            break;
    }
   
    $theme->clearContent();
    $theme->insertContent($conteudo);
}

?>