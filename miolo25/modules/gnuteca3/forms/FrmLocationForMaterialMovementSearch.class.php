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
 * Location for material movement search form
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
 * Class created on 20/08/2008
 *
 **/
class FrmLocationForMaterialMovementSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('LocationForMaterialMovement', array('locationForMaterialMovementIdS', 'descriptionS'), array('locationForMaterialMovementId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new MTextField('locationForMaterialMovementIdS', $this->locationForMaterialMovementIdS->value, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField('descriptionS', $this->descriptionS->value, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField('observationS', $this->observationS->value, _M('Observação', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('sendLoanReceiptByEmailS', null, _M('Forçar recibo de empréstimo por e-mail', $this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('sendRenewReceiptByEmailS', null, _M('Forçar recibo de renovação por e-mail', $this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('sendReturnReceiptByEmailS', null, _M('Forçar recibo de devolução por e-mail', $this->module), GUtil::listYesNo(0));
        
        $validators[] = new MIntegerValidator('locationForMaterialMovementIdS');

        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>