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
 *
 * @since
 * Class created on 28/06/2010
 *
 **/

$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/ReceiptWork.class.php', 'gnuteca3');

class Receipt extends GFunction
{
    /**
     * the resultant content
     * @var string
     */
    public $content;
    /**
     * if is to post mail
     * @var boolean
     */
    public $isPostable;
    // 
    /**
     * if is to print
     * @var boolean
     */
    public $isPrintable;
    /**
     * relation of varios receipts
     * @var object
     */
    public $itens;
    /** 
     * the id of person of this receipt
     * $USER_CODE
     * @var integer
     */
    public $personId;
    /**
     * the name of person of this receipt
     * @var string
     */
    public $personName;
    /**
     * O campo login da Pessoa
     * @var string 
     */
    public $login;
    
    /**
     * the email of person of this receipt
     * $USER_NAME
     * 
     * @var string
     */
    public $email;
    /**
     * the id of LibraryUnit
     * @var integer
     */
    public $libraryUnitId;
    /**
     * the name of libraryUnit
     * $LIBRARY_UNIT_DESCRIPTION
     * 
     * @var string
     */
    public $libraryName;
    /**
     * bus material used to get author and title
     * @var BusMaterial 
     */
    protected $busMaterial;
    /**
     * path to store the pdf
     * @var string
     */
    public $storagePath;
    /**
     * GPDF object
     * @var GPDF
     */
    public $pdf;
    /**
     * the receipt generate date
     * @var string
     */
    public $date;
    /**
     * the receipt generate time
     * @var string
     */
    public $time;
    /**
     * the system operator
     * @var string
     */
    public $operator;
    /**
     * operação (e tipo do recibo)
     * @var string
     */
    public $operation;
    /**
     * model to generate the receipt
     * @var string
     */
    public $model;
    /**
     * the model for detail (itens)
     * @var string
     */
    public $modelDetail;
    /**
     * modelo de assunto de email
     * @var string
     */
    public $modelMailSubject;
    /**
     * modelo de conteúdo de email
     * @var string
     */
    public $modelMailContent;
    /**
     * mensagem a ser passada para a operação geral
     * @var string
     */
    public $message;

    public function __construct($data, $isPostable = false, $isPrintable = false )
    {
        parent::__construct();
        $MIOLO  = MIOLO::getInstance();
        $this->setVariable('$LN', "\r\n");
        $this->setVariable('$SP', " ");
        $this->setData($data);
        $this->storagePath = $MIOLO->getAbsolutePath( $MIOLO->getConf('options.receiptPdfFilePath'), MIOLO::getCurrentModule() );
        $this->mail = new GMail();
        //get the default business
        $this->isPrintable = $isPrintable;
        $this->isPostable  = $isPostable;
        $this->businessMaterial = $MIOLO->getBusiness('gnuteca3', 'BusMaterial');
    }

    /**
     * Define the data of receipt, it is diferent for each receipt
     *
     * @param stdclass $data Define the data of receipt
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
     * @return stdClasss the data of receipt
     *
     */
    public function getData()
    {
        return (Object) get_object_vars($this);
    }

    /**
     * Add and item to item list
     * @param type $item Add and item to item list
     */
    public function addItem($item)
    {
        $this->itens[] = $item;
    }

    /**
     * Define all itens
     *
     * @param array $itens Define all itens
     *
     */
    public function setItens(Array $itens)
    {
        $this->itens = $itens;
    }

    /**
     * Return all itens
     *
     * @return array Return all itens
     */
    public function getItens()
    {
        return $this->itens;
    }

    /**
     * Pass each item set it model, calling it parseData and making it generate.
     *
     * It set the generated string to $WORKS variable
     *
     * @return <string> the generate string;
     */
    public function parseItemData()
    {
        $itens = $this->getItens();

        if ( is_array( $itens ) )
        {
            foreach ( $itens as $line => $item )
            {
                $item->parseData();

                //set the model if needed
                if ( !$item->getModel() )
                {
                    $item->setModel( $this->getModelDetail() );
                }

                $generate .= $item->generate();
            }
        }

        $this->setVariable('$WORKS', $generate);

        return $generate;
    }

