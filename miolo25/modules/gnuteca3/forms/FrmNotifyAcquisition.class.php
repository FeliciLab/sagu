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
 * Notify acquisition form
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
 * Class created on 18/11/2008
 *
 **/
class FrmNotifyAcquisition extends GForm
{
    public $MIOLO;
    public $module;
    public $busLibraryUnit;
    /**
     * Bus de operação de material
     * @var BusinessGnuteca3BusOperationMaterial
     */
    public $busOperationMaterial;

    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit       = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busOperationMaterial = $this->MIOLO->getBusiness($this->module, 'BusOperationMaterial');
        $this->setTransaction('gtcSendMailNotifyAcquisition');
        parent::__construct(_M('Notificar aquisições', $this->module));
    }

    public function mainFields()
    {
    	$fields[] = new MDiv('divDescription', _M('Comunicar usuários por e-mail sobre novos materiais da biblioteca, de acordo com suas áreas de interesse', $this->module), 'reportDescription');

        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, true);
        $fields[] = new MCalendarField('endDate', GDate::now()->getDate(GDate::MASK_DATE_DB), _M('Data final', $this->module), FIELD_DATE_SIZE);
        $fields[] = new MButton('btnOk', _M('Enviar', $this->module), ':doAction',Gutil::getImageTheme('accept-16x16.png'));
        $fields[] = new MDiv('divGrid');

        $this->setFields($fields);
    }

    public function doAction( $args )
    {
        $validators[] = new MDateDMYValidator('endDate', null, 'required');
        $this->setValidators($validators);

        if ( ! $this->validate() )
        {
            return false;
        }

        $ok   = $this->busOperationMaterial->notifyAcquisition($args->endDate, $args->libraryUnitId);
        $data = $this->busOperationMaterial->getGridData();

        if ( $data &&  count( $data ) > 0  )
        {
            $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdOperationMaterial');
            $grid->setData($data);
            $this->setResponse($grid, 'divGrid');
        }
        else if (count($this->busOperationMaterial->getMessages()) > 0)
        {
            $this->injectContent( $this->busOperationMaterial->getMessagesTableRaw(), true );
        }
        else
        {
            $this->information(_M('Não há aquisições para notificar', $this->module) );
        }
    }
}
?>
