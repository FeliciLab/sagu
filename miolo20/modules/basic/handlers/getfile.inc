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
 * Obtem o download de arquivo pedido para o usuario
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @since
 * Class created on 25/04/2011
 */

$MIOLO = MIOLO::getInstance();
$busFile = new BusinessBasicBusFile();
$fileId = MIOLO::_REQUEST('fileId');

try
{
	if ( !$busFile->checkFilePermission($fileId) )
	{
		throw new Exception( _M('Você não tem permissão para baixar este arquivo.', $module) );
	}

	$file = $busFile->getFile($fileId);

	if ( !$file->fileId )
	{
		throw new Exception( _M('O arquivo passado para download é inválido.', $module) );
	}

	$busFile->getFileAsDownload($fileId, $file->contentType);
}
catch (Exception $e)
{
    exit;
//    $MIOLO->error( $e->getMessage() );
}

?>