    /**
     * Parse the data of receipt, finding needed information in database
     * and set the variables
     */
    public function parseData()
    {
        $MIOLO     = MIOLO::getInstance();

        /*
         * these are security functions, this data has to come with object
         * if don't come, the class search to this.
         * Do the default variables too (operator, date, time)
         */

        $this->setVariable('$USER_CODE', $this->personId);

        //obtem informações básicas
        if ( !$this->personName || !$this->login)
        {
            $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
            $person    = $busPerson->getBasicPersonInformations( $this->personId );
            
            $this->personName = $person->name;
            $this->email      = $person->email;
            $this->login = $person->login;
        }

        $this->setVariable('$USER_NAME', $this->personName);
        $this->setVariable('$USER_LOGIN', $this->login);

        $this->operator = GOperator::getOperatorName(GOperator::getOperatorId()); //obtém o nome do operador

        $this->setVariable('$OPERATOR',  $this->operator);
        $this->setVariable('$OPERATOR_ID',  GOperator::getOperatorId());

        if ( !$this->libraryName )
        {
            $busLibrary = $MIOLO->getBusiness('gnuteca3', 'BusLibraryUnit');
            $this->libraryName = $busLibrary->getLibraryName( $this->libraryUnitId );
        }

        $this->setVariable('$LIBRARY_UNIT_DESCRIPTION', $this->libraryName);
        $this->setVariable('$DATE',  date('d/m/Y'));
        $this->setVariable('$TIME',  date('H:i'));
        $this->setVariable('$RECEIPT_FOOTER', $this->getReceiptHash());
        $this->setVariable('$OPERATION', $this->operation);

        $itens = $this->getItens();

        if ( $itens )
        {
            $this->parseItemData();
        }
    }

    /**
     * Return the generate content
     *
     * @return string the generate content
     *
     */
    public function getContent()
    {
        return $this->content;
    }


    /**
     * Define the content of receipt
     *
     * @param string $content the content of receipt
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Define if receipt if printable
     * @param boolean $isPrintable
     */
    public function setIsPrintable($isPrintable)
    {
        $this->isPrintable = $isPrintable;
    }

    /**
     * Return if receipt is printable
     *
     * @return boolean if receipt is printable
     *
     */
    public function getIsPrintable()
    {
        return $this->isPrintable;
    }

    /**
     * Define if receipt can be postable (mailed)
     *
     * @param boolean $isPostable if receipt can be postable (mailed)
     *
     */
    public function setIsPostable($isPostable)
    {
        $this->isPostable = $isPostable;
    }

    /**
     * Return if receipt can be postable (mailed)
     *
     * @return boolean if receipt can be postable (mailed)
     *
     */
    public function getIsPostable()
    {
        return $this->isPostable;
    }

    /**
     * Define the receipt model, it is a static definition
     *
     * @param string $model
     * @param string $detailModel
     */
    public function setModel( $model, $detailModel , $modelMailSubject, $modelMailContent )
    {
        $this->model            = $model;
        $this->modelDetail      = $detailModel;
        $this->modelMailSubject = $modelMailSubject;
        $this->modelMailContent = $modelMailContent;
    }

    /**
     * Return the static model of this type of receipt
     *
     * @return string the static model of this type of receipt
     *
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Return the static model of detailof this type of receipt
     *
     * @return string  the static model of this type of receipt
     */
    public function getModelDetail()
    {
        return $this->modelDetail;
    }

    /**
     * Gera o hash para o recibo
     *
     * @param integer $personId
     * @param object $itensObject
     * @return hash md5
     */
    public function getReceiptHash()
    {
        $stringHash = md5(HASH_KEY.$this->personId.$this->libraryUnitId);

        foreach ($this->itens as $line => $obj)
        {
            $stringHash.= $obj->itemNumber.$obj->loanDate ? $obj->loanDate : $obj->returnForecastDate;
        }

        return md5($stringHash);
    }

