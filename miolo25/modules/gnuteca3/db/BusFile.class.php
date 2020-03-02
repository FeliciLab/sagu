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
 * Handle with files.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 14/10/2010
 *
 **/
class BusinessGnuteca3BusFile extends GBusiness
{
    const DEFAULT_PATH = '/modules/gnuteca3/html/files';
    const VIEW_ALL = 1; //Todos podem ver
    const VIEW_OPERATOR = 2; //Somente operador pode ver
    const VIEW_OPERATOR_USER = 3; //Somente operador e usuario dono do arquivo podem ver.
    
    private static $serverPath;

    /**
     * //constantes do type GLOB_, pode ser um array
     * @var integer 
     */
    public $type; 
    public $folder;
    public $fileName;
    public $extension;
    /**
     * verifica necessidade de busca recursiva ou seja, dentro das subpastas
     * @var boolean
     */
    public $recursiveSearch = false;
    public $files; //são os arquivos upados definidos pelo servidor
    
    function __construct()
    {
        parent::__construct();
        self::defineServerPath();
    }

    /**
     * Verifica se existe a possibilidade de gravação na pasta de arquivos.
     * Utilizado pelo depedencyCheck.
     *
     * @return boolean
     */
    public static function isWritable()
    {
        return is_writable( self::getAbsoluteServerPath(true) );
    }

    public static function defineServerPath()
    {
        self::$serverPath = getcwd();
    }

    /**
     * Retorna a pasta do servidor onde os arquivos são guardados.
     * Caminho absolute.
     *
     *
     *
     * @param boolean $complete
     * @return string
     */
    public static function getAbsoluteServerPath($complete = false)
    {
        $MIOLO = MIOLO::getInstance();
        self::defineServerPath();

        $filesPath = $MIOLO->getConf('gnuteca.files');

        if ( $filesPath )
        {
            $result = $filesPath;
        }
        else
        {
            if ( $complete)
            {
                $aPath = explode('/', self::$serverPath);
                unset($aPath[count($aPath)-1]);
                $result = implode('/', $aPath) . self::DEFAULT_PATH;
            }
            else
            {
                $result = self::$serverPath;
            }
        }

        return $result;
    }

    /**
     * Retorna o caminho absoluto de um arquivo, passando as suas partes
     *
     *
     * @param string $folder pasta
     * @param string $file nome do arquivo
     * @param string $ext extensão
     * @return string
     *
     */
    public static function getAbsoluteFilePath($folder, $file=null, $ext=null)
    {
        //caso exista extensão, inclui o ponto
        if ( $ext )
        {
            $ext = '.' . $ext;
        }

        return self::getAbsoluteServerPath(true).'/'.$folder.'/'.$file.$ext;
    }

    /**
     * Escreve $buffer no $baseFilename e disponibiliza para download em uma nova janela no browser
     *
     * @param String $folder
     * @param String $filename
     * @param String $buffer
     * @param String $label
     */
    public static function openDownload($folder, $baseFilename, $buffer = null, $label = null, $print = null)
    {
        $baseFilename   = self::getValidFilename( $baseFilename ); #tira espaços, acentos, e limita quantidade de caracteres
        $MIOLO          = MIOLO::getInstance();
        $module         = MIOLO::getCurrentModule();

        if ( $buffer )
        {
            //verifica se o pasta permite escrita
            if ( !is_writable(self::getAbsoluteFilePath($folder)) )
            {
                GForm::error(_M('Sem permissão para gravar arquivo na pasta', $module).' '.$folder, null, _M('Erro', $module), null, true);
                return false;
            }
            
            file_put_contents(self::getAbsoluteFilePath($folder,$baseFilename), $buffer); //escreve os dados no arquivo
        }

        if ( $label )
        {
            $label = "&label=$label";
        }

        //Se for para imprimir
        if ( $print )
        {
            //manda imprimir em vez de fazer download
            $print = "&print=$print";
        }

        $MIOLO->page->onload("mywindow = window.open('file.php?folder={$folder}&file={$baseFilename}{$label}{$print}')"); //abre popup de download/impressão no browser


        return true;
    }

