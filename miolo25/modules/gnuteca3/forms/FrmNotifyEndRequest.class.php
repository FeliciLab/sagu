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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 23/07/2010
 *
 **/
class FrmNotifyEndRequest extends GForm
{
    public $MIOLO;
    public $module;
    public $busLibraryUnit, $busRequestChangeExemplaryStatus;


    public function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busLibraryUnit = $this->MIOLO->getBusiness($this->module, 'BusLibraryUnit');
        $this->busRequestChangeExemplaryStatus = $this->MIOLO->getBusiness($this->module, 'BusRequestChangeExemplaryStatus');
        $this->setTransaction('gtcSendMailNotifyEndRequest');
        parent::__construct(_M('Notificar fim da requisição', $this->module));
        
    }


    public function mainFields()
    {
    	$fields[] = new MDiv('divDescription', _M('Avisar professores sobre o fim do período da requisição de troca de estado do exemplar.<BR> Informando que podem renovar por mais uma período o congelamento dos materiais.', $this->module), 'reportDescription');
        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = new GSelection('libraryUnitId', null, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit(), null, null, null, true);
        $period = $this->busRequestChangeExemplaryStatus->getPeriodInterval();
        $fields[] = new MTextField('advance', $period->requestChangeDays, _M('Dias antes de vencer', $this->module), 5);
        $validators[] = new MIntegerValidator('advance', '', 'required');
        
        $fields[] = new MButton('btnOk', _M('Enviar', $this->module), ':doAction');
        $fields[] = new MDiv('divGrid');

        $this->setValidators($validators);
        $this->setFields($fields);
        
    }


    public function doAction()
    {
    	$data = $this->getData();
        //valida o formulário
    	$this->gValidator->errors = $errors;
        $errors = $this->gValidator->validate( $data );

        if ( $errors && is_array( $errors ) )
        {
            $this->error(implode('<br>', $errors), 'gnuteca.closeAction()');
            return false;
        }
        
        $ok     = $this->busRequestChangeExemplaryStatus->notifyEndRequest($data->advance, $data->libraryUnitId);
        $goto   = $this->MIOLO->getActionURL($this->module, $this->_action);

        if (($data = $this->busRequestChangeExemplaryStatus->getGridData()) && (count($data) > 0))
        {
            $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdNotifyEndRequest');
            $grid->setData($data);
            $this->setResponse($grid, 'divGrid');
        }
        else if (count($this->busRequestChangeExemplaryStatus->getMessages()) > 0)
        {
            $this->injectContent($this->busRequestChangeExemplaryStatus->getMessagesTableRaw(), true);
        }
        else
        {
            $this->information(_M('Não há fim de requisições para enviar.', $this->module), $goto);
        }
    }
}
?>
