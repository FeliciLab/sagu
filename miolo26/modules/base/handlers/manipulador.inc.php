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

// Obtém a chave da ação.
$chave = MIOLO::_REQUEST('chave');

// Obtém instância do Miolo, módulo e ação.
$MIOLO = MIOLO::getInstance();
$modulo = MIOLO::getCurrentModule();
$acao = MIOLO::getCurrentAction();

// Faz a manipulação de formulários caso exista uma chave.
if ( $chave )
{
    // Obtém o caminho completo para o formulário de gerênciamento padrão.
    /*$formulario = $this->manager->getModulePath($modulo, 'forms/frm' . ucfirst($chave) . '.class.php');

    // Se nao existir formulário padrão, é passo-a-passo.
    if ( !file_exists($formulario) )
    {
        // Chama o handler que controla o passo-a-passo.
        $MIOLO->invokeHandler($modulo, $chave);
    }
    else
    {*/
        // Faz a chamada dos formulários.
        bManipular($chave);
    /*}*/
}

// Chama handler do lookup, logout ou login.
if ( in_array($acao, array('lookup', 'logout', 'login') ) )
{
    $MIOLO->invokeHandler($modulo, $acao);
}

function obterCaminhoForm($modulo, $nome)
{
    $MIOLO = MIOLO::getInstance();

    $frmNome = 'frm' . $nome;
    $caminhoDir = $MIOLO->getModulePath($modulo, 'forms/' . $frmNome . '.class.php');
    
    if ( file_exists($caminhoDir)  )
    {
        return $frmNome;
    }
    else
    {
        $frmNome = 'frm' . ucfirst($nome);
        $caminhoDir = $MIOLO->getModulePath($modulo, 'forms/' . $frmNome . '.class.php');
        
        if ( file_exists($caminhoDir) )
        {
            return $frmNome;
        }
    }
    
    return false;
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
//    $descricaoChave = ucfirst($chave);
    $descricaoChave = $chave;
    
    $parametros = array(
        'modulo' => $modulo,
        'funcao' => $funcao,
        'tipo' => $chave
    );
    
    $checked = false;
    // FIXME: adicionar checkAccess.
    $theme = $MIOLO->getTheme();
    $theme->clearContent();
    
    $nomeFormulario = 'frm' . $descricaoChave;
    $perms = $MIOLO->perms;
    $perms instanceof BPermsBase;

    if ($perms->hasTransaction($nomeFormulario))
    {
        if ($MIOLO->checkAccess($nomeFormulario, $perms->converterFuncaoDaBaseParaAccess($funcao), true, true))
        {
            $checked = true;
        }
    }
    else
    {
        $checked = true;
    }
    
    if ($checked == true)
    {
        switch ($funcao)
        {
            case FUNCAO_INSERIR:
            case FUNCAO_EDITAR:
            case FUNCAO_EXPLORAR:
//                $nomeFormulario = 'frm' . $descricaoChave;
                $formularioCadastro = obterCaminhoForm($modulo, $descricaoChave);

                // Verifica se o código do formulário existe.
                if ( !$formularioCadastro )
                {
                    $formularioCadastro = 'frmDinamico';
                    $modulo = 'base';
                }

                $conteudo = $ui->getForm($modulo, $formularioCadastro, $parametros);

                break;

            case FUNCAO_BUSCAR:
            default:
//                $nomeFormulario = 'frm' . $descricaoChave . 'Busca';
                $caminho = $descricaoChave . 'Busca';
                $formularioBusca = obterCaminhoForm($modulo, $caminho);

                // Verifica se o código do formulário existe.
                if ( !$formularioBusca )
                {
                    $formularioBusca = 'frmDinamicoBusca';
                    $modulo = 'base';
                }
                $MIOLO->checkAccess($formularioBusca, $access, $deny);
                // Instância formulário de busca dinâmica.
                $conteudo = $ui->getForm($modulo, $formularioBusca, $parametros);

                break;
        }
        $theme->setContent($conteudo);
    }
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
//    $descricaoChave = ucfirst($chave);
    $descricaoChave = $chave;
    
    $nomeFormulario = 'frm' . $descricaoChave;
    if ($MIOLO->perms->hasTransaction($nomeFormulario))
    {
        if ($MIOLO->checkAccess($nomeFormulario, $MIOLO->perms->converterFuncaoDaBaseParaAccess($funcao), true))
        {
            $theme->clearContent();
        }
    }
    
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