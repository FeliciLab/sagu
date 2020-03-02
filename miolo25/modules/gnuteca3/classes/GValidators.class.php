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
 * Replace of original MIOLO validator functions
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 21/01/2009
 *
 **/
class GValidators //extends MGrid
{
	public $errors;
	public $validators = array();
	public /*GForm*/ $form;

    /**
     * Define os validadores
     * @param array $validators
     */
    public function setValidators($validators)
    {
        if ( is_null( $validators ) )
        {
            $this->validators = array();
            return false;
        }

        if ( !is_array( $validators ) )
        {
            $validators = array( $validators );
        }

        /*MRequiredValidator Object (
         [field] =>
         weekDayId [min] => 0
         [max] => 3
         [type] => required
         [chars] => ALL
         [mask] =>
         [checker] =>
         [msgerr] => algum erro obscuro
         [html] =>
         [label] => Errão
         [value] =>
         [hint] =>
         [form] =>
         [formName] =>
         [showLabel] => 1
         [autoPostBack] =>
         [validator] =>
         [_numberId:private] => 46
         [id] => required
         [uniqueId] => m46
         [cssClass] =>
         [enabled] => 1 */

        //filtra em função do $this->miolo, para ser possível guardar na sessão
        foreach ( $validators as $line => $validator )
        {
            $valid[$line] = $this->convertValidator($validator);
        }

        $this->validators = $valid;
    }

    /**
     *
     * @param MValidator $validator
     */
    public function addValidator($validator)
    {
        $this->validators[] = $this->convertValidator($validator);
    }

    private function convertValidator($validator)
    {
        $valid = new stdClass();

        $valid->id     = $validator->id;
        $valid->field  = $validator->field;
        $valid->min    = $validator->min;
        $valid->max    = $validator->max;
        $valid->type   = $validator->type;
        $valid->chars  = $validator->chars;
        $valid->mask   = $validator->mask;
        $valid->checker= $validator->checker;
        $valid->msgerr = $validator->msgerr;
        $valid->html   = $validator->html;
        $valid->label  = $validator->label;
        $valid->value  = $validator->value;
        $valid->hint   = $validator->hint;
        $valid->regexp = $validator->regexp;

        return $valid;
    }


    /**
     * Retornar array com validadores
     * 
     * @return array com validadores
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Define o formulário a validar os dados
     * 
     * @param GForm $form 
     */
    public function setForm(GForm $form)
    {
        $this->form = $form;
    }


