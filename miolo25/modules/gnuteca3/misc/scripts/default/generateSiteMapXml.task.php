<?php
/**
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @since
 * Class created on 04/10/2011
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
class generateSiteMapXml extends GTask
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
        $this->isRunningFromGCron();
        $folder = $this->MIOLO->getConf('home.html');

        //aumenta a memória caso necessário
        ini_set('memory_limit', '256M' );
        
        //primeiramente remove todos arquivos referentes a sitemap
        $files = glob( $folder. '/sitemap*');
        
        if ( is_array( $files ) )
        {
            foreach ( $files as $line => $file )
            {
                unlink ($file);
            }
        }

        //lista TODOS números de controles
        $busMaterialControl = $this->MIOLO->getBusiness($this->module, 'BusMaterialControl');
        $busMaterialControl = new BusinessGnuteca3BusMaterialControl();
        $materialList = $busMaterialControl->listALlControlNumbers();
        
        //caso seja maior que 50000 registro é necessário criar arquivos em partes
        //conforme http://www.sitemaps.org/protocol.php
        $count = count( $materialList );
        
        if ( $count > 49000 ) 
        {
            //para ter certeza que será um valor inteiro
            $quant = intval( ceil( $count / 49000 ) ) ;
                      
            for ( $i=0; $i < $quant ; $i++ )
            {
                $start = $i*49000;
                $end = ($i+1) * 49000 ;
                //corta o arrayzão em arrays menores
                $controls[] = array_slice( $materialList, $start, $end );
            }
            
            //gera cada um dos sitemap, concatenando o line+1
            foreach ( $controls as $line => $control )
            {
                $ok[] = $this->generateSiteMap( $control, $folder, $line+1 );
            }
            
            $ok[] = $this->genereteSiteMapIndex($quant, $folder );
           
            return in_array( true, $ok );
        }
        else
        {
            return $this->generateSiteMap($materialList, $folder);
        }
       
        return true;
    }
    
    /**
     * Gera arquivo de indice cada existam mais de 1 arquivo de sitemap
     */
    protected function genereteSiteMapIndex( $quant , $folder)
    {
        $xml = new SimpleXMLElement('<sitemapindex></sitemapindex>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
        
        for ( $i= 0 ; $i <= $quant ; $i++ )
        {
            $siteUrl = $this->MIOLO->getConf('home.url');
            
            //adiciona versão compactada
            $url = $xml->addChild('sitemap');
            $url->addChild('loc', $siteUrl . '/sitemap'.($i+1).'.xml.gz' );
            $url->addChild('lastmod', GDate::now()->getDate( GDate::MASK_DATE_DB ) );
            
            //adiciona versão normal
            $url = $xml->addChild('sitemap');
            $url->addChild('loc', $siteUrl . '/sitemap'.($i+1).'.xml' );
            $url->addChild('lastmod', GDate::now()->getDate( GDate::MASK_DATE_DB ) );
        }
        
        $content = $this->prettyXML( $xml->asXML() );
        
        if ( function_exists( 'gzopen' ) )
        {
            $filename = "compress.zlib://{$folder}/sitemap.xml.gz";
            file_put_contents( $filename , $content );
        }
        
        return file_put_contents( $folder.'/sitemap.xml' , $content );
    }
    
    protected function generateSiteMap( $materialList, $folder ,  $fileIndex = '' )
    {
        $xml = new SimpleXMLElement('<urlset></urlset>');
        $xml->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9' );
        
        $href = htmlentities( $this->MIOLO->getConf('home.url').'/index.php?module=gnuteca3&action=main:search&controlNumber=');
        
        if ( !is_array( $materialList ) )
        {
            throw new Exception("Formato da lista de materiais deve ser Array!");
        }
        else
        {
            foreach ( $materialList as $line => $info )
            {
                $controlNumber = $info[0];
                $url = $xml->addChild('url');
        
                $url->addChild('loc', $href.$controlNumber );
                $url->addChild('lastmod', GDate::now()->getDate( GDate::MASK_DATE_DB ) );
                $url->addChild('changefreq', 'monthly');
                //$url->addChild('priority', '1');
            }
        }
        
        $content = $this->prettyXML( $xml->asXML() );
        
        if ( function_exists( 'gzopen' ) )
        {
            $filename = "compress.zlib://{$folder}/sitemap{$fileIndex}.gz";
            file_put_contents( $filename , $content );
        }
        
        $ok = file_put_contents( $folder.'/sitemap'.$fileIndex.'.xml' , $content );
        
        return $ok;
    }
    
    /**
     * Returns s formated xml
     * 
     * @param   string $xml the xml text to format
     * @param   boolean $debug set to get debug-prints of RegExp matches
     * @returns string formatted XML
     * @copyright TJ
     * @link kml.tjworld.net
     * @link http://forums.devnetwork.net/viewtopic.php?p=213989
     * @link http://recursive-design.com/blog/2007/04/05/format-xml-with-php/
     */
    protected function prettyXML($xml, $debug=false)
    {
        // add marker linefeeds to aid the pretty-tokeniser
        // adds a linefeed between all tag-end boundaries
        $xml = preg_replace('/(>)(<)(\/*)/', "$1\n$2$3", $xml);

        // now pretty it up (indent the tags)
        $tok = strtok($xml, "\n");
        $formatted = ''; // holds pretty version as it is built
        $pad = 0; // initial indent
        $matches = array(); // returns from preg_matches()

        /*
         * pre- and post- adjustments to the padding indent are made, so changes can be applied to
        * the current line or subsequent lines, or both
        */
        while($tok !== false)// scan each line and adjust indent based on opening/closing tags
        {
            // test for the various tag states
            if (preg_match('/.+<\/\w[^>]*>$/', $tok, $matches))// open and closing tags on same line
            {
                if($debug) echo " =$tok= ";
                $indent=0; // no change
            }
            else if (preg_match('/^<\/\w/', $tok, $matches)) // closing tag
            {
                if($debug) echo " -$tok- ";
                $pad--; //  outdent now
            }
            else if (preg_match('/^<\w[^>]*[^\/]>.*$/', $tok, $matches))// opening tag
            {
                if($debug) echo " +$tok+ ";
                $indent=1; // don't pad this one, only subsequent tags
            }
            else
            {
                if($debug) echo " !$tok! ";
                $indent = 0; // no indentation needed
            }

            // pad the line with the required number of leading spaces
            $prettyLine = strPad($tok, strlen($tok)+$pad, ' ', STR_PAD_LEFT);
            $formatted .= $prettyLine . "\n"; // add to the cumulative result, with linefeed
            $tok = strtok("\n"); // get the next token
            $pad += $indent; // update the pad size for subsequent lines
        }

        return $formatted; // pretty format
    }
}
?>
