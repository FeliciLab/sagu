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
 * Class GDate
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 17/06/2011
 *
 **/

//$MIOLO = MIOLO::getInstance();
//$MIOLO->uses('security/mauthldap.class.php');
class gAuthMutipleLdap extends MAuth
{
    private $baseId;
    
	public function __construct($baseId = null) 
    {
        $this->baseId = $baseId;
        parent::__construct();
    }
    
    /**
     * Método privado para autenticar na base Ldap
     * @return (object) conexão 
     */
    private function connect()
    {
        try
        {
            if ( $this->manager->getConf("login.ldap.{$this->baseId}.host") ) //verifica se existe um host configurada para a base
            {
                $conn = ldap_connect($this->manager->getConf("login.ldap.{$this->baseId}.host"), $this->manager->getConf("login.ldap.{$this->baseId}.port")); //conecta na base ldap
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3); //seta a versão do protocolo
                ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
                
                $loginBind = $this->manager->getConf("login.ldap.{$this->baseId}.user");
                $pwdBind = $this->manager->getConf("login.ldap.{$this->baseId}.password");
                
                if ( (strlen($loginBind) > 0) && (strlen($pwdBind) > 0) )
                {
                    ldap_bind($conn, $loginBind, $pwdBind); //autentica no ldap para poder fazer a pesquisa
                }
                elseif ( strlen($loginBind) > 0 )
                {
                    ldap_bind($conn, $loginBind); //autentica no ldap para poder fazer a pesquisa
                }

                return $conn;
            }
            else
            {
                return false;
            }
        }
        catch ( Exception $e )
        {
            return false;
        }
    }
    
    /**
     * Obtém dados do Ldap
     * @param (String) $login
     * @param (object) $conn
     * @return (array) de dados 
     */
    public function searchData($login, $conn = null)
    {
        if (!$login )
        {
            return false;
        }
        
        try
        {
            if ( !$conn )
            {
                $conn = $this->connect(); //conecta na base
            }
            
            if ( $conn )
            {
                $vars   = array(
                        'AND('      =>'&(',
                        'OR('       =>'|(',
                    );

                $search = strtr(str_replace("%login%", $login, $this->manager->getConf("login.ldap.{$this->baseId}.filter")), $vars);
                $ldapData = ldap_get_entries($conn, @ldap_search($conn, $this->manager->getConf("login.ldap.{$this->baseId}.base"), $search));

                return $ldapData;
            }
            else
            {
                return false;
            }
        }
        catch ( Exception $e )
        {
            return false;
        }
    }
    
    /**
     * Método de autenticação do Ldap
     * 
     * @param (String) $login
     * @param (String) $password
     * @return boolean, true se autenticou 
     */
    public function authenticate($login, $password=null)
    {
        try
        {
            if ( $this->manager->getConf("login.ldap.{$this->baseId}.host") )
            {
                if (!$login )
            	{
            		return false;
            	}

                $conn = $this->connect(); //conecta na base
                $ldapData = $this->searchData($login); //obtém dados do login

                if ( $conn && is_array($ldapData) ) //conectou e achou usuário na base ldap
                {

                    if ( ldap_bind($conn, $ldapData[0]['dn'], $password) ) //autenticou usuário e senha
                    {
                        return true;
                    }
                }
                else
                {
                	return false;
                }
            }

        }
        catch ( Exception $e )
        {
            return false;
        }
    }
}
?>
