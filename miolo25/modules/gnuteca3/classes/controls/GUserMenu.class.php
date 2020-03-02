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
 *
 * @since
 * Class created on 16/03/2011
 *
 **/

class GUserMenu extends MDiv
{
	public $orientation;

	public function __construct( $orientation = 'horizontal' )
	{
		$class = $orientation ==  'horizontal' ? 'gUserMenu' : 'gUserMenuVertical';
        parent::__construct('searchPanel', null, $class );
        $this->orientation = $orientation;
	}

	public function getUrl()
	{
		$MIOLO           = MIOLO::getInstance();
		$module          = MIOLO::getCurrentModule();
		$busAuthenticate = $MIOLO->getBusiness($module, 'BusAuthenticate');

        //Se tiver um operador logado, pega a pesquisa simples configurada por ele
        if (GOperator::isLogged())
        {
            $location = 'main:search:simpleSearch&formContentId=1&formContentTypeId=1';
        }
        else
        {
            $location = 'main:search:simpleSearch';
        }

        $url = array();
        $txt = explode("\n", GNUTECA_USER_MENU_LIST );

        //cria o array com as urls
        foreach ( $txt as $line => $info )
        {
        	if ( $info )
        	{
	        	$explode = explode(';',$info);
	        	$url[ trim( $explode[ 0 ] ) ]->action = trim( $explode[1] ) ;

	        	if ( $explode[2])
	        	{
                    $url[ trim( $explode[ 0 ] ) ]->icon   = trim( $explode[2] );
	        	}
        	}
        }
		return $url;
	}


	public function generate()
	{
		$MIOLO  = MIOLO::getInstance();
		$module = MIOLO::getCurrentModule();
		$ui 	= $MIOLO->getUi();
		$url 	= self::getUrl();

        $buttons[] = new MDiv('myLibraryTitle', _M('Minha biblioteca', 'gnuteca3'),'myLibraryTitle');

		if ( is_array( $url ) )
		{
			foreach ( $url as $line => $info )
			{
				$content    = null;
				$imageUrl	= Gutil::getImageTheme( $info->icon );
                //$content[]  = new MImage( null, $line, $imageUrl);
				//$content[]  = $imageButton = new MImageButton( null,  _M($line, $module), $info->action.';', $imageUrl );
                //$image = new MImage( null, $line, $imageUrl );
                //evita que se chegue nele através do tab, referente a acessibilidade
                //$imageButton->addAttribute('tabIndex','-1');
                $image = "<img src='{$imageUrl}'></img>";
				$content[]  = $mLink = new MLink(null, $image. $line, $info->action.';');
                $mLink->addAttribute('tabindex','50');
				$class      = $this->orientation ==  'horizontal' ? 'gUserMenuButton' : 'gUserMenuButtonVertical';
				$classEx    = $this->orientation ==  'horizontal' ? 'gUserMenuButtonEx' : 'gUserMenuButtonExVertical';
                $inner      = new MDiv(null, $content, $classEx);
				$button     = new MDiv(null, $inner, $class);
				$buttons[]	= $button;
			}
		}

		$this->setInner( $buttons );

		return parent::generate();
	}
}
?>
