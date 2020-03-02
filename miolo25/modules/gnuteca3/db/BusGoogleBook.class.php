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
 * Google Book Integration
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
 * Class created on 05/09/2010
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->getClass( 'gnuteca3', 'GMaterialItem' );
class BusinessGnuteca3BusGoogleBook extends GBusiness
{
    public $query;
    public $isbn;
    public $issn;
    public $titleS;
    public $author;
    public $publisher;
    public $limit = 100;
    public $language;
    public $searchFormat; //formato de pesquisa
    public $xml; //objeto xml retornado pelo google

    const GB_URL                = 'http://books.google.com/books/feeds/volumes?hl=pt-BR';
    const GB_EMBEDDABLE_TRUE    = 'embeddable';
    const GB_EMBEDDABLE_FALSE   = 'not_embeddable';
    const GB_VIEW_PARTIAL       = 'view_partial';
    const GB_VIEW_ALL_PAGES     = 'view_all_pages';
    const GB_VIEW_NO_PAGES      = 'view_no_pages';

    public static function translate($string)
    {
        $module     = MIOLO::getCurrentModule();
        $original   = array(
                            'pages',
                            'book',
                            'updated',
                            'category',
                            'title',
                            'embeddability',
                            'openAccess',
                            'viewability',
                            'disabled',
                            'creator',
                            'date',
                            'description',
                            'format',
                            'identifier',
                            'publisher',
                            'subject',
                            'thumbnail',
                            'info',
                            'preview',
                            'annotation',
                            'alternate'
                            );
        $translated = array(
                            _M('páginas', $module),
                            _M('livro', $module) ,
                            _M('Atualização', $module),
                            _M('Categoria', $module),
                            _M('Título', $module),
                            _M('Embutível', $module),
                            _M('Acesso aberto', $module),
                            _M('Visualização', $module),
                            _M('Desabilitado', $module),
                            _M('Autor', $module),
                            _M('Dados', $module),
                            _M('Descrição', $module),
                            _M('Formato', $module),
                            _M('Identificador', $module),
                            _M('Editora', $module),
                            _M('Assunto', $module),
                            _M('Capa', $module),
                            _M('Informações', $module),
                            _M('Pré-visualização', $module),
                            _M('Anotação', $module),
                            _M('Alternativo', $module)
                            );

        return str_replace($original, $translated, $string);
    }

    public function listGoogleBook()
    {
        $this->limit = 1;
        $this->searchGoogleBook(true);
    }

    /**
     * Retorna um google livro, passando o código do ISBN
     *
     * @param <string> $isbn
     * @param <boolean> $getControlNumber procura ou não por controlNumbers no Gnuteca
     * @return <stdclass> o objeto do livro
     */
    public function getGoogleBook($isbn, $getControlNumber)
    {
        $this->isbn = $isbn;
        $this->limit = 1;
        $books = $this->searchGoogleBook(true, $getControlNumber );

        return $book = $books[0];
    }


    /**
     * Localiza um livro do google baseado em um número de controle do gnuteca.
     *
     * @param <integer> $controlNumber número de control do gnuteca
     * @return <stdclass> o objeto do livro
     */
    public function getGoogleBookByControlNumber($controlNumber)
    {
        $busMaterial    = $this->MIOLO->getBusiness( $this->module, "BusMaterial");
        $isbn           = $busMaterial->getContentTag($controlNumber, MARC_ISBN_TAG);

        if ( $isbn )
        {
            $book = $this->getGoogleBook($isbn, false);
            
            if ( $book)
            {
                $book->isbn = $isbn;
                $book->controlNumber = $controlNumber;
            }
            
            return $book;
        }

        return null;
    }

