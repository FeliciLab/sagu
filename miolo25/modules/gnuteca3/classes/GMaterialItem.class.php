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
 * GMaterialItem - classe de determina campos de um cadastro de material
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
  *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
  *
 * @since
 * Class created on 03/12/2008
 *
 **/
$MIOLO->getBusiness($module, 'BusMaterial');

class GMaterialItem
{
    public $controlNumber;
    public $fieldid;
    public $subfieldid;
    public $line;
    public $indicator1;
    public $indicator2;
    public $content;
    public $searchContent; //uso não recomendado, deixe para o BusMaterial controlar
    public $prefix;
    public $suffix;
    
    public function __construct( $fieldId, $subfieldId, $content, $line = 0 )
    {
        $this->fieldid      = $fieldId;
        $this->subfieldid   = $subfieldId;
        $this->line         = $line;
        $this->setContent($content);
    }

    /**
     * Função que define e trata o conteúdo e indicadores
     *
     * @param string $content
     *
     */
    public function setContent($content)
    {
        //trata os indicares
        $this->indicator1       = str_replace('_', '', $this->indicator1);
        $this->indicator2       = str_replace('_', '', $this->indicator2);
        //trata campo e subcampo
        $this->fieldid          = trim( $this->fieldid );
        $this->subfieldid       = trim( $this->subfieldid );

        //$MIOLO                  = MIOLO::getInstance();
        //$busMaterial            = $MIOLO->getBusiness('gnuteca3', 'BusMaterial');
        $content                = BusinessGnuteca3BusMaterial::converteConteudoImportacao( $this->fieldid,$this->subfieldid, $content );
        $this->content          = trim($content);
    }

    /**
     * Função que verifica integradide minima para inserção na base
     *
     * @return boolean
     */
    public function check()
    {
        return ( $this->fieldid && $this->subfieldid && $this->content );
    }

    /**
     * Formata um array de GMaterialItem usando um formato de pesquisa
     *
     * @param <array> $data array de GMaterialItem
     * @param <integer> $searchFormat
     * @param <boolea> $detail se é para detalhes usa formato de detalhes, caso contrário de busca
     * @return <string> a string pronta pra uso
     */
    public static function getFormatedData( $data , $searchFormat , $detail = true )
    {
        if ( !$searchFormat )
        {
            return false;
        }

        $MIOLO              = MIOLO::getInstance();
        $busSearchFormat    = $MIOLO->getBusiness('gnuteca3', 'BusSearchFormat');
        $format             = $busSearchFormat->getSearchFormat($searchFormat,true);
        $gFunction          = new GFunction();

        if ( is_array ( $data ) )
        {
            foreach ( $data as $line => $materialItem)
            {
                $tag = "\${$materialItem->fieldid}.{$materialItem->subfieldid}";

                //verifica se o valor já existe
                $value = $gFunction->getVariable($tag);

                // caso exista adiciona uma linha nova
                if ( $value )
                {
                    $value .= "\n";
                }

                $value .= $materialItem->content;

                $gFunction->setVariable( $tag , $value );
            }
        }

        //define o formato, busca ou detalhe
        if ( $detail )
        {
            $format = str_replace("\n", '', $format->searchPresentationFormat[0]->detailFormat);
        }
        else
        {
            $format = str_replace("\n", '', $format->searchPresentationFormat[0]->searchFormat);
        }

        $gFunction->setVariable('$LN', "\n");

        return str_replace("\n", '<br>', $gFunction->interpret( $format, true ) );
    }

    /**
     * Converte um item GMaterialItem para StdClass útil para guardar na sessão
     *
     * @return stdClass
     */
    public function toStdClass( )
    {
        $stdClass = new stdClass();
        $stdClass->content          = $this->content;
        $stdClass->searchContent    = $this->searchContent;;
        $stdClass->indicator1       = $this->indicator1;
        $stdClass->indicator2       = $this->indicator2;
        $stdClass->fieldid          = $this->fieldid;
        $stdClass->subfieldid       = $this->subfieldid;
        $stdClass->line             = $this->line;

        return $stdClass;
    }

    /**
     * Retorna um novo objeto GMaterialItem passando um stdClass.
     *
     * @return GMaterialItem
     *
     */
    public static function fromStdClass( $stdClass )
    {
        $gMaterialItem = new GMaterialItem();
        $gMaterialItem->content          = $stdClass->content;
        $gMaterialItem->searchContent    = $stdClass->searchContent;;
        $gMaterialItem->indicator1       = $stdClass->indicator1;
        $gMaterialItem->indicator2       = $stdClass->indicator2;
        $gMaterialItem->fieldid          = $stdClass->fieldid;
        $gMaterialItem->subfieldid       = $stdClass->subfieldid;
        $gMaterialItem->line             = $stdClass->line;
        $gMaterialItem->prefix           = $stdClass->prefix;
        $gMaterialItem->suffix           = $stdClass->suffix;

        return $gMaterialItem;
    }
}
?>