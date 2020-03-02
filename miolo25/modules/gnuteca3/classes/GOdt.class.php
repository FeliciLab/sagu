<?php
$MIOLO->uses( "/classes/odt/odf.php", 'gnuteca3');
class GOdt extends Odf
{
    public $encoding = 'UTF-8';
    public $filename = '';
    public $filePath = '';
    
    /**
     * Contrutor padrão.
     * 
     * @param string $filename para alteração
     */
    public function  __construct( $filename )
    {
        //Necessário para pois a Odt trabalha com ISO e utiliza funções de strings.
        ini_set('mbstring.internal_encoding', 'ISO-8859-1');

        $config = array(
                'ZIP_PROXY' => 'PhpZipProxy', //troca a classe de zip para utilizar biblioteca nativa do PHP
                'DELIMITER_LEFT' => '$',
                'DELIMITER_RIGHT' => '',
                'PATH_TO_TMP' => null
            );
        
        $this->setFilename($filename);
        parent::__construct( $filename, $config );
        //variáveis padrão de operador, código e nome da unidade
        $this->setVars( 'GOPERATOR', GOperator::getOperatorId() );
        $this->setVars( 'GLOGGED_LIBRARYUNIT', GOperator::getLibraryUnitLogged() );
        $this->setVars( 'GLOGGED_LIBRARYNAME', GOperator::getLibraryNameLogged() );
    }

    function  __destruct()
    {
        //Habilita novamente o UTF-8 que é o padrão do gnuteca
        ini_set('mbstring.internal_encoding', 'UTF-8');
        parent::__destruct();
    }

    /**
     * Define o conteúdo de uma variável
     * 
     * @param string $key variável
     * @param string $value valor
     */
    public function setVars( $key, $value )
    {
        try
        {
            parent::setVars( $key, $value, true, $this->encoding );
        }
        catch (Exception $exc)
        {
            //does nothing
        }
    }

    /*
     * Define a codificação
     * Compatibilidade com GPDF
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
    }

    /*
     * Retorna a codificação
     * Compatibilidade com GPDF
     *
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Define caminho de exportação
     * Compatibilidade com GPDF
     *
     * @param string $filePath caminho para exportação
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * retorna o nome do arquivo gerado
     * Compatibilidade com GPDF
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * O nome do arquivo de modelo aberto
     * Compatibilidade com GPDF
     *
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * O nome do arquivo de modelo aberto
     * Compatibilidade com GPDF
     *
     * @return string O nome do arquivo de modelo aberto
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Gera um arquivo, seguindo o padrão do FPdf
     * Compatibilidade com FPDF
     *
     * @param string $name
     * @param string $dest
     */
    function output( $name='', $dest='' )
    {
        //escolhe o destino passado ou o definido no construtor
        $dest = $dest ? $dest : $this->filePath;

        if ( $dest )
        {
            $this->setFilePath( $dest );
            $this->saveToDisk( $dest );
        }
        else
        {
            $this->exportAsAttachedFile( $name );
        }
    }
}
?>
