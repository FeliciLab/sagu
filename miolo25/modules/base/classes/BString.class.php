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
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 01/07/2011
 *
 **/
class BString
{
    private $string;
    private $encoding = 'UTF-8';
    
    /**
     * Constroi objeto
     *
     * @param string $string Conteudo inicial
     * @param string $encoding Codificacao
     */
    public function __construct($string = null, $encoding = null)
    {
        // Caso nao tenha passado codificacao, obtem da constante
        if ( $encoding == null && defined('BASE_ENCODING') )
        {
            $encoding = BASE_ENCODING;
        }
        
        // Define a codificação somente se ela foi passada por parâmetro ou se está definida a constante BASE_ENCODING, caso contrário usa UTF-8.
        if ( $encoding )
        {
            $this->setEncoding($encoding);
        }
        
        $this->setString($string);
    }

    /**
     * Contrutor estático usado para que possa se utilizar
     * o construtor e chamar a função necessária na mesma linha.
     *
     * @param string $string
     * @return BString
     *
     * @example BString::construct( $string )->generate() = retorna a string em formato de usuário
     */
    public static function construct( $string, $encoding = null )
    {
        return new BString($string, $encoding);
    }

    
    /**
     * Define a string
     * 
     * @param $string
     */
    public function setString($string)
    {
        $this->string = $this->_convert( $string );
    }

    /**
     * Retorna a string na codificação necessária
     *
     * @param string $string
     * @return string retorna a string na codificação necessária
     */
    protected function _convert( $string )
    {
        $enc = BString::detectEncoding($string);

        if ( $enc == $this->getEncoding() )
        {
            return $string;
        }
        else
        {
            return iconv($enc, $this->getEncoding(), $string );
        }

        return $string;
    }

    /**
     * Adiciona algum texto a string.
     *
     * Passa pela função de conversão para garantir a string esteja na codificação utilizada.
     *
     * @param string $string texto a ser adicionado
     */
    public function append( $string )
    {
        $this->string .= $this->_convert( $string ) ;
    }

    /**
     * Troca um contéudo por outro, na string atual.
     * Além disso retorna a nova string
     *
     * @param string $search conteúdo original, a buscar
     * @param string $replace novo conteúdo a subistituir
     * @param string retorna a nova string
     */
    public function replace( $search, $replace )
    {
        $this->string = str_replace($search, $replace, $this->string );
        
        return $this;
    }

    /**
     * Converte o texto para minusculas
     *
     * @return BString
     */
    public function toLower()
    {
        $this->string = mb_strtolower( $this->string,$this->getEncoding() );

        return $this;
    }

    /**
     * Converte o texto para maisculas
     *
     * @return BString
     */
    public function toUpper()
    {
        $this->string = mb_strtoupper( $this->string ,$this->getEncoding() );

        return $this;
    }

    /**
     * Retorna o caracter solicitado pelo parametro index
     *
     * @param integer $index indice do caracter a obter
     * @return char retorna o caracter solicitado
     */
    public function charAt($index)
    {
        return $this->string[ $index ];
    }
   
    /**
     * Obtém a string
     * 
     * @return dia
     */
    public function getString()
    {
    	return $this->string;
    }

    /**
     * Seta a codificação
     *
     * @param $encoding
     */
    public function setEncoding($encoding)
    {
    	$this->encoding = $encoding;
    }

    /**
     * Obtém a codificação
     *
     * @return dia
     */
    public function getEncoding()
    {
    	return $this->encoding;
    }

    /**
     * Verifica se a string é UTF8
     *
     * @param string o texto a verificar
     * @return boolean
     */
    public static function isUTF8( $string )
    {
        //return mb_detect_encoding($this->getString(), 'UTF-8', true);
        //return iconv('ISO-8859-1', 'UTF-8', iconv('UTF-8', 'ISO-8859-1', $string ) ) == $string;
        return BString::checkEncoding($string, 'UTF-8');
    }

    /**
     * Verifica se a string é da codificação passada
     *
     * @param string $string
     * @param string $enc
     * @return boolean
     */
    public static function checkEncoding( $string , $enc  )
    {
        return BString::detectEncoding( $string ) == $enc;
    }

    /**
     * Retorna a codifificação da string
     *
     * @param string $string
     * @return string retorna a codifificação da string
     */
    public static function detectEncoding($string)
    {
        $encList = array('UTF-8','ISO-8859-1');

        if ( is_array( $encList ) )
        {
            foreach ( $encList as $line => $enc)
            {
                if ( $enc == 'UTF-8' )
                {
                    if ( iconv('ISO-8859-1', 'UTF-8', iconv('UTF-8', 'ISO-8859-1', $string ) ) === $string )
                    {
                        return 'UTF-8';
                    }
                }
                else
                {
                    if ( iconv('UTF-8', $enc, iconv( $enc, 'UTF-8', $string ) ) === $string )
                    {
                        return $enc;
                    }
                }
            }
        }
    }

    /**
     * Retorna o tamnho da string
     *
     * @return tamanho da string
     */
    public function length()
    {
        return mb_strlen( $this->getString() , $this->getEncoding() );
    }

    /**
     * Remove os espaços no inicio e fim do texto
     * 
     * @return BString
     */
    public function trim()
    {
        $this->string = trim($this->string);
        return $this;
    }

    /**
     * Converte a string para caracteres ASCII.
     * Retira acentos e outros caracteres especificos.
     *
     * @return BString
     */
    public function toASCII()
    {
        $this->trim(); //remove espaços
        $content = $this->string;
        $content = eregi_replace("[ÁÀÂÃÄáàâãä]", "A", $content);
        $content = eregi_replace("[ÉÈÊËéèêë]",   "E", $content);
        $content = eregi_replace("[ÍÌÎÏíìîï]",   "I", $content);
        $content = eregi_replace("[ÓÒÔÕÖóòôõö]", "O", $content);
        $content = eregi_replace("[ÚÙÛÜúùûü]",   "U", $content);
        $content = eregi_replace("[Ññ]",         "N", $content);
        $content = eregi_replace("[Çç]",         "C", $content);
        $content = eregi_replace("\+",           "",  $content);

        $this->string = $content;

        $this->toUpper(); //coloca tudo em maisculas

        return $this;
    }

    /**
     * Corta a string de um ponto inicial, considerando ou não um tamanho
     *
     * @param integer $start posição inicial
     * @param integer $length quantidade de caracteres até o corte / tamanho
     * @return BString
     */
    public function sub($start, $length)
    {
        $this->string = mb_substr( $this->string, $start, $length, $this->getEncoding() );

        return $this;
    }
    
    /**
     * Explode a string retornando um array
     * 
     * @param string $delimiter delimitador
     * @return array array com a string explodida
     */
    public function explode( $delimiter )
    {
        return explode( $delimiter, $this->string );
    }
         
    /**
     * Função chamada automaticamente pelo PHP quando precisa converter objeto para String
     * 
     * @return a data no formato do usuário
     */
    public function __toString()
    {
        //$this->string colocado entre "" para garantir que a string é realmente uma string
        return $this->string;
    }
    
    /**
     * Função que o miolo chama automaticamente, convertendo o objeto para string
     * 
     * @return a data no formato do usuário
     */
    public function generate()
    {
        return $this->getString();
    }
}
?>
