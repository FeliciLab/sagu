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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/

class MAuthGnuteca extends MAuth
{
    /**
     * Função para autenticar os operadores via LDAP. Esta função suporta um server e um server-slave
     *
     * @param MLogin $login
     * @param <type> $password
     * @return <type>
     */
    public function authenticate($login, $password, $log=true)
    {
        $this->manager->logMessage("[LOGIN] Authenticating $login");
        
        try
        {
            //Login LDAP
            if ( $this->manager->getConf('login.ldap.server') || $this->manager->getConf('login.ldap.server-slave') )
            {
                $userLdap = $this->manager->getConf('login.ldap.user');
                $passwordLdap = $this->manager->getConf('login.ldap.password');

                $conn = ldap_connect($this->manager->getConf('login.ldap.server'));
                ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
               
                //somente autentica se o usuário e senha está configurado no LDAP
                if ( strlen($userLdap) > 0 && strlen($passwordLdap) > 0 )
                {
                    //não bloqueia a operação para tentar com o ldap slave
                    if (ldap_bind($conn, $userLdap, $passwordLdap ))
                    {
                        $ldapData = ldap_get_entries($conn, @ldap_search($conn, $this->manager->getConf('login.ldap.base'), str_replace("%login%", $login, $this->manager->getConf('login.ldap.filter'))));
                    }
                }
                else
                {
                    $ldapData = ldap_get_entries($conn, @ldap_search($conn, $this->manager->getConf('login.ldap.base'), str_replace("%login%", $login, $this->manager->getConf('login.ldap.filter'))));
                }

                if ( !$ldapData )
                {
                    $conn = ldap_connect($this->manager->getConf('login.ldap.server-slave'));
                    ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);

                    //somente autentica se o usuário e senha está configurado no LDAP
                    if ( strlen($userLdap) > 0 && strlen($passwordLdap) > 0 )
                    {
                        if ( ldap_bind($conn, $userLdap, $passwordLdap ) == false )
                        {
                            return false;
                        }
                   }

                   $ldapData = ldap_get_entries($conn, @ldap_search($conn, $this->manager->getConf('login.ldap.base'), str_replace("%login%", $login, $this->manager->getConf('login.ldap.filter'))));
                }

                
                $user = $this->manager->getBusinessMAD('user');

                if ( $ldapData[0]['dn'] ) //achou usuário na base ldap
                {

                    if ( ldap_bind($conn, $ldapData[0]['dn'], $password) ) //autenticou usuário e senha
                    {
                        $this->manager->logMessage("[LOGIN] Authenticated $login");

                        if ($log)
                        {
                            $user->getByLogin($login);
                            $login = new MLogin($user);
                            $this->setLogin($login);
                        }

                        return true;
                    }

                }
            }
            
            $this->manager->logMessage("[LOGIN] $login NOT Authenticated - Password not match at LDAP");
            return false;
        
        }
        catch ( Exception $e )
        {
            $this->manager->logMessage("[LOGIN] $login NOT Authenticated - " . $e->getMessage());
        }
    }

}
?>
