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
 *
 * Component that extends MSelection and implement Gnuteca needed behavior
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 12/01/2009
 *
 **/
class GSelection extends MSelection
{
	protected $_options;
	protected $hideDefaultSelect;

	/**
	 * Construct the Gnuteca GnutecSelection, it has an addiontional parameter $hideDefaultSelect
	 *
	 * @param string       $name the id of the field
	 * @param string       $value the value of the field
	 * @param string       $label the label of the field
	 * @param array        $options the options of the field
	 * @param boolean      $showValues if is to showvalues or not
	 * @param string       $hint field default hint
	 * @param integer      $size the size of compoment
	 * @param boolean      $hideDefaultSelect if is to hide default --select-- option of MSelection
	 */
	public function __construct( $name='', $value='',$label='', $options, $showValues=false, $hint='',$size='', $hideDefaultSelect = false)
	{
        parent::__construct( $name, $value, $label, null, $showValues, $hint, $size );

	    if ( !$options )
        {
            $this->hideDefaultSelect(true);
        }

        if ( $hideDefaultSelect )
        {
        	$this->hideDefaultSelect();
        }

        $this->setOptions( $options ? $options : array(''=> _M('Dados não encontrados') ) );
	}

	/**
	 * Hide --select-- default option of miolo.
	 *
	 * @param boolean  $hide Hide --select-- default option of miolo.
	 */
	public function hideDefaultSelect( $hide = true )
	{
		$this->hideDefaultSelect = $hide;
		$this->setOptions();
	}

	/**
	 * Define the options of this field
	 *
	 * @param array $options the options array
	 */
    public function setOptions( $options = null )
    {
    	if ($options)
    	{
            $this->_options = $options;
    	}
    	else
    	{
            $options = $this->_options;
    	}

    	if ( !$this->hideDefaultSelect  && $options)
    	{
    		parent::setOptions( $options );
    	}
    	else
    	{
	        $MIOLO = MIOLO::getInstance();
	        $MIOLO->Assert( is_array($options) ,_M('$options necessárias para um array') );
            $this->options = $options;
    	}
	}

    /**
     * Define se pode selecionar várias informações.
     */
    public function setMultiple( $multiple = true )
    {
        if ( $multiple )
        {
            $this->addAttribute('multiple','multiple');
            $this->name = $this->name.'[]'; //somente o name em função do post
        }
    }
    
    /**
     * Retorna se pode ou não selecionar vários valores
     *
     * @return string
     */
    public function getMultiple()
    {
        return $this->getAttribute('multiple');
    }

	public function generate()
	{
		if ($this->validator->type == 'required')
		{
			$this->hideDefaultSelect();
		}
		return parent::generate();
	}
}
?>