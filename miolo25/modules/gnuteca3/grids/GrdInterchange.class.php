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
 * Grid
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
 * Class created on 20/02/2009
 *
 * */
class GrdInterchange extends GSearchGrid
{

    public $busSupplierTypeAndLocation;
    public $busInterchange;

    public function __construct($data)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $busInterchange = $MIOLO->getBusiness($module, 'BusInterchange');
        $this->busSupplierTypeAndLocation = $MIOLO->getBusiness($module, 'BusSupplierTypeAndLocation');
        $this->busInterchange = $MIOLO->getBusiness($module, 'BusInterchange');

        $columns = array(
            new MGridColumn(_M('Código', $module), MGrid::ALIGN_RIGHT, null, null, true, null, true),
            new MGridColumn(_M('Tipo', $module), MGrid::ALIGN_LEFT, null, null, true, $busInterchange->listTypes(), true),
            new MGridColumn('supplierId', MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Fornecedor', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Nome da companhia', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Descrição', $module), MGrid::ALIGN_LEFT, null, null, false, GUtil::listYesNo(), true),
            new MGridColumn(_M('Data', $module), MGrid::ALIGN_RIGHT, null, null, true, null, true, MSort::MASK_DATETIME_BR),
            new MGridColumn('Status code', MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Estado', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn('typeId', MGrid::ALIGN_LEFT, null, null, false, null, true),
            new MGridColumn(_M('Tipo', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
            new MGridColumn(_M('Operador', $module), MGrid::ALIGN_LEFT, null, null, true, null, true),
        );

        parent::__construct($data, $columns);

        $args['interchangeId'] = '%0%';
        $args['function'] = 'update';
        $hrefUpdate = $MIOLO->getActionURL($module, $action, null, $args);
        $args['function'] = 'delete';
        $hrefDelete = GUtil::getAjax('tbBtnDelete_click', $args);

        $this->setIsScrollable();
        $this->addActionUpdate($hrefUpdate);
        $this->addActionDelete($hrefDelete);

        //Só mostra se a pessoa tiver permissão de editar
        if (GPerms::checkAccess($this->transaction, 'update', false))
        {
            
            $args['function'] = 'detail';
            $args['events'] = 'loadFields';
            $args['supplierId'] = '%2%';
            
            //Argumentos necessarios para abrir a edicao do fornecedor.
            $argsSupplier['function'] = 'update';
            $argsSupplier['supplierId'] = '%2%';
            $this->addActionIcon(_M('Editar fornecedor', $this->module), GUtil::getImageTheme('user-16x16.png'), $MIOLO->getActionURL($module, 'main:administration:supplier', null, $argsSupplier));
            $this->addActionIcon(_M('Gerar envio da carta', $this->module), GUtil::getImageTheme('document-16x16.png'), 'javascript:' . GUtil::getAjax('generateLetterSend', $args));
            $this->addActionIcon(_M('Enviar e-mail de permuta', $this->module), GUtil::getImageTheme('email-16x16.png'), 'javascript:' . GUtil::getAjax('sendMail', $args));
            $this->addActionIcon(_M('Confirmar', $this->module), GUtil::getImageTheme('confirm-16x16.png'), 'javascript:' . GUtil::getAjax('confirm', $args));
        }
        $this->setRowMethod($this, 'checkValues');
    }

    public function checkValues($i, $row, $actions, $columns)
    {
        $type = $columns[1]->control[$i]->value;
        $supplierId = $columns[2]->control[$i]->value;
        $statusId = $columns[7]->control[$i]->value;
        $interchangeTypeId = $columns[9]->control[$i]->value;

        $x = 0;
        if ($this->actionUpdate)
            $x++;
        if ($this->actionDelete)
            $x++;
        $actionEdit = $actions[$x++];
        $actionGenerateLetter = $actions[$x++];
        $actionSendMail = $actions[$x++];
        $actionConfirm = $actions[$x++];

        $interchangeStatusId = $columns[7]->control[$i]->value;

        $data = new GDate($columns[6]->control[$i]->value);
        $columns[6]->control[$i]->setValue($data->getDate(GDate::MASK_DATE_USER));

        //SÃ³ mostra se a pessoa tiver permissÃ£o de editar
        if (($actionGenerateLetter) || ($actionSendMail))
        {
            //($interchangeTypeId == INTERCHANGE_TYPE_SEND) && -- Retirada esta condicao de acordo com pedido no ticket #6221 - Item 12
            if ($statusId == INTERCHANGE_STATUS_LETTER_SENT) //quando for do tipo envio/doacao e o estado estiver como Carta enviada
            {
                $actionConfirm->enable();
            }
            else
            {
                $actionConfirm->disable();
            }

            //Se nao for do tipo Envio ou estado for >= confirmado, desabilita a acao de Gerar carta de envio
            if (($columns[9]->control[$i]->value != INTERCHANGE_TYPE_SEND) || ($interchangeStatusId >= INTERCHANGE_STATUS_CONFIRMED))
            {
                $actionGenerateLetter->disable();
            }
            else
            {
                $actionGenerateLetter->enable();
            }

            if (($columns[9]->control[$i]->value != INTERCHANGE_TYPE_RECEIPT)
                    || ($interchangeStatusId == INTERCHANGE_STATUS_GRATEFUL)
                    || ($interchangeStatusId == INTERCHANGE_STATUS_CONFIRMED))
            {
                $actionSendMail->disable();
            }
            else
            {
                $actionSendMail->enable();
            }
        }
    }

}

?>
