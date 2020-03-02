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
 * Class Digitable Number
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
 * Class to generate the digitable number value
 */
class digitableNumber
{
    protected $mainFields;
    protected $moduleCalcField;

    public function setDigitableNumberField($mainField, $name, $value, $size, $fill = 0)
    {
        $this->mainFields[$mainField][$name] = fields::formatField($value, $size, $fill);
    }

    /*
    * Set module for calc for field
    * @params: (integer) contains a module that must be calculated the field
    * @return: (boolean) Return true or false
    */
    public function setModuleCalcField($field, $moduleCalcField)
    {
        if ( strlen($moduleCalcField) > 0 )
        {
            $this->moduleCalcField[$field] = $moduleCalcField;
            return true;
        }
        else
        {
            $this->errors[] = _('Campo módulo de cálculo inválido').$field;
            return false;
        }
    }
    
   /*
    * Returns the module for calculating to the field 1
    * @params: No parameters needed
    * @return: (string) String containing the modulo for calc
    */
    public function getModuleCalcField($field)
    {
        if ( !isset($this->moduleCalcField[$field]) )
        {
            return 11;
        }
        else
        {
            return $this->moduleCalcField[$field];
        }
    }
    
    public function returnDigitableNumber()
    {
        if ( is_array($this->mainFields) )
        {
            foreach ( $this->mainFields as $key => $mainField )
            {
                $mainFieldsData[$key] = implode($this->mainFields[$key], '');
                if ( isset($this->mainFields[$key]['mainFieldDV']) )
                {
                    $mainFieldsData[$key] = implode($this->mainFields[$key], '');
                    $moduleType           = $this->getModuleCalcField($key);

                    if ( $moduleType == 11 )
                    {
                        $this->mainFields[$key]['mainFieldDV'] = fields::modulo11(fields::onlyNumbers($mainFieldsData[$key]));
                    }
                    else
                    {
                        $this->mainFields[$key]['mainFieldDV'] = fields::modulo10(fields::onlyNumbers($mainFieldsData[$key]));
                    }
                    $mainFieldsData[$key] = implode($this->mainFields[$key], '');
                }
            }
            return implode($mainFieldsData, '');
        }
    }
}

/*
 * Class to generate the barCodeData value
 */
class barCodeData
{
    public $fields;

    public function setBarCodeField($name, $value, $size, $fill = 0)
    {
        $this->fields[$name] = fields::formatField($value, $size, $fill);
    }
    
    public function returnBarCode()
    {
        return implode($this->fields, '');    
    }
}

/*
 * Class to generate the fields (barCodeData and digitableNumber)
 * This class generate that values and need be extended to generate the extended functions
 */
abstract class fields
{
    const BARCODE_SIZE         = 44;
    const DIGITABLENUMBER_SIZE = 47;
    public $barCodeData;
    public $digitableNumber;    
    var $errors;

    abstract public function generateModulesCalcField();
    abstract public function generateBarCode(sabStruct $sabStruct);

   /*
    * Method constructor, process the bar code and digitable number
    * @param: $sabStruct (sabStruct) Object containing invoice information  
    * @return: Nothing
    */
    public function __construct(sabStruct $sabStruct)
    {
        $this->barCodeData     = new barCodeData();
        $this->digitableNumber = new digitableNumber();
        $this->generateModulesCalcField();
        try
        {
            $this->generateBarCode($sabStruct);
            $this->generateDigitableNumber($sabStruct);
            return true;
        }   
        catch (Exception $e)
        {
            return false;
        }
        
    }

   /*
    * Extract numbers for a variable
    * @param $value (string): Variable containing data to extract the numbers
    * @returns: (int): Only numbers, if exists on variable value
    */
    public function onlyNumbers($value)
    {
        return ereg_replace('[^0-9]', '', $value);
    }

   /*
    * Format on a specific size for the field
    * TODO: Make this function better (Considering the metadata type)
    * @param $value (string): Variable to format
    *        $size (int): Size of the field
    *        $fill (string): String containing the separator
    *
    * @returns: (int): Only numbers, if exists on variable value
    *
    */
    public function formatField($value, $size, $fill = '')
    {
        if ( strlen($value)>$size )
        {
            $value = substr($value, 0, $size);
        }
        else
        {
            if ( strlen($fill)!=1 )
            {
                $fill = ' ';
            }
            $value = str_pad($value, $size, $fill, STR_PAD_LEFT);
        }
        return $value;
    }

   /*
    * Generate the maturity factor extracted by the FEBRABAN defaults.
    *
    * @param: $date Date to extract the maturity factor
    * @returns: The maturity factor value.
    *
    */
    private function maturityFactor($date, $beginDate = '07/10/1997')
    {
        $beginDate = explode('/', $beginDate);
        $date      = explode('/', $date);
        return abs((self::dateToDays($beginDate[2], $beginDate[1], $beginDate[0]))-(self::dateToDays($date[2], $date[1], $date[0])));
    }

