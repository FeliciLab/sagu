<?php
 /**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 * 
 * Este arquivo é parte do programa Sagu.
 * 
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * This class 
 *
 * @author William Prigol Lopes [william@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 22/02/2008
 *
 **/
 
include('validators.php');
include('layout.php');
include('fields.php');
abstract class sabCore
{
    // Invoice info
    private $bankCode                  = '';       // Código do banco
    private $bankDV                    = '';       // Dí­gito verificador do banco
    private $bankCodeAuX               = '';       // Código do banco
    private $bankDVAux                 = '';       // Dí­gito verificador do banco
    private $invoiceTax                = '';       // Taxa bancária
    private $invoiceMaturityDate       = '';       // Data de vencimento
    private $invoiceValue              = '';       // Valor do tí­tulo
    private $invoiceWallet             = '';       // Carteira
    private $invoiceWalletModel        = '';       // Modalidade da carteira
    private $invoiceOurNumber          = '';       // Nosso número
    private $invoiceOurNumberDv        = '';       // Dí­gito verificador do nosso número
    private $invoiceMessage            = '';       // Mensagens do boleto
    private $invoiceNumber             = '';       // Número do tí­tulo
    private $invoiceDate               = '';       // Data do documento
    private $invoiceProcessDate        = '';       // Data de processamento
    private $invoiceQuantity           = '';       // Quantidade
    private $invoiceAccepted           = '';       // Aceite
    private $invoiceKind               = '';       // Tipo de documento
    private $isRegister                = '';       // Se o boleto é registrado ou não (0 = não registrado; 1 = registrado)
    private $clientName                = '';       // Nome do cliente
    private $clientAddress             = '';       // Endereço do cliente
    private $clientAddress2            = '';       // Endereço do cliente 2
    private $clientCityState           = '';       // Cidade e estado do cliente
    private $transferorName            = '';       // Nome do cedente
    private $bodiesNumber              = '';       // Número de vias (mí­nimo duas)
    private $bodyData                  = '';       // Informações do corpo (Informações de cabeçalho e rodapé)
    private $paymentPlace              = '';       // Local de pagamento
    private $moneyKind                 = '';       // Tipo monetário
    private $transferorAccountNumber   = '';       // Número da conta bancária
    private $transferorAccountNumberDV = '';       // Dígito verificador da conta bancária
    private $transferorAgreement       = '';       // Convênio do cedente
    private $transferorComplement      = '';       // Comlemento = sequencial atribuido pelo cliente
    private $transferorCode            = '';       // Agência/Código do cedente
    private $transferorCnpj            = '';       // Cnpj da empresa
    private $transferorBankAccount     = '';       // Número da conta do cedente
    private $maturityFactor            = true;     // Use maturity factor?
    private $moneyCode                 = 9;        // Por padrões criados pela FEBRABAN, o código da moeda é 9 (real) por padrão
    private $sentTransferor            = '';       // Boleto enviado pelo cendente
                                               // TODO: Fazer um "getter" para esta propriedade
    public  $errors;                           // Erros gerados no processamento do sistema
   

    abstract public function setInvoiceOurNumber($ourNumber);
    abstract public function setInvoiceOurNumberDV($ourNumberDV = null);
    abstract public function setInvoiceNumber($invoiceNumber);
    abstract public function setTransferorCode($transferorCode);

   /*
    * Return the invoice our number setted by child abstract function
    *
    * @param: No parameters needed
    *
    * @returns: (varchar) The invoice our number, if setted
    *
    */
    public function getInvoiceOurNumber()
    {
        return $this->invoiceOurNumber;
    }

	/*
    * Return the invoice our number setted by child abstract function
    *
    * @param: No parameters needed
    *
    * @returns: (varchar) The invoice our number, if setted
    *
    */
    public function getInvoiceOurNumberDv()
    {
        return $this->invoiceOurNumberDv;
    }
    
   /*
    * Set the layout type 
    * @param: $bankCode (varchar): The bank code to try load specific configurations
    * @returns: (boolean): True if do, else false
    */
    public function __construct($bankCode)
    {
        $this->setBankCode($bankCode);
    }

