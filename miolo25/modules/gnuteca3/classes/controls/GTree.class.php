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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 **/

class GTree extends MDiv
{
	public $data;
    protected $closed = false;

    public function __construct($name, $data)
    {
    	parent::__construct( $name, '' );

    	if ($data)
    	{
    	   $this->setData($data);
    	}

        //valor padrão
        $this->closed = MUtil::getBooleanValue( SIMPLE_SEARCH_HIDE_EXEMPLAR );
    }

    public function setClosed( $closed )
    {
        $this->closed = $closed;
    }

    public function getClosed()
    {
        return $this->closed;
    }

    public function setData($data)
    {
    	$this->data = $data;
    }

    public function foreachData($data)
    {
        $MIOLO              = MIOLO::getInstance();
        $module             = MIOLO::getCurrentModule();

    	$imagePlus          = GUtil::getImageTheme('plus-8x8.png');
        $imagePlusObj       = new MImage('imagePlus', _M('Mais', $module) , $imagePlus);
        $imagePlusObj->addStyle('padding-right', '4px');

        $imageMinus         = GUtil::getImageTheme('minus-8x8.png');
        $imageMinusObj      = new MImage('imagePlus', _M('menos', $module) , $imageMinus);
        $imageMinusObj->addStyle('padding-right', '4px');

        //gera a imagem de acordo com estar fechada ou aberta
        if ( $this->closed  )
        {
            $imageGenerate = $imagePlusObj->generate();
        }
        else
        {
            $imageGenerate = $imageMinusObj->generate();
        }

    	if (is_array( $data ))
    	{
    		foreach ( $data as $line => $info)
    		{
    			$subFields = null;

    			if ( $info->content)
    			{
    				if ( is_array( $info->content))
    				{
    					$content = $this->foreachData( $info->content );
    				}
    				else
    				{
    					$content = $info->content;
    				}
    			}

    			if ( $content )
    			{
                    $signal = $imageGenerate;
    			}
    			else
    			{
    				$signal = '';
    			}

    			$divTitleName   = $this->name.'_title_'.$line .rand();
    			$divContenName  = $this->name.'_content_'.$line .rand();
    			$title          = new Span(null, $info->title, 'gTreeTitle');
    			$subFields[0]   = new MDiv( $divTitleName , $signal . $title->generate() );

    			if ($content)
    			{
                    $subFields[0]->addStyle('cursor','pointer');
                    $subFields[0]->addAttribute('onclick',"gnuteca.changeDisplay('$divContenName', '$divTitleName')" );
    			}

    			$subFields[1] = new MDiv( $divContenName , $content );

                //esconde se é para esconder por padrão
                if ( $this->closed )
                {
                    $subFields[1]->addStyle('display', 'none');
                }

                $divBody = new MDiv($this->name.'_body_'.$line.rand(), $subFields );
                $divBody->addStyle('padding','2px');

    			$fields[] = $divBody;
    		}
    	}
    	return $fields;
    }

    public function generate()
    {
    	$MIOLO              = MIOLO::getInstance();
    	$module             = MIOLO::getCurrentModule();

    	$imagePlus          = GUtil::getImageTheme('plus-8x8.png');
        $imagePlusObj       = new MImage('imagePlus', _M('Mais', $module) , $imagePlus);
        $imagePlusObj->addStyle('padding-right', '4px');

        $imageMinus         = GUtil::getImageTheme('minus-8x8.png');
        $imageMinusObj      = new MImage('imagePlus', _M('menos', $module) , $imageMinus);
        $imageMinusObj->addStyle('padding-right', '4px');

        $this->page->addJsCode("imagePlus = '$imagePlus'; imageMinus = '$imageMinus'; ");

    	$fields = $this->foreachData( $this->data );
    	$this->setInner( $fields );
    	$this->addStyle('padding', '2px');
    	return parent::generate();
    }
}
?>