    /**
     * Retorna um nome de arquivo válido
     *
     * @param String $title qualquer string
     * @return String $title string com nome do arquivo formatado
     */
    public static function getValidFilename($someString)
    {
        $str = new GString($someString);
        $str->toASCII(); //tira acentos
        $str->toLower(); //minuscula
        $str->replace(' ','_'); //troca espaços por underline
        $str->sub(0, 150); //reduz para 150 caracteres
        return $str->generate();
    }

    public function listFile()
    {
    }

    /**
     * Lista as imagens de uma pasta.
     * Gif, png, jpg (jpeg) e png
     *
     * @param string $folder a pasta para lista
     * @param boolean $toObject determina o tipo de retorno, se true objeto, se não array
     * @return array
     */
    public function listImages($folder, $toObject = true)
    {
        $this->folder = $folder;
        $this->type = GLOB_BRACE; //suporta sepadores
        $this->fileName = '{*.gif,*.jpg,*.jpeg,*.png,*.GIF,*.JPG,*.JPEG,*.PNG}'; //imagens
        return $this->searchFile( $toObject );
    }

    /**
     * Lista somente pastas, pronto para MSelection.
     *
     * @param boolean $onlyRelative somente caminho relativo
     * @return array
     */
    public function listFolder($onlyRelative = true)
    {
        $this->type = GLOB_ONLYDIR;
        $result = $this->searchFile();

        if ( is_array($result) && $onlyRelative )
        {
            foreach ( $result as $line => $folder )
            {
                $r[$line] = array($folder[2], $folder[2]); //pega só o caminho relativo
            }

            $result = $r;
        }

        return $result;
    }
    
    /**
     * Lista as imagens do tema configurado no miolo.conf
     * 
     * @param boolean para converter retorno em array de objetos ou matriz
     * @return array de GFile com imagens 
     */
    public function listThemeImages($toObject = true)
    {
        $theme = $this->MIOLO->getConf( "theme.main" );
        $path = $this->MIOLO->getConf( "home.themes" ) . '/' . $theme . '/';
        
        $this->folder = 'images';
        $this->type = GLOB_BRACE; //suporta sepadores
        $this->fileName = '{*.gif,*.jpg,*.jpeg,*.png,*.GIF,*.JPG,*.JPEG,*.PNG}'; //imagens
        
        $files =  $this->searchFile( $toObject, $path );
        
        //substitui o folder com a pasta theme
        foreach ( $files as $i => $file )
        {
            $files[$i]->mioloLink = str_replace('folder=images', 'folder=theme', $file->mioloLink);
        }
        
        return $files;
    }
    
    /**
     * Retorna o objeto com as informações do arquivo
     *
     * @param strng $filePath caminho relativo
     * @return GFile retorna o objeto com as informações do arquivo
     *
     */
    public function getFile($relativeFilePath)
    {
        $this->fileName = $relativeFilePath;
        $file = $this->searchFile(true);
        return $file[0];
    }

    /**
     * Verifica se um arquivo existe.
     * Caso passe os 3 parametros otimiza usando diretamente
     * um file_exists, caso contrário executado busca
     *
     * @param string $folder pasta
     * @param string $file nome do arquivo
     * @param string $ext extensao
     * @return boolean
     */
    public function fileExists($folder, $file, $ext=null)
    {
        //caso tenha os 3 otimiza usando diretamente um file_exists
        if ( $folder && $file && $ext )
        {
            return file_exists($this->getAbsoluteFilePath($folder, $file, $ext));
        }
        else //caso contrário efefua busca
        {
            $this->folder       = $folder;
            $this->fileName     = $file;
            $this->extension    = $ext;
            $result = $this->searchFile(true);

            return $result ? true : false;
        }
    }

