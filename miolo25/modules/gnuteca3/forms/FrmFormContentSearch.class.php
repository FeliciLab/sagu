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
 * FormContentSearch form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 07/04/2009
 *
 **/


class FrmFormContentSearch extends GForm
{
	public $MIOLO;
	public $module;
	public $busFormContentType;


    public function __construct()
    {
    	$this->MIOLO   = MIOLO::getInstance();
    	$this->module  = MIOLO::getCurrentModule();
    	$this->busFormContentType = $this->MIOLO->getBusiness($this->module, 'BusFormContentType');
        $this->setAllFunctions('FormContent', array('formContentIdS'),array('formContentId'));
        parent::__construct();
    }


    public function mainFields()
    {
    	$fields[] = new MTextField('formContentIdS', NULL, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('operatorS', NULL, _M('Operador', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('_formS', NULL, _M('Formulário',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('_nameS', NULL, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('descriptionS', NULL, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MSelection('formContentTypeS', NULL, _M('Tipo do conteúdo do formulário', $this->module), $this->busFormContentType->listFormContentType());
        $this->setFields( $fields );
    }


    public function searchFunction($args)
    {
    	$args->formS = $args->_formS;
    	$args->nameS = $args->_nameS;
    	parent::searchFunction($args);
    }
}
?>