    /**
     * Generate the string of receipt
     *
     * @return string the generated string of receipt
     */
    public function generate()
    {
        //caso precise gerar o recibo de alguma outra forma que não seja pelo gnuteca receipt pode pegar os modelos por aqui
        $this->parseData();
        $result = $this->interpret( $this->model );

        //Como o GnutecaFunction trabalha na maioria dos casos com a formatação apra HTML e neste caso precisamos fazer uma formatação PDF, não podemos deixar a tag <\br> no conteúdo porque vai zoar os recibos
        $result = str_replace("<br/>", "\n", $result);
        $this->content = $result;

        if ( $this->isPostable )
        {
            $this->send();
        }

        if ( $this->isPrintable )
        {
            return $result;
        }
        else
        {
            return null;
        }
    }

    /**
     * Return the generated string of receipt
     *
     * @return string Return the generated string of receipt
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * Definr a message to receipt
     *
     * @param string $message the message of receipt, will be colected in operation
     */
    public function addMessage( $message )
    {
        $this->message = $message;
    }

    /**
     * Return the message defined in receipt
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     *
     * Create a pdf of the receipt
     *
     */
    public function createPDF()
    {
        $personNameToFile = str_replace(' ', '', ucwords( strtolower( iconv('UTF-8', 'ASCII//TRANSLIT', $this->personName) ) ) ) ;

        $file = $this->storagePath . "/gnutecaReceipt_".$this->personId.'_'.$personNameToFile.'_'. md5(time()+ microtime()) .".pdf";
        $file = BusinessGnuteca3BusFile::getAbsoluteFilePath('receipt', "gnutecaReceipt_".$this->personId.'_'.$personNameToFile.'_'. md5(time()+ microtime()),'pdf');

        $this->pdf = new GPDF( $file  );
        $this->pdf->setTextContent( $this->getContent() );
        $generate = $this->pdf->generate();

        if ( !$generate )
        {
            $this->addMessage( _M('Não foi possível gerar recibo para @1', MIOLO::getCurrentModule(), "{$this->personId} - {$this->personName}"));
            return false;
        }

        return true;
    }

    /**
     * Return the file path (filename) of generated PDF
     *
     * @return string the file path (filename) of generated PDF
     */
    public function getPdfFileName()
    {
        if ( $this->pdf )
        {
            return $this->pdf->getFilePath();
        }
    }

    /**
     * Send the email if is postable.
     *
     * @param fake if you need a fake send (that not realy send, but create objet and put in session, use fake = true
     *
     */
    public function send( $fake = false )
    {
        //if not to post, don't send email
        if ( !$this->isPostable && !$fake )
        {
            return false;
        }

        if ( !strlen( $this->email ) )
        {
            $this->addMessage( _M('Email não encontrado para pessoa <b>@1</b>', MIOLO::getCurrentModule(), "{$this->personId} - {$this->personName}"  ));
            return false;
        }

        $this->createPDF();
        $file = $this->pdf->getFilePath();

        if ( !$file )
        {
            $this->addMessage( _M('Impossível gerar recibo <b>@1</b>', MIOLO::getCurrentModule(), "{$this->personId} - {$this->personName}"  ));
            return false;
        }

        $receiptMail = new ReceiptMail($this->email, $this->modelMailSubject, $this->interpret( $this->modelMailContent), $file);
        $send        = $receiptMail->send(true, $fake);

        if ( $send == 1 )
        {
            $this->addMessage( _M('Enviado email de <b>@1</b> para @2', $this->module, $this->operation, "{$this->personId} - {$this->personName} - {$this->email}"  ));
            return true;
        }
        else
        {
            $this->addMessage( _M('Falha ao enviar email de <b>@1</b> para @2.',$this->module, $this->operation, "{$this->personId} - {$this->personName} - {$this->email}" ) );
            return false;
        }      
    }
}
?>