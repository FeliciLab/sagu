<?php

/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
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
 * Sagu2 diagrams documentation handler.
 * Constains script to download diagrams
 *
 * @author Daniel Afonso Heisler [daniel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Eduardo Beal Miglioransa [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 *
 * @since
 * Class created on 20/10/2006
 *
 **/

$MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
$MIOLO->checkAccess("ADMIN", A_ACCESS, true, true);

$home = 'main:documentation:administrator:downloadDiagram';
$navbar->addOption(_M('Baixar arquivo', $module), $module, $home);

$ui   = $MIOLO->getUI();
$file = MIOLO::_request('file');

//testa se o arquivo existe
if ( file_exists(SAGU::getParameter('BASIC', 'DOCUMENTATION_PATH') . '/diagrams/' . $file) )
{
    //testa tentativa de hackear, passando outros diretórios e se é um arquivo do dia
    if ( !( strpos($file, "/") > 0 ) && !( strpos($file, "..") > 0 ) && ( strpos($file, "dia") > 0 ) )
    {
        if ( $parse = file_get_contents('compress.zlib://' . SAGU::getParameter('BASIC', 'DOCUMENTATION_PATH') . '/diagrams/' . $file) )
        {
            header("Cache-Control: public, must-revalidate");
            header("Pragma: hack");
            header("Content-Type: " . 'dia/diagram');
            header("Content-Length: " .(string)(strlen($parse)) );
            header('Content-Disposition: attachment; filename="' . $file . '"');
            header("Content-Transfer-Encoding: binary\n");
            print $parse;
        }
    }
    else
    {
        //mensagem de erro de tentativa de acesso indevido
    }
}
else
{
    //mensgem de erro de arquivo inexistente
}

?>
