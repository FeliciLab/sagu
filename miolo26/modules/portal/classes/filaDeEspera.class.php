<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * @author Nataniel I. da Silva [nataniel@solis.com.br]
 *
 * @version $Id$
 *
 * @since
 * Class created on 05/09/2014
 * */

$MIOLO->uses('forms/frmFilaDeEspera.class.php', $module);

class filaDeEspera
{
    /**
     * Constante para o valor padrão do tempo de inatividade caso não 
     * seja informado no parâmetro TEMPO_LIMITE_DE_INATIVIDADE_DO_USUARIO
     */
    const TEMPO_DE_INATIVIDADE = '15';
    
    public static function autenticacaoDoUsuario()
    {
        $MIOLO = MIOLO::getInstance();
        $action = $MIOLO->getCurrentAction();
        
        self::autenticaoUsuario();
        
        self::verificaInatividade();
        
        // Se não autorizou, redireciona para mensagem de espera
        if ( !self::autorizaUsuario() )
        {
            if ( $action != 'main:filaDeEspera' )
            {
                self::redirecionaFilaDeEspera();
            }   
        }
        else
        {
            if ( $action == 'main:filaDeEspera' )
            {
                self::redirecionaMainPortal();
            }
        }
    }
    
    /**
     * Verifica se o usuário é o primeiro na fila de espera
     * 
     * @return type
     */
    public function posicaoDoUsuarioNaFilaDeEspera()
    {
        $MIOLO = MIOLO::getInstance();
        $basFilaDeEspera = new BasFilaDeEspera();
        
        $fila = $basFilaDeEspera->obtemFilaDeEspera(false);
        
        $login = $MIOLO->getLogin();
        $loginUsuario = $login->id;
        
        $filaDeEspera = array();
        
        foreach ( $fila as $key => $usuario )
        {
            $filaDeEspera[$usuario[1]] = $key+1;
        }
        
        return $filaDeEspera[$loginUsuario];
    }
    
    
    /**
     * Redireciona para a interface de espera
     */
    public function redirecionaFilaDeEspera()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->redirect($MIOLO->getActionURL('portal', 'main:filaDeEspera'));
    }
    
    /**
     * Redireciona para o main do portal 
     */
    public function redirecionaMainPortal()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->redirect($MIOLO->getActionURL('portal', 'main'));
    }
    
    /**
     * Verifica se existe vaga e autoriza o usuário
     * 
     * @return boolean
     */
    public function autorizaUsuario()
    {
        $MIOLO = MIOLO::getInstance();        
        $login = $MIOLO->getLogin();
        $basFilaDeEspera = new BasFilaDeEspera();
        
        $ok = false;
                
        $data = new stdClass();
        $data->usuario = $login->id;
        
        $usuario = $basFilaDeEspera->searchFilaDeEspera($data);
        $autorizadoEm = $usuario[0][3];
        
        $vagasFilaDeEsepra = self::obtemVagasFilaDeEspera();
                
        // Verifica se o usuário já está autorizado
        if ( !strlen($autorizadoEm) > 0 )
        {
            // Verifica se existe configuração de quantidade máxima de usuário
            if ( self::verificaConfiguracaoQuantidadeMaximaUsuario() )
            {
                if ( self::posicaoDoUsuarioNaFilaDeEspera() == '1' )
                {
                    // Verifica se existe vaga
                    if ( $vagasFilaDeEsepra > 0 )
                    {
                        // Atualiza a coluna autorizadoEm para now() 
                        $basFilaDeEspera->atualizaAutorizacaoFilaDeEspera($data);
                        $ok = true;
                    }
                }
            }
            else
            {
                // Atualiza a coluna autorizadoEm para now() 
                $basFilaDeEspera->atualizaAutorizacaoFilaDeEspera($data);
                $ok = true;
            }
        }
        else
        {
            $ok = true;
        }
        
        // Atualiza a coluna referente a data do último registro para now()
        $basFilaDeEspera->registroDeAtividadeFilaDeEspera($data);
        
        return $ok;
    }
    
    /**
     * Verifica se o usuário já foi autenticado, se não o autentica (insere registro na tabela basFilaDeEspera)
     * 
     */
    public function autenticaoUsuario()
    {
        $MIOLO = MIOLO::getInstance();
        $login = $MIOLO->getLogin();
        
        $basFilaDeEspera = new BasFilaDeEspera();
        
        $filters = new stdClass();    
        $filters->usuario = $login->id;
                
        if ( !count($basFilaDeEspera->searchFilaDeEspera($filters)) > 0 )
        {
             $result = $basFilaDeEspera->insertFilaDeEspera($filters);
        }
        
        return true;
    }
    
    /**
     * Verifica se existe configuração de quantidade máxima de usuário para o portal
     * 
     * @return type boolean
     */
    public function verificaConfiguracaoQuantidadeMaximaUsuario()
    {
        $acessosSimultaneos = SAGU::NVL(SAGU::getParameter('BASIC', 'QUANTIDADE_MAXIMA_DE_ACESSOS_SIMULTANEOS'), '0');
                
        return ($acessosSimultaneos != '0');
    }
    
    /**
     * Verifica se existe usuário que excedeu o tempo de inatividade e o cancela
     * 
     * @return type
     */
    public function verificaInatividade($user = null)
    {
        $basFilaDeEspera = new BasFilaDeEspera();
                        
        // Obtém todos usuários autorizados
        $autorizados = $basFilaDeEspera->obtemFilaDeEspera(true);
        
        // Se não configurado o parâmetro, assume que o limite de tempo para invatividade é 15 minutos
        $inatividade = SAGU::NVL(SAGU::getParameter('BASIC', 'TEMPO_LIMITE_DE_INATIVIDADE_DO_USUARIO'), filaDeEspera::TEMPO_DE_INATIVIDADE);
        $inatividade = ($inatividade == '0') ? filaDeEspera::TEMPO_DE_INATIVIDADE : $inatividade;
                
        $excluiuUsuario = array();
        
        foreach ( $autorizados as $usuario )
        {   
            // Obtém o time da data de registro de atividade
            $dataRegistro = substr($usuario[4], 11, 8);
            
            // Obtém o time da data atual
            $dataAtual = strtotime(date('G:i:s'));
            $dataDeResitroAtividade = strtotime($dataRegistro); //data de registro
                        
            // Obtém a diferença em segundos
            $inativo = $dataAtual - $dataDeResitroAtividade;
            
            // Transforma os segundos em minutos
            $inativo = floor($inativo/60);
                                                                      
            if ( ( $inativo >= $inatividade ) || ( !strlen($usuario[3]) > 0 && $inativo >= 1 ) )
            {
                // Excluí o registro da fila de espera
                $basFilaDeEspera->excluiUsuarioInativo($usuario[0]);
                                
                $excluiuUsuario[] = DB_TRUE; 
            }
        }
        
        return in_array(DB_TRUE, $excluiuUsuario);
    }
    
    /**
     * Obtém vagas para o acesso, caso elas existam
     * 
     * @return type
     */
    public function obtemVagasFilaDeEspera()
    {
        $basFilaDeEspera = new BasFilaDeEspera();
                        
        // Obtém todos usuários autorizados
        $autorizados = $basFilaDeEspera->obtemUsuariosAutorizados();
        
        // Obtém o número de acessos simultâneos configurado
        $acessosSimultaneos = SAGU::NVL(SAGU::getParameter('BASIC', 'QUANTIDADE_MAXIMA_DE_ACESSOS_SIMULTANEOS'), '0');
        
        return ($acessosSimultaneos - count($autorizados));
    }
    
}

