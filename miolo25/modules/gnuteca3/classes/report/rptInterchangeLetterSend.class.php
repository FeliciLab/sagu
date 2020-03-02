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
 *
 * Interchange letter send report
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/05/2009
 *
 **/
class rptInterchangeLetterSend
{
    public $MIOLO;
    public $module;
    public $filename;
    public $pathFile;
    public $modelFile;
    public $args;
    public $supplier;
    public $supplierTypeAndLocation;

    public function __construct($args = null)
    {
        //parent::__construct();
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->args   = $args;
        $supplier     = $args->supplier;
        $this->supplier                 = $supplier;
        $this->supplierTypeAndLocation  = $args->supplierTypeAndLocation;

        //Cria o nome do arquivo e instancia GPDF
        $this->filename = 'interchange_' . date('Ymd') . '.pdf';
        $this->pathFile = BusinessGnuteca3BusFile::getAbsoluteFilePath('pdf', $this->filename);
    }

    public function generate()
    {
        $content = $this->args->reportContent;
        $path = BusinessGnuteca3BusFile::getAbsoluteFilePath('images','logo','jpg');
        $content = str_replace( htmlentities( $this->getImageURL(),null, 'UTF-8' ), $path, $content );
        $dp = new MDOMPDF();
        $dp->dompdf->load_html($content);
        $dp->dompdf->render();
        file_put_contents($this->pathFile, $dp->dompdf->output());
        return file_exists($this->pathFile);
    }

    /**
     * Get download link of the report file
     *
     * @return String
     */
    public function getDownloadURL()
    {
        return "index.php/{$this->module}/files/pdf/{$this->filename}";
    }
    
    
    public function getModelFile()
    {
        return $this->modelFile;
    }
    
    public function readModelFile()
    {
        $content = (INTERCHANGE_LETTER_MODEL);
        $content = strtr($content, array(
            '$IMG_LOGO'                 => $this->getImageURL(),
            '$SUPPLIER_NAME'            => $this->supplierTypeAndLocation->companyName
        ));
        return $content;
    }
    
    public function getImageURL()
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
        $file = $busFile->getFile('images/logo.jpg');
        return $file->mioloLink;
    }
    
    public function getLogoFile()
    {
        return 'logo.jpg';
    }
}
?>