    /**
     * Busca livro no google
     *
     * @param <boolean> $toObject retorna como objetou/array
     * @param <boolean> $getControlNumber retornar números de controle do gnuteca.
     * @param <boolean> $parseLinkInformation trata informações dos links
     * @return <array> array de objetos ou array para grid
     */
    public function searchGoogleBook($toObject = FALSE, $getControlNumber = true , $parseLinkInformation= true)
    {
        $url = self::GB_URL;

        $query = $this->query;

        if ( $this->isbn )
        {
            $query .= '+isbn:'.$this->isbn;
        }

        if ( $this->issn )
        {
            $query .= '+issn:'.$this->issn;
        }

        if ( $this->titleS )
        {
            $query .= '+intitle:'.$this->titleS;
        }

        if ( $this->author )
        {
            $query .= '+inauthor:'. $this->author;
        }

        if ( $this->publisher )
        {
            $query .= '+inpublisher:'.$this->publisher;
        }

        if ( $this->limit )
        {
            $url .= '&max-results='.$this->limit;
        }
  
        if ( $this->language )
        {
            $url .= "&lr={$this->language}";
        }

        $url .= '&q='.$query;

        $url = str_replace(" ", "+",$url); //troca espaço por +, para funcionar na url de busca
        //$url = iconv("ISO-8859-1", "UTF-8", $url); //converte para a codificação do google

        //?hl=pt-BR&tbs=bks:1,bkv:p&tbo=p&q=a+b+isbn:isbn+issn:issn+intitle:titulo+inauthor:autor+inpublisher:editora&num=10&lr=lang_pt

        try
        {
            //obtem feed com os dados retornados do google
            $data = file_get_contents($url);

            //troca informações de forma que o SimpleXML suporte as string
            $data = str_replace('gbs:', 'gbs_', $data);
            $data = str_replace('dc:', 'dc_', $data);
            $data = str_replace("http://schemas.google.com/books/2008/","", "$data");
            $data = str_replace("http://schemas.google.com/books/2008#","", "$data");
            //retira informações desnecessárias dos dados
            $data = str_replace("http://www.google.com/books/feeds/volumes/","", "$data");

            //cria um xml com os dados obtidos
            $xml  = new SimpleXMLElement($data);
            //guarda o xml no bus para uso posterior
            $this->xml = $xml;
        }
        catch ( Exception $e )
        {
            throw new Exception( _M('Impossível conectar ao google livros! Contate o administrador para resolução do problema!'));
            return false;
        }

        $count = count($xml->entry);
        $entry = $xml->entry;

        foreach ( $entry as $line => $gBook)
        {
            $book = new stdClass();

            foreach ( $gBook->children() as $item => $data )
            {
                $item = "$item";
                $attributes = $data->attributes();

                $count = count($attributes);

                if ( $count > 0)
                {
                    foreach( $attributes as $attribute => $value)
                    {
                        $attribute  = "$attribute";
                        //$value      = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $value);

                        if ( $item == 'link' )
                        {
                            if ( $attribute == 'rel')
                            {
                                //tira informação desnecessária do location
                                $location = $value;
                                //http://www.google.com/books/feeds/volumes/F
                            }

                            if ( $attribute == 'href')
                            {
                                $location = "$location";
                                //guarda o thumbnail (link para imagem da capa) em uma propriedade do livro
                                if ( $location == 'thumbnail')
                                {
                                    $book->thumbnail = $value;
                                }
                                else  if ($location != 'self')
                                {
                                    $book->link[$this->translate($location)] = $value;
                                }
                            }
                        }
                        else if ( $attribute == 'value')
                        {
                            //embebe and others
                            if ( $value == self::GB_EMBEDDABLE_TRUE || $value == self::GB_VIEW_ALL_PAGES)
                            {
                                $value = DB_TRUE;
                            }
                            else if ( $value == self::GB_EMBEDDABLE_FALSE || $value ==self::GB_VIEW_NO_PAGES)
                            {
                                $value = DB_FALSE;
                            }
                            else if ( $value == self::GB_VIEW_PARTIAL )
                            {
                                $value = 'p';
                            }

                            $book->$item = $value;
                        }
                        else
                        {
                            //faz a tradução de algumas strings
                            $book->$item->$attribute = $this->translate($value);
                        }
                    }
                }
                else
                {
                    $exists = $book->$item;

                    $i = 0;

                    //vai tradando dados repetidos, para transformar em dados únicos
                    while ( $exists )
                    {
                        $item = str_replace($i-1,'', $item);
                        $item = $item.$i;
                        $i++;
                        $exists = $book->$item;
                    }

                    //converte para a codificação do gnuteca
                    //$data = iconv("UTF-8", "ISO-8859-1//TRANSLIT", $data);
                    $data = $this->translate($data);
                    $book->$item = count($data)>0 ? $data : "$data";
                }

            }

            //encontra o isbn e retira identificador desnecesários
            $book       = self::findISBN($book);
            $isbns[]    = $book->isbn;

            if ( $this->searchFormat )
            {
                $book->marc = self::bookToMarc($book);
                //obtem formato de pesquisa
                $book->searchFormat = GMaterialitem::getFormatedData( $book->marc, $this->searchFormat ,false);
            }

            if ( $parseLinkInformation)
            {
                $book = self::parseLinks($book);
            }

            $books[] = $book;
        }

        if ( $getControlNumber && $books)
        {
            $books = $this->getControlNumber($books, $isbns);
        }

        //converte para array caso seja necessário
        if ( !$toObject && $books)
        {
            $books = self::booksToArray($books);
        }

        return $books;
    }