	/*
    * Returns the bank code
    * @param: No parameters needed
    * @return: bank code
    */
    public function gettBankId()
    {
        return $this->bankCode;
    }
    
   /*
    * Set if you want to use the maturity factor on invoice
    * @param: $factor (boolean): Set if use (true) or don't use (false)
    * @return: Nothing
    */
    public function setUsesMaturityFactor(boolean $factor)
    {
        $this->maturityFactor = $factor;
    }

   /*
    * Returns the maturity factor
    * @param: No parameters needed
    * @return: True or false, according the private property maturity factor
    */
    public function getUsesMaturityFactor()
    {
        return $this->maturityFactor;
    }

   /*
    *
    *  Set the invoice tax value
    *  @params: $invoiceTax (double)
    *  @return: True if a valid value, otherwise false
    *
    */
    public function setInvoiceTax($invoiceTax)
    {
        $validators = new validators();
        if ( $this->invoiceTax = $validators->checkReal($invoiceTax) )
        {
            if ($this->invoiceTax !== null)
            {
                return true;
            }
            else
            {
                $this->invoiceTax = null;
                return true;
            }
        }
        else
        {
            $this->errors[] = _('A taxa da fatura não é numérica');
            return false;
        }
    }

   /*
    * Get the invoice tax value
    * @params: No parameters needed
    * @return: (float) The invoice tax, if that exists
    */
    public function getInvoiceTax()
    {
        return $this->invoiceTax;
    }
    
   /*
    *  Set the invoice maturity date 
    *  @params: $invoiceMaturityDate (float)
    *  @return: True if a valid value, otherwise false
    */
    public function setInvoiceMaturityDate($invoiceMaturityDate)
    {
        if ( validators::checkDate($invoiceMaturityDate) )
        {
            $this->invoiceMaturityDate = $invoiceMaturityDate;
            return true;
        }
        else
        {
            $this->errors[] = _('A data de vencimento da fatura não é numérica');
            return false;
        }
    }

   /*
    * Get the invoice maturity date value
    * @params: No parameters needed
    * @return: (float) The invoice maturity date, if that exists
    */
    public function getInvoiceMaturityDate()
    {
        return $this->invoiceMaturityDate;
    }
    
   /*
    * 
    */
    public function setInvoiceMessage($message)
    {
    	if ( ! is_array($message) )
    	{
    		$message = array($message);
    	}
        
    	$this->invoiceMessage = $message;
    }

   /*
    * Get the invoice message
    * @params: No parameters needed
    * @return: (varchar) The invoice massage
    */
    public function getInvoiceMessage()
    {
    	return $this->invoiceMessage;
    }
    
   /*
    *  Set the invoice value
    *  @params: $invoiceValue (double)
    *  @return: True if a valid value, otherwise false
    */
    public function setInvoiceValue($invoiceValue)
    {
        $validators = new validators();
        if ( $this->invoiceValue = $validators->checkReal($invoiceValue) )
        {
            if ($this->invoiceValue !== null)
            {
                return true;
            }
            else
            {
                $this->invoiceValue = null;
                return true;
            }
        }
        else
        {
            $this->errors[] = _('O valor da fatura não é numérico');
            return false;
        }
    }

   /*
    * Get the invoice value
    * @params: No parameters needed
    * @return: (float) The invoice value if that exists
    */
    public function getInvoiceValue()
    {
        return $this->invoiceValue;
    }

   /*
    * Get the invoice value
    * @params: No parameters needed
    * @return: (float) The invoice value if that exists
    */
    public function getFormattedInvoiceValue()
    {
        return number_format($this->getInvoiceValue(), 2, ',', '');
    }

   /*
    * Get the invoice value
    * @params: No parameters needed
    * @return: (float) The invoice value, if that exists
    */
    public function setInvoiceWallet($wallet)
    {
        if ( is_null($wallet) )
        {
            $this->errors[] = _('Carteira deve ser preenchida');
            return false;
        }
        else
        {
            $this->invoiceWallet = $wallet;
            return true;
        }
    }

   /*
    * Get the invoice value
    * @params: No parameters needed
    * @return: (float) The invoice value, if that exists
    */
    public function getInvoiceWallet()
    {
        return $this->invoiceWallet;
    }

    
    public function setInvoiceWalletModel($wallet)
    {
        if ( is_null($wallet) )
        {
            $this->errors[] = _('Carteira deve ser preenchida');
            return false;
        }
        else
        {
            $this->invoiceWalletModel = $wallet;
            return true;
        }
    }

