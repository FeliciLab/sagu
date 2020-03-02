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
 * Examplary Status handler.
 *
 * @author Luiz Gilberto Gregory Filho
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @since
 * Class created on 13/ago/2008
 *
 **/
defaultHandler('main:configuration:exemplaryStatus', _M('Estado do exemplar', $module) );

$function       = MIOLO::_REQUEST('function');

switch ( $function )
{
    case 'search':
        //Apagando variaveis devido a um problema que estava ocorrendo apos fazer uma atualizacao de registro e clicar no botao OK
        unset($_REQUEST['exemplaryStatusIdS']);
}
?>