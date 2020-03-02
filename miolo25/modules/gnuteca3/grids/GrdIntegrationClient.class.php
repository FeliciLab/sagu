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
 * @author Lucas Gerhardt [lucas_gerhardt@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 14/04/2014
 *
 * */
class GrdIntegrationClient extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;

    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $columns = array(
            new MGridColumn(_M('Código', $this->module), MGrid::ALIGN_RIGHT, null, null, true,  null, true),
            new MGridColumn(_M('Endereço', $this->module), MGrid::ALIGN_LEFT, null, null, true,  null, true),
            new MGridColumn(_M('Nome', $this->module), MGrid::ALIGN_LEFT, null, null, true,  null, true),
            new MGridColumn(_M('E-mail', $this->module), MGrid::ALIGN_LEFT, null, null, true,  null, true),
            new MGridColumn(_M('Quantidade de materiais', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Quantidade de exemplares', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Periodicidade de sincronização', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true),
            new MGridColumn(_M('Integration Server', $this->module), MGrid::ALIGN_LEFT,  null, null, false,  null, true),
            new MGridColumn(_M('Integration Client', $this->module), MGrid::ALIGN_LEFT,  null, null, false,  null, true),
            new MGridColumn(_M('Status', $this->module), MGrid::ALIGN_LEFT,  null, null, true,  null, true));

        parent::__construct($data, $columns);

        $args = array( 'function' => 'update','integrationClientId' => '%0%'        );//Make update action
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        
        $args = array( 'function' => 'delete','integrationClientId' => '%0%'  );//Make delete action

        $args['function'] = 'detail';

        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setIsScrollable();
    }
}
?>
