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
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2010
 *
 **/
class GrdDomain extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $home = 'main:configuration:domain';

        $columns = array(
            new MGridColumn( _M('Domínio', $this->module),           MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Sequência', $this->module),        MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Chave', $this->module),                MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Abreviatura', $this->module),   MGrid::ALIGN_LEFT,  null, null, true, null, true ),
            new MGridColumn( _M('Etiqueta', $this->module),            MGrid::ALIGN_LEFT,  null, null, true, null, true ),
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'      => 'update',
            'domainId'  => '%0%',
            'sequence'     => '%1%'
        );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        $args = array(
            'function'      => 'delete',
            'domainId'  => '%0%',
            'sequence'     => '%1%'
        );

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setRowMethod($this, 'rowMethod');
    }
}
?>