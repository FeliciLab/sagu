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
 * This class Make a decent header
 *
 * @author Leovan Tavares da Silva [leovan] [leovan@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 14/06/2006
 *
 **/
 
/*
 * TODO: Make a decent header
 */ 
class sabStruct extends sabCore
{
   /*
    * By default called on new instance of this object
    * @params: No parameters needed
    * @return: Nothing, set the default information for that object
    */
    public function __construct()
    {
        parent::__construct('748');
        $this->setBankCode('748');
        $this->setBankDV('X');
        $this->setInvoiceNumber('1234567890');

        //Define wallet
        $this->setInvoiceWallet('1');
        //Define acceoted
        $this->setInvoiceAccepted('N');
        //Defines whether it is registered or not (2 = Not register; 1 = register)
        $this->setIsRegister('3');
        //Defines if it is issued by the transferor (4 = transferor )
        $this->setSentTransferor('4');
        //Defines if value expressed in field
        $this->setValueExpressedField('1');
        
        if ( strlen($this->getInvoiceProcessDate()) == 0 )
        {
            $this->setInvoiceProcessDate(date('d/m/Y'));
        }
        if ( strlen($this->getInvoiceDate()) == 0 )
        {
            $this->setInvoiceDate(date('d/m/Y'));
        }
        $this->setNumberOfBodies(3);
        $this->setHeaderInfo(1, 'TEXT', 'RECIBO DO SACADO');
        $this->setHeaderInfo(2, 'TEXT', 'FICHA DE CAIXA');
        $this->setHeaderInfo(3, 'DIGITABLE NUMBER');

        if ( strlen($this->getPaymentPlaceDescription()) == 0 )
        {
            $this->setPaymentPlaceDescription(_('Pagável em qualquer Banco até o vencimento'));
        }
        $vars = get_object_vars($this);
    }

   /*
    * Set the invoice number (parent abstract function)
    * TODO: MAKE THIS FUNCTION WORK ON CORRECT PARAMETERS
    * @param: No parameters needed
    * @return: No return, just change the sab object property our number 
    */
    public function setInvoiceNumber($invoiceNumber)
    {
        $this->invoiceNumber = $invoiceNumber;
        return true;
    }

   /*
    * Set the invoice our number (parent abstract function)
    * TODO: MAKE THIS FUNCTION WORK ON CORRECT PARAMETERS
    * @param: $invoiceOurNumber (The our number)
    * @return: No return, just change the sab object property our number 
    */
    public function setInvoiceOurNumber($invoiceOurNumber)
    {
        $this->invoiceOurNumber = $invoiceOurNumber;
        return true;
    }

   /*
    * Set the invoice our number DV (parent abstract function)
    * TODO: MAKE THIS FUNCTION WORK ON CORRECT PARAMETERS
    * @param: $invoiceOurNumberDV (The DV for our number)
    * @return: No return, just change the sab object property our number DV
    */
    public function setInvoiceOurNumberDV($invoiceOurNumberDV = null)
    {
        if (is_null($invoiceOurNumberDV))
        {
            $this->invoiceOurNumberDV = fields::modulo11($this->getInvoiceOurNumber());
        }
        else
        {
            $this->invoiceOurNumberDV = $invoiceOurNumberDV;
        }
        return true;
    }


   /*
    * Set the transferor code (parent abstract function)
    * TODO: MAKE THIS FUNCTION WORK ON CORRECT PARAMETERS
    * @param: No parameters needed
    * @return: No return, just change the sab object property our number 
    */
    public function setTransferorCode($transferorCode)
    {
        $this->transferorCode = $transferorCode;
        return true;
    }

   /*
    * Set the transferor code dv (parent abstract function)
    * TODO: MAKE THIS FUNCTION WORK ON CORRECT PARAMETERS
    * @param: No parameters needed
    * @return: No return, just change the sab object property our number 
    */
    public function setTransferorCodeDV($transferorCodeDV)
    {
        $this->transferorCodeDV = $transferorCodeDV;
        return true;
    }
    
    /**
     * Return the digit verifier of the field free
     *
     * @return: $freeFieldDV (integer); Is digit verifier
     **/
    function getFreeFieldsDVM11()
    {
        $freeFieldDV  = $this->getIsRegister();
        $freeFieldDV .= $this->getInvoiceWallet();
        $freeFieldDV .= $this->getInvoiceOurNumber();
        $freeFieldDV .= $this->getTransferorBankAccount();
        $freeFieldDV .= $this->getTransferorBankAccountDV();
        $freeFieldDV .= $this->getTransferorCode();
        $freeFieldDV .= $this->getValueExpressedField();
        $freeFieldDV .= 0;

        $return = fields::modulo11($freeFieldDV);
        if ( ($return == 0) || ($return == 1) )
        {
            return 0;
        }
        else
        {
            return $return;
        }
    }

    /**
     * Set value is expressed in the field
     *
     * @parameter $valueExpressedField (integer):
     **/
    function setValueExpressedField($valueExpressedField)
    {
        if ( strlen($valueExpressedField) > 0 )
        {
            $this->valueExpressedField = $valueExpressedField;
            return true;
        }
        else
        {
            $this->errors[] = _('Valor inválido para o campo expresso');
            return false;
        }
    }
        
    /**
     * Return if value is expressed in the field
     *
     * @return: $valueExpressedField (integer); 0 (zero) if value not expressed and 1 if value expressed
     **/
    function getValueExpressedField()
    {
        return $this->valueExpressedField;
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
}
?>
