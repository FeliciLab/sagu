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
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 14/11/2013
 *
 **/


/**
 * Grid used by form to display search results
 **/
class GrdSipEquipament extends GSearchGrid
{
    public $MIOLO;
    public $module;
    public $action;


    public function __construct($data)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        
        $home = 'main:configuration:sipEquipament';

        $columns = array(
            new MGridColumn(_M('Descrição', $this->module), MGrid::ALIGN_LEFT, null, NULL, true, null, true),
            new MGridColumn(_M('Usuário', $this->module), MGrid::ALIGN_RIGHT, null, NULL, true, null, true),
            new MGridColumn(_M('Unidade Biblioteca', $this->module), MGrid::ALIGN_LEFT, null, NULL, true, null, true),
            new MGridColumn(_M('Empréstimo', $this->module), MGrid::ALIGN_CENTER,  null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Devolução', $this->module), MGrid::ALIGN_CENTER,  null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Renovação', $this->module), MGrid::ALIGN_CENTER, null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Bloqueia cartão', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Modo off-line', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Autentica apenas com senha', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Local Circulação de Material', $this->module), MGrid::ALIGN_LEFT,null, NULL, true, null, true),
            new MGridColumn(_M('Exceder limite de empréstimos', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Exceder limite de atrasos', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Exceder limite de penalidades', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true),
            new MGridColumn(_M('Exceder limite de multas', $this->module), MGrid::ALIGN_CENTER,null, NULL, true, GUtil::getYesNo(), true)
        );

        parent::__construct($data, $columns);

        $args = array(
            'function'      => 'update',
            'sipEquipamentId' => '%1%',
            'libraryunitid'   => '%2%',
            'makeLoan' => '%3%',
            'makeReturn' => '%4%',
            'makeRenew' => '%5%',
            'denyUserCard' => '%6%',
            'offlineMode' => '%7%',
            'requiredpassword' => '%8',
            'locationformaterialmovementid' => '%9%',
            'psLoanlimit' => '%10%',
            'psOverduelimit' => '%11%',
            'psPenaltylimit' => '%12%',
            'psFinelimit' => '%13%'
        );
        

        $hrefUpdate = $this->MIOLO->getActionURL($this->module, $this->action, null, $args);
        
        $args = array(
            'function' => 'delete',
            'sipEquipamentId' => '%1%',
            //'description' => '%1%'
            'libraryunitid' => '%2%'
        );
        
        $args['function'] = 'search';
        
        //Coloca atalho para o Histórico
        $imageHistoric = GUtil::getImageTheme('info-16x16.png');
       
        $this->setIsScrollable();
        $this->addActionUpdate( $hrefUpdate );
        $this->addActionDelete( GUtil::getAjax('tbBtnDelete_click', $args) );
        $this->addActionIcon(_M('Histórico', $this->module), $imageHistoric, GUtil::getAjax('showDetail', $args) );
        
    }
}
?>