    /**
     * Tipos de glob/tipo
     * GLOB_MARK - Acrescenta uma barra a cada item retornado
     * GLOB_NOSORT - Retorna os arquivos conforme eles aparecem no diretório (sem ordenação)
     * GLOB_NOCHECK - Retorna o padrão da busca se nenhuma combinação de arquivo for encontrada
     * GLOB_NOESCAPE - Barras invertidas não escapam metacaracteres.
     * GLOB_BRACE - Expande {a,b,c} para combinar com 'a', 'b' ou 'c'
     * GLOB_ONLYDIR - Retorna apenas diretórios que combinem com o padrão
     * GLOB_ERR - Pára em erros de leitura (como diretórios que não podem ser lidos), por padrão os erros são ignorados.
     *
     * @param boolean $toObject
     * @return array
     *
     */
    public function searchFile($toObject = FALSE, $absolutePath = null)
    {
        if ( !$absolutePath )
        {
            $absolutePath = $this->getAbsoluteServerPath(true).'/';
        }

        if ( $this->folder )
        {
            //para não suportar subpastas
            $folder = str_replace('/','',$this->folder). '/';
        }

        if ( $this->fileName )
        {
            $fileName = $this->fileName;
        }

        if ( $this->extension )
        {
            $extension = '.'.$this->extension.'*';
        }

        $searchString = $absolutePath . $folder.$fileName."*".$extension;

        //troca % por * para compatibilidade com outros bus
        $searchString = str_replace('%','*', $searchString);

        //glob é uma função que busca nas pastas
        $result = glob( $searchString , $this->type );
        
        //trata dados e busca dados extras 
        foreach ( $result as $line => $file)
        {
            $fileObj = new GFile();
            $fileObj->absolute  = $file;

            $pathinfo = pathinfo($file);

            $fileObj->dirname       = str_replace ( '','/', str_replace( $absolutePath, '', $pathinfo['dirname']) );
            $fileObj->basename      = $pathinfo['basename'];
            $fileObj->filename      = $pathinfo['filename'];
            $fileObj->extension     = $pathinfo['extension'];
            $fileObj->size          = $this->formatBytes( filesize($file) );
            $fileObj->type          = filetype($file);
            $fileObj->mimeContent   = mime_content_type($file);

            //caso específico do recibo do gnuteca
            if ( $fileObj->extension == 'rcpt')
            {
                $fileObj->mimeContent = 'application/gnuteca3receipt';
                $fileObj->type = 'text';
            }

            //caso especifico do csv
            if ( $fileObj->extension == 'csv')
            {
                $fileObj->mimeContent = 'text/csv';
                $fileObj->type = 'text';
            }
            
            // Se for diretório, substitui o caminho absoluto por '/'.
            if ( $fileObj->mimeContent == 'directory' )
            {
                $fileObj->dirname = '/';
                
            }
            
            //lógica do tipo
            $imageTypes = array('image/png','image/gif', 'image/jpeg', 'image/bmp');

            if ( in_array($fileObj->mimeContent, $imageTypes ) )
            {
                $fileObj->type = 'image';
            }

            $textType = array('text/plain','application/gnuteca3receipt');

            if ( in_array($fileObj->mimeContent, $textType ) )
            {
                $fileObj->type = 'text';
            }

            $mioloFolder = $fileObj->dirname ? 'folder='.$fileObj->dirname : '';

            $fileObj->mioloLink  = $this->MIOLO->getConf('home.url').'/file.php?'.$mioloFolder . '&file=' . $fileObj->basename;
            $fileObj->lastChange = date("d/m/Y H:i:s", filectime($file) );

            if ( $toObject )
            {
                $result[$line] = $fileObj;
            }
            else
            {
                $result[$line] = array_values( (array) $fileObj );
            }
        }

        //abaixo é o processo de busca em subpastas,
        //apesar da função ser chamada recursivamente, só entra em um único nível

        //guarda os dados para futura pesquisa
        $tmpType = $this->type;
        $tmpFile = $this->fileName;
        $tmpExtension = $this->extension;

        //caso não tenha selecionado diretório e não esteja pesquisando somente diretórios
        if ( !$this->folder && $this->type != GLOB_ONLYDIR && $this->recursiveSearch)
        {
            //limpa para pesquisa de diretórios
            $this->fileName = '';
            $this->extension = '';
            $folders =$this->listFolder(true);

            if ( is_array( $folders ) )
            {
                foreach ( $folders as $line => $folder )
                {
                    //define novamente para pesquisa nos subdiretórios
                    $this->folder = $folder[0] ;
                    $this->type = $tmpType;
                    $this->extension = $tmpExtension;
                    $this->fileName = $tmpFile;
                    //efetua busca em subpasta
                    $subResult = $this->searchFile( $toObject );
                    $this->folder = '';

                    //une os resultados
                    if ( is_array( $subResult ) )
                    {
                        $result = array_merge( $result, $subResult);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Formata um tamanho em bytes para um tamanho mais human readable
     *
     * @param int $size
     * @return string
     */
    function formatBytes( $size )
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; $i++) $size /= 1024;
        return round($size, 2).$units[$i];
    }

    public function insertFile()
    {
        if (is_array($this->files) )
        {
            foreach ($this->files as $line => $file )
            {
                if ( $file->tmp_name )
                {
                    $targetPath = $this->getAbsoluteServerPath(true).'/'.$this->folder.'/';
                    $targetPath = $targetPath . self::getValidFilename( basename( $file->basename ) );

                    $i = 1;

                    while ( file_exists($targetPath) )
                    {
                        $targetPath = str_replace('_'.($i-1),'',$targetPath); //tira o _n anterior
                        $explode    = explode('.',"$targetPath"); //separa nome e extensão
                        $targetPath = $explode[0].'_'.$i.'.'.$explode[1]; //adiciona contador no meio
                        $i++; //aumenta contador
                    }

                    $ok[] = copy( $file->tmp_name , $targetPath );

                    //remove o temporário
                    unlink($file->tmp_name );
                }
                else //caso vierem do setData
                {
                    //remove do disco o arquivo definido como remove
                    if ( $file->remove || $file->removeData )
                    {
                        $ok[] = $this->deleteFile($file->absolute);
                    }
                    else
                    {
                        $ok[] = true; //caso for objeto sempre retorna true, porque não tem muito o que fazer
                    }
                }
            }
        }

        if ( ! in_array(true, $ok) )
        {
            if ( $file->tmp_name )
            {
                $errorFile = basename( $file->basename );
            }

            throw new Exception( _M('Impossível o arquivo "@1" para a pasta "@2".', 'gnuteca3', $errorFile, $this->folder) );
        }

        return in_array(true, $ok);
    }

    public function updateFile()
    {
        $this->insertFile();
    }

    /**
     * Remove a file form disk.
     *
     * @param string $completePath
     * @return <boolean>
     */
    public function deleteFile($completePath)
    {
        if ( $completePath && file_exists( $completePath ) )
        {
            return unlink($completePath);
        }
    }

    /**
     * Grava uma stream (variável) em um arquivo.
     *
     * Flags possíveis
     * FILE_USE_INCLUDE_PATH Search for filename in the include directory. See include_path for more information.
     * FILE_APPEND 	If file filename already exists, append the data to the file instead of overwriting it.
     * LOCK_EX 	Acquire an exclusive lock on the file while proceeding to the writing.
     *
     * Use FILE_USE_INCLUDE_PATH  | FILE_APPEND para considerar 2 flags
     *
     * @param mixed $stream a variável apra gravar no arquivo, pode ser string ou binário (imagem por exemplo)
     * @param string $path caminho absoluto obtido pela função BusinessGnuteca3BusFile::getAbsoluteFilePath(0
     * @param integer $flags, flags do file_put_contents do php
     * @param boolean $overwrite
     */
    public static function streamToFile( $stream, $path, $flags = null , $overwrite = false)
    {
        $exists = file_exists ($path );

        if ( !$exists || ( $exists && $overwrite ) )
        {
            return file_put_contents( $path, $stream, $flags);
        }

        return true;
    }

    /**
     * Ao passar o caminho da imagem, a função retorna a extensão real do arquivo
     *
     * @param string $file caminho da imagem a verificar
     */
    public function getRealExtensionForImage( $file)
    {
        $possibleExtension = array('jpg','jpeg','png','gif');
        $imageInfo = getimagesize($file);

        //obtem o mime
        $realExtension = new GString($imageInfo['mime']);
        $realExtension->replace('image/','');
        $realExtension->toLower();

        //invalida extensão caso não for conhecida.
        if ( !in_array( $realExtension.'' , $possibleExtension ))
        {
            $realExtension = null;
        }

       return $realExtension.'';
    }

    /**
     * Lista todos os formulários ou subformulários do sistema.
     * Quando se passa $subForm = true, serão listados os subformulários.
     * 
     * @param boolean $subForm
     * @return array
     */
    public function listForms($subForm = false)
    {
        $MIOLO = MIOLO::getInstance();

        $formPath = $MIOLO->getConf('home.modules') . "/" . $this->module; //Caminho base para o diretório do módulo atual

        if ( $subForm ) //Se é para mostrar subform
        {
            $formPath .= "/classes/subform/"; //Direciona para os subforms
        }
        else
        {
            $formPath .= "/forms/"; //Direciona para os forms
        }
        
        $formFiles = glob($formPath."*.php"); //Lista arquivos

        foreach ($formFiles as $formFile ) //Prepara os nomes dos formulários.
        {
            $formFile = str_replace('.class.php','',basename($formFile));  //Retira o caminho completo do formulário deixando só o nome.
            $forms[$formFile] = $formFile; //Prepara o array de nomes de formulários.
        }
        
        return $forms;
    }
    
    public function backup()
    {
        $folders = $this->listFolder(true);
        $filename = self::getAbsoluteFilePath( 'tmp', 'files', 'zip' );
        $this->deleteFile( $filename );
        
        //pastas para não fazer backup
        $deny = array('tmp', 'receipt', 'grid', 'report', 'pdf', 'storage', 'log');
        
        if ( is_array( $folders ) )
        {
            $zip = new ZipArchive();

            if ( ! $zip->open( $filename , ZIPARCHIVE::CREATE ) )
            {
                throw new Exception( _M("Impossível criar arquivo zip em @1.",'gnuteca3', $filename ) );
            }        

            foreach ( $folders as $line => $folder )
            {
                $folder = $folder[0];

                if ( in_array($folder, $deny ) )
                {
                    continue;
                }
                
                $this->folder = $folder;
                $this->type = GLOB_NOESCAPE;
                $this->fileName = "*";
                $list = $this->searchFile( true );
                
                if ( is_array( $list ) )
                {
                    foreach ( $list as $line => $file )
                    {
                        $zip->addFile( $file->absolute , $folder.'/'.$file->basename );
                    }
                }
            }
            
            $zip->close();
                        
            chmod( $filename, 0777 );
            
            return '/tmp/files.zip';
        }
        
        return false;        
    }
    
    /**
     * Funcao que verifica permissao de acesso aos arquivos conforme permissao
     * dos diretorios.
     * 
     * @param string $folderName 
     * @param string $fileName
     * @return boolean se e ou nao para exibir arquivo
     */
    public function checkFolderAccess($folderName = null,$fileName = null)
    {

        //Define permissoes dos diretorios
        $folderPermission['cover'] = self::VIEW_ALL;
        $folderPermission['coverpre'] = self::VIEW_ALL;
        $folderPermission['images'] = self::VIEW_ALL;
        $folderPermission['materialType'] = self::VIEW_ALL;
        $folderPermission['material'] = self::VIEW_ALL;
        $folderPermission['tmp'] = self::VIEW_ALL;
        $folderPermission['searchTheme'] = self::VIEW_ALL;
        $folderPermission['pdf'] = self::VIEW_OPERATOR;
        $folderPermission['grid'] = self::VIEW_OPERATOR_USER;
        
        $folderPermission['odt'] = self::VIEW_OPERATOR;
        $folderPermission['report'] = self::VIEW_OPERATOR;
        $folderPermission['workflow'] = self::VIEW_OPERATOR;
        $folderPermission['zebra'] = self::VIEW_OPERATOR;
        
        $folderPermission['person'] = self::VIEW_OPERATOR_USER;
        $folderPermission['receipt'] = self::VIEW_OPERATOR_USER;    
        
        $folderPermission['doc'] = self::VIEW_OPERATOR;
        $folderPermission['log'] = self::VIEW_OPERATOR;
        $folderPermission['wsdl'] = self::VIEW_ALL;
                
        if (is_null($folderName))
        {
            $folderName = $this->folder;
        }
        
        if (is_null($fileName))
        {
            $fileName = $this->fileName;
        }
        
        if ( $folderPermission[$folderName] == self::VIEW_ALL ) 
        {
            return true;
        }
        else if ( $folderPermission[$folderName] == self::VIEW_OPERATOR )
        {
            //Se operador logado
            return GOperator::isLogged() ? true:false;
        }
        else if ( $folderPermission[$folderName] == self::VIEW_OPERATOR_USER )
        {
            //se operador tiver logado pode mostrar.
            if ( GOperator::isLogged() )
            {
                return true;
            }
            else
            {
                
                //obter codigo pessoa logada
                $userLoggedId = BusinessGnuteca3BusAuthenticate::getUserCode();

                //Se nao tiver pessoa logada nao permite nada.
                if (is_null($userLoggedId) || is_null($fileName) )
                {
                    return false;
                }
                
                //Para a pasta de pessoas
                if ($folderName == 'person')
                {
                    //valida nome da foto como userLoggedId.* -> ISSO NAO FUNCIONA PRA INTEGRACAO DE FOTO EXTERNA E COM SAGU.
                    return (stripos($fileName, $userLoggedId.".") !== false )? true:false;
                    
                }
                else if ($folderName == 'receipt')
                {
                    //expressao para achar a pessoa : _userLoggedId_
                    return (stripos($fileName, "_".$userLoggedId."_") !== false )? true:false;
                }
                else if ($folderName == 'grid')
                {
                    //expressao para achar a pessoa : _userLoggedId_
                    return (stripos($fileName, '_' . $userLoggedId) !== false )? true:false;
                }
            }
        }
        else //Se nao tiver nenhuma permissao pasta/arquivo nao sera gerenciada
        {
            return true;
        }
    }
}

/**
 * Arquivo de tipo para arquivo
 */
class GFile
{
     /**
     * Caminho absoluto/completo do arquivo
     * @var string
     * @example /home/trialforce/svn/miolo25/modules/gnuteca3/html/files/odt/asddfa.odt
     */
    public $absolute;
    /**
     * diretório
     * @var string
     * @example odt
     */
    public $dirname;
    /**
     * Nome base do arquivo
     * @var string
     * @example asddfa.odt
     */
    public $basename;
    /**
     * Somente nome do arquivo sem extensão
     * @var string
     * @example asddfa
     */
    public $filename;
    /**
     * Somente extensão
     * @var string
     * @example odt
     */
    public $extension;
    /**
     * Tamanho do arquivo, não formatado
     * @var string
     * @example file
     */
    public $size;
    /**
     * Tipo interno do gnuteca
     * @var string
     * @example application/vnd.oasis.opendocument.text
     */
    public $type;
    /**
     * Tipo mime do arquivo, tipos válidos são file (genérico), text, image
     * @var string
     * @example file
     */
    public $mimeContent;
    /**
     * Caminho do miolo, link para usuário fazer download
     * @var string
     * @example http://gnuteca/file.php?folder=odt&file=asddfa.odt
     */
    public $mioloLink;
     /**
     * Data da última alteração do arquivo, formatado d/m/Y H:i:s
     * @var string
     * @example 30/05/2011 10:36:35
     */
    public $lastChange;

    /**
     * Executa a remoção (no HD) do arquivo atual
     */
    public function delete()
    {
        return BusinessGnuteca3BusFile::deleteFile( $this->absolute );
    }

    /**
     * Função normalmente chamada pelo sistema (de forma automatizada)
     * por isso retorna o caminho completo
     *
     * @return string
     */
    public function __toString()
    {
        return $this->absolute;
    }

    /**
     * Função normalmente chamada pelo miolo
     * Em função disso retorna um link válido para o miolo.
     * 
     * @return string
     */
    public function generate()
    {
        return $this->mioloLink;
    }

}
?>
