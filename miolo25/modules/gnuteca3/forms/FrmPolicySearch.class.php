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
 * Policy search form
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
 * Class created on 04/08/2008
 *
 **/
class FrmPolicySearch extends GForm
{
    public $businessPrevilegeGroup;
    private $busBond;

    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $this->setAllFunctions('Policy', array('privilegeGroupIdS','descriptionS'),array('privilegeGroupId','linkId','materialGenderId') );
        $this->businessPrevilegeGroup = $MIOLO->getBusiness( 'gnuteca3' , 'BusPrivilegeGroup');
        $this->busBond = $MIOLO->getBusiness( 'gnuteca3' , 'BusBond');
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new GSelection('privilegeGroupIdS', $this->privilegeGroupIdS->value, _M('Grupo de privilégio',$this->module), $this->businessPrevilegeGroup->listPrivilegeGroup());

        $fields[] = new GSelection('linkIdS', '', _M('Código do grupo de usuário', $this->module), $this->busBond->listBond(true));    

        $materialGenderIdLabelS = new MLabel (_M('Código do gênero do material',$this->module));
        $materialGenderIdLabelS->width = FIELD_LABEL_SIZE;
        $materialGenderIdLabelS->baseModule = 'gnuteca3';
        $materialGenderIdS = new GLookupTextField ('materialGenderIdS','','',FIELD_LOOKUPFIELD_SIZE);
        $materialGenderIdS->setContext($this->module, $this->module, 'materialGender', 'filler', 'materialGenderIdS,materialGenderIdDescriptionS', '', true);
        $materialGenderIdDescriptionS = new MTextField ('materialGenderIdDescriptionS','',null, FIELD_DESCRIPTION_LOOKUP_SIZE);
        $materialGenderIdDescriptionS->setReadOnly(true);
        $fields[] = new GContainer('materialGenderIdContainerS', array ($materialGenderIdLabelS, $materialGenderIdS, $materialGenderIdDescriptionS,));;

        $lblDate             = new MLabel(_M('Data do empréstimo', $this->module) . ':');
        $lblDate->setWidth(FIELD_LABEL_SIZE);
        $beginLoanDateS     = new MCalendarField('beginLoanDateS', $this->beginLoanDateS->value, null, FIELD_DATE_SIZE);
        $endLoanDateS       = new MCalendarField('endLoanDateS', $this->endLoanDateS->value, null, FIELD_DATE_SIZE);
        
        $fields[] = new GContainer('hctDates', array($lblDate, $beginLoanDateS, $endLoanDateS));
        $fields[] = new MTextField('loanDaysS', $this->loanDays->value, _M('Dias de empréstimo',$this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('loanLimitS', $this->loanLimit->value, _M('Limite de empréstimo',$this->module), FIELD_ID_SIZE);

        $this->setFields( $fields );
    }
}
?>