   /*
    * Get the invoice value
    * @params: No parameters needed
    * @return: (float) The invoice value, if that exists
    */
    public function getInvoiceWalletModel()
    {
        return $this->invoiceWalletModel;
    }
    

   /*
    * Get the invoice value
    * @params: No parameters needed
    * @return: (float) The invoice value, if that exists
    */
    public function getInvoiceNumber()
    {
        return $this->invoiceNumber;
    }
    
   /*
    *  Set the invoice date 
    *  @params: (string) $invoiceDate
    *  @return: (boolean) True if a valid value, otherwise false
    */
    public function setInvoiceDate($invoiceDate)
    {
        $validators = new validators();
        if ( $validators->checkDate($invoiceDate) )
        {
            $this->invoiceDate = $invoiceDate;
            return true;
        }
        else
        {
            $this->errors[] = _('O valor da data é inválido');
            return false;
        }
    }

   /*
    * Get the invoice date value
    * @params: No parameters needed
    * @return: (float) The invoice date, if that exists
    */
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

   /*
    * Set the invoice process date 
    * @params: (string) $invoiceProcessDate
    * @return: (boolean) True if a valid value, otherwise false
    */
    public function setInvoiceProcessDate($invoiceDate)
    {
        $validators = new validators();
        if ( $validators->checkDate($invoiceDate) )
        {
            $this->invoiceProcessDate = $invoiceDate;
            return true;
        }
        else
        {
            $this->errors[] = _('O valor da data é inválido');
            return false;
        }
    }

   /*
    * Get the invoice date value
    * @params: No parameters needed
    * @return: (float) The invoice date, if that exists
    */
    public function getInvoiceProcessDate()
    {
        return $this->invoiceProcessDate;
    }

   /*
    * Set the invoice client name
    * @params: (string) $clientName
    * @return: (boolean) True if a string with length major than 0
    */
    public function setClientName($clientName)
    {
        if ( strlen($clientName)>0 )
        {
            $this->clientName = $clientName;
            return true;
        }
        else
        {
            $this->errors[] = _('Nome do cliente inválido');
            return false;
        }
    }

   /*
    * Returns the protected client name method
    * @params: No parameters needed
    * @return: (string) String containing the name of client
    */
    public function getClientName()
    {
        return $this->clientName;
    }

   /*
    *  Set the invoice client address
    *  @params: (string) $clientAddress
    *  @return: (boolean) True if a string with length major than 0
    */
    public function setClientAddress($clientAddress)
    {
        if ( strlen($clientAddress)>0 )
        {
            $this->clientAddress = $clientAddress;
            return true;
        }
        else
        {
            $this->errors[] = _('Endereço do cliente inválido');
            return false;
        }
    }

   /*
    * Returns the protected client address attribute
    * @params: No parameters needed
    * @return: (string) String containing the name of client
    */
    public function getClientAddress()
    {
        return $this->clientAddress;
    }

    
   /*
    *  Set the invoice client city and state
    *  @params: (string) $clientAddress
    *  @return: (boolean) True if a string with length major than 0
    */
    public function setClientCityState($clientCityState)
    {
        if ( strlen($clientCityState) > 0 )
        {
            $this->clientCityState = $clientCityState;
            return true;
        }
        else
        {
            $this->errors[] = _('Código de cidade/estado do cliente inválido');
            return false;
        }
    }

   /*
    * Returns the protected client address attribute
    * @params: No parameters needed
    * @return: (string) String containing the name of client
    */
    public function getClientCityState()
    {
        return $this->clientCityState;
    }
    
   /*
    *  Set the invoice client address two
    *  @params: (string) $clientAddress2
    *  @return: (boolean) True if a string with length major than 0
    */
    public function setClientAddress2($clientAddress2)
    {
        if ( ! is_null($clientAddress2) )
        {
            if ( strlen($clientAddress2)>0 )
            {
                $this->errors[] = _('Nome sem valor');
                return false;
            }
            else
            {
                $this->clientAddress2 = $clientAddress2;
                return true;
            }
        }
        else
        {
            $this->errors[] = _('Valor vazio');
            return false;
        }
    }

