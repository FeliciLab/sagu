<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
 * 
 * Este arquivo é parte do programa Gnuteca.
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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * 
 * @since
 * Class created on 06/10/2010
 *
 **/
abstract class BDependencyCheck
{
    private $message;

    /**
     * 
     * @param string $message
     */
    public function setMessage($message)
    {
        if ( $message instanceof BString )
        {
            $message = $message->__toString();
        }
        
        $this->message = new BString( $message );
    }

    public function getMessage()
    {
        return $this->message;
    }
    
    /**
     * Adiciona mensagem quebrando com <br> para melhor exibicao
     *
     * @param string $msg 
     */
    public function addMessage($msg)
    {
        if ( $this->hasMessage() )
        {
            $msg = '<br>' . $msg;
        }
        
        $this->setMessage( $this->getMessage() . $msg );
    }
    
    /**
     * Retorna se existe alguma mensagem
     *
     * @return string
     */
    public function hasMessage()
    {
        return strlen($this->getMessage()) > 0;
    }
    

    public function listDependency()
    {
        $module = MIOLO::getCurrentModule();

        //Gnuteca  
        $list[] = array('gdInstalled', _M('Gd (Biblioteca de imagens)', $module));
        $list[] = array('zipExtension', _M('Suporte a arquivos zip', $module));
        $list[] = array('apacheVersion', _M('Versão do Apache', $module));
        $list[] = array('phpVersion', _M('Versão do PHP', $module));
        $list[] = array('postgresVersion', _M('Versão do PostgreSQL', $module));
        $list[] = array('ldapInstalled', _M('LDAP instalada', $module));
        $list[] = array('filePermission', _M('Permissão padrão dos arquivos', $module));
        $list[] = array('logPermission', _M('Permissão de escrita no Log', $module));
        $list[] = array('emailConfigured', _M('E-mail está configurado e enviando', $module));
        $list[] = array('registerGlobals', _M('Register globals - segurança', $module));
        $list[] = array('magicQuotesGpc', _M('Magic quotes - segurança', $module));
        $list[] = array('magicQuotesSybase', _M('Magic quotes sybase', $module));
        $list[] = array('sessionGcMaxLifeTime', _M('Session - garbage max life time', $module));
        $list[] = array('mioloTimeOut', _M('Configuração do Miolo - session.timeout', $module));
        $list[] = array('dblinkCheck', _M('Checagem dblink', $module));
        
        //converte para BString
        foreach ( $list as $key => $val )
        {
            $list[$key][1] = new BString($val[1]);
        }
        
        return $list;
    }

    public function getDependencyLabel($dependency)
    {
        $list = $this->listDependency();

        if ( is_array($list))
        {
            foreach ( $list as $line => $info )
            {
                if ( $info[0] == $dependency)
                {
                    return $info[1];
                }
            }
        }
    }

    public function gdInstalled()
    {
        $this->setMessage( GD_VERSION );

        return extension_loaded ('gd') && function_exists ('gd_info');
    }

    public function logPermission()
    {
        $MIOLO      = MIOLO::getInstance();
        $logPath    = $MIOLO->getConf('home.logs');

        $this->setMessage($logPath);

        return is_writable($logPath);
    }

    public function ldapInstalled()
    {
        return (function_exists('ldap_connect'));
    }

    public function apacheVersion()
    {
        $version = apache_get_version(); #Apache/2.2.14 (Ubuntu)

        $version = explode('/', $version);
        $version = $version[1]; //2.2.14 (Ubuntu)
        
        if ( ! $version )
        {
            $version = explode('.', $version);
            $this->setMessage( 'Versão do apache não disponível, presumindo que é superior a 2.' );

            return true;
        }
        else
        {
            $this->setMessage('Versão do apache= '.apache_get_version() . ' - ' . new BString(_M('2 ou maior', 'base')));
            return $version[0] >=2;
        }
    }

