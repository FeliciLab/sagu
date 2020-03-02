<?php

/**
 * <--- Copyright 2005-2012 de Solis - Cooperativa de Soluções Livres Ltda.
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
 * Classe que representa a tabela de mensagem de ouvidoria
 *
 * @author Jader Fiegenbaum [jader@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jader Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 13/09/2012
 *
 */
$MIOLO->uses('tipos/rccMensagemOuvidoria.class.php', 'relcliente');
class rccMensagemOuvidoriaPortal extends rccMensagemOuvidoria
{
    /**
     * Constante que define a origem da mensagem de ouvidoria no portal.
     */
    const ORIGEM_MENSAGEM_OUVIDORIA = 1;
    
    public function __construct()
    {
        parent::__construct('rccMensagemOuvidoria');
    }
    
    /**
     * Método reescrito para disparar e-mail para o usuário.
     * 
     * @return boolean Retorna verdadeiro caso tenha inserido com sucesso. 
     */
    public function inserir()
    {
        $inserir = parent::inserir();
        
        if ( $inserir )
        {
            if ( $this->enviarCopia == DB_TRUE )
            {
                $this->enviarCopiaParaEmail();
            }
        }
        
        return $inserir;
        
    }
    
    /**
     * Enviar cópia da mensagem de ouvidoria por e-mail.
     * 
     * @return boolean Retorna verdadeiro caso tenha enviado o e-mail com sucesso. 
     */
    private function enviarCopiaParaEmail()
    {
        // Obtém o assunto de contato.
        $assuntoDeContato = bTipo::instanciarTipo('rccAssuntoDeContato', 'relcliente');
        $assuntoDeContato->assuntodecontatoid = $this->assuntodecontatoid;
        $assuntoDeContato->popular();
        
        // Envia o e-mail.
        $email = new rccEmail();
        $email->definirEndereco($this->email);
        $email->definirAssunto($assuntoDeContato->descricao);
        $email->definirConteudo($this->mensagem);
        $enviar = $email->enviar();
        
        if ( $enviar )
        {
            // Envia e-mail para administrador.
            $emailAdministrador = clone($email);
            $destinatario = $emailAdministrador->obterEmailAdministrador();
            $emailAdministrador->definirEndereco($destinatario->email);
            
            $emailAdministrador->enviar();
        }

        return $enviar;
    }
    
}

?>