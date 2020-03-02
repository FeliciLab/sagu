<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa base.
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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 17/09/2012
 *
 **/
$MIOLO->uses( "/classes//phpmailer/class.phpmailer.php", 'base');
$MIOLO->uses( "/classes/BString.class.php", 'base');

class bEmail extends PHPMailer
{
    /**
     * @var boolean $log Define se é necessário gravar log. 
     */
    private $log = FALSE;
    
    /**
     * @var string $arquivoDeLog Caminho completo do log. 
     */
    private $arquivoDeLog;

    function __construct($host, $porta, $enderecoRemetente, $nomeRemetente, $necessidadeDeAutenticacao, $usuario, $senha, $tipoDeConteudo)
    {
        parent::__construct();
        $this->SetLanguage('br');
        $this->definirCodificacao('UTF-8');
        
        // Define que será usado smtp.
        $this->IsSMTP();

        // Define os vaores dos parâmetros.
        $this->definirHost($host);
        $this->definirPorta($porta);
        $this->definirRemetente($enderecoRemetente);
        $this->definirNomeRemetente($nomeRemetente);
        $this->definirNecessidadeAutenticacao($necessidadeDeAutenticacao);
        $this->definirUsuario($usuario);
        $this->definirSenha($senha);
        $this->definirTipoConteudo($tipoDeConteudo);
        
        // Aumenta o tempo limite de conexão com serviço de e-mail.
        $this->Timeout = 60; 
    }

    /**
     * Define a codificação do e-mail para o email.
     *
     * @param string $codificacao Codificação do e-mail.
     */
    public function definirCodificacao( $codificacao )
    {
        $this->CharSet = $codificacao;
    }

    /**
     * Retorna a codificação do e-mail.
     * 
     * @return string Codificação definida no e-mail.
     */
    public function obterCodificacao( )
    {
        return $this->CharSet;
    }

    /**
     * Retorna o arquivo de log
     */
    private function definirCaminhoCompletoLog()
    {
        if ( strlen( $this->arquivoDeLog ) )
        {
            return;
        }

        // Verifica configuração do log.
        if ( MUTIL::getBooleanValue( MAIL_LOG_GENERATE ) )
        {
            return;
        }

        $MIOLO = MIOLO::getInstance();
        $this->arquivoDeLog  = $MIOLO->getConf('home.logs')."/";
        $this->arquivoDeLog .= defined('MAIL_LOG_FILE_NAME') ? MAIL_LOG_FILE_NAME : "mail.log";
    }

    /**
     * Este método incrementa o vetor de destinatários.
     *
     * @param string $endereco Destinatários separados por vírgula.
     */
    public function adicionarDestinatario($endereco)
    {
        // Caso não encontre arroba não adiciona,
        if ( !ereg( "@", $endereco ) )
        {
            return;
        }

        // Caso seja separado por vírgulas.
        if ( ereg(",", $endereco ) )
        {
            $destinatarios = explode(",", $endereco);
            
            foreach($destinatarios as $enderecoDestinatario)
            {
                parent::addAddress($enderecoDestinatario);
            }
        }
        else
        {
            parent::addAddress( trim( $endereco ) );
        }
    }
    
    /**
     * Define o endereço do destinatário.
     *
     * @param string $endereco Endereço do destinatário.
     */
    public function definirEndereco($endereco)
    {
        // Limpa todos destinatários anteriores.
        $this->ClearAddresses();
        
        // Adiciona o endereço do destinatário.
        $this->adicionarDestinatario($endereco);
    }
    
    /**
     * Retorna os destinatários do e-mail.
     *
     * @return array Vetor com endereços de e-mail.
     */
    public function obterEnderecos()
    {
        return array_keys( $this->all_recipients );
    }

