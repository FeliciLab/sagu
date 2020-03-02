<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Gnuteca é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 18/09/2012
 *
 **/
$MIOLO->uses( "/classes/bEmail.class.php", 'base');
class rccEmail extends bEmail
{
    
    const EMAIL_MALA_DIRETA = 'mala_direta';
    
    const EMAIL_OUVIDORIA = 'ouvidoria';
    
    public function __construct($tipo = NULL)
    {
        // Obtém as configurações de e-mail padrão do Sagu.
        $configuracaoServidor = bTipo::instanciarTipo('basmailserver', 'relcliente');
        $configuracaoServidor = $configuracaoServidor->buscar();
        
        if ( is_array($configuracaoServidor) )
        {
            $configuracaoServidor = $configuracaoServidor[0];
        }
        
        // Define as configurações de e-mail para o pai.
        parent::__construct($configuracaoServidor->host, $configuracaoServidor->port, "", "", $configuracaoServidor->enableauth, $configuracaoServidor->smtpuser, $configuracaoServidor->smtppassword, "html");

        // Define remetente.
        if ( $tipo == self::EMAIL_MALA_DIRETA )
        {
            $remetente = $this->obterEmailMalaDireta();
        }
        else
        {
            $remetente = $this->obterEmailAdministrador();
        }
        $this->definirNomeRemetente($remetente->nome);
        $this->definirRemetente($remetente->email);
    }
    
    /**
     * Método reescrito para chamar o método de gravação de log em base de dados.
     * 
     * @return boolean Retorna verdadeiro caso tenha inserido. 
     */
    public function enviar()
    {
        $enviou = parent::send();
        
        if ( $enviou )
        {
            $this->gravarLog();
        }
        
        return $enviou;
    }
    
    /**
     * Método reescrito para gravar o log na base de dados.
     * 
     * @return boolean Retorna verdadeiro caso tenha inserido log.
     */
    private function gravarLog()
    {
        $registroDeEmail = bTipo::instanciarTipo('rccRegistroEmail', 'relcliente');
        $registroDeEmail->datahora = date('d/m/Y H:i:s');
        
        // FIXME obter o operador.
        $registroDeEmail->operador = 1;
        
        $registroDeEmail->mensagem = $this->obterConteudo();
        $registroDeEmail->assunto = $this->obterAssunto();
        $registroDeEmail->destinatarios = implode(", ", $this->obterEnderecos());
        $registroDeEmail->destinatarios = strlen($registroDeEmail->destinatarios) > 0 ? $registroDeEmail->destinatarios : '-';
        $registroDeEmail->anexos = implode(", ", $this->obterAnexos());
        
        return $registroDeEmail->inserir(); 
    }
    
    /**
     * Obtém as configurações de e-mail da preferência OUVIDORIA_EMAIL_ADMIN.
     * 
     * @return stdClass Objeto com nome e e-mail do administrador.
     */
    public function obterEmailAdministrador()
    {
         // Obtém valor da preferência OUVIDORIA_EMAIL_ADMIN.
        $tipoBasconfig = bTipo::instanciarTipo('basConfig');
        $tipoBasconfig->moduleconfig = 'RELCLIENTE';
        $tipoBasconfig->parameter = 'OUVIDORIA_EMAIL_ADMIN';
        $tipoBasconfig->popular();
        
        $emailRemetente = new stdClass();
        
        // Obtém nome do remetente e e-mail.
        if ( strlen($tipoBasconfig->value) )
        {
            $preferencia = explode(";", $tipoBasconfig->value);
            
            $emailRemetente->nome = $preferencia[0];
            $emailRemetente->email = $preferencia[1];
        }
        
        return $emailRemetente;
    }
    
    /**
     * Obtém as configurações de e-mail da preferência MALA_DIRETA_EMAIL_ADMIN.
     * 
     * @return stdClass Objeto com nome e e-mail do administrador.
     */
    public function obterEmailMalaDireta()
    {
         // Obtém valor da preferência OUVIDORIA_EMAIL_ADMIN.
        $tipoBasconfig = bTipo::instanciarTipo('basConfig');
        $tipoBasconfig->moduleconfig = 'RELCLIENTE';
        $tipoBasconfig->parameter = 'MALA_DIRETA_EMAIL_ADMIN';
        $tipoBasconfig->popular();
        
        $emailRemetente = new stdClass();
        
        // Obtém nome do remetente e e-mail.
        if ( strlen($tipoBasconfig->value) )
        {
            $preferencia = explode(";", $tipoBasconfig->value);
            
            $emailRemetente->nome = $preferencia[0];
            $emailRemetente->email = $preferencia[1];
        }
        
        return $emailRemetente;
    }
}

?>