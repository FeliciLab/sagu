<?php

/**
 * Form common functions
 *
 * @author Equipe SAGU [sagu@solis.com.br]
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
class prtCommonFormPedagogico
{
    public static function cronograma($dataaula, $habilitarBiometria = false)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $MIOLO->uses('classes/prtDisciplinasPedagogico.class.php', $module);
        $disciplinas = new PrtDisciplinasPedagogico();
        
        $descricao = $disciplinas->obterCronogramaPelaData(MIOLO::_REQUEST('ofertacomponentecurricularid'), $dataaula);
        $anterior = null;
        $diaAnt = $disciplinas->obterDiaAnterior(MIOLO::_REQUEST('ofertacomponentecurricularid'), $dataaula);

        if ( $diaAnt )
        {
            $anterior = $disciplinas->obterCronogramaPelaData(MIOLO::_REQUEST('ofertacomponentecurricularid'), $diaAnt);
        }

        $info = new MLabel(_M('Preencha este campo com o relato da aula e conteúdo ministrado.'));
        $info->addStyle('font-size', '12px');
        $cronograma = new MMultiLineField('cronograma', $descricao, null, 50, 4, 50);
        $spacer = new MSpacer();
        $buttons[] = new MButton('salvar', _M('Salvar', $module), MUtil::getAjaxAction('salvarPeloBotao'));

        if ( strlen($anterior) > 0 )
        {
            $anteriorHtml = str_replace("\n", '<br>', $anterior);
            $anteriorJs = str_replace("\n", "\\n", $anterior);

            $buttons[] = new MButton('usarAnterior', _M('Utilizar anterior', $module), "$('#cronograma').val('{$anteriorJs}');");            
            
            $controls[] = new MLabel($anteriorHtml);
            $cronAnterior = new MBaseGroup('bgr' . rand(), _M('Cronograma anterior (@1)', null, $diaAnt), $controls);
            $cronAnterior->addStyle('margin', '0px !important');
            $cronAnterior->addStyle('border-width', '1px !important');
        }
        
        // Se no perfil de curso o registro de frequências por biometria estiver
        // habilitado, não pode marcar frequências até o arquivo estar importado
        if ( $habilitarBiometria )
        {
            $MIOLO->uses('types/AcpFrequencia.class', 'pedagogico');
            
            $filterS = new stdClass();
            $filterS->ofertaComponenteCurricularId = MIOLO::_REQUEST('ofertacomponentecurricularid');
            $filterS->dataAula = $dataaula;
            
            $frequencias = AcpFrequencia::searchFrequencias($filterS);
            
            // Senão encontrou frequências ainda não foi importado
            if ( !(count($frequencias) > 0) )
            {
                $cronograma->setReadOnly(true);
                
                unset($info);
                unset($cronAnterior);
                $info = new MLabel(_M('Não será possível a digitação de conteúdo e/ou a marcação de frequências enquanto o arquivo de biometria não for importado pela administração.'));
                $info->addStyle('font-size', '12px');
                $info->addStyle('color', 'red');
                $info->addStyle('font-weight', 'bold');
            }
        }
        
        $campos[] = new MDiv('', array($cronograma, $info, $spacer, $buttons));
        $campos[] = $cronAnterior;
        
        // FIXME Sera implementado no futuro
//            $fields[] = new MButton('reposicao', _M('Solicitar reposição'), $action);
        
        return new MHContainer('hct'.rand(), $campos);
    }
    
    /**
     *
     * @return \MDiv 
     */
    public static function obterFoto($fileId, $width=null, $height=null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $ui = $MIOLO->getUI();
        
        if ( strlen($fileId) > 0 )
        {
            $personPhoto = new SPersonPhotoDisplayField(array('baseGroup' => false, 'fileId' => $fileId, 'width' => $width, 'height' => $height));
            $photoContent = $personPhoto->generate();
            $divFoto = new MDiv('', $photoContent);
        }
        else
        {
            $divFoto = new MDiv('', '<img style="width:64px; height:64px; margin-top:0px;" src="'.$ui->getImageTheme($module, 'sem_foto.png').'" />');
        }
        
        $divFoto->addStyle('margin-top', '-4px');
        
        return $divFoto;
    }
    
    /**
     *
     * @return \MLabel 
     */
    public function obterEstadoMatricula($enrollId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $busEnrollStatus = $MIOLO->getBusiness('academic', 'BusEnrollStatus');
        
        $list = $busEnrollStatus->listEnrollStatus(1);
        $statusId = $this->obterEstadoDaMatriculaId($enrollId);
        $label = new MLabel('<b>' . $list[$statusId] . '</b>');
        
        if ( $statusId == SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_APPROVED') )
        {
            $label->setColor('green');
        }
        else //if ( $statusId == SAGU::getParameter('ACADEMIC', 'ENROLL_STATUS_DISAPPROVED') )
        {
            $label->setColor('red');
        }
        
        return $label;
    }
    
    public function obterEstadoDaMatriculaId($enrollId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $busEnroll = $MIOLO->getBusiness('academic', 'BusEnroll');
        $statusId = $busEnroll->getFutureStatusId($enrollId);
        
        return $statusId;
    }
    
    public function obterEstadoDetalhado($estadoDetalhadoId)
    {
        
    }
    
    public function obterEstadoDetalhadoDaMatriculaId($enrollId)
    {
        $msql = new MSQL();
        $msql->setTables('acdenroll');
        $msql->setColumns('detailenrollstatusid');
        $msql->setWhere('enrollid = ?');
        $msql->addParameter($enrollId);
        
        $resultado = bBaseDeDados::consultar($msql);
        
        return $resultado[0][0];
    }
}
?>