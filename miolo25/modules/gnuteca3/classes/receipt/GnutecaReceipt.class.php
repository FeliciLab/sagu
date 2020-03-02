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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 11/02/2009
 *
 **/
$MIOLO = MIOLO::getInstance();
$MIOLO->uses('/classes/receipt/Receipt.class.php', 'gnuteca3');
$MIOLO->uses('/classes/receipt/ReceiptMail.class.php', 'gnuteca3');
$MIOLO->uses('/classes/receipt/ReturnReceipt.class.php', 'gnuteca3');
$MIOLO->uses('/classes/receipt/LoanReceipt.class.php', 'gnuteca3');
$MIOLO->uses('/classes/receipt/FineReceipt.class.php', 'gnuteca3');
$MIOLO->uses('/classes/GPDF.class.php', 'gnuteca3');

class GnutecaReceipt extends GMessages
{
    private $receiptFilePath;

    public $module;
    public $operation;

    const RECEIPT_USER_CONFIG = i; //ignore
    const RECEIPT_ALL  = DB_TRUE;
    const RECEIPT_NONE = DB_FALSE;
    const PRINT_MODE_SOCKET = 1;
    const PRINT_MODE_NAVIGATOR = 2;
    const PRINT_MODE_POPUP = 3;

    /**
     * Constantes para definir codificação do recibo. 
     */
    const RECEIPT_CHARSET_UNACCENT = 1;
    const RECEIPT_CHARSET_UTF8 = 2;
    const RECEIPT_CHARSET_ISO88591 = 3;

    function __construct()
    {
        $this->module = MIOLO::getCurrentModule();
    }

    /**
     * Return an array with receipt config list. Ready for MCombo.
     *
     * @return array of array
     */
    public static function getConfigList()
    {
        return array(
                     self::RECEIPT_USER_CONFIG => _M('Usuário', MIOLO::getCurrentModule() ),
                     self::RECEIPT_ALL         => _M('Todos',MIOLO::getCurrentModule() )  ,
                     self::RECEIPT_NONE        => _M('Nenhum',MIOLO::getCurrentModule() )
                    );
    }

    /**
     * Adds an receipt to list
     *
     * @param object $itemObject
     * @param varchar $operationType (Loan, Renew, Return)
     */
    public function addItem($receipt)
    {
        //if has an receipt to this person add it as an item
        if ( $this->itens[get_class($receipt)][$receipt->personId] )
        {
            $this->itens[get_class($receipt)][$receipt->personId]->addItem($receipt);
        }
        else
        {
            $this->itens[get_class($receipt)][$receipt->personId] = $receipt;
        }
    }

    public function setItens($itens)
    {
        $this->itens = $itens;
    }

    /**
     * Return the itens of receipt list
     *
     * @return object
     */
    public function getItens()
    {
        return $this->itens;
    }

    /**
     * Return an unique item, for it class and Person
     */
    public function getItem( $receiptClass, $personId )
    {
        return $this->itens[$receiptClass][$personId];
    }

    /**
     * The string with generated receipts
     *
     * @return string with generated receipts;
     */
    public function generate()
    {
        $itens = $this->getItens();

        //para cada tipo, um array de pessoas/recibo
        if ( is_array( $itens ) )
        {
            foreach ($itens as $types => $person)
            {
                foreach ( $person as $line => $receipt )
                {
                    //define model
                    if ( $receipt instanceof ReturnReceipt )
                    {
                        $receipt->setModel( RETURN_RECEIPT,
                                            RETURN_RECEIPT_WORK,
                                            EMAIL_RETURN_RECEIPT_SUBJECT,
                                            EMAIL_RETURN_RECEIPT_CONTENT ) ;
                    }
                    else
                    if ( $receipt instanceof LoanReceipt )
                    {
                        $receipt->setModel( LOAN_RECEIPT,
                                            LOAN_RECEIPT_WORK,
                                            EMAIL_LOAN_RENEW_RECEIPT_SUBJECT,
                                            EMAIL_LOAN_RENEW_RECEIPT_CONTENT );
                    }
                    else
                    if ( $receipt instanceof FineReceipt )
                    {
                        $receipt->setModel( FINE_RECEIPT,
                                            FINE_RECEIPT_WORK,
                                            EMAIL_FINE_RECEIPT_SUBJECT,
                                            EMAIL_FINE_RECEIPT_CONTENT );
                    }
                    else
                    {
                        //pula outras objetos incluídos no processo
                        continue;
                    }

                    $result .= $receipt->generate();
                    
                    //Se for para desacentuar os recibos (certas impressoras nao conseguem imprimir acentos recibos.)
                    if( MUtil::getBooleanValue( UNACCENT_RECEIPTS ) )
                    {
                        $result = GString::construct($result,'UTF-8')->unaccent()->generate();
                    }
                    
                    if ( $result )
                    {
                        $result .= "\n";
                    }

                    if ( $receipt->getMessage() )
                    {
                        $this->addInformation( $receipt->getMessage() );
                    }
                }
            }
        }

        $MIOLO = MIOLO::getInstance();
        $session = $MIOLO->getSession();
        //guarda a string com todos recibos gerados na sessão
        $session->setValue('receiptResult', $result);

        return $result;
    }

    /**
     * Retorna último recibo (pode ser vários recibos) gerado como texto (string).
     *
     * @return string
     */
    public static function getReceiptsText()
    {
        $MIOLO   = MIOLO::getInstance();
        $session = $MIOLO->getSession();
        return $session->getValue('receiptResult');
    }

    public static function clearReceitpsText()
    {
        $MIOLO   = MIOLO::getInstance();
        $session = $MIOLO->getSession();
        return $session->setValue('receiptResult','');
    }

    /**
     * Reenvia recibos da última operação.
     */
    public function resendStoredMails()
    {
        $emails = ReceiptMail::resendStoredEmails();

        if ( is_array($emails) )
        {
            foreach ( $emails as $line => $email )
            {
                if ( $email->ErrorInfo )
                {
                    $this->addError( _M('Problemas ao reenviar email para <b>@1</b>.Mensagem do servidor: @2',MIOLO::getCurrentModule(),$email->getEmail(),$email->ErrorInfo) );
                }
                else
                {
                    $this->addInformation( _M("Email reenviado com sucesso para <b>@1</b>." , MIOLO::getCurrentModule(), $email->getEmail() ) );
                }
            }
        }
        else
        {
            $this->addAlert(_M('Sem emails para reenviar.', MIOLO::getCurrentModule() ) );
        }
    }

    /**
     * Limpa o diretorio de armazenamento dos recibos.
     *
     */
    function cleanReceiptStoragePath()
    {
        $path = scandir($this->receiptFilePath);

        foreach ($path as $file)
        {
            if(ereg(".pdf", $file))
            {
                @unlink($this->receiptFilePath ."/$file");
            }
        }
    }

    /**
     * Clean the receipt
     */
    public function clean()
    {
    	parent::clean(); //limpa mensagens
        self::clearReceitpsText(); //limpa receiptText
        ReceiptMail::clearStoredEmails();
        $this->setItens(null); //limpa dados
    }
    
    /**
     * Envia o recibo para o servidor de impressora ou para o browser
     * De acordo com a situação
     * 
     * @param string $receiptText texto do recibo
     * 
     * @return bool
     */
    public function sendPrintServer($receiptText)
    {
        $MIOLO = MIOLO::getInstance();
        
        if ((PRINT_SERVER_CUT_COMMAND != 'PRINT_SERVER_CUT_COMMAND') && PRINT_SERVER_CUT_COMMAND)
        {
            $str = '';
            $exp = explode(',', PRINT_SERVER_CUT_COMMAND);
            foreach ($exp as $e)
            {
                $str .= chr($e);
            }
            $receiptText .= $str;
        }

        if (!$receiptText)
        {
            return;
        }

        if ( PRINT_MODE == self::PRINT_MODE_SOCKET && PRINT_SERVER_ENABLED == DB_TRUE ) //Envia o conteudo do recibo para a impressora
        {
            $MIOLO->getClass($this->module, 'GPrinterClient');
            $socket = new GPrinterClient();
            $socket->send( $receiptText );
            $socket->send( PRINTER_SERVER_SIGNAL_PRINT );
            
            return $socket->waitingResponse( $socket->getTextConfirmCode() );
        }
        elseif ( PRINT_MODE == self::PRINT_MODE_NAVIGATOR || PRINT_MODE == self::PRINT_MODE_POPUP ) //Imprimi pelo navegador ou é popup
        {
            if ( RECEIPT_CHARSET )
            {
                // Retira acentos do recibo.
                if ( RECEIPT_CHARSET == self::RECEIPT_CHARSET_UNACCENT )
                {
                    $receiptText = GUtil::unaccent($receiptText);
                } 
                elseif ( RECEIPT_CHARSET == self::RECEIPT_CHARSET_ISO88591 )
                {
                    // Converte o recibo em ISO.
                    $receipt = new GString(NULL, 'ISO-8859-1');
                    $receipt->setString($receiptText);
                    $receiptText = $receipt->getString();
                }
            }
            
            $print = false;

            //Se for por popup manda imprmir
            if ( PRINT_MODE == self::PRINT_MODE_POPUP )
            {
                $print = true;
            }

            // Quando RECEIPT_CHARSET for RECEIPT_CHARSET_UTF8 não é necessário fazer conversão, pois o Gnuteca já trabalha em UTF-8. 
            return BusinessGnuteca3BusFile::openDownload('receipt', "receipt." . mktime() + rand() . '.rcpt', $receiptText, 'recibo.rcpt', $print);
        }
        else
        {
            return false;
        }
    }
}
?>