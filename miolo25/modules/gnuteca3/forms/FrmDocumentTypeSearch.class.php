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
 * @author Jonas C. Rosa [jonas_rosa@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2012
 *
 * */
class FrmDocumentTypeSearch extends GForm
{
    
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('DocumentType', array('documentTypeIdS'), array('documentTypeId'));
        parent::__construct();
    }
    
    public function mainFields()
    {

        $fields = array();
        $fields[] = new MIntegerField('documentTypeId', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('_name', null, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('mask', null, _M('Máscara', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('sex', null, _M('Sexo', $this->module), BusinessGnuteca3BusDocumentType::listMascFem(0));
        $fields[] = new GSelection('personType', null, _M('Tipo de pessoa', $this->module), BusinessGnuteca3BusDocumentType::listTypePerson(0));
        $fields[] = new MTextField('minAge', null, _M('Idade mínima', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('maxAge', null, _M('Idade máxima', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('needDeliver', null, _M('Nessecita de estrega?', $this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('isBlockenRoll', null, _M('Bloqueia matrícula?', $this->module), GUtil::listYesNo(0));
        $fields[] = new MTextField('fillHint', null, _M('Dica de preenchimento', $this->module), FIELD_DESCRIPTION_SIZE);

        $validators[] = new MRequiredValidator('_name');
        $validators[] = new MRequiredValidator('personType');
        $this->setFields($fields);
    }

}

?>