   /*
    * Returns the protected client address two attribute
    * @params: No parameters needed
    * @return: (string) String containing the name of client
    */
    public function getClientAddress2()
    {
        return $this->clientAddress2;
    }

   /*
    * Return a text containing a description for last error, if have an error 
    * @params: Return an specific error
    * @return: (string) A string containing the errors, if exists
    */
    public function getErrors()
    {
        return $this->errors;
    }

   /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorName
    * @return: (boolean) True if a valid string, otherwise false
    */
    public function setTransferorName($transferorName)
    {
        $validators = new validators();
        if ( $validators->checkString($transferorName) )
        {
            $this->transferorName = $transferorName;
            return true;
        }
        else
        {
            $this->errors[] = $validators->getErrors();
            return false;
        }
    }

   /*
    * Returns the protected transferor name attribute
    * @params: No parameters needed
    * @return: (string) String containing the transferor name
    */
    public function getTransferorName()
    {
        return $this->transferorName;
    }
    
   /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorCnpj
    * @return: (boolean) True if a valid string, otherwise false
    */
    public function setTransferorCnpj($transferorCnpj)
    {
        $validators = new validators();
        if ( $validators->checkString($transferorCnpj) )
        {
            $this->transferorCnpj = $transferorCnpj;
            return true;
        }
        else
        {
            $this->errors[] = $validators->getErrors();
            return false;
        }
    }

   /*
    * Returns the protected transferor cnpj attribute
    * @params: No parameters needed
    * @return: (string) String containing the transferor name
    */
    public function getTransferorCnpj()
    {
        return $this->transferorCnpj;
    }


    
   /* TODO: Make a complete check with DV confirmation
    * Check if the transferor is a valid string
    * @params: (string) $transferorName
    * @return: (boolean) True if a valid string, otherwise false
    *
    */
    public function setBankCode($bankCode)
    {
        if ( strlen($bankCode) == 3 )
        {
            $this->bankCode = $bankCode;
            return true;
        }
        else
        {
            $this->errors[] = _('Tamanho do campo código do banco inválido');
            return false;
        }
    }

   /*
    * Returns the protected attribute bank code 
    * @params: No parameters needed
    * @return: (string) String containing the bank code
    */
    public function getBankCode()
    {
        return $this->bankCode;
    }

   /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorName
    * @return: (boolean) True if a valid string, otherwise false
    *
    */
    public function setBankDV($bankDV)
    {
        if ( strlen($bankDV) == 1 )
        {
            $this->bankDV = $bankDV;
            return true;
        }
        else
        {
            $this->errors[] = _('Tamanho da conta bancária DV inválido');
            return false;
        }
    }

   /*
    * Returns the protected attribute bank code 
    * @params: No parameters needed
    * @return: (string) String containing the bank code
    */
    public function getBankDV()
    {
        return $this->bankDV;
    }
    
    /*
    * Returns the protected transferor Agreement attribute
    * @params: No parameters needed
    * @return: (string) String containing the agreement transferor
    */
    public function getBankCodeAux()
    {
        return $this->bankCodeAux;
    }
    
    /*
    * Returns the protected transferor Agreement attribute
    * @params: No parameters needed
    * @return: (string) String containing the agreement transferor
    */
    public function getBankDVAux()
    {
        return $this->bankDVAux;
    }
    
    /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorBankAccount
    * @return: (boolean) True if a valid string, otherwise false
    *
    */
    public function setTransferorBankAccount($bankAccount)
    {
        if ( strlen($bankAccount) > 0 )
        {
            $this->transferorBankAccount = $bankAccount;
            return true;
        }
        else
        {
            $this->errors[] = _('Conta bancária inválida');
            return false;
        }
    }
    
   /*
    * Returns the bank account
    * @params: No parameters needed
    * @return: (string) String containing the bank account
    */
    public function getTransferorBankAccount()
    {
        return $this->transferorBankAccount;
    }