    /**
     * Adiciona anexo. Podem ser vários separados por vírgula.
     *
     * @param string Caminho completo do(s) arquivo(s).
     */
    public function adicionarAnexo($caminhoCompletoArquivo)
    {
        $caminhoCompletoArquivo = trim($caminhoCompletoArquivo);

        // Suporta vários arquivos separados por vírgula.
        if ( ereg( ",", $caminhoCompletoArquivo ) )
        {
            $arquivos = explode(",", $caminhoCompletoArquivo);

            foreach($arquivos as $arquivo)
            {
                parent::addAttachment($arquivo);
            }
        }
        else
        {
            if ( file_exists( $caminhoCompletoArquivo ) )
            {
                parent::addAttachment( $caminhoCompletoArquivo );
            }
        }
    }
    
    /**
     * Obtém os anexos.
     *
     * @return array Vetor com os caminho completo dos arquivos anexados.
     */
    public function obterAnexos()
    {
        return $this->attachment;
    }

    /**
     * Define o conteúdo do e-mail.
     *
     * @param string $conteudo Conteúdo do e-mail.
     */
    public function definirConteudo($conteudo)
    {
        $this->Body = new BString($conteudo);
    }
    
    /**
     * Obtém o conteúdo do e-mail.
     *
     * @return string Conteúdo do e-mail.
     */
    public function obterConteudo()
    {
        if ($this->obterEHtml())
        {
            $this->Body->replace("\n", "<br>");
        }
        
        return $this->Body->getString();
    }

    /**
     * Define o assunto do email.
     *
     * @param string $assunto Assunto do e-mail.
     */
    public function definirAssunto($assunto)
    {
        $assunto = str_replace('  ', ' ', $assunto);
        $this->Subject = new BString($assunto);
    }
    
    /**
     * Obtém o Assunto do e-mail.
     *
     * @return string Assunto do e-mail.
     */
    public function obterAssunto()
    {
        return $this->Subject->getString();
    }

    /**
     * Define o usuário de autenticação.
     *
     * @param string $usuario Usuário de autenticação.
     */
    public function definirUsuario($usuario)
    {
        $usuario = new BString($usuario);
        $usuario->replace(array("\n", "\t", "\r"), "");
        $usuario->trim();
        $this->Username = $usuario;
    }
    
    /**
     * Obtém o usuário utilizado na autenticação.
     *
     * @return string Usuário utilizado.
     */
    public function obterUsuario()
    {
        return $this->Username->getString();
    }

    /**
     * Define a senha de autenticação.
     *
     * @param string $senha Senha necessária para autenticação.
     */
    public function definirSenha($senha)
    {
        $this->Password = new BString($senha);
    }
    
     /**
     * Obtém a senha de autenticação.
     *
     * @return string Senha de autenticação.
     */
    public function obterSenha()
    {
        return $this->Password->getString();
    }

    /**
     * Define o endereço do servidor.
     *
     * @param string $host Endereço do servidor.
     */
    public function definirHost($host)
    {
        $this->Host = $host;
    }
    
    /**
     * Obtém o host/servidor utilizado para envio do e-mail.
     *
     * @return string Endereço do servidor.
     */
    public function obterHost()
    {
        return $this->Host;
    }

    /**
     * Define a porta de conexao.
     *
     * @param Integer $porta Número da porta.
     */
    public function definirPorta($porta)
    {
        $this->Port = $porta;
    }
    
    /**
     * Obtém a porta utilizada na conexão.
     *
     * @return Integer Número da porta.
     */
    public function obterPorta()
    {
        return $this->Port;
    }

    /**
     * Define o remetente do e-mail.
     *
     * @param string $remetente Remetente do e-mail.
     */
    public function definirRemetente($remetente)
    {
        $remetente = new BString($remetente);
        $remetente->replace(array("\n", "\t", "\r"), "");
        $remetente->trim();
        $this->From = $remetente;
    }

    /**
     * Obtém endereço do remetente.
     *
     * @return string Endereço do remetente.
     */
    public function obterRemetente()
    {
        return $this->From->getString();
    }
    
    /**
     * Define o nome do remetente.
     *
     * @param string $nomeRemetente Nome do remetente. 
     */
    public function definirNomeRemetente($nomeRemetente)
    {
        $this->FromName = new BString($nomeRemetente);
    }
    
