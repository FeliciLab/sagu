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
 *
 * @since
 * Class created on 04/08/2008
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdPolicy extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $home = 'main:configuration:policy';

        $columns = array(
            new MGridColumn( 'Privilege Group code', MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Grupo de privilégio', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Código do vínculo', $this->module), MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Vínculo', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Código do gênero do material', $this->module), MGrid::ALIGN_LEFT,   null, null, false, null, true ),
            new MGridColumn( _M('Gênero do material', $this->module), MGrid::ALIGN_LEFT,   null, null, true, null, true ),
            new MGridColumn( _M('Data', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true, MSort::MASK_DATE_BR ),
            new MGridColumn( _M('Dias de empréstimo', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de empréstimo', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de renovação', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Valor da multa', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Valor da multa momentânea por @1', $this->module,(LOAN_MOMENTARY_PERIOD == 'H' )? 'hora':'dia'), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Penalidade por atraso', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de reserva', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Dias de espera por reserva', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de reserva de nível inicial', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Dias de espera por reserva no nível inicial', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Limite de renovações web', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true ),
            new MGridColumn( _M('Bônus de renovação web', $this->module), MGrid::ALIGN_CENTER, null, null, true, GUtil::getYesNo(), null, true ),
            new MGridColumn( _M('Adicional de dias para feriado', $this->module), MGrid::ALIGN_RIGHT,  null, null, true, null, true )
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'          => 'update',
            'privilegeGroupId'  => '%0%',
            'linkId'            => '%2%',
            'materialGenderId'    => '%4%'
        );

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);

        $args = array(
            'function'          => 'delete',
            'privilegeGroupId'  => '%0%',
            'linkId'            => '%2%',
            'materialGenderId'    => '%4%'
        );

        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->setRowMethod($this, 'checkRowDate');
    }


    function checkRowDate($i, $row, $actions, $columns)
    {
        $data = new GDate($columns[6]->control[$i]->value);
        $columns[6]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));
    }

    /**
     * Este generate é especifico para a grid de politicas, porque os itens que
     * vão ser excluídos precisam ser encontrados manualmente na grid.
     *
     * @return string
     */

    public function generate()
    {
        $this->setPrimaryKey(array('privilegeGroupId'=> '0', 'linkId'=>'2', 'materialGenderId'=>'4')); //Define primarykeys com indices apontando para as colunas especificas da grid para este caso.

        return parent::generate();
    }
}
?>