    /**
     * Converte um objeto do gogole para um array de GMaterialItem
     *
     * @param <stdClass> $book google Book
     * @return <array> array de GMaterialItem
     */
    public static function bookToMarc($book)
    {
        $authorTag      = explode( '.', MARC_AUTHOR_TAG);
        $titleTag       = explode( '.', MARC_TITLE_TAG);
        $dateTag        = explode( '.', MARC_PUBLICATION_DATE_TAG);
        $isbnTag        = explode( '.', MARC_ISBN_TAG);
        $subjectTag     = explode( '.', MARC_SUBJECT_TAG);
        $editorTag      = explode( '.', MARC_EDITOR_TAG);
        $noteTag        = explode( '.', MARC_GERAL_NOTE_TAG);
        $extensionTag   = explode( '.', MARC_EXTENSION_TAG);
        $languageTag    = explode( '.', MARC_LANGUAGE_TAG);
        $linkTag        = explode( '.' , MARC_NAME_SERVER );

        $marc[] = new GMaterialItem( $authorTag[0], $authorTag[1],$book->dc_creator);
        $marc[] = new GMaterialItem( $titleTag[0],$titleTag[1],$book->dc_title);
        $marc[] = new GMaterialItem( $dateTag[0],$dateTag[1],$book->dc_date);
        $marc[] = new GMaterialItem( $isbnTag[0], $isbnTag[1],$book->isbn);
        $marc[] = new GMaterialItem( $subjectTag[0], $subjectTag[1],$book->dc_subject);
        $marc[] = new GMaterialItem( $editorTag[0],$editorTag[1],$book->dc_publisher);
        $marc[] = new GMaterialItem( $noteTag[0], $noteTag[1], $book->dc_description);
        $marc[] = new GMaterialItem( $extensionTag[0], $extensionTag[1],$book->dc_format);
        $marc[] = new GMaterialItem( $languageTag[0], $languageTag[1], $book->dc_language );
        $marc[] = new GMaterialItem( $linkTag[0], $linkTag[1], $book->thumbnail);
        $marc[] = new GMaterialItem( $linkTag[0], $linkTag[1], $book->link['info'], 1);
        $marc[] = new GMaterialItem( $linkTag[0], $linkTag[1], $book->link['review'], 2);
        $marc[] = new GMaterialItem( $linkTag[0], $linkTag[1], $book->link['annotation'], 3);
        $marc[] = new GMaterialItem( $linkTag[0], $linkTag[1], $book->link['alternate'].'', 4);

        return $marc;
    }

