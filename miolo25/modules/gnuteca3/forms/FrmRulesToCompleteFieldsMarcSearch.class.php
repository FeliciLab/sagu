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
 * RulesToCompleteFieldsMarcSearch form
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
class FrmRulesToCompleteFieldsMarcSearch extends GForm
{
	public $busMarcTagListingOption;
	
	
    public function __construct()
    {
    	global $MIOLO, $module;
    	$this->busMarcTagListingOption = $MIOLO->getBusiness($module, 'BusMarcTagListingOption');
        $this->setAllFunctions('RulesToCompleteFieldsMarc', array('category', 'originField'), array('rulesToCompleteFieldsmarcId') );
        parent::__construct();
    }


    public function mainFields()
    {
    	$fields[] = new MTextField('rulesToCompleteFieldsMarcIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('categoryS', null, _M('Categoria', $this->module), $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY'));
        $fields[] = new GSelection('affectRecordsCompletedS', null, _M('Afeta registros concluídos', $this->module), GUtil::listYesNo(0));
        $this->setFields($fields);
    }
}
?>
