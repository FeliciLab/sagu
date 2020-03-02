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
 * Class Validators
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
 
class validators
{
    private $roundValue = 2;
    private $separator = '.';
    private $thousandSeparator = '';

   /*
    * TODO: Can choose date format as you wish. ;)
    * This function checks a date (only in dd/mm/yyyy format) at now
    * @params: $date (string) String containing the date to check
    * @return: (boolean) True if correct, otherwise false
    */
    public function checkDate($date)
    {
        if (is_string($date))
        {
            $d = explode('/', $date);
            if (sizeof($d) == 3)
            {
                if (checkDate($d[1], $d[0], $d[2]))
                {
                    return true;
                }
                else
                {
                    $this->error = _('Data inválida');
                    return false; 
                }
            }
            else
            {
                $this->error = _('Formato do texto inválido');
                return false;
            }
        }   
        $this->error = _('Tipo de data inválida');
        return false;
    }

   /*
    * This function checks a date (only in dd/mm/yyyy format)
    * @params: $date (string) String containing the date to check
    * @return: (boolean) True if correct, otherwise false
    */
    public function checkReal($value)
    {
        if (is_double($value) || is_int($value))
        {
            return number_format($value, $this->roundValue, $this->separator, $this->thousandSeparator);
        }
        else
        {
            $this->error = _('O valor informado não é do tipo real');
            return false;
        }
    }

   /*
    * This function checks a date (only in dd/mm/yyyy format)
    * @params: $date (string) String containing the date to check
    * @return: (boolean) True if correct, otherwise false
    */
    public function checkInt($value)
    {
        if (is_int($value))
        {
            return true; 
        }
        else
        {
            $this->error = _('O valor informado não é do tipo inteiro');
            return false;
        }
    }

   /*
    * This function checks a string
    * @params: $date (string) String containing the string to check :p
    * @return: (boolean) True if correct, otherwise false
    */
    public function checkString($value)
    {
        if (is_string($value))
        {
            return true; 
        }
        else
        {
            $this->error = _('O valor informado não é do tipo string');
            return false;
        }
    }

   /*
    * Return the errors if a validation failed
    * @params: No parameters required
    * @return: (String) The message containing details for the last error;
    *
    */
    public function getErrors()
    {
        return $this->error;
    }
}
?>
