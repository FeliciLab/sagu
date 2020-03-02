<?php

/**
 * <--- Copyright 2011-2015 de Solis - Cooperativa de Soluções Livres Ltda.
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
 * Manipulador de formulários. Adaptação da versão do MIOLO26 para o MIOLO20
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 *
 * @since
 * Arquivo criado em 03/06/2015
 */
$MIOLO = MIOLO::getInstance();
$MIOLO->uses("classes/bBaseDeDados.class.php", "base");
$MIOLO->uses("classes/bCatalogo.class.php", "base");
$MIOLO->uses("classes/bInfoColuna.class.php", "base");

$modulo = MIOLO::getCurrentModule();

$acao = MIOLO::getCurrentAction();

// Obtém a type da ação
$type = MIOLO::_REQUEST('type');

global $navbar;
$navbar->addOption(_M(AdmMioloTransaction::obterNomeDaTransacao("Frm" . $type), $modulo), $modulo, $acao);

// Faz a manipulação de formulários caso exista uma type
if ( $type )
{
    limpaConteudoTema();
    
    try
    {
        bManipular($type);
    }
    catch( Exception $e )
    {
        SAGU::error($e->getMessage(), $MIOLO->GetActionURL("sagu2", "main"));

    }
}

// Chama handler do lookup, logout ou login
if ( in_array($acao, array('lookup', 'logout', 'login') ) )
{
    $MIOLO->invokeHandler($modulo, $acao);
}

/**
 * Função para manipular formulários
 * 
 * @param String $type Tipo manipulado pelo formulário
 */
function bManipular($type)
{
    $nomeFormulario = 'Frm' . $type;
    $temAcessoAoFormulario = verificaAcessoFormulario($nomeFormulario);
    
    if ($temAcessoAoFormulario)
    {
        renderizarFormulario($type);
    }
}

/**
 * Limpa o conteúdo atual do tema
 * 
 */
function limpaConteudoTema()
{
    $theme = MIOLO::getInstance()->getTheme();
    $theme->clearContent();
}

/**
 * Obtém e renderiza o formulário
 * 
 * @param String $type Nome do tipo manipulado pelo formulário
 */
function renderizarFormulario($type)
{
    list($formulario, $modulo) = obterFormularioPelaFuncaoETipo(MIOLO::_REQUEST("function"), $type);

    $MIOLO = MIOLO::getInstance();
    
    $conteudo = $MIOLO->getUI()->getForm($modulo, $formulario, obterParametrosParaFormularioPeloTipo($type));

    $MIOLO->getTheme()->setContent($conteudo);
}

/**
 * Obtém as informações do formulário de pesquisa e de edição para o type
 * 
 * @param String $funcao Função requisitada
 * @param String $type Nome do tipo manipulado
 * @return Array Lista com o formulário e o módulo deste
 */
function obterFormularioPelaFuncaoETipo($funcao, $type)
{
    $modulo = MIOLO::getCurrentModule();
    $informacaoFormulario = null;
    
    switch ($funcao)
    {
        case SForm::FUNCTION_INSERT:
        case SForm::FUNCTION_UPDATE:
        case SForm::FUNCTION_DELETE:
            $informacaoFormulario = obterFormularioDeEdicao($modulo, $type);
                        
            break;

        case SForm::FUNCTION_SEARCH:
        default:
            $informacaoFormulario = obterFormularioDePesquisa($modulo, $type);
    }
    
    return $informacaoFormulario;
}

/**
 * Obtém o formulário de edição para o tipo especificado
 * 
 * @param String $modulo Nome do módulo original
 * @param String $type Nome do type manipulado
 * @return Array Lista com o formulário e o módulo deste. Caso o formulário respectivo
 * do tipo não tenha sido encontrado, retorna a referência ao formulário "FrmDinamico"
 */
function obterFormularioDeEdicao($modulo, $type)
{
    $formularioCadastro = obterNomeFormularioParaGeracao($modulo, $type);

    // Verifica se o código do formulário existe
    if ( !$formularioCadastro )
    {
        $formularioCadastro = 'FrmDinamico';
        $modulo = 'basic';
    }
    
    return array($formularioCadastro, $modulo);
    
}

/**
 * Obtém o formulário de pesquisa para o tipo especificado
 * 
 * @param String $modulo Nome do módulo original
 * @param String $type Nome do type manipulado
 * @return Array Lista com o formulário e o módulo deste. Caso o formulário respectivo
 * do tipo não tenha sido encontrado, retorna a referência ao formulário "FrmDinamicoSearch"
 */
function obterFormularioDePesquisa($modulo, $type)
{
    $formularioBusca = obterNomeFormularioParaGeracao($modulo, $type . 'Search');

    // Verifica se o código do formulário existe
    if ( !$formularioBusca )
    {
        $formularioBusca = 'FrmDinamicoSearch';
        $modulo = 'basic';
    }
    
    return array($formularioBusca, $modulo);
}

/**
 * Obtém os parâmetros para o formulário
 * 
 * @param String $type Tipo manipulado pelo formulário
 * @return Array Lista com os parâmetros
 */
function obterParametrosParaFormularioPeloTipo($type)
{
    $modulo = MIOLO::getCurrentModule();
    $funcao = MIOLO::_REQUEST('function');
        
    return array(
        'modulo' => $modulo,
        'funcao' => $funcao,
        'tipo' => $type
    );
    
}

/**
 * Obtém o nome do formulário para geração dinâmica
 * 
 * @param String $modulo Modulo do formulário
 * @param String $nome Nome do tipo a ter seu formulário procurado
 * @return String|Boolean Nome do fomulário a ser gerado, FALSE caso este não seja
 * econtrado
 */
function obterNomeFormularioParaGeracao($modulo, $nome)
{
    $MIOLO = MIOLO::getInstance();

    $nomeFormulario = 'Frm' . $nome;
    $caminhoFormulario = $MIOLO->getModulePath($modulo, 'forms/' . $nomeFormulario . '.class');
    
    if ( file_exists($caminhoFormulario) )
    {
        return $nomeFormulario;
    }
    
    return false;
}

/**
 * Veririfica o acessso ao formulário
 * 
 * @param String $nomeFormulario Nome da transação do formulário
 * @return Boolean Se é possível ou não o usuário atual entrar no formulário
 */
function verificaAcessoFormulario($nomeFormulario)
{
    $MIOLO = MIOLO::getInstance();
    $funcao = MIOLO::_REQUEST('function');
    
    $permissao = obterPermissaoPelaFuncao($funcao);
    
    return $MIOLO->checkAccess($nomeFormulario, $permissao, true, true);
    
}

/**
 * Obtém a permissão necessária conforme a função desejada
 * 
 * @param String $funcao Função do formulário
 * @return Inteiro Valor da constante representando a função
 */
function obterPermissaoPelaFuncao($funcao)
{
    $perms = array(
        SForm::FUNCTION_INSERT => A_INSERT,
        SForm::FUNCTION_UPDATE => A_UPDATE,
        SForm::FUNCTION_DELETE => A_DELETE,
        SForm::FUNCTION_SEARCH => A_ACCESS,
        "" => A_ACCESS
    );
    
    return $perms[$funcao];
    
}
?>