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
 * Grid from Google Book Integration - search
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 05/09/2010
 *
 **/
class GrdGoogleBookSearch extends GGrid
{
    public function __construct($data)
    {
        global $MIOLO, $module, $action;
        $module = 'gnuteca3';
        $this->module = 'gnuteca3';
        
        $columns = array(
            new MGridColumn(_M('Índice', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Capa', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Google control Number', $this->module), MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Número de controle do Gnuteca', $this->module), MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Current date', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('unused', $this->module), MGrid::ALIGN_LEFT,null, null, false, null, true),
            new MGridColumn(_M('unused', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('links', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, false),
            new MGridColumn(_M('Embeddable',$this->module), MGrid::ALIGN_LEFT,  null, null, false, GUtil::getYesNo(), true),
            new MGridColumn(_M('Open Access', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Visualização', $this->module), MGrid::ALIGN_LEFT,  null, null, true, $this->getView(), true),
            new MGridColumn(_M('Título', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Autor', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Editora', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Assunto', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Ano', $this->module), MGrid::ALIGN_RIGHT,  null, null, false, null, true),
            new MGridColumn(_M('Abstract', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, false),
            new MGridColumn(_M('Formato', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Isbn', $this->module), MGrid::ALIGN_RIGHT,  null, null, false, null, true),
            new MGridColumn(_M('Identifiers', $this->module), MGrid::ALIGN_LEFT,  null, null, false, null, true),
            new MGridColumn(_M('Dados', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
        );

        parent::__construct($data, $columns);
        $this->setIsScrollable();
        $this->addActionIcon(_M('Detalhes', $this->module), GUtil::getImageTheme('detail.png'), "javascript:" . GUtil::getAjax('detail','%0%'));
        $this->setRowMethod($this, 'rowMethod');
        $this->useCSV = false;
    }

    public function getView($key=null)
    {
        $MIOLO  = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $data = array( DB_TRUE  => _M('Sim',$module), DB_FALSE => _M('Não', $module),'p'=>_M('Parcial', $module) );
        
        if ($key)
        {
            return $data[$key];
        }

        return $data;
    }

    public function rowMethod($i, $row, $actions, $columns)
    {
        $data       = $this->getData();
        $line       = $data[$i];

        //thumbnail
        $thumb = $columns[1]->control[$i]->getValue();

        if ( $thumb )
        {
            $columns[1]->control[$i]->setValue( "<img src=$thumb' alt='{$columns[16]->control[$i]->getValue()}' title='{$columns[16]->control[$i]->getValue()}' />" );
        }
    }
}
?>