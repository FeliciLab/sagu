<?php

/**
 * JQuery Mobile Sagu basic form.
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 *
 * @since
 * Creation date 2012/10/23
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2012 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */
$MIOLO->uses('forms/frmMobile.class.php', $module);
class frmMaterialProfessor extends frmMobile
{
    public function __construct()
    {
        self::$fazerEventHandler = FALSE;
        parent::__construct(_M('Solicitação de material', MIOLO::getCurrentModule()));

        $this->setShowPostButton(FALSE);
    }

    public function defineFields()
    {
        $fields[] = $this->timeStampField('data_retirada', $value, _M('Data de retirada'));
        
        $fields[] = new MSpacer();
        
        $fields[] = $this->timeStampField('data_devolucao', $value, _M('Data de devolucao'));
        
        $fields[] = new MSpacer();
        
        $fields[] = new MLabel(_M('Tipo de material'));
        $fields[] = new Mdiv('materialTypeDiv', array($this->materialType($args, false)) );
        
        $fields[] = new MSpacer();
        
        $fields[] = new MLabel(_M('Material'));
        $fields[] = new Mdiv('materialDiv', $this->material($args, false) );
        
        $fields[] = new MSpacer();
        
        $fields[] = new MLabel(_M('Recurso físico'));
        $fields[] = $this->recursoFisico($args);
        
        $fields[] = new MSpacer();
        
        $fields[] = new MMultiLineField('observation', null, _M('Observações compĺementares'));
        
        $fields[] = new MSpacer();
        $fields[] = new MSpacer();

        $fields[] = new MButton('btSolicitar', _M('Solicitar material'), MUtil::getAjaxAction('solicitarMaterial'));
        
        //$fields[] = new MButton('btVisualizar', _M('Visualizar materiais solicitados'), MUtil::getAjaxAction('visualizarMaterial'));
        
        $bg[] = new MBaseGroup('divSolicitacaoMaterial', _M('Solicitação de material'), $fields);
        
        $bg[] = new MBaseGroup('divMateriaisSolicitados', _M('Solicitações de material em aberto'), $this->visualizarMaterial($args));
        
	parent::addFields($bg);
    }
    
    public function materialType($args, $ajax=true)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/InsMaterialType.class', 'institutional');
        
        foreach (InsMaterialType::listMaterialType() as $m)
        {
            $options[] = array($m[0],$m[1]);
        }
        
        $selection = new MSelection('materialTypeId', null, '', $options);
        $selection->setAttribute('onchange', MUtil::getAjaxAction('material'));
        
        if($ajax)
        {
            $this->setResponse(null, 'materialTypeDiv');
        }
        else
        {
            return $selection;
        }
    }
    
    public function material($args, $ajax=true)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/InsMaterial.class', 'institutional');
        $args = $this->getAjaxData();
        
        if($args->materialTypeId)
        {
            $filters->materialTypeId = $args->materialTypeId;
            foreach (InsMaterial::searchGrid($filters) as $m)
            {
                $options[] = array($m[0],$m[7]);
            }
        }
        else
        {
            foreach (InsMaterial::listMaterial() as $m)
            {
                $options[] = array($m[0],$m[1]);
            }
        }
        
        $selection = new MSelection('materialId', null, '', $options);
        
        if($ajax)
        {
            $this->setResponse($selection, 'materialDiv');
        }
        else
        {
            return $selection;
        }
    }
    
    public function recursoFisico($args)
    {
        #TODO: implementar recurso fisico
        $selection = new MSelection('physicalResourceId', $options[0][0], '', $options);
        
        return $selection;
    }
    
    public function visualizarMaterial($args)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->uses('types/InsMaterialStatus.class', 'institutional');
        $MIOLO->uses('types/InsMaterialRequest.class', 'institutional');
        $dataTable = InsMaterialRequest::listPersonActiveRequestsAndLoans($this->personid);
        
        $colunas = array( _M('Código'),
                            _M('Status'),
                            _M('Motivo do status'),
                            _M('Tipo de material'),
                            _M('Material'),
                            _M('Data de saída (solicitada)'),
                            _M('Data de retorno (solicitada)'),
                            _M('Data de retirada (agendada)'),
                            _M('Data de retorno (agendada)'),
                            _M('Data de devolução') );
        
        $table = new MTableRaw('', $dataTable, $colunas);
        $table->SetAlternate(true);
        $table->setWidth('700');
        $fields[] = $table;
        
        return $fields;
    }
    
    public function solicitarMaterial($args)
    {
        $MIOLO = MIOLO::getInstance();
        $args = $this->getAjaxData();
        
        $MIOLO->uses('types/InsMaterial.class', 'institutional');
        $MIOLO->uses('types/InsMaterialStatus.class', 'institutional');
        $MIOLO->uses('types/InsMaterialRequest.class', 'institutional');
        
        
        $insMaterialRequest = new InsMaterialRequest();

        $insMaterialRequest->personId = $this->personid;
        $insMaterialRequest->beginDate = $args->data_retirada;
        $insMaterialRequest->endDate = $args->data_devolucao;
        $insMaterialRequest->materialTypeId = $args->materialTypeId;
        $insMaterialRequest->materialId = $args->materialId;
        $insMaterialRequest->observation = $args->observation;

        $insMaterialRequest->save();
        
        $this->setResponse($this->visualizarMaterial($args), 'divMateriaisSolicitados');   
    }
    
    public function salvar($args)
    {
        $this->setResponse(null, 'responseDiv');   
    }

}

?>
