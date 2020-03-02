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
 * @author Guilherme Soares Soldatelli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Guilherme Soares Soldatelli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 30/11/2011
 *
 **/
class FrmMaterialHistorySearch extends GForm
{
    public $MIOLO;
    public $module;
    
    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->setAllFunctions('MaterialHistory');
        $this->setGrid('GrdMaterialHistory');
        parent::__construct();
    }
    
    public function mainFields()
    {
        $beginDateHourS = new MTimeStampField('beginDateHourS',null,_M('Data/Hora Início'));
        $endDateHourS = new MTimeStampField('endDateHourS',null,_M('Data/Hora Fim'));

        $fields[] = new MTextField('controlNumberS', null, _M('Número de controle', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('revisionNumberS', null, _M('Revisão', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('operatorS', null, _M('Operador', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('chancestypeS', null, _M('Operação', $this->module), $this->business->listChangeTypes());
        $fields[] = $beginDateHourS;
        $fields[] = $endDateHourS;

        $fields[] = new MTextField('fieldIdS', null, _M('Campo', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MTextField('subFieldIdS', null, _M('Subcampo', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MTextField('currentContentS', null, _M('Conteúdo atual', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MTextField('previousContentS', null, _M('Conteúdo anterior', $this->module), FIELD_DATE_SIZE);

        $this->setFields( $fields );
        $this->_toolBar->hideButton( MToolBar::BUTTON_NEW );
        $this->_toolBar->hideButton( MToolBar::BUTTON_DELETE );
    }
}
?>