	/**
	 * Return the message to show for validator
	 *
	 * @param string $message to show
	 * @param object $valid validator do detect the message
	 * @return string the message
	 */
	public function getValidatorMessage($message, $valid)
	{
	    $module = MIOLO::getCurrentModule();
	    $msg = _M($message , $module, $valid->field);

	    if ( $valid->msgerr )
	    {
            $msg = $valid->msgerr;
	    }
	    else if ( $valid->label )
	    {
            $msg = _M( $message, $module, $valid->label);
	    }
	    else if ( $this->form )
	    {
            //Get label name of form field
	    	$field = $this->form->getField($valid->field);

            //procura label no campo

            $value = '';
            if ( $field->label ) //pega label do campo normal (MTextField)
	    	{
                $value = $field->label;
	    	}
            elseif ( $field->caption ) //pega label da GnutecaRepetitiveField
            {
                $value = $field->caption;
            }
            else//procura label do lado do campo
            {
                $controls = $this->form->getControls();
                if ( is_array($controls) )
                {
                    foreach ($controls as $i=>$control)
                    {
                        if ( strlen($value) == 0 ) //só procura se ainda não tiver achado a label
                        {
                            //pega containers do formulário
                            if ( $control instanceof MContainer ) //se for da instância mcontainer (mhcontainer, mvcontainer e gcontainer)
                            {
                                $internalControls = $control->getControls();
                                if (is_array($internalControls) )
                                {
                                    $tmpValue = '';
                                    foreach ($internalControls as $k=>$internalControl)
                                    {
                                        if ($internalControl instanceof MLabel)
                                        {
                                            $tmpValue = $internalControl->value;
                                        }

                                        if ( $internalControl->name == $field->name) //achar o campo
                                        {
                                            $value = $tmpValue;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        else
                        {
                            //retira o ":" se estiver no final da string
                            if (substr($value, -1, 1) == ':')
                            {
                                $value = substr($value, 0, strlen($value)-1);
                            }

                            break;
                        }
                    }
                }
            }
            $msg = _M( $message, $module, $value);
	    }
        
	    return $msg . ' ';
	}


	public function validate( $data = NULL, $validators = NULL )
	{
		$errors = $this->errors;

        //tenta encontrar validadores, caso não sejam passados
		if (!$validators)
		{
            $validators = $this->getValidators();
		}

        //tenta encontrar os dados caso, não sejam passados
		if (!$data)
		{
            foreach ($validators as $v)
            {
                $field = $v->field;
                $data->$field = MIOLO::_REQUEST( $field );
            }
		}

        //caso não tenha validadores retorna os erros definidos no objeto
		if (!$validators ||  !is_array($validators)  )
	    {
	        return $errors;
	    }

        foreach ($validators as $line => $valid)
        {
            $field = $valid->field; /*id do campo*/

            if ( $this->form )
            {
                $fieldObj =  $this->form->getField($field);
            }

            //tamanho máximo
            if ( $valid->max && $data->$field )
            {
                if ( strlen($data->$field) > $valid->max )
                {
                    $errors[$field] .= $this->getValidatorMessage( _M('"@1" poder ter no máximo "@2" caracteres.', $module, null, $valid->max ), $valid );
                }
            }

            //tamanho máximo
            if ( $valid->min && $data->$field )
            {
                if ( strlen($data->$field) < $valid->min )
                {
                    $errors[$field] .= $this->getValidatorMessage( _M('"@1" precisa ao menos "@2" caracteres.', $module, null, $valid->max ), $valid );
                }
            }

            //requerido
            if ( ($valid->type == 'required') )
            {
                //valor requerido na GRepetiveField
                if ( $fieldObj instanceof GRepetitiveField )
                {
                    $tmpData = GRepetitiveField::getData($field, true);
                    $quant = 0;

                    if ( is_array($tmpData) )
                    {
                        foreach ($tmpData as $i=>$tmpD)
                        {
                            if ( !$tmpD->removeData )
                            {
                                $quant++;
                            }
                        }
                    }

                    if ( $quant == 0 )
                    {
                        $errors[$field] .= $this->getValidatorMessage( _M('"@1"  requerido.', $module), $valid);
                    }
                }
                elseif ( $data->$field == '' && $_REQUEST[$field] !== '0') //tem que ser pego do request para reconhecer o 0, o miolo esta filtrando este valor
                {
                    $errors[$field] .= $this->getValidatorMessage( _M('"@1"  requerido.', $module), $valid);
                }
            }
            else if ($data->$field)
            {
            	$msg_invalid = $this->getValidatorMessage( _M('"@1"  inválido.', $module), $valid);

	            if ( ($valid->checker == 'REGEXP') && ($valid->regexp) )
	            {
	                $ok = ereg($valid->regexp, $data->$field);
	                if (!$ok)
	                {
	                    $errors[$field] .= $msg_invalid;
	                }
	            }
                else if ( ($valid->id == 'date') )
                {
                    $mask = ereg("/", $data->$field) ? "dd/mm/yyyy" : null;
                    $mask = ereg("-", $data->$field) ? "yyyy-mm-dd" : $mask;

                    if(strlen($data->$field) && is_null(GDate::construct($data->$field)->isValid()))
                    {
                        $errors[$field] .= $msg_invalid;
        			}

                }
                else if ( ($valid->checker == 'TIME') && (!ereg('^[0-9]{2}:[0-5][0-9]$', $data->$field)) )
                {
                	$errors[$field] .= $msg_invalid;
                }
                else if ( ($valid->checker == 'EMAIL') && (!is_valid_email($data->$field)) )
                {
                    $errors[$field] .= $msg_invalid;
                }
                else if ( ($valid->id == 'cep') && (!ereg("^[0-9]{5}-[0-9]{3}$", $data->$field)) )
                {
                    $errors[$field] = $msg_invalid;
                }
            }

            //verifica validador de unicidade do GRepetitiveField
			if ($valid->checker == 'unique')
			{
			    $tempData = GRepetitiveField::getData($data->GRepetitiveField, true);
                
			    if ( is_array($tempData) && $tempData )
			    {
                    $isEditing = is_numeric( $data->arrayItemTemp );

			        foreach ($tempData as $l => $i)
			        {
                        //verifica se é o mesmo registro que está editando
                        $isSame = ( $data->arrayItemTemp || $data->arrayItemTemp === '0' ) && $l == $data->arrayItemTemp;
                        
			            if ( ( ( $isEditing && !$isSame ) || !$isEditing ) && !$i->removeData)
			            {
			                if ($i->$field == $data->$field)
			                {
			                    $errors[$data->GRepetitiveField] .= $this->getValidatorMessage( _M('"@1" precisa ser único.', $module ), $valid);
			                }
			            }
			        }
			    }
			}
        }

	    return $errors;
	}
}

/**
 * Validador de unicidade do campo repetitivo.
 *
 */
class GnutecaUniqueValidator extends MValidator
{
    /**
     * Construct a GnutecaUniqueValidator
     *
     * @param string $field the id of the field to validate
     * @param string $label the label of validator
     * @param string $type the type of validator
     * @param string $msgerr the error message to show
     */
    function __construct( $field, $label=null, $type='optional', $msgerr=null )
    {
        parent::__construct();
        $this->id       = 'unique';
        $this->field    = $field;
        $this->label    = $label;
        $this->mask     = '';
        $this->type     = $type;
        $this->checker  = 'unique';
        //$this->min      = 0;
        //$this->max      = $max;
        $this->chars    = 'ALL';
        $this->msgerr   = $msgerr;
    }
}

/**
 * Validador de data do Gnuteca.
 *
 */
class GnutecaDateValidator extends MValidator
{

	function __construct( $field, $label = null, $msgerr=null, $mask = "dd/mm/yyyy" )
	{
		parent::__construct();
		$this->id       = 'date';
		$this->field    = $field;
		$this->label    = $label;
		$this->mask     = $mask;
		$this->type     = 'date';
		$this->chars    = 'ALL';
		$this->msgerr   = $msgerr;
	}
}



/**
 * Verifica, por expreção regular, se um email é válido
 * @param string $email
 * @return boolean
 */
function is_valid_email($email)
{
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
	{
        return FALSE;
	}
    
	return TRUE;
}

/**
 * Verifica se o valor passado é um CNPJ válido
 *
 * @author Gabriel Fróes - www.codigofonte.com.br
 * @param string $cnpj o dado em si
 * @return boolean se o cpng é válido ou não
 */
function validaCNPJ($cnpj)
{
    if (strlen($cnpj) <> 18) return 0;
    $soma1 = ($cnpj[0] * 5) +

    ($cnpj[1] * 4) +
    ($cnpj[3] * 3) +
    ($cnpj[4] * 2) +
    ($cnpj[5] * 9) +
    ($cnpj[7] * 8) +
    ($cnpj[8] * 7) +
    ($cnpj[9] * 6) +
    ($cnpj[11] * 5) +
    ($cnpj[12] * 4) +
    ($cnpj[13] * 3) +
    ($cnpj[14] * 2);
    $resto = $soma1 % 11;
    $digito1 = $resto < 2 ? 0 : 11 - $resto;
    $soma2 = ($cnpj[0] * 6) +

    ($cnpj[1] * 5) +
    ($cnpj[3] * 4) +
    ($cnpj[4] * 3) +
    ($cnpj[5] * 2) +
    ($cnpj[7] * 9) +
    ($cnpj[8] * 8) +
    ($cnpj[9] * 7) +
    ($cnpj[11] * 6) +
    ($cnpj[12] * 5) +
    ($cnpj[13] * 4) +
    ($cnpj[14] * 3) +
    ($cnpj[16] * 2);
    $resto = $soma2 % 11;
    $digito2 = $resto < 2 ? 0 : 11 - $resto;
    return (($cnpj[16] == $digito1) && ($cnpj[17] == $digito2));
}
?>