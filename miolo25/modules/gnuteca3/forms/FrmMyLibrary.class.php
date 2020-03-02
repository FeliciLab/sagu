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
 * Class created on 05/10/2011
 *
 **/
class FrmMyLibrary extends GForm
{
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('MyLibrary', array('myLibraryId'), array('myLibraryId'), array('personId') );
        parent::__construct();
    }

    public function mainFields()
    {   
        if ( MIOLO::_REQUEST('function') == 'update' )
        {
            $fields[] = new MIntegerField('myLibraryId', null, _M('Código',$this->module), FIELD_ID_SIZE,'',null, true);
        }
        
        $fields[] = new GPersonLookup('personId', _M('Pessoa','gnuteca3') );
        $fields[] = new MTextField('tableName', null, _M('Tabela',$this->module), FIELD_ID_SIZE );
        $fields[] = new MIntegerField('tableId',null, _M('Código da tabela', $this->module) ,FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTimesTampField('date', GDate::now()->getDate(GDate::MASK_TIMESTAMP_USER), _M('Data',$this->module) );
        $fields[] = new GEditor('message', null, _M('Mensagem',$this->module) );
        $fields[] = new GSelection('_visible', DB_TRUE , _M('Visível',$this->module), GUtil::getYesNo() );

        $validators[] = new MRequiredValidator('personId');
        $validators[] = new MRequiredValidator('date');
        $validators[] = new MRequiredValidator('message');
        $validators[] = new MRequiredValidator('_visible');
        
        $this->setFields( $fields );
        $this->setValidators($validators);
    }
    
    /**
     * Reescrito para tratar campo visible
     * pois é palavra reservada do miolo
     * 
     * @return stdClass
     */
    public function getData()
    {
        $data = parent::getData();
        $data->visible = $data->_visible;
        
        return $data;
    }
    
    /**
     * Reescrito para tratar campo visible
     * 
     * @param stdClass $data 
     */
    public function setData($data)
    {
        $data->_visible = $data->visible;
        
        parent::setData( $data );
    }
}
?>