    public function phpVersion()
    {
        $version = phpversion();
        $version = explode('.', $version);

        $this->setMessage( phpversion(). ' - ' .new BString(_M('5.2 ou maior que não seja a 5.3.2', 'base')));

        //Verifica a micro-versao
        $microVersion = explode('-',$version[2]);
        //Verifica se a versao do php for igual a 5.3.2, se for o teste tem que dar falso
        //pois esta versao tem problemas complicados de resolver
        $goodVersion = !( $version[0] == 5 && $version[1] == 3 && $microVersion[0] == 2 );
        
        //Se for maior que 5.2 e nao for 5.3.2 esta ok.
        return ($version[0] >= 5 && $version[1] >= 2 && $goodVersion);
    }

    public function postgresVersion()
    {
        $version = pg_version();

        $server = explode('.', $version['server']);
        $client = explode('.', $version['client']);

        $aviso = new BString(_M('Cliente @1 e servidor @2.', 'base', $version['client'], $version['server']));


        $result = ( ( $client[0] . $client[1] ) >= 83 && ( $server[0] . $server[1] ) >= 83 );

        if ( !$result )
        {
            $this->setMessage( $aviso . ' ' .  new BString(_M('Versão deve ser maior que 8.3', 'base') ));
        }
        else
        {
            $this->setMessage( $aviso );
        }

        return $result;
    }

    public function registerGlobals()
    {
        $globals = ini_get('register_globals');

        if ( $globals )
        {
            $this->setMessage(new BString(_M("Register globals estão ativados em php.ini, isto é um problema de segurança. ", 'base')));
        }

        return !$globals;
    }

    public function magicQuotesGpc()
    {
        $magicQuotes = get_magic_quotes_gpc();

        if ( !$magicQuotes )
        {
            $this->setMessage(new BString(_M("Magic quotes deve ser ativada, isto é uma questão de segurança.", 'base')));
        }

        return $magicQuotes;
    }

    public function magicQuotesSybase()
    {
		$magicQuotesSybase = strtolower(ini_get('magic_quotes_sybase'));

		//Para mostrar a imagem correta, é necessário alterar o valor da variável
		$magicQuotesSybase = $magicQuotesSybase ? false : true;

        if ( !$magicQuotesSybase )
        {
            $this->setMessage(new BString(_M("Magic quotes sybase não pode estar ativa.", 'base')));
        }

        return $magicQuotesSybase;
    }
    
    public function sessionGcMaxLifeTime()
    {
        $gc_maxlifetime = strtolower(ini_get('session.gc_maxlifetime'));

        //Para mostrar a imagem correta, é necessário alterar o valor da variável
        $gc_maxlifetime = ($gc_maxlifetime >= 18000);

        if ( !$gc_maxlifetime )
        {
            $this->setMessage(new BString(_M("Sugere-se que o valor de session.gc_maxlifetime seja maior ou igual a 18000 (5 horas).", 'base')));
        }

        return $gc_maxlifetime;
    }
    
    public function mioloTimeOut()
    {
        $MIOLO = MIOLO::getInstance();
        $mTimeOut = strtolower($MIOLO->getConf('session.timeout'));

        //Para mostrar a imagem correta, é necessário alterar o valor da variável
        $mTimeOut = ($mTimeOut >= 300);

        if ( !$mTimeOut )
        {
            $this->setMessage(new BString(_M("Sugere-se que o valor de session.timeout do miolo seja maior ou igual a 300 (5 horas).", 'base')));
        }

        return $mTimeOut;
    }

    public function zipExtension()
    {
        $ok = class_exists('ZipArchive');

        if ( !$ok )
        {
            $this->setMessage( new BString(_M("É necessário adicionar a extensão Zip (Classe ZipArchive) ao PHP")));
            return false;
        }

        return $ok;
    }
    
    public function dblinkCheck()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $database = $MIOLO->getDatabase($module);
        
        //Consulta ao catálogo do postgres
        $sql = new MSQL();
        $sql->setTables('pg_catalog.pg_proc');
        $sql->setColumns('*');
        $sql->setWhere("proname = 'dblink'");
        $sql = $sql->select();
        
        $result = $database->Query($sql);
        
        return count($result) > 0;
    }

    public abstract function filePermission();
    public abstract function emailConfigured();
}
?>
