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
 * UserGroup search form
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
 **/
class FrmUserGroupSearch extends GForm
{

    public function __construct()
    {
        $this->setAllFunctions('UserGroup', array('linkIdS','descriptionS'), array('linkId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[]       = new MTextField('linkIdS', "", _M('Código',$this->module), FIELD_ID_SIZE);
        $validators[]   = new MIntegerValidator('linkIdS','','');
        $fields[]       = new MTextField('descriptionS', $this->descriptionS->value, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]       = new MTextField('levelS', $this->levelS->value, _M('Nível',$this->module), 2);
        $validators[]   = new MIntegerValidator('levelS','','');

        $fields[] = new GSelection('isVisibleToPersonS', null, _M('É visível à pessoa', $this->module), GUtil::listYesNo(0), null, null, null, false);
        
        if ( PERSON_IS_A_OPERATOR == DB_TRUE )
        {
            $fields[] = new GSelection('isOperatorS', null, _M('É operador', $this->module), GUtil::listYesNo(0), null, null, null, false);
        }

        $this->setFields( $fields );
        $this->setValidators($validators);
    }
}
?>