    /*
    * Check if the transferor dv is a valid string
    * @params: (string) $transferorBankAccountDV
    * @return: (boolean) True if a valid string, otherwise false
    *
    */
    public function setTransferorBankAccountDV($bankAccountDV)
    {
        if ( strlen($bankAccountDV) > 0 )
        {
            $this->transferorBankAccountDV = $bankAccountDV;
            return true;
        }
        else
        {
            $this->errors[] = _('Conta bancária DV inválida');
            return false;
        }
    }
    
   /*
    * Returns the bank account
    * @params: No parameters needed
    * @return: (string) String containing the bank account
    */
    public function getTransferorBankAccountDV()
    {
        return $this->transferorBankAccountDV;
    }

    /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorBankAccount
    * @return: (boolean) True if a valid string, otherwise false
    */
    public function setTransferorAccountNumber($accountNumber)
    {
        if ( strlen($accountNumber) > 0 )
        {
            $this->transferorAccountNumber = $accountNumber;
            return true;
        }
        else
        {
            $this->errors[] = _('Número de conta inválido');
            return false;
        }
    }
    
   /*
    * Returns the bank account
    * @params: No parameters needed
    * @return: (string) String containing the bank account
    */
    public function getTransferorAccountNumber()
    {
        return $this->transferorAccountNumber;
    }

    /*
    * Check if the transferor dv is a valid string
    * @params: (string) $transferorBankAccountDV
    * @return: (boolean) True if a valid string, otherwise false
    *
    */
    public function setTransferorAccountNumberDV($accountNumberDV)
    {
        if ( strlen($accountNumberDV) > 0 )
        {
            $this->transferorAccountNumberDV = $accountNumberDV;
            return true;
        }
        else
        {
            $this->errors[] = _('Conta bancária DV inválida');
            return false;
        }
    }
    
   /*
    * Returns the bank account
    * @params: No parameters needed
    * @return: (string) String containing the bank account
    */
    public function getTransferorAccountNumberDV()
    {
        return $this->transferorAccountNumberDV;
    }
    
   /*
    * Get the money kind
    * @params: (string) $moneyKind
    * @return: (boolean) True if a valid string, otherwise false
    *
    */
    public function setMoneyKind($moneyKind)
    {
        if ( ! is_null($moneyKind) )
        {
            if ( strlen($moneyKind)>0 )
            {
                $this->moneyKind = 'R$';
                return true;
            }
            else
            {
                $this->errors[] = _('Valor inválido');
                return false;
            }
        }
        else
        {
            $this->errors[] = _('Valor em branco para o tipo da moeda');
            return false;
        }
    }

   /*
    * Get the protected attribute money kind
    * @params: No parameters needed
    * @return: (string) String containing th  money kind
    */
    public function getMoneyKind()
    {
        if ( strlen($this->moneyKind) == 0 )
        {
            $this->moneyKind = 'R$';
        }
        return $this->moneyKind;
    }

   /*
    * Set the number of bodies to show
    */ 
    public function setNumberOfBodies($bodiesNumber)
    {
        if ( is_int($bodiesNumber) && ($bodiesNumber>=1 && $bodiesNumber<=3) )
        {
            $this->bodiesNumber = $bodiesNumber;
            return true;
        }
        else
        {
            $this->errors[] = _('Número de órgãos inválido');
            return false;
        }
    }


   /*
    * Get the number of bodies to show
    */ 
    public function getNumberOfBodies()
    {
        if ( is_int($this->bodiesNumber) )
        {
            return $this->bodiesNumber;
        }
        else
        {
            return 1;
        }
    }

   /*
    * Check if exists special fields by the layout selected
    * TODO: All, this function do nothing now. :p
    *
    */
    public function checkSpecialFields()
    {
        return true;
    }

   /*
    * Get the header information
    *
    * @params $position (int): The header position that want the information return
    *
    * @returns: If setted, the bodydata object, otherwise, null, when the header position is null, returns false
    *
    */
    public function getHeaderInfo($position)
    {
        if ( ($position>$this->getNumberOfBodies()) && ($position>0) )
        {
            $this->errors[] = _('Posição principal inválida');
            return false;
        }
        else
        {
            if ( (is_array($this->bodyData)) & (($position) <= count($this->bodyData)) )
            {
                return $this->bodyData[$position];
            }
        }
    }

