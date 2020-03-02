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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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

class GoogleBookViewer extends MControl
{
    public $identifier;
    public $heigth;
    public $width;
    public $buttons;

    const BTN_CLOSE     = 'btnClose';
    const BTN_NEXT      = 'btnNext';
    const BTN_PREVIOUS  = 'btnPrevious';

    public function __construct($name, $identifier , $width, $height)
    {
        parent::__construct($name);
        $this->setAttribute('border','0');
        $this->setAttribute('frameborder','0');
        $this->setAttribute('framespacing','0');
        $this->setDimension($width, $height);
        $this->setIdentifier($identifier);
    }

    public function setCloseButton($btnClose)
    {
        $this->buttons[self::BTN_CLOSE] = $btnClose;
    }

    public function setDimension($width, $height)
    {
        $this->width  = str_replace('px','',$width  ? $width: '700');
        $this->height = str_replace('px','',$height ? $height: '415');
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function setISBNNIdentifier($isbn)
    {
        $this->identifier = 'ISBN:'.$isbn;
    }

    public function getIdentfier($identifier)
    {
        return $this->identifier;
    }

    public function generate()
    {
        $this->setAttribute('style',"width:{$this->width}px;height:{$this->height}px;");

        if ( $this->buttons )
        {
            $this->height = $this->height - 45;
        }

        $this->overflow = 'hidden';
        
        $src = "googleViewer.php?identifier={$this->identifier}&width={$this->width}&height={$this->height}&btnClose={$this->buttons[self::BTN_CLOSE]}";
        return "\n<iframe class=\"viewerCanvas\" id=\"{$this->name}\" name=\"{$this->name}\" src=\"{$src}\"" . $this->getAttributes(). ">\n</iframe>";
    }
}
?>