    /**
     * Troca os links por objetos ancora html (com os links)
     *
     * @param <object> $googleBook
     * @return <object>
     */
    public static function parseLinks($googleBook)
    {
        $links = $googleBook->link;

        $googleBook->link = ''; //limpa $info

        foreach ( $links as $type => $link)
        {
            $googleBook->link .= "<a href='{$link}' target='_blank'>{$type}</a><br/>";
        }

        return $googleBook;

   }

    /**
     * Localiza os números de controle do gnuteca para um array de livros do google
     * @param <array> $books array de livros do google
     * @param <type> $isbns
     * @return <array> array de livros do google
     */
    public function getControlNumber( $books, $isbns)
    {
        $busMaterial    = $this->MIOLO->getBusiness( $this->module, "BusMaterial");
        $controlNumbers = $busMaterial->getControlNumberByISBN($isbns);

        if ( is_array($controlNumbers) && is_array($books) )
        {
            foreach ( $books as $line => $book)
            {
                if (  $controlNumbers[$book->isbn] )
                {
                    $books[$line]->controlNumber = $controlNumbers[$book->isbn];
                }
            }
        }

        return $books;
    }

    /**
     * Converte um livro google para um array
     */
    public static function booksToArray($googleBooks)
    {
        if (is_array($googleBooks))
        {
            foreach ( $googleBooks as $line => $book)
            {
                $bookArray = array();
                $bookArray[] = $line;
                $bookArray[] = $book->thumbnail.'';
                $bookArray[] = $book->id.'';
                $bookArray[] = $book->controlNumber.'';
                $bookArray[] = $book->updated.'';
                $bookArray[] = ''; //$book->category.'';
                $bookArray[] = ''; //$book->title.'';
                $bookArray[] = $book->link.'';
                $bookArray[] = $book->gbs_embeddability.'';
                $bookArray[] = $book->gbs_openAccess.'';
                $bookArray[] = $book->gbs_viewability.'';

                //só concatena o subtitulo caso tiver
                if ( strlen($book->dc_title0) > 0 )
                {
                    $bookArray[] = $book->dc_title . ": " . $book->dc_title0;
                }
                else
                {
                    $bookArray[] = $book->dc_title;
                }

                $bookArray[] = $book->dc_creator.'';
                $bookArray[] = $book->dc_publisher.'';
                $bookArray[] = $book->dc_subject.'';
                $bookArray[] = $book->dc_date.'';
                $bookArray[] = $book->dc_description.'';
                $bookArray[] = $book->dc_format . '- ' . $book->dc_format0;
                $bookArray[] = $book->isbn.'';
                $bookArray[] = $book->dc_identifier. "\n". $book->dc_identifier0 ."\n" .$book->dc_identifier1;
                $bookArray[] = $book->searchFormat;
                $bookArray[] = $book->marc;

                $googleBooks[$line] = $bookArray;
            }
        }

        return $googleBooks;
    }

    /**
     *
     * Busca o ISBN do livro registrando no objeto.
     * Passa por todos dc_identifier.
     *
     * @param <stdClass> $googleBook
     * @return <stdClass> googleBook
     */
    public static function findISBN($googleBook)
    {
        $book = $googleBook;

        $variable = 'dc_identifier';
        $toSeek   = $variable;
        $counter  = '0';
        $doSearch = true;

        while ($doSearch)
        {
            $value = $book->$toSeek;

            //caso não tenha encontrado valor, é porque é o último identificador
            if ( !$value )
            {
                break;
            }

            if ( stripos($value,'ISBN') !== false )
            {
                //retira o identificador, e adicionad o isbn
                unset($book->$toSeek);
                $book->isbn = str_replace('ISBN:', '', $value);
                $doSearch = false;
                break;
            }
            else
            {
                //se o identificador for igual ao id do livro, não existe necessidade de ele estar , pois é informação duplicada
                if ( $value == $book->id )
                {
                    unset($book->$toSeek);
                }

                $doSearch = true;
                $toSeek   = $variable . $counter;
                $counter ++;
            }
        }

        return $book;
    }

