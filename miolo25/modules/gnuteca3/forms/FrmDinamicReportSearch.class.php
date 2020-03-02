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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 **/
class FrmDinamicReportSearch extends GForm
{
    public $business;

    public function __construct()
    {
        $this->setTransaction('gtcLibraryUnit');
        $this->setBusiness('BusDinamicReport');
        $this->setGrid("GrdDinamicReport");
        $this->setSearchFunction('executeDinamicReport');
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = $table = new GSelection('table',null, _M('Relação', $this->module), $this->business->listTables() , false, false, null, true );
        $table->addAttribute('onchange', 'javascrip:'.Gutil::getAjax('onChangeTable'));
        
        $fields[] = new MDiv('divColumns');
        $fields[] = new MDiv('divOrderby');
        
        $conditions[] = array('=',_M('Igual','gnuteca3'));
        $conditions[] = array('>=',_M('Maior igual','gnuteca3'));
        $conditions[] = array('<=',_M('Menor igual','gnuteca3'));
        $conditions[] = array('ilike',_M('Parece','gnuteca3'));
        
        $type[] = array('and' , _M('E','gnuteca3') );
        $type[] = array('or' , _M('Ou','gnuteca3') );
        $type[] = array('not' , _M('Não','gnuteca3') );
        
        $columns[] = new MGridColumn(_M('Campo'), 'left', false, null, true, 'column', $order, $filter);
        $columns[] = new MGridColumn(_M('Tipo'), 'left', false, null, true, 'type', $order, $filter);
        $columns[] = new MGridColumn(_M('Condição'), 'left', false, null, true, 'condition', $order, $filter);
        $columns[] = new MGridColumn(_M('Filtro'), 'left', false, null, true, 'filter', $order, $filter);        
        
        $controls[] = new GContainer('divFilters',new GSelection('column',null, _M('Campo', $this->module), null , false, false, null, true ) );
        $controls[] = new GContainer('',new GSelection('type',null, _M('Condição', $this->module), $type, false, false, null, true ) );
        $controls[] = new GContainer('',new GSelection('condition',null, _M('Condição', $this->module), $conditions , false, false, null, true ) );
        $controls[] = new GContainer('',new MTextField('filter',null, _M('Valor'), FIELD_DESCRIPTION_SIZE ));
        
        $valids[] = new MRequiredValidator('column');
        $valids[] = new MRequiredValidator('condition');
        
        $fields[] = $filter = new GRepetitiveField( 'filters', _M('Filtros','gnuteca3'), $columns, null, true);
        $filter->setFields($controls);
        $filter->setValidators($valids);

        $this->setFields( $fields );
    }
    
    public function onChangeTable($args)
    {
        $tableColumns = $this->business->listColumns($args->table);
        
        $columns = new GSelection('columns',null, _M('Campos', $this->module), $tableColumns , false, false, null, true );
        $columns->setMultiple(true);
        $columns->addAttribute('size',count($tableColumns));

        $fields[] = new GContainer('', array($columns) );
        
        $this->setResponse($fields, 'divColumns');

        $columns = new GSelection('column',null, _M('Campo', $this->module), $tableColumns , false, false, null, true );
        
        $fields = new GContainer('', array($columns) );
        
        $this->setResponse(array($fields), 'divFilters');
        
        $orderType[] = array('Asc', _M("Ascendente", 'gnuteca3') );
        $orderType[] = array('desc', _M("Descendente", 'gnuteca3') );
        
        $columns = null;
        $columns[] = new GSelection('orderBy',null, _M('Ordenação', $this->module), $tableColumns , false, false, null, true );
        $columns[] = new GSelection('orderType',null, '', $orderType , false, false, null, true );
        
        $fields = new GContainer('', $columns );
        
        $this->setResponse(array($fields), 'divOrderby');
    }
}
?>