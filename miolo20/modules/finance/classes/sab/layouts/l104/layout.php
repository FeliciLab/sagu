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
        parent::__construct('104');
        $this->setBankCode('104');
        $this->setBankDV('0');

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
            $this->setPaymentPlaceDescription('PREFERENCIALMENTE NAS CASAS LOTÉRICAS E AGÊNCIAS DA CAIXA');
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
        if ( (strlen($invoiceOurNumber) >= 16) || (strlen($invoiceOurNumber) <= 7) )
        {
            $this->errors[] = _('Campo nosso número inválido');
            return false;
        }
        else
        {
            $this->invoiceOurNumber = $invoiceOurNumber;
            return true;
        }
    }


   /*
    * Set the invoice our number DV (parent abstract function)
    * TODO: MAKE THIS FUNCTION WORK ON CORRECT PARAMETERS
    * @param: $invoiceOurNumberDV (The DV for our number)
    * @return: No return, just change the sab object property our number DV
    */
    public function setInvoiceOurNumberDv($invoiceOurNumberDv = null)
    {
        if (is_null($invoiceOurNumberDv))
        {
            $isRegister = $this->getIsRegister();
            $sentTransferror = $this->getSentTransferor();
            $this->invoiceOurNumberDv = $this->digitVerifiedModulo11CEF($isRegister . $sentTransferror . $this->invoiceOurNumber);
        }
        else
        {
            $this->invoiceOurNumberDv = $invoiceOurNumberDv;
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
    * Set the transferor code (parent abstract function)
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
    
    /**
     * Return the digit verifier of the field free
     *
     * @return: $freeFieldDV (integer); Is digit verifier
     **/
    function getFreeFieldsDVM11()
    {
        $freeFieldDV  = $this->getTransferorCode();
        $freeFieldDV .= $this->digitVerifiedModulo11CEF($this->getTransferorCode());
        $freeFieldDV .= substr($this->getInvoiceOurNumber(), 0, 3);
        $freeFieldDV .= $this->getIsRegister();
        $freeFieldDV .= substr($this->getInvoiceOurNumber(), 3, 3);
        $freeFieldDV .= $this->getSentTransferor();
        $freeFieldDV .= substr($this->getInvoiceOurNumber(), 6, 9);

        return $this->digitVerifiedModulo11CEF($freeFieldDV);
    }
    
    /**
     * Set code operation
     * @params: $codeOperation (integer): Is code of the operation
     * @return: boolean: True if a valid value, otherwise false
     **/
    public function setCodeOperation($codeOperation)
    {
        if ( is_null($codeOperation) )
        {
            $this->errors[] = _('Valor nulo para operação de código');
            return false;
        }
        else
        {
            $this->codeOperation = $codeOperation;
            return true;
        }
    }

   /**
    * Get the code operation
    * @params: No parameters needed
    * @return: (integer) Return th code operation
    **/
    public function getCodeOperation()
    {
        return $this->codeOperation;
    }

    /**
     * Set code provide by agency
     * @params: $codeProvideAg (integer): Is code provide by agency
     * @return: boolean: True if a valid value, otherwise false
     **/
    public function setCodeProvideAg($codeProvideAg)
    {
        if ( is_null($codeProvideAg) )
        {
            $this->errors[] = _('Valor nulo para a agência estabelecida');
            return false;
        }
        else
        {
            $this->codeProvideAg = $codeProvideAg;
            return true;
        }
    }
    
    public function digitVerifiedModulo11CEF($num, $factor = 2, $factorMax = 9)
    {
        $totalX  = 0;
        $value   = array();
        $factor_ = $factor;
        for ( $x = strlen($num); $x > 0; $x-- )
        {
            $pos       = substr($num, $x-1, 1);
            $value[$x] = $pos*$factor;
            if ( $factor == $factorMax )
            {
                $factor = $factor_;
            }
            else
            {
                $factor++;
            }
        }    
        $totalX = array_sum($value);
        $mod    = $totalX % 11;
        if ( in_array($mod, array(0, 1, 11)) )
        {
            return 0;        
        }
        else
        {
           return 11 - $mod;
        }
    }

   /**
    * Get the code provide by agency
    * @params: No parameters needed
    * @return: (integer) Return th code provide by agency
    */
    public function getCodeProvideAg()
    {
        return $this->codeProvideAg;
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
