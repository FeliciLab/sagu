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
 * Bond form
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
 * Class created on 06/08/2008
 *
 **/


/**
 * Form to manipulate a preference
 **/
class FrmBond extends GForm
{
    function __construct()
    {
        $this->setAllFunctions('Bond');
        $this->setPrimaryKeys(array('personId', 'linkId', 'oldDateValidate'));
        $this->setSaveArgs(array('personId', 'linkId', 'oldDateValidate'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = $personId = new GPersonLookup('personId', _M('Pessoa', $this->modules), 'person');
        $fields[] = $linkId =  new GSelection('linkId', '', _M('Código do grupo de usuário', $this->module), $this->business->listBond(true));
        
        if ($this->function != "insert")
        {
            $personId->setReadOnly(true);
            $linkId->setReadOnly(true);
        }

        $dateValidate = new MCalendarField('dateValidate', NULL, _M('Data de validade', $this->module), FIELD_DATE_SIZE, null);
        $fields[] = $dateValidate;
        
        $fields[] = $oldDateValidate = new MTextField('oldDateValidate');
        $oldDateValidate->addStyle('display', 'none');

        $this->setFields($fields);
        
        //validadores
        $validators[] = new MRequiredValidator('personId', _M('Pessoa', $this->module));
        $validators[] = new MRequiredValidator('linkId');
        $validators[] = new MDateDMYValidator('dateValidate', NULL);
        
        $this->setValidators($validators);
    }


    /**
     * Método reescrito para tratar o oldDateValite
     */
    public function loadFields()
    {
        $data = $this->business->getBond(MIOLO::_REQUEST('personId'), MIOLO::_REQUEST('linkId'), MIOLO::_REQUEST('oldDateValidate'));
        $date = new GDate($data->dateValidate);
        $this->business->dateValidate = $date->getDate(GDate::MASK_DATE_USER);
        $this->business->oldDateValidate = $date->getDate(GDate::MASK_DATE_USER);

        $this->setData($this->business);
    }
}
?>
