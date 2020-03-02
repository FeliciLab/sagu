<?php
/**
 * Gnuteca temp files remover
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 19/07/2011
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solucoes Livres \n
 * The Gnuteca3 Development Team
 *
 * \b CopyLeft: \n
 * CopyLeft (L) 2010 SOLIS - Cooperativa de Solucoes Livres \n
 *
 * \b License: \n
 * Licensed under GPL (for further details read the COPYING file or http://www.gnu.org/copyleft/gpl.html
 *
 * \b History: \n
 * See history in SVN repository: http://gnuteca.solis.coop.br
 *
 */

class importCoverFromGoogle extends GTask
{
    public function __construct($MIOLO, $myTaskId)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        parent::__construct($MIOLO, $myTaskId);
    }

    public function execute()
    {
        $limit = is_numeric($this->parameters[0]) ? $this->parameters[0] : 100;

        if ( !MUtil::getBooleanValue(GB_INTEGRATION ) )
        {
            throw new Exception("Importação de capas do google está desativada.");
        }

        $busGoogle = $this->MIOLO->getBusiness('gnuteca3','BusGoogleBook');
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');

        $busFile->folder = 'cover';
        $result = $busFile->searchFile(true);

        //monta listagem dos materiais que tem capa
        if ( is_array($result ) )
        {
            foreach ( $result as $line => $info )
            {
                if ( is_numeric( $info->filename ) )
                {
                    $hasCoverList[] = $info->filename;
                }
            }
        }

        $busMaterial = $this->MIOLO->getBusiness( $this->module, "BusMaterial");
        $materialWithoutCover = $busMaterial->getControlNumberByISBN( null , $hasCoverList );

        $cont = 0;

        if ( is_array($materialWithoutCover ) )
        {
            foreach ( $materialWithoutCover as $isbn => $controlNumber )
            {
                $cont++;

                //reinicia variáveis no foreach
                $img = null;
                $realExtension = null;

                try
                {
                    $img = $busGoogle->getCover( $isbn );
                }
                catch (Exception $e)
                {
                    //não mostra excessão pois é uma tarefa rodando no console
                }
                               
                if ( $img )
                {
                    $path = BusinessGnuteca3BusFile::getAbsoluteFilePath('cover', $controlNumber, 'png');
                    //salva capa no local definifitivo
                    $ok = $busFile->streamToFile( $img, $path, null , true );

                    $realExtension = $busFile->getRealExtensionForImage($path);

                    //caso tenha reconhecido uma extensão real e seja diferente de png,
                    //remove a capa salva e salva com a extensão real
                    if ( $realExtension &&  $realExtension != 'png' )
                    {
                        $path = BusinessGnuteca3BusFile::getAbsoluteFilePath('cover', $controlNumber, $realExtension); //extensão real
                        $ok = $busFile->streamToFile( $img, $path, null , true );
                    }
                }

                //limitador
                if ( $cont >= $limit )
                {
                    break;
                }
            }

        }

        return true;
    }
}
?>