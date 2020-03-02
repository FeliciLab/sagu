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
 * Answered reserves form
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
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 17/11/2008
 *
 **/
class FrmAnsweredReserves extends GForm
{
    public $MIOLO;
    public $module;
    public $busOperationReserve;
    public $busLibraryUnit;


    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        $this->busLibraryUnit   = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->setTransaction('gtcSendMailAnsweredReserves');
        parent::__construct(_M('Comunicar reservas atendidas', $this->module));
        
    }


    public function mainFields()
    {
	    $fields[] = new MDiv('divDescription', _M('Comunicar por email usuários que tem reservas atendidas', $this->module), 'reportDescription');

        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), NULL, NULL, NULL, TRUE);
        $fields[] = new MButton('btnOk', _M('Enviar', $this->module), ':doAction');
        $fields[] = new MDiv('divGrid');

        $this->setFields($fields);
    }


    public function doAction()
    {
        $data   = $this->getData();
        $this->busOperationReserve->setLibraryUnit($data->libraryUnitId);
        $ok     = $this->busOperationReserve->comunicateReserveAnswered();
        $goto   = $this->MIOLO->getActionURL($this->module, $this->_action);

        if (($data = $this->busOperationReserve->getGridData()) && (count($data) > 0))
        {
            $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdOperationReserve');
            $grid->setData($data);
            $this->setResponse($grid, 'divGrid');
        }
        else if (count($this->busOperationReserve->getMessages()) > 0)
        {
            $this->injectContent($this->busOperationReserve->getMessagesTableRaw(), true);
        }
        else
        {
            $this->information(_M('Não há reservas atendidas para comunicar', $this->module), $goto);
        }
    }
}
?>
