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
 * Delayed loan form
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
 * Class created on 17/11/2008
 *
 **/
class FrmDelayedLoan extends GForm
{
    public $MIOLO;
    public $module;
    public $busOperationLoan;
    public $busLibraryUnit;


    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busOperationLoan = $this->MIOLO->getBusiness($this->module, 'BusOperationLoan');
        $this->busLibraryUnit   = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->setTransaction('gtcSendMailDelayedLoan');
        parent::__construct(_M('Empréstimo atrasado', $this->module));
        
    }


    public function mainFields()
    {
	    $fields[] = new MDiv('divDescription', _M('Comunicar por email os usuários que tem empréstimo atrasados nos últimos X dias', $this->module), 'reportDescription');

        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), NULL, NULL, NULL, TRUE);
        $fields[] = new MButton('btnGo', _M('Enviar', $this->module), ':doAction');
        $fields[] = new MDiv('divGrid');

        $this->setFields($fields);
    }


    public function doAction()
    {
        $data = $this->getData();
        $ok     = $this->busOperationLoan->communicateDelayedLoan( $data->libraryUnitId );
        $goto   = $this->MIOLO->getActionURL($this->module, $this->_action);

        if (($data = $this->busOperationLoan->getGridData()) && (count($data) > 0))
        {
            $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdOperationLoan');
            $grid->setData($data);
            $controls[] = $grid;
            if (count($this->busOperationLoan->getMessages()) > 0)
            {
                $controls[] = new MSeparator();
                $controls[] = $this->busOperationLoan->getMessagesTableRaw();
            }
            $this->setResponse($controls, 'divGrid');
        }
        else if (count($this->busOperationLoan->getMessages()) > 0)
        {
            $this->injectContent($this->busOperationLoan->getMessagesTableRaw(), true);
        }
        else
        {
            $this->information(_M('Não há empréstimos atrasados para serem comunicados', $this->module), $goto);
        }
    }
}
?>
