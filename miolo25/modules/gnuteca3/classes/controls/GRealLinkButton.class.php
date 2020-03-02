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
 * Make a button that is a link, but this is a real link ( it reload the browser )
 *
 * @author eduardo
 *
 */
class GRealLinkButton extends MLink
{
    public function __construct($id=NULL, $title=NULL, $link=NULL, $image=Null, $args=NULL)
    {
        $MIOLO        = MIOLO::getInstance();
        $module       = MIOLO::getCurrentModule();
        $href         = $MIOLO->getActionURL($module, $link, null, $args);

        $name = new MButton(null, $title , null,  $image, $args);
        //FIXME evento adicionado para tirar o miolo.doPostBack, ticket #8657
        $name->addEvent('click', 'return true;');
        $name = $name->generate();

        parent::__construct($id, $name , $href);
        $this->setGenerateOnClick(false);
    }
}
?>