   /*
    *
    * Function found on boletoPHP project: www.boletophp.net.
    * Extracts the number of total days baseed on 01/01/1 since the date extracted.
    * 
    * @param: $year (int): The year for date
    *         $month (int): The month for date
    *         $day (int): The day for date
    *
    * @returns: The total of days for that date.
    * 
    */
    public function dateToDays($year, $month, $day)
    {
        $century = substr($year, 0, 2);
        $year    = substr($year, 2, 2);
        if ( $month > 2 )
        {
            $month -= 3;
        } 
        else 
        {
            $month += 9;
            if ( $year )
            {
                $year--;
            } 
            else
            {
                $year = 99;
                $century --;
            }
        }
        return (floor(( 146097 * $century) / 4 ) + floor(( 1461 * $year) /  4 ) + floor(( 153 * $month +  2) /  5 ) + $day +  1721119);
    }

   /*
    * Generate the first part of barcode numeric information with date factor
    * @param: $requiredData (sabStruct): The sabStruct object containing the invoice information to process the code.
    * @returns: (int): The required field number for barcode according with FEBRABAN defaults
    */
    public function barCodeDataFixedValuesWithFactor(sabStruct $requiredData)
    {
        $this->barCodeData->setBarCodeField('bankCode',       $requiredData->getBankCode(),  3, 0);
        $this->barCodeData->setBarCodeField('moneyCode',      $requiredData->getMoneyCode(), 1, 0);
        $this->barCodeData->setBarCodeField('barCodeDV',      'X', 1, 0);
        $this->barCodeData->setBarCodeField('maturityFactor', $this->maturityFactor($requiredData->getInvoiceMaturityDate()), 4, 0);
        $this->barCodeData->setBarCodeField('invoiceValue', self::onlyNumbers($requiredData->getInvoiceValue()), 10, 0);
    }

   /*
    * Generate the first part of barcode numeric information without date factor
    * @param: $requiredData (sabStruct): The sabStruct object containing the invoice information to process the code.
    * @returns: (int): The required field number for barcode according with FEBRABAN defaults
    */
    public function barCodeDataFixedValues(sabStruct $requiredData)
    {
        $this->barCodeData->setBarCodeField('bankCode',    $requiredData->getBankCode(),  3, 0);
        $this->barCodeData->setBarCodeField('moneyCode',   $requiredData->getMoneyCode(), 1, 0);
        $this->barCodeData->setBarCodeField('barCodeDV',   'X', 1, 0);
        $this->barCodeData->setBarCodeField('invoiceValue',$this->formatField(self::onlyNumbers($requiredData->getInvoiceValue()), 14, 0));
    }

   /*
    * Generate the first part of open digitable number
    * @param: $requiredData (sabStruct): The sabStruct object containing the invoice information to process the code.
    * @return: (int): The required field number for barcode according with FEBRABAN defaults
    */
    public function generateDigitableNumber(sabStruct $requiredData)
    {
        $this->digitableNumber->setDigitableNumberField(1, 'bankId',      $requiredData->getBankCode(),          3,  0);
        $this->digitableNumber->setDigitableNumberField(1, 'moneyCode',   $requiredData->getMoneyCode(),         1,  0);
        $this->digitableNumber->setDigitableNumberField(1, 'openField',   $this->extractBarCodePosition(19, 5),  5,  0);
        $this->digitableNumber->setDigitableNumberfield(1, 'mainFieldDV', 'X',                                   1,  0);
        $this->digitableNumber->setDigitableNumberField(2, 'openField2',  $this->extractBarCodePosition(24, 10), 10, 0);
        $this->digitableNumber->setDigitableNumberField(2, 'mainFieldDV', 'Y',                                   1,  0);
        $this->digitableNumber->setDigitableNumberField(3, 'openField3',  $this->extractBarCodePosition(34, 10), 10, 0);
        $this->digitableNumber->setDigitableNumberField(3, 'mainFieldDV', 'Z',                                   1,  0);
        $this->digitableNumber->setDigitableNumberField(4, 'globalDV',    $this->extractBarCodePosition(4, 1),   1,  0);
        $this->digitableNumber->setDigitableNumberField(5, 'openField4',  $this->extractBarCodePosition(5, 14),  14, 0);
    }
   
