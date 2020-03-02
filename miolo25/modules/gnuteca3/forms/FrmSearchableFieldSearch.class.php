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
 * FrmSearchableField form
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
 * Class created on 01/12/2008
 *
 **/
class FrmSearchableFieldSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('SearchableField', array('description'),array('searchableFieldId'));
        parent::__construct();
    }


    public function mainFields()
    {
    	$fields[] = new MTextField('searchableFieldIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
    	$fields[] = new MTextField('descriptionS', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('fieldS', null, _M('Campo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('identifierS', null, _M('Identificador', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('observationS', null, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('isRestrictedS', null, _M('É restrita', $this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('filterTypeS', null, _M('Tipo de filtro', $this->module), $this->business->listFilterType());
        
        $fields[]       = new MTextField('levelS', $this->levelS->value, _M('Nível',$this->module), 2);
        $validators[]   = new MIntegerValidator('levelS','','');

        $this->setFields($fields);
    }
}
?>