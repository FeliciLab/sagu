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
 * Grid
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 25/09/2008
 *
 **/
class GrdTag extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $yn = GUtil::getYesNo();
        $columns = array(
            new MGridColumn(_M('Código', $this->module), MGrid::ALIGN_RIGHT,  null, null, true,  null, true),
            new MGridColumn(_M('Código do subcampo', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Descrição', $this->module), MGrid::ALIGN_LEFT,   null, null, true,  null, true),
            new MGridColumn(_M('Observação', $this->module), MGrid::ALIGN_LEFT,   null, null, false, null, true),
            new MGridColumn(_M('É repetitivo', $this->module), MGrid::ALIGN_CENTER, null, null, true,  $yn,  true),
            new MGridColumn(_M('Tem subcampos', $this->module), MGrid::ALIGN_CENTER, null, null, true,  $yn,  true),
            new MGridColumn(_M('Está ativo', $this->module), MGrid::ALIGN_CENTER, null, null, true,  $yn,  true),
            new MGridColumn(_M('Em demonstração', $this->module), MGrid::ALIGN_CENTER, null, null, true,  $yn,  true),
            new MGridColumn(_M('É obsoleto', $this->module), MGrid::ALIGN_CENTER, null, null, true,  $yn,  true),
            new MGridColumn(_M('Ajuda', $this->module), MGrid::ALIGN_LEFT,   null, null, false, null, true),
        );

        parent::__construct($data, $columns);

        //Make update action
        $args = array(
            'function'   => 'update',
            'fieldId'    => '%0%',
            'subfieldId'  => '%1%'
        );
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        $this->addActionUpdate( $hrefUpdate );

        $args_d = array(
            'function'    => 'detail',
            'fieldId'     => '%0%',
            'subfieldId'  => '%1%'
        );

        $img        = GUtil::getImageTheme('report-16x16.png');
        $imgDelete  = GUtil::getImageTheme('table-delete.png');

        $this->addActionIcon(_M('Apagar', $this->module), $imgDelete, GUtil::getAjax('deleteTag', $args_d) );
        $this->setIsScrollable();
        
    }
}
?>