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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 08/01/2009
 *
 **/
class GGridActionIcon extends MGridAction
{
    public $path;
    public $visible = true;
    public $imageName = true;
    protected $tabIndex;

    public function __construct($grid, $value, $href=null, $alt = null, $imageName = null)
    {
        parent::__construct($grid, 'image', $alt, $value, $href);

        if ( !stripos($value, 'png'))
        {
            $this->path[true]  = GUtil::getImageTheme($this->value.'.png');
            $this->path[false] = GUtil::getImageTheme($this->value.'.png');;
        }
        else
        {
            $this->path[true]  = $value;
            $this->path[false] = $value;
        }

        $this->imageName = $imageName;
    }

    public function setVisible($visible)
    {
        $this->visible = $visible;
    }

    public function show()
    {
        $this->setVisible(true);
    }

    public function hide()
    {
        $this->setVisible(false);
    }

    public function getVisible($visible)
    {
        return $this->visible;
    }

    public function setTabIndex( $tabIndex )
    {
        $this->tabIndex = $tabIndex;
    }

    public function getTabIndex(  )
    {
        return $this->tabIndex;
    }

    public function generate()
    {
        $path   = $this->path[$this->enabled];
        $class  = "m-grid-action-icon";

        if ($this->enabled)
        {
            $row = $this->grid->data[$this->grid->currentRow];
            $href = $this->generateLink( $row );
            $control = new MImageButton( $this->imageName , $this->alt, $href, $path);
            $control->addAttribute('title', $this->alt);
            $control->addAttribute('alt', $this->alt);

            if ( $this->tabIndex )
            {
                $control->addAttribute( 'tabindex' , $this->tabIndex );
            }
        }
        else
        {
            $control = new MImage( $this->imageName , $this->alt, $path);
            $control->addAttribute('title', $this->alt);
            $control->addAttribute('alt', $this->alt);
            $control->addStyle('opacity', '0.3');
            $control->addStyle('filter', 'Alpha(opacity=30)');
        }

        if ( !$this->visible )
        {
            $control->addStyle('display','none');
        }

        return $control;
    }
}
?>