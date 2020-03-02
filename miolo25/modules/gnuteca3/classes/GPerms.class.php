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
 * GPerms - Permission functions
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 08/01/2009
 *
 **/
class GPerms
{
    public static function checkAccess($transaction, $function=null, $deny=true)
    {
        $MIOLO = MIOLO::getInstance();

    	if (!$function)
    	{
    		$function = MIOLO::_REQUEST('function');
    	}

        //Está esperando INSERT e não NEW
        if ($function == 'new' || $function == 'addChildren' )
        {
            $function = 'insert';
        }

       	return $MIOLO->checkAccess($transaction, self::getPerms( $function ), $deny);
    }

    public static function getPerms( $function )
    {
    	if (!$function)
        {
            $function = MIOLO::_REQUEST('function');
        }

        $function = strtolower($function);

        if (($function == 'search') || ($function == 'dinamicmenu') || ($function == 'resetstack') || ($function == null) || ($function == 'detail') || ($function == A_ACCESS))
        {
            $perms = A_ACCESS;
        }
        elseif ($function == 'insert' || ($function == A_INSERT))
        {
            $perms = A_INSERT;
        }
        elseif ($function == 'update' || ($function == A_UPDATE))
        {
            $perms = A_UPDATE;
        }
        elseif ($function == 'delete' || ($function == A_DELETE))
        {
            $perms = A_DELETE;
        }
        else if ($function == 'duplicate' || ($function == A_INSERT))
        {
            $perms = A_INSERT;
        }

        //caso padrão
        if ( !$perms )
        {
            $perms = A_ACCESS;
        }

        return $perms;
    }

    /**
     * Verifica acesso para várias transações simultanemente
     *
     * @param array $transaction
     * @param $function
     * @return unknown_type
     */
    public static function verifyAccess( array $transaction)
    {
        $MIOLO       = MIOLO::getInstance();
        $user        = $MIOLO->getBusinessMAD('user');
        $transaction = implode("','",$transaction);
        $transaction = strtolower( $transaction  );

        $sql    = new MSQL();
        $sql->setColumns('distinct t.m_transaction');
        $sql->setTables('miolo_user u,
                       miolo_groupuser g,
                       miolo_access a,
                       miolo_transaction t');
        $sql->setWhere("u.iduser = g.iduser
                   AND g.idgroup = a.idgroup
                   AND a.idtransaction = t.idtransaction
                   AND u.login = '{$MIOLO->auth->getLogin()->id}'
                   AND lower(t.m_transaction) in ('$transaction')");

        $result = $user->query( $sql )->result;

        //trata os dados para retornar um um array linear
        if ( is_array( $result ) )
        {
            foreach ( $result as $line => $info )
            {
                $return[] = $info[0];
            }
        }

        return $return;

    }


    public static function addUserAction($transaction = null, $access = null, MActionPanel $pnl, $label, $image, $module, $action, $item = NULL, $args = NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $auth = $MIOLO->getConf('options.authenticate');
    	if (($transaction) && (MUtil::getBooleanValue($auth) == TRUE || !isset($auth)))
    	{
    		if (!$access)
    		{
    			$access = A_ACCESS;
    		}
			$pnl->addUserAction($transaction, $access, $label, $image, $module, $action, $item, $args);
    	}
    	else
    	{
    		$pnl->addAction($label, $image, $module, $action, $item, $args);
    	}
    }
}
?>
