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
 * Reserve queue form
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
 * Class created on 17/11/2008
 *
 * */
class FrmReserveQueue extends GForm
{
    public $MIOLO;
    public $module;
    public $busLibraryUnit;

    /**
     * Regras de negocio de operação de reserva
     * 
     * @var BusinessGnuteca3BusOperationReserve
     */
    public $busOperationReserve;

    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busOperationReserve = $this->MIOLO->getBusiness($this->module, 'BusOperationReserve');
        $this->setTransaction('gtcSendMailReserveQueue');

        parent::__construct(_M('Fila de reserva', $this->module));
    }

    public function mainFields()
    {
        $msg = _M('Reorganiza reservas pertencentes à biblioteca selecionada que tenham 
data limite menor que a data informada.<br/>
Executa o vencimento das reservas, a troca do estado do exemplar de reservado para 
disponível e o atendimento da reserva para o próximo da lista. <br/>
Para que um material não fique muito tempo parado, sem ninguém retirá­lo, 
recomenda­se a execução desta operação, periodicamente.', 'gnuteca3');

        $fields[] = new MDiv(null, $msg, 'reportDescription');

        $date = GDate::now();

        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, TRUE);

        $fields[] = new MCalendarField('date', $date, _M('Data', $this->module));
        $fields[] = new MButton('btnOk', _M('Organizar', $this->module), ':doAction', Gutil::getImageTheme('accept-16x16.png'));
        $fields[] = new MDiv('divGrid');

        $this->setFields($fields);
        $this->setValidators(array( new MDateDMYValidator('date', null, 'required') ));
    }

    public function doAction()
    {
        $this->setValidators(array( new MDateDMYValidator('date', null, 'required') ));

        if ( !$this->validate() )
        {
            return false;
        }

        $data = $this->getData();
        $ok = $this->busOperationReserve->reorganizeQueueReserve($data->date, $data->libraryUnitId);
        $gridData = $this->busOperationReserve->getGridData();

        if ( $gridData && count($gridData) > 0 )
        {
            $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdReserveQueue');
            $grid->setData($gridData);
            $this->setResponse($grid, 'divGrid');
        }
        else if ( count($this->busOperationReserve->getMessages()) > 0 )
        {
            $this->injectContent($this->busOperationReserve->getMessagesTableRaw(), true);
        }
        else
        {
            $this->information(_M('Nenhuma reserva para organizar.', $this->module));
            $this->setResponse('', 'divGrid'); //limpa a grid
        }
    }
}
?>
