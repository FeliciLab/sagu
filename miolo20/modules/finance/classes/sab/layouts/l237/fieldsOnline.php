<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de SoluçÃµes Livres Ltda.
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
 * Você deve ter recebido uma cÃ³pia da Licença Pública Geral GNU, sob o título
 * "LICENCA.txt", junto com este programa, se não, acesse o Portal do Software
 * Público Brasileiro no endereço www.softwarepublico.gov.br ou escreva para a
 * Fundação do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 *
 * This class Fields Struct
 *
 * @author Arthur Lehdermann [arthur] [arthur@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Arthur Lehdermann [arthur@solis.coop.br]
 *
 * @since
 * Class created on 14/06/2006
 *
 **/

class fieldsStruct extends fields
{
   /*
    * Function to construct that object, by default calls the parent function to set default
    * values to some variables
    * @param: $sabStruct (sabStruct object)
    * @return: Nothing.
    */
    public function __construct(sabStruct $sabStruct)
    {
        parent::__construct($sabStruct);
    }

    //Defines the calculation of modules of each field.
    public function generateModulesCalcField()
    {
        //Defines the module for calculating the digit verifier
        $this->digitableNumber->setModuleCalcField(1, 10);
        $this->digitableNumber->setModuleCalcField(2, 10);
        $this->digitableNumber->setModuleCalcField(3, 10);
    }

   /*
    *
    * This function is an abstract declaration on parent class, needed to generate the barcode
    * If you wish, the parent class has a method called "barCodeFixedValues" that implements rules of
    * barcode fixed values based on FEBRABAN defaults. See more at parent function on "include" path
    * ATTENTION: This function needs return a string containing length 25 and only numbers to process
    *            the information correctly
    * @params: As you wish, by default, the sabStruct object
    * @return: (String) String with length 25
    */
    public function generateBarCode(sabStruct $sabStruct)
    {
        $this->barCodeDataFixedValuesWithFactor($sabStruct);
        $this->barCodeData->setBarCodeField('bankAccount'   , $sabStruct->getTransferorBankAccount()       ,  4, 0);
        $this->barCodeData->setBarCodeField('wallet'        , $sabStruct->getInvoiceWallet()               ,  2, 0);
        $this->barCodeData->setBarCodeField('invoiceNumber' , $sabStruct->getInvoiceOurNumber()            , 11, 0);
        $this->barCodeData->setBarCodeField('transferorCode', substr($sabStruct->getTransferorCode(), -7,7),  7, 0);
        $this->barCodeData->setBarCodeField('constant'      , 0                                            ,  1, 0);
        $this->barCodeData->setBarCodeField('barCodeDV'     , $this->modulo11(self::onlyNumbers($this->barCodeData->returnBarCode())), 1, 0);
    }
}
?>