    /**
     * Encontra a url da capa para o isbn atual
     *
     * @param string $isbn
     * @return GString
     */
    public function getCoverUrl( $isbn )
    {
        $this->isbn = $isbn;
        $result = $this->searchGoogleBook(true, false, false);
        $coverUrl = new GString( $result[0]->thumbnail[0].'' );
        $coverUrl->replace('&zoom=5',''); //tira o zoom para obter a imagem maior

        return $coverUrl;
    }

    /**
     * Obtem a capa do livro do servidor do google. Retorna o stream binário da capa.
     *
     * Desconsidera imagem com dizeres "Imagem não disponível"
     * Desconsidera imagens pela metade altura = 92.
     *
     * Tenta obter a miniatura caso não encontre a imagem de alta qualidade.
     *
     * @param string $isbn
     * @return binary
     */
    public function getCover( $isbn )
    {
        if ( !$isbn )
        {
            throw new Exception(  _M('É necessário um ISBN para importar a capa!','gnuteca3') );
        }
        
        $coverUrl = $this->getCoverUrl($isbn);

        if ( !$coverUrl )
        {
            throw new Exception( _M('Isbn @1 não possui capa disponível!','gnuteca3',$isbn) );
        }
        else
        {
            //obtem arquivo externo
            $img = file_get_contents( $coverUrl );

            if ( !$img )
            {
                throw new Exception( _M('Não foi possível encontrar a capa para o ISBN @1. Capa indísponível.','gnuteca3', $isbn ) ) ;
            }
            else
            {
                $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
                //usa png como extensão temporária e salva em um arquivo temporaŕio para testes
                $fileName = BusinessGnuteca3BusFile::getValidFilename($isbn);
                //caminho temporário para testes
                $path = BusinessGnuteca3BusFile::getAbsoluteFilePath('tmp', $fileName, 'png');
                $ok = $busFile->streamToFile( $img, $path, null , true );

                if ( !$ok )
                {
                    throw new Exception( _M('Impossível salvar imagem no caminho temporário "@1"','gnuteca3',$path) ) ;
                }

                $imageInfo = getimagesize($path);
                $imageInfo['height'] = $imageInfo[1];
                //após obter a altura pode remover o arquivo temporário
                unlink($path);

                //obtem imagem padrão de capa não disponível
                $urlImageNotAvailable = BusinessGnuteca3BusFile::getAbsoluteFilePath('cover', 'imageNotAvailable', 'png');
                $imageNotAvailable = file_get_contents( $urlImageNotAvailable );

                //caso existe imageNotAvailable no servidor e a imagem atual for igual a ela desconsidera
                if ( $imageNotAvailable )
                {
                    if ( $img == $imageNotAvailable )
                    {
                        throw new Exception( _M('Capa não disponível para o ISBN @1.','gnuteca3', $isbn ) ) ;
                    }
                }

                //caso a altura da imagem seja 92 quer dizer que é uma imagem sem direitos autorais
                // e que a visualização não é completa, então tentaremos baixar a versão com menos zoom
                if ( $imageInfo['height'] == 92 )
                {
                    //monta url da imagem menor
                    $coverUrl = $coverUrl . '&zoom=1';
                    $img = file_get_contents( $coverUrl );

                    if ( !$img )
                    {
                        throw new Exception( _M('Não foi possível encontrar a capa nem a miniatura para o ISBN @1.','gnuteca3', $isbn ) ) ;
                    }
                }
            }
        }

        return $img;
    }
}
?>