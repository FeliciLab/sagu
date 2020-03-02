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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 05/10/2011
 *
 **/
$MIOLO->getClass( $module, 'controls/GMaterialDetail' );
class FrmMyLibrarySearch extends GForm
{
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('MyLibrary', array('myLibraryId') , array('myLibraryId'));
        parent::__construct();
    }

    public function mainFields()
    {   
        $fields[] = new MIntegerField('myLibraryIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new GPersonLookup('personIdS', _M('Pessoa','gnuteca3') );
        $fields[] = new MTextField('tableNameS', null, _M('Tabela',$this->module), FIELD_ID_SIZE );
        $fields[] = new MIntegerField('tableIdS',null, _M('Código da tabela', $this->module) , FIELD_ID_SIZE );
        $searchByPeriod = new MLabel(_M('Busca por periodo', $this->module) . ':');
        $beginDate = new MCalendarField('beginDateS', $this->beginDateS->value, null, FIELD_DATE_SIZE );
        $endDate = new MCalendarField('endDateS',  $this->endDateS->value, null, FIELD_DATE_SIZE );
        $fields[] = new GContainer('container', array($searchByPeriod,$beginDate, $endDate ));
        $fields[] = new MTextField('messageS', null, _M('Mensagem',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('visibleS', null, _M('Visível',$this->module), GUtil::getYesNo() );

        $this->setFields( $fields );
    }
}
?>