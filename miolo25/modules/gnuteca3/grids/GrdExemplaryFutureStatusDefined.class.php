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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 21/10/2008
 *
 **/
class GrdExemplaryFutureStatusDefined extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();

        $yn = GUtil::listYesNo();
        $columns = array(
            new MGridColumn(_M('Código', $this->module), MGrid::ALIGN_LEFT,  null, null, true, null, true),
            new MGridColumn(_M('Estado do exemplar', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Número do exemplar', $this->module),      MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Aplicado', $this->module),          MGrid::ALIGN_CENTER, null, null, true, $yn,  true),
            new MGridColumn(_M('Data', $this->module),             MGrid::ALIGN_LEFT,   null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Operador', $this->module),         MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Observação', $this->module),      MGrid::ALIGN_LEFT,   null, null, true, null, true),
            new MGridColumn(_M('Observação do e-mail de cancelamento de reserva', $this->module),      MGrid::ALIGN_LEFT,   null, null, true, null, true),
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'                       => 'update',
            'exemplaryFutureStatusDefinedId' => '%0%'
        );
        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        
        $this->setRowMethod($this, 'checkValues');
    }


    public function checkValues($i, $row, $actions, $columns)
    {
        $data = new GDate($columns[4]->control[$i]->value);
        $columns[4]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
    }
}
?>