   /*
    * Get specific positions on bar code values to process the open digitable number
    * TODO: Put a decent name for this function
    * @param: No parameters needed, internal values are used
    * @return: The value of  barcode position
    */
    public function extractBarCodePosition($pos, $size)
    {
        if ( ($pos>=0) && ($pos<=self::BARCODE_SIZE) )
        {
            if ( ($pos+$size) <= self::BARCODE_SIZE )
            {
                if ( strlen($this->barCodeData->returnBarCode()) == self::BARCODE_SIZE )
                {
                    return substr(fields::onlyNumbers($this->barCodeData->returnBarCode()), $pos, $size);
                }
                else
                {
                    $this->errors[] = _('Tamanho do código de barras inválido ao gerar valor digitável em aberto');
                    return false;
                }
            }
            else
            {
                $this->errors[] = _('Intervalo do código de barras inválido, os tamanhos do intervalo são ').($pos+$size)._(' e o tamanho do código de barras é ').self::BARCODE_SIZE;
                return false;
            }
        }
        else
        {
            $this->errors[] = _('Posição do código de barras inválida, faixa permitida vai de 0 a ').self::BARCODE_SIZE._(', especificada ').$pos;
            return false;
        }
    }

   /*
    * Get the open digitable number processed
    * @param: No parameters needed
    * @return: The open digitable number property, if that don't make the size specifications, the 
    *          system returns false and register an internal error.
    *
    */
    public function getDigitableNumber()
    {   
        $digitableNumberValue = $this->digitableNumber->returnDigitableNumber();
        
        if ( strlen($digitableNumberValue) == self::DIGITABLENUMBER_SIZE )
        {
            return $this->formatDigitableNumber($digitableNumberValue);
        }
        else
        {
            $this->errors[] = _('Especificações de tamanho inválidas, talvez o número digitado não foi totalmente concluído');
            return false;
        }
    }

   /*
    * Format the digitable number with specific separators
    * @param: Digitable number value
    * @return: Digitable number with specific separators.
    */
    private function formatDigitableNumber($digitableNumber)
    {
        $sep[] = substr($digitableNumber, 0, 5);
        $sep[] = '.';
        $sep[] = substr($digitableNumber, 5, 5);
        $sep[] = ' ';
        $sep[] = substr($digitableNumber, 10, 5);
        $sep[] = '.';
        $sep[] = substr($digitableNumber, 15, 6);
        $sep[] = ' ';
        $sep[] = substr($digitableNumber, 21, 5);
        $sep[] = '.';
        $sep[] = substr($digitableNumber, 26, 6);
        $sep[] = ' ';
        $sep[] = substr($digitableNumber, 32, 1);
        $sep[] = ' ';
        $sep[] = substr($digitableNumber, 33, 14);
        return implode($sep, '');
    }    

   /*
    * Get the bar code number
    * @param: No parameters needed
    * @return: The bar code number, if the bar code don't follow the size specifications, the
    *          system returns false and register an internal error.
    */
    public function getBarCodeNumber()
    {
        $barCodeData = $this->barCodeData->returnBarCode();
        if ( strlen($barCodeData) == self::BARCODE_SIZE )
        {
            return $barCodeData;
        }
        else
        {
            $this->errors[] = _('Especificações de tamanho inválidas, talvez o número digitado não foi totalmente concluído');
            return false;
        }
    }

    public function modulo11($num, $factor = 2, $factorMax = 9)
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
        if ( in_array($mod, array(0, 10, 1)) )
        {
            return 1;        
        }
        else
        {
           return 11 - $mod;
        }
    }

   /*
    * Calculate the modulo10 (extracted by boletoPHP)
    * @param: $num (int): Number to generate the modulo10
    * @return: The number for modulo 10
    */
    public function modulo10($num, $factor = 2, $max=false)
    {
        $numTotal10 = 0;
        for ($i = strlen($num); $i > 0; $i--) 
        {
            $numbers[$i] = substr($num,$i-1,1);
            $partial10[$i] = $numbers[$i] * $factor;
            
            if ($partial10[$i] > 9 && $max)
            {
                $partial10[$i] = $partial10[$i] - 9;
            }
            
            $numTotal10 .= $partial10[$i];
            if ($factor == 2) 
            {
                $factor = 1;
            }
            else 
            {
                $factor = 2; 
            }
        }
        $sum = 0;
        for ($i = strlen($numTotal10); $i > 0; $i--) 
        {
            $numbers[$i] = substr($numTotal10,$i-1,1);
            $sum += $numbers[$i]; 
        }
        $reminder = $sum % 10;
        if ( ($reminder >= 10) || ($reminder == 0) )
        {
            return 0;
        }
        else
        {
            return 10 - $reminder;
        }
    }

   /*
    * Return the errors
    * @param: No parameters needed
    * @return: (array) Array containing errors
    */
    public function getErrors()
    {
        return $this->errors;
    }
}
?>