    /**
     * Obtém o nome do remetente.
     *
     * @return string Nome do remetente.
     */
    public function obterNomeRemetente()
    {
        return $this->FromName->getString();
    }
    
    /**
     *
     * Seta se o metodo de conexao com o server é autenticado ou não
     *
     * @param boolean $autenticacao Caso positivo, usa autenticação no envio de e-mail.
     */
    public function definirNecessidadeAutenticacao($autenticacao)
    {
        $this->SMTPAuth = $autenticacao;
    }
    
    /**
     * Obtém a necessidade de utilizar autenticação para envio de e-mail.
     *
     * @return boolean Retorna verdadeiro se é necessário autenticação.
     */
    public function obterNecessidadeAutenticacao()
    {
        return $this->SMTPAuth;
    }

    /**
     * Seta o tipo do conteudo que será enviado (HTML|TEXT).
     *
     * @param boolean $html Define se e-mail será no formato HTML.
     */
    public function definirEmailFormatoHTML($html=TRUE)
    {
        $this->IsHTML($html);
    }
    
    /**
     * retorna se o tipo de texto é html (isHTML true|false)
     *
     * @return (Boolean)
     */
    public function obterEHtml()
    {
        return $this->ContentType == 'text/html';
    }

    /**
     * Define a liguagem do conteudo.
     *
     * @param string $tipo Tipo do conteúdo do e-mail.
     */
    public function definirTipoConteudo($tipo = 'html')
    {
        switch ($tipo)
        {
            default:
                $this->definirEmailFormatoHTML(TRUE);
        }
    }

    /**
     * Obtém o tipo do conteúdo do e-mail.
     * 
     * @return Tipo do conteúdo. 
     */
    public function obterTipoConteudo()
    {
        return $this->ContentType;
    }

    /**
     * Envia e-mail.
     *
     * @return boolean Retorna verdadeiro caso tenha enviado o e-mail.
     */
    public function enviar()
    {
        $enviou = parent::send();
        $this->gravarLog($enviou);
        
        return $enviou;
    }

    /**
     * Escreve o arquivo de log.
     *
     * @param boolean Resultado do envio de e-mail.
     */
    private function gravarLog($resultado)
    {
        $this->definirCaminhoCompletoLog();

        if(!strlen($this->arquivoDeLog))
        {
            return;
        }

        $recordSeparator    = "+----------------------------------------------------------------+";

        $content = new BString("\n{$recordSeparator}\n");
        $content.= ($result) ? "E-mail foi enviado com sucesso!\n" : "Não foi possível enviar o e-mail.\n";
        $content.= "Destino: '". (!is_null($this->obterEnderecos()) ? implode(",\n\t", $this->obterEnderecos()) : "null") ."'\n";
        $content.= "Data/Hora: '". date("d/m/Y H:i:s") ."'\n";
        $content.= "ContentType: '". $this->ContentType ."'\n";
        $content.= "Authenticate: '". $this->obterNecessidadeAutenticacao() ."'\n";
        $content.= "Host: '". $this->obterHost() .":". $this->obterPorta() ."'\n";
        $content.= "User: '". $this->obterUsuario() ."'\n";
        $content.= "Password: '". $this->obterSenha() ."'\n";
        $content.= "From: '". $this->obterRemetente() ."'\n";
        $content.= "From Name: '". $this->obterNomeRemetente() ."'\n";
        $content.= "Subject: '". $this->obterAssunto() ."'\n";
        $content.= "Content: '". $this->obterConteudo() ."'\n";
        
        if(!$result)
        {
            $content.=  "\n". $this->ErrorInfo ."\n";
        }

        file_put_contents($this->arquivoDeLog, $content, FILE_APPEND);
    }
    
    /**
     * Instancia a variável SMTPSecure
     * 
     * @param type $SMTPSecure
     */
    public function definirSMTPSecure($SMTPSecure)
    {
        $this->SMTPSecure = $SMTPSecure;
    }
}

?>