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
 * UserGroup form
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
class FrmUserGroup extends GForm
{
    function __construct()
    {
        $this->setAllFunctions('UserGroup', array('linkIdS','descriptionS'), 'linkId', 'description');
        parent::__construct();
    }


    public function mainFields()
    {
        if($this->function != 'insert')
        {
            $fields[] = new MTextField('linkId', $this->linkId->value, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
        }

        $fields[]       = new MTextField('description', $this->description->value, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);
        $validators[]   = new MRequiredValidator('description');
        $fields[]       = new MIntegerField('level', $this->level->value, _M('Nível',$this->module), FIELD_ID_SIZE);
        $validators[]   = new MIntegerValidator('level', null, 'required');

        $fields[] = new GRadioButtonGroup('isVisibleToPerson', _M('É visível à pessoa', $this->module), GUtil::listYesNo(1), DB_FALSE);
        
        if ( PERSON_IS_A_OPERATOR == DB_TRUE )
        {
            $fields[] = $isOperator = new GRadioButtonGroup('isOperator', _M('É operador', $this->module), GUtil::listYesNo(1), DB_FALSE);
        }
       
        $this->setFields($fields);
        $this->setValidators($validators);
    }
    
    /**
     * Método reescrito para tratar o dado isOperator
     * 
     * @return object FormData com dados do formulário
     */
    public function getData()
    {
        $data = parent::getData();
        
        if( PERSON_IS_A_OPERATOR == DB_FALSE )
        {
            $data->isOperator = DB_FALSE;
        }
        
        return $data;
    }
}
?>