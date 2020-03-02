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
 * Domain form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 11/07/2010
 *
 **/
class FrmDomain extends GForm
{
    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
    	$this->setAllFunctions('Domain', null, array('domainId','sequence'), 'domainId');
        $this->setTransaction('basDomain');
        parent::__construct(_M('Domínio'));
    }
    
    public function mainFields()
    {
        $fields[]       = new MTextField('domainId', null, _M('Domínio',$this->module), FIELD_DESCRIPTION_SIZE, null, null, MIOLO::_REQUEST('function') == 'update');
        $fields[]       = new MTextField('sequence', null, _M('Sequência',$this->module), FIELD_DESCRIPTION_SIZE, null, null, MIOLO::_REQUEST('function') == 'update');
        $fields[]       = new MTextField('key', null, _M('Chave',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]       = new MTextField('abbreviated', null, _M('Abreviatura',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]       = new MTextField('label', null, _M('Etiqueta',$this->module), FIELD_DESCRIPTION_SIZE);
        $validators[]   = new MRequiredValidator('domainId');
        $validators[]   = new MRequiredValidator('sequence');
        $validators[]   = new MRequiredValidator('key');
        $validators[]   = new MRequiredValidator('abbreviated');
        $validators[]   = new MRequiredValidator('label');
        
        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>