   /*
    * Se the header information
    *
    * @params $position (int): Position of header that want set the information
    *         $type (varchar): TEXT or DIGITABLENUMBER, that indicates the type of header
    *         $header (varchar) default null: If the type has setted to TEXT, the header will contain the text to set on body header
    * 
    * @returns: Return true if succeeded the information setter
    *
    */
    public function setHeaderInfo($position, $type, $header = null)
    {
        if ( ($position>$this->getNumberOfBodies()) && ($position>0) )
        {
            $this->errors[] = _('Posição principal inválida');
            return false;
        }
        else
        {
            if ( in_array($type, array('TEXT', 'DIGITABLE NUMBER')) )
            {
                $this->bodyData[$position]->type = $type;
                if ( $type == 'TEXT' )
                {
                    if ( strlen($header)>0 )
                    {

                        $this->bodyData[$position]->text = $header;
                        return true;
                    }
                    else
                    {

                        $this->errors[] = _('Cabeçalho sem informações');
                        return false;
                    }
                }
                elseif ($type == 'DIGITABLE NUMBER')
                {
                    return true;
                }
            }
            else
            {
                $this->errors[] = _('Tipo de cabeçalho inválido');
                return false;   
            }
        }
    }

   /*
    * Get the invoice quantity (used on parcels)
    *
    * @params $value (varchar): Value to invoice quantity
    * 
    * @returns (boolean): True if a valid value (string with length najor than zero), otherwise false;
    *
    */
    public function setInvoiceQuantity($value)
    {
        if ( strlen($value)>0 )
        {
            $this->invoiceQuantity = $value;
            return true;
        }
        else
        {
            $this->errors[] = _('Valor da quantidade da fatura inválida');
            return false;
        }
    }

   /*
    * Get the invoice quantity
    *
    * @params: No parameters needed
    *
    * @returns (varhcar): Invoice quantity value, if setted
    */
    public function getInvoiceQuantity()
    {
        return $this->invoiceQuantity;
    }

   /*
    * Set the payment place description
    *
    * @param $paymentPlace (varchar): Varchar containing the payment place to set
    *
    * @returns (boolean): True if a valid value, otherwise false
    * 
    */ 
    public function setPaymentPlaceDescription($paymentPlace)
    {
        if ( strlen($paymentPlace)>0 )
        {
            $this->paymentPlace = $paymentPlace;
            return true;
        }
        else
        {
            $this->errors[] = _('Valor de pagamento em branco');
            return false;
        }
    }

   /*
    * Get the payment place description, if setted
    *
    * @param: No parameters needed
    *
    * @returns (varchar): The paymentPlace property
    *
    */
    public function getPaymentPlaceDescription()
    {
        return $this->paymentPlace;
    }
   
   /*
    * Set the accepted value for invoice
    * TODO: Check if exists default values for that, the actual check is just for the size of value.
    * 
    * @params $value (varchar): The value for accepted invoice
    *
    * @returns (varchar): The accepted value
    *
    */
    public function setInvoiceAccepted($value)
    {
        if ( (strlen($value)>0) && (strlen($value)<9) )
        {
            $this->invoiceAccepted = $value;
            return true;
        }
        else
        {
            $this->errors[] = _('Tamanho inválido para o valor da fatura aceito');
            return false;
        }
    }
   
   /*
    * Get the accepted value for invoice
    * TODO: Check if exists default values for that, the actual check is just for the size of value.
    * 
    * @params: No parameters needed
    *
    * @returns (varchar): The accepted value
    *
    */
    public function getInvoiceAccepted()
    {
        return $this->invoiceAccepted;
    }

   /*
    * Set the accepted value for invoice
    * TODO: Check if exists default values for that, the actual check is just for the size of value.
    * 
    * @params $value (varchar): The value for invoice kind
    *
    * @returns (varchar): The invoice kind value
    *
    */
    public function setInvoiceKind($value)
    {
        if ( (strlen($value)>0) && (strlen($value)<9) )
        {
            $this->invoiceKind = $value;
            return true;
        }
        else
        {
            $this->errors[] = _('Tamanho inválido para o tipo de tamanho da fatura aceita');
            return false;
        }
    }

