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
 * Form that administrates gtcRulesforMaterialMoviment Table
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
class FrmRulesForMaterialMovementSearch extends GForm
{
    public $busExemplaryStatus;
    public $busOperation;
    public $busLocationForMaterialMovement;

    public function __construct()
    {
        $this->setAllFunctions('RulesForMaterialMovement', array('currentState','operationId',' locationForMaterialMovementId', 'futureState') );
        $this->MIOLO                    = MIOLO::getInstance();
        $this->module                   = MIOLO::getCurrentModule();
        $this->busExemplaryStatus                 = $this->MIOLO->getBusiness( $this->module, 'BusExemplaryStatus');
        $this->busOperation             = $this->MIOLO->getBusiness( $this->module, 'BusOperation');
        $this->busLocationForMaterialMovement  = $this->MIOLO->getBusiness ($this->module , 'BusLocationForMaterialMovement');
        parent::__construct();
    }

    public function mainFields()
    {
        $temp = $this->busExemplaryStatus->listExemplaryStatus(false, false, false, true);

        foreach ( $temp as $line => $info)
        {
            $exemplaryStatus[$line] = $info;
        }

        $fields[] = new GSelection('currentStateS', null, _M('Estado atual', $this->module), $exemplaryStatus);
        $fields[] = new GSelection('operationIdS', null, _M('Operação', $this->module), $this->busOperation->listOperation(true));
        $fields[] = new GSelection('locationForMaterialMovementIdS', null, _M('Local', $this->module), $this->busLocationForMaterialMovement->listLocationForMaterialMovement()  );
        $fields[] = new GSelection('futureStateS', null, _M('Estado futuro', $this->module),$exemplaryStatus);
        
        $this->setFields( $fields );
    }
}
?>