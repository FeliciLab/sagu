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
 *
 * Library Unit search form
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
 * Class created on 29/07/2008
 *
 **/
class FrmCostCenterSearch extends GForm
{
    /** @var BusinessGnuteca3BusLibraryUnit */
    public $business;

    
    public function __construct()
    {
        $this->setAllFunctions('CostCenter', array('costCenterIdS'),array('costCenterId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $businessLibraryUnit = $this->MIOLO->getBusiness( $this->module, 'BusLibraryUnit');
       
        $fields[] = new MIntegerField('costCenterIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection('libraryUnitIdS', null, _M('Unidade de biblioteca',$this->module),$businessLibraryUnit->listLibraryUnit() );
        $fields[] = new MTextField('descriptionS', null, _M('Descrição',$this->module),FIELD_DESCRIPTION_SIZE );

        $this->setFields( $fields );
    }

}
?>