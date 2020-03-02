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
 * Class GPDF
 *
 * @author Luiz Gilberto Gregory Filho [luz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/02/2009
 *
 **/
$MIOLO  = MIOLO::getInstance();
$module = MIOLO::getCurrentModule();
$MIOLO->Uses('classes/fpdf/fpdf.php', $module);

class GPDF extends FPDF
{
    private $filePath; //caminho absoluto, evite defini-lo direramente
    public $filename; //o nome do arquivo exemplo file.pdf
    public $folder = 'pdf'; //a pasta onde o arquivo deve ser salvo
    public $absolutePath;
    public $textContent;
    public $lineHeight = 4;
    public $linePage   = 70;
    public $pdf;
    public $MIOLO;
    public $module;
    public $busFile;
    
    /**
     * Construtor
     *
     * @param string $filePath evite definir o filePath
     */
    function __construct($filePath = null, $orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        //Necessário para pois a FPDF trabalha com ISO e utiliza funções de strings.
        ini_set('mbstring.internal_encoding', 'ISO-8859-1');

        $this->setFilePath($filePath);

        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busFile = new BusinessGnuteca3BusFile();

        parent::FPDF($orientation, $unit, $format);
        $this->setAuthor('Gnuteca');
        $this->setCreator('Gnuteca');
        $this->setFont('Courier', '', 11);
        $this->setMargins(10, 30, 0);
    }

    function __destruct()
    {
        //Habilita novamente o UTF-8 que é o padrão do gnuteca
        ini_set('mbstring.internal_encoding', 'UTF-8');
    }

    /**
     * Previne checagens internas do FPDF que gerariam erro com UTF-8.
     */
    function _dochecks()
    {
        //Disable runtime magic quotes
        if(get_magic_quotes_runtime())
        {
            @set_magic_quotes_runtime(0);
        }
    }

    /**
     * Prints a cell (rectangular area) with optional borders, background color and character string.
     * The upper-left corner of the cell corresponds to the current position. The text can be aligned or centered.
     * After the call, the current position moves to the right or to the next line. It is possible to put a link on the text.
     * If automatic page breaking is enabled and the cell goes beyond the limit, a page break is done before outputting.
     *
     * (Documentação retirada do site do FPDF).
     *
     * Sobreescreve a função para alterar a codificação, suportar UTF-8 mas gerar em ISO.
     *
     * @param float $w width. If 0, the cell extends up to the right margin
     * @param float $h height. Default value: 0
     * @param string $txt String to print. Default value: empty string.
     * @param mixed $border Indicates if borders must be drawn around the cell. The value can be either a number: 0: no border 1: frame  or a string containing some or all of the following characters (in any order): L: left T: top R: right B: bottom Default value: 0.
     * @param int $ln Indicates where the current position should go after the call. Possible values are: 0: to the right 1: to the beginning of the next line 2: below    Putting 1 is equivalent to putting 0 and calling Ln() just after. Default value: 0.
     * @param string $align Allows to center or align the text. Possible values are: L or empty string: left align (default value) C: centerR: right align
     * @param boolean $fill Indicates if the cell background must be painted (true) or transparent (false). Default value: false.
     * @param mixed $link URL or identifier returned by AddLink().
     */
    public function cell($w, $h, $txt, $border, $ln, $align, $fill=false , $link='')
    {
        
        parent::Cell($w, $h, GString::construct($txt,'ISO-8859-1'), $border, $ln, $align, $fill, $link);
    }

    //Sobreescreve a função para alterar a codificação
    public function text($x, $y, $txt)
    {
        parent::Text($x, $y, GString::construct($txt,'ISO-8859-1'));
    }

    //Sobreescreve a função para alterar a codificação
    public function write($h, $txt, $link)
    {
        parent::Write($h, GString::construct($txt,'ISO-8859-1'), $link);
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * retorna o nome do arquivo gerado
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }


    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * seta o height entre as linhas
     *
     * @param int $height
     */
    public function setLineHeight($height)
    {
        $this->lineHeight = $height;
    }

    /**
     * seta o conteudo do arquivo
     *
     * @param text $textContent
     */
    public function setTextContent($textContent)
    {
        $this->textContent = $textContent;
    }

    /**
     * Seta numero de linhas de uma pagina.
     *
     * @param int $linePage
     */
    public function setMaxLinePage($linePage)
    {
        $this->linePage = $linePage;
    }

    /**
     * gera o conteudo do pdf
     *
     * @return boolean
     */
    public function generate($processContent = true)
    {
        if ( !$this->filePath )
        {
            $this->filePath = $this->busFile->getAbsoluteFilePath($this->folder, $this->filename);
        }
        
    	//testa se o diretório a ser gravado possui permissão de escrita
    	if ( !is_writable( $this->busFile->getAbsoluteFilePath( $this->folder ) ) )
        {
        	return false;
        }
        else
        {
	    	if ($processContent)
	    	{
                $this->addPage();

		        $lines  = explode("\n", $this->textContent);
		        $lh     = $this->lineHeight;

		        foreach($lines as $n => $l)
		        {
		            $this->Text(30, $lh, $l);

		            $lh += $this->lineHeight;

		            if($lh >= ($this->linePage * $this->lineHeight))
		            {
		                $this->addPage();
		                $lh = $this->lineHeight;
		            }
		        }
	    	}

	        $this->output($this->filePath, 'F');
	        return true;
        }
    }

    /**
     * Seta a fonte como negrito ou não
     *
     * @param $bold
     * @return unknown_type
     */
    public function setBold( $bold = true)
    {
        $this->SetFont( null, $bold ? 'B' : '' );
    }

    /**
     * Remove o arquivo...
     */
    public function delete()
    {
        @unlink($this->filePath);
    }

    /**
     * Get download link of the report file
     *
     * @return String
     */
    public function getDownloadURL()
    {
        $file = $this->busFile->getFile( $this->folder.'/'.$this->filename );
        return $file->mioloLink;
    }

    public function showDownloadInfo()
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
        $file = $this->busFile->getFile($this->folder.'/'.$this->filename);
        GFileUploader::downloadFile($file);
    }
}
?>
