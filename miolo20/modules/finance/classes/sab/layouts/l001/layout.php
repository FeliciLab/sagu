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
 * This class Fields Struct
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
        parent::__construct('001');
        $this->setBankCode('001');
        $this->setBankDV('9');
        
        if (strlen($this->getInvoiceProcessDate()) == 0)
        {
            $this->setInvoiceProcessDate(date('d/m/Y'));
        }
        if (strlen($this->getInvoiceDate()) == 0)
        {
            $this->setInvoiceDate(date('d/m/Y'));
        }
        $this->setNumberOfBodies(3);
        $this->setHeaderInfo(1, 'TEXT', 'RECIBO DO SACADO');
        $this->setHeaderInfo(2, 'TEXT', 'FICHA DE CAIXA');
        $this->setHeaderInfo(3, 'DIGITABLE NUMBER');

        if (strlen($this->getPaymentPlaceDescription()) == 0)
        {
            $this->setPaymentPlaceDescription(_('PAGÁVEL EM QUALQUER BANCO ATÉ A DATA DE VENCIMENTO'));
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
    public function setTransferorCodeDv($transferorCodeDv)
    {
        $this->transferorCodeDv = $transferorCodeDv;
        return true;
    }
    
   /*
    * Returns the protected transferor Agreement attribute
    * @params: No parameters needed
    * @return: (string) String containing the agreement transferor
    */
    public function getTransferorCodeDv()
    {
        return $this->transferorCodeDv;
    }
   /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorAgreement
    * @return: (boolean) True if a valid string, otherwise false
    */
    public function setTransferorAgreement($transferorAgreement)
    {
        if ( strlen($transferorAgreement) > 0 )
        {
            $this->transferorAgreement = $transferorAgreement;
            return true;
        }
        else
        {
            $this->errors[] = _('Acordo cedente inválido');
            return false;
        }
    }

   /*
    * Returns the protected transferor Agreement attribute
    * @params: No parameters needed
    * @return: (string) String containing the agreement transferor
    */
    public function getTransferorAgreement()
    {
        return $this->transferorAgreement;
    }

   /*
    * Check if the transferor is a valid string
    * @params: (string) $transferorComplement
    * @return: (boolean) True if a valid string, otherwise false
    */
    public function setTransferorComplement($transferorComlement)
    {
        if ( strlen($transferorComlement) > 0 )
        {
            $this->transferorComplement = $transferorComlement;

            return true;
        }
        else
        {
            $this->errors[] = _('Complemento cedente inválido');
            return false;
        }
    }

   /*
    * Returns the protected transferor comlement attribute
    * @params: No parameters needed
    * @return: (string) String containing the complement transferor
    */
    public function getTransferorComplement()
    {
        return $this->transferorComplement;
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