   /*
    * Get the kind value for invoice
    * 
    * @params: No parameters needed
    *
    * @returns (varchar): The accepted value
    *
    */
    public function getTransferorCode()
    {
        return $this->transferorCode;
    }

    
   /*
    * Get the kind value for invoice
    * TODO: Check if exists default values for that, the actual check is just for the size of value.
    * 
    * @params: No parameters needed
    *
    * @returns (varchar): The accepted value
    *
    */
    public function getInvoiceKind()
    {
        return $this->invoiceKind;
    }
  
   /*
    * Check the required fields for a correct invoice process
    *
    * @param: No parameters needed
    *
    * @return: (boolean) True if all required fields are ok, otherwise false
    * 
    */
    public function checkRequiredFields()
    {
        $requiredFields[] = 'paymentPlace';
        $requiredFields[] = 'invoiceMaturityDate';
        $requiredFields[] = 'transferorName';
        $requiredFields[] = 'transferorCode';
        $requiredFields[] = 'invoiceProcessDate';
        $requiredFields[] = 'invoiceOurNumber';
        //$requiredFields[] = 'invoiceOurNumberDv';
        $requiredFields[] = 'clientName';
        $requiredFields[] = 'clientAddress';
        foreach ( $requiredFields as $rf )
        {
            if ( strlen($this->$rf) == 0 )
            {
                $this->errors[] = _('Campo requerido não preenchido');
                return false;
            }   
        }
        return true;
    }

   /*
    * Get the money code
    * @param: No parameters needed
    * @return: The private property money code
    */
    public function getMoneyCode()
    {
        return $this->moneyCode;
    }

   /*
    * Set the money code
    * @param: $value (mixed) Value for money code
    * @return: true if runs, otherwise false
    */
    public function setMoneyCode($value)
    {
        if ( strlen($value) == 1 )
        {
            $this->moneyCode = $value;
        }
        else
        {
            $this->errors[] = _('Tamanho do código monetário inválido, o numero de caracteres deve ser igual a 1');
            return false;
        }
    }


    /**
     *Set if the ticket is recorded
     *
     * @params: $isRegister (integer):
     *
     * @return: true in case of success and false in case of error
     **/
    function setIsRegister($isRegister)
    {
        if ( strlen($isRegister) > 0 )
        {
            $this->isRegister = $isRegister;
            return true;
        }
        else
        {
            $this->errors[] = _('Tamanho inválido para registro');
            return false;
        }
    }

    /**
     * Return type register ticket
     *
     * @return: $this->isRegister (integer): the ticket is recorded
     **/
    function getIsRegister()
    {
        return $this->isRegister;
    }

    /**
     *Set if the ticket is recorded
     *
     * @params: $sentTransferor (integer):
     *
     * @return: true in case of success and false in case of error
     **/
    function setSentTransferor($sentTrasferor)
    {
        if ( strlen($sentTrasferor) > 0 )
        {
            $this->sentTrasferor = $sentTrasferor;
            return true;
        }
        else
        {
            $this->errors[] = _('Tamanho inválido para o cedente enviado');
            return false;
        }
    }

    /**
     * Return is sent transferor
     *
     * @return: $this->sentTransferor (integer): Sent transferor
     **/
    function getSentTransferor()
    {
        return $this->sentTrasferor;
    }
    

   /*
    * Search into object for strings and make a upper on strings on two levels
    * TODO: Optimize the function and make work to more levels, only two levels has been processed here
    *
    * @params: No parameters needed
    *
    * @return Nothing :p
    *
    */
    public function normalizeValues()
    {

        $objVars = get_object_vars($this);
        
        foreach ( $objVars as $ov => $ovv )
        {
            if ( is_string($this->$ov) )
            {
                $this->$ov = strtoupper($ovv);
            }
            elseif ( is_array($ovv) )
            {
                foreach ( $ovv as $ov2 => $ovv2 )
                {
                    if ( is_object($ovv2) )
                    {
                        $objVars2 = get_object_vars($ovv2);
                        foreach ( $objVars2 as $ov_ => $ovv_ )
                        {
                            if ( is_object($this->$ov) )
                            {
                                if ( is_string($this->$ov->$ov_) )
                                {
                                    $this->$ov->$ov_ = strtoupper($ovv_);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
?>
