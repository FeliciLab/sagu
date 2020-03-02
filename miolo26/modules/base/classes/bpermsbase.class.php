<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Solu��es Livres Ltda.
 * 
 * Este arquivo � parte do programa Sagu.
 * 
 * O Sagu � um software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * dentro dos termos da Licen�a P�blica Geral GNU como publicada pela Funda��o
 * do Software Livre (FSF); na vers�o 2 da Licen�a.
 * 
 * Este programa � distribu�do na esperan�a que possa ser �til, mas SEM
 * NENHUMA GARANTIA; sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO
 * ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU/GPL em
 * portugu�s para maiores detalhes.
 * 
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo
 * "LICENCA.txt", junto com este programa, se n�o, acesse o Portal do Software
 * P�blico Brasileiro no endere�o www.softwarepublico.gov.br ou escreva para a
 * Funda��o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 * 
 *
 * Class Miolo Permissions
 *
 * @author Leovan Tavares da Silva [leovan] [leovan@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 14/06/2006
 *
 **/

class BPermsBase extends MPerms
{
    private $auth;
    public  $perms;
    
    public function __construct()
    {
        parent::__construct();

        $this->auth  = $this->manager->GetAuth();
        
        $this->perms = array
            (
            A_ACCESS  => "SELECT",
            A_INSERT  => "INSERT",
            A_DELETE  => "DELETE",
            A_UPDATE  => "UPDATE",
            A_EXECUTE => "EXECUTE",
            A_ADMIN   => "SYSTEM"
            );

    }

    public function converterFuncaoDaBaseParaAccess($baseFuncao)
    {
        $baseFuncao = strlen($baseFuncao) > 0 ? $baseFuncao : FUNCAO_BUSCAR;
        
        $lista = array();
        $lista[FUNCAO_BUSCAR]  = A_ACCESS;
        $lista[FUNCAO_EDITAR]  = A_UPDATE;
        $lista[FUNCAO_INSERIR] = A_INSERT;
        $lista[FUNCAO_REMOVER] = A_DELETE;
        $lista[FUNCAO_EXPLORAR] = A_EXECUTE;
        return $lista[$baseFuncao];
    }
    
    
    public function checkAccess($transaction, $rights, $deny = false, $group = false)
    {
        $transaction = strtolower($transaction);
        
        if ( $this->auth->isLogged() )
        {            
            $login   = $this->auth->getLogin(); 
            $isAdmin = $login->isAdmin(); 
           // $rights  = $login->rights;
            
            if( $rights )
            {
                $listRights = $this->getRights($login->id);
                $listRights = array_change_key_case($listRights, CASE_LOWER); // Indifere se esta camelCase ou nao. Estava causando problemas.
                
                $login->setRights( $listRights );
            }
            
            if (is_array($listRights[$transaction]))
            {
                $check = in_array($rights, $listRights[$transaction]);
                if ( !$check )
                {
                    // Se tiver uma permissão mais alta do que a procurada, retorna true.
                    foreach ( $listRights[$transaction] as $right )
                    {
                        if ( $right > $rights )
                        {
                            $check = true;
                        }
                    }
                }
            }    
        }

        if ( !$check && $deny )
        {
            $msg = _M('Access Denied') . "<br><br>\n" .
                      '<center><big><i><font color=red>' . _M('Transaction: ') . "$transaction\n<br /><br />" .
                   _M('Please inform a valid login/password to access this content.') . "<br>";
           
            $go = $this->manager->getActionURL($this->manager->getConf('options.startup'), 'main' );
            
            $error = Prompt::Error($msg, $go, $caption, '');
            $this->manager->Prompt($error,$deny);
        }
//                                var_dump($check);

        return $check;
    }

    public function getRights($login)
    {
        $this->manager->loadMADConf();
        $db = $this->manager->getDatabase('base');
             
        if ( $this->manager->getSession()->getValue('miolo26_rights') )
        {
            return $this->manager->login->rights;
        }

        $sql = "SELECT DISTINCT T.m_transaction,
                       A.rights
                  FROM miolo_user U, 
                       miolo_groupuser G, 
                       miolo_access A,
                       miolo_transaction T 
                 WHERE U.iduser = G.iduser 
                   AND G.idgroup = A.idgroup 
                   AND U.login = ?
                   AND T.idtransaction = A.idtransaction";
        
        $params = array();
        $params[] = $login;
        $result = $db->query($db->prepare($sql, $params));
        $rows = is_array($result) ? $result : $result->result;
        $rights = array();
        
        foreach((array)$rows as $r)
        {
            $rights[ $r[0] ][] = $r[1];
        }
        
        $this->manager->getSession()->setValue('miolo26_rights', TRUE);
        
        return $rights;        
    }

    public function getTransactionName($transaction)
    {
        $this->manager->loadMADConf();
        $db = $this->manager->getDatabase('base');
        
        $sql = "SELECT A.nametransaction
                  FROM miolo_transaction A
                 WHERE LOWER(A.m_transaction) = ?";
        
        $params = array();
        $params = strtolower($transaction);

        $result = $db->query($db->prepare($sql, $params));
        return is_array($result) ? $result[0][0] : $result->result[0][0];
    }
    
    public function hasTransaction($transaction)
    {
        $transaction = $this->getTransactionName($transaction);
        return strlen($transaction)>0;
    }
}
?>
