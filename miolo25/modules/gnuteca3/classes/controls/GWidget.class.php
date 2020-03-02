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
class GWidget extends MBaseGroup
{
    public $module;
    public $transaction;

    public function __construct($name, $caption, $close, $icon)
    {
        //parent::__construct($name, $caption, $controls, $disposition);
        //$close
        parent::__construct($name, $caption);
        //$this->box->id = 'widget_'.$name;
        $this->setIcon($icon);
        $this->module = 'gnuteca3';

        //chama a função padrão que monta o widget
        $this->widget();
    }

    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
    }

    public function getTransaction()
    {
        return $this->transaction;
    }

    public function checkAccess()
    {
        if ($this->transaction)
		{
			return GPerms::checkAccess($this->transaction, 'SEARCH', false);
		}
        
		return true;
    }

    public function setClose($closeAction)
    {
        //$this->box->boxTitle->setClose( $closeAction) ;
    }

    public function setIcon($icon)
    {
        $MIOLO = MIOLO::getInstance();
        $icon  = GUtil::getImageTheme($icon);
        //$this->box->boxTitle->setIcon( $icon );
    }

    public function setControls($controls,$recursive=false)
    {
        foreach ($controls as $line => $control )
        {
            if ( $control instanceof MTableRaw && method_exists($control, 'generate') )
            {
                $controls[$line] = new MDiv(null, $control->generate(),'GWidgetTableDiv');
            }
        }

        parent::setControls($controls, $recursive);
    }

    public function generate()
    {
        if ( $this->checkAccess())
        {
            $this->generateInner();
            if ( $this->scrollable )
            {
                $f[]  = new MDiv( '', $this->caption, 'mScrollableLabel' );
                $html = $this->getInnerToString();
                $f[]  = $div = new MDiv( '', $html, 'mScrollableField' );
                $div->height = $this->scrollHeight;
            }
            else
            {
                $class = $this->getClass();
                $this->setClass('mBaseGroup', false);
                $f = $this->getRender( 'fieldset' );
            }
            return $f;
        }
        else
        {
            return null;
        }
    }
}
?>