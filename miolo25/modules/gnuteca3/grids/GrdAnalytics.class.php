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
 * Class created on 29/07/2008
 *
 * */
class GrdAnalytics extends GSearchGrid
{

    public $MIOLO;
    public $module;
    public $action;
    public $busAnalytics;
    public $logLevel;
    public $accessType;

    public function __construct($data)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->action = MIOLO::getCurrentAction();
        $this->busAnalytics = $this->MIOLO->getBusiness($this->module, 'BusAnalytics');

        $columns = array(
            new MGridColumn(_M('Código', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Query', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Ação', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Evento', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Unidade de biblioteca', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Operador', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Código da pessoa', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Hora', $this->module), MGrid::ALIGN_LEFT, null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn(_M('Ip', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Navegador', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Nível do log', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Tipo do acesso', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Menu', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Tempo gasto', $this->module), MGrid::ALIGN_RIGHT, null, null, true, null, true)
        );

        parent::__construct($data, $columns);

        $args = array('function' => 'update', 'analyticsId' => '%0%');
        $this->setIsScrollable();
        $this->addActionUpdate($this->MIOLO->getActionURL($this->module, $this->action, null, $args));
        $args = array('function' => 'delete', 'analyticsId' => '%0%');
        $this->addActionDelete(GUtil::getAjax('tbBtnDelete_click', $args));
        $args['function'] = 'search';
        $this->setRowMethod($this, 'checkRow');

        //Define arrays com as informações do nível de log e tipo de acesso.
        $this->accessType = $this->busAnalytics->listAccessType();
        $this->logLevel = $this->busAnalytics->listLogLevel();
    }

    public function checkRow($i, $row, $actions, $columns)
    {
        //troca os códigos das constantes pelo valor escrito delas.
        $columns[10]->control[$i]->setValue($this->logLevel[$columns[10]->control[$i]->value]);
        $columns[11]->control[$i]->setValue($this->accessType[$columns[11]->control[$i]->value]);
    }

}

?>