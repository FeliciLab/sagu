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
 * Class GServer
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Guilherme Soldateli [guilherme@solis.com.br]
 * Jader Osvino Fiegenbaum [jader@solis.com.br]
 * Jonas Rosa [jonas_rosa@solis.com.br]
 *
 * @since
 * Class created on 05/03/2013
 *
 **/

class GServer
{
    /**
     * Obtém endereço IP do cliente remoto da requisição.
     * 
     * @return String Endereço IP da requisição.
     */
    public static function getRemoteAddress()
    {
        $address = null;
        
        if ( isset($_SERVER['HTTP_X_REAL_IP']) )
        {
            // Obtém IP real da requisição quando é utilizado proxy.
            $address = $_SERVER['HTTP_X_REAL_IP'];
        }
        else
        {
            // Obtém IP da requisição, posição padrão do Apache.
            $address = $_SERVER['REMOTE_ADDR'];
        }
        
        return $address;
    }
}

?>
