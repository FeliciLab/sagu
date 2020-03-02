<?php
/**
 * Gnuteca temp files remover
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 31/08/2010
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
class removeTempFiles extends GTask
{
    public $MIOLO;
    public $module;

    public function __construct($MIOLO, $myTaskId)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        parent::__construct($MIOLO, $myTaskId);
    }

    public function execute()
    {
        $path = BusinessGnuteca3BusFile::getAbsoluteServerPath(true).'/';

        if (!file_exists($path))
        {
            throw new Exception("Caminho de arquivos temporários não existe!");
        }

        $subpaths = array('tmp/', 'receipt/', 'grid/', 'report/', 'pdf/', 'storage/');

        foreach ($subpaths as $subpath)
        {
            //busca todos arquivos no diretorio
            $scan = glob("{$path}{$subpath}*.*");
           
            if ($scan)
            {
                foreach ($scan as $filename)
                {
                    @unlink($filename);
                }
            }
        }
        
        return true;
    }
}

?>
