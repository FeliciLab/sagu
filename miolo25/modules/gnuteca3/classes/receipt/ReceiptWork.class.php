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
 * Class Generic Gnuteca Receipt;
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 28/06/2010
 *
 **/

class ReceiptWork extends GFunction
{
    public $itemNumber; // $ITEM_NUMBER
    public $author; //$MATERIAL_AUTHOR
    public $title; //$MATERIAL_TITLE
    public $model;
    public $content;
    public $businessMaterial;

    public function __construct($data )
    {
        parent::__construct();
        $this->setVariable('$LN', "\r\n");
        $this->setVariable('$SP', " ");
        $this->setData($data);
        $MIOLO = MIOLO::getInstance();
        $this->businessMaterial = $MIOLO->getBusiness('gnuteca3', 'BusMaterial');
    }

    public function generate()
    {
        $this->parseData();
        $result = $this->interpret( $this->model );
        $this->content = $result;

        return $this->content;
    }

    /** Define the data of receipt, it is diferent for each receipt
     *
     * @param <stdclass> $data Define the data of receipt
     */
    public function setData($data)
    {
        $array = (Array) $data;

        foreach ( $array as $line => $info )
        {
            $this->$line = $info;
        }
    }

    /**
     * Return the data of receipt
     *
     * @return <stdClasss> the data of receipt
     *
     */
    public function getData()
    {
        return (Object) get_object_vars($this);
    }

    /**
     * Parse the data of receiptWork
     */
    public function parseData()
    {
        if ( $this->itemNumber )
        {
            $this->author = $this->getAuthor();
            $this->title  = $this->getTitle();

            $this->setVariable('$ITEM_NUMBER',       $this->itemNumber);
            $this->setVariable('$MATERIAL_TITLE',    strlen($this->title)  ? $this->title : ' -- ');
            $this->setVariable('$MATERIAL_AUTHOR',   strlen($this->author) ? $this->author: ' -- ');
        }

        $this->setVariable('$DATE',  date('d/m/Y'));
        $this->setVariable('$TIME',  date('H:i'));

    }

    public function getItemNumber()
    {
        return $this->itemNumber;
    }

    public function setItemNumber($itemNumber)
    {
        $this->itemNumber = $itemNumber;
    }

    public function getTitle()
    {
        if ( !$this->title)
        {
            $this->title = $this->businessMaterial->getContentByItemNumber( $this->itemNumber, MARC_TITLE_TAG);
        }

        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getAuthor()
    {
        if ( !$this->author)
        {
            $this->author = $this->businessMaterial->getContentByItemNumber( $this->itemNumber, MARC_AUTHOR_TAG);
        }

        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     *Define the model of ReceiptWork
     * @param <string> $model Define the model of ReceiptWork
     */
    public function setModel( $model )
    {
        $this->model = $model;
    }

    /**
     * Return the model of receito work
     * @return <string> the model of receito work
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Define the generated content
     * @param <string> $content
     */
    public function setContent( $content )
    {
        $this->content = $content;
    }

    /**
     * Return the generated content
     * @return <string> the generated content
     */
    public function getContent( )
    {
        return $$this->content;
    }
}
?>
