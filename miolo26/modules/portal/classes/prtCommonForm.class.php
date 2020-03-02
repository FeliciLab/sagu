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
class prtCommonForm
{
    public static function cronograma($dia, $scheduleId = null, $personId = null)
    {
        $container = NULL;
        
        if(SAGU::getParameter('SERVICES', 'LOCK_FUTURE_FREQUENCY') == DB_TRUE)
        {
            $now = SAGU::getDateNow();

            if ( SAGU::compareTimestamp($now, '<', $dia) )
            {
                return NULL;
            }
        }
        
        if ( strlen($dia) > 0 )
        {
            $MIOLO = MIOLO::getInstance();
            $module = MIOLO::getCurrentModule();

            $MIOLO->uses('classes/prtDisciplinas.class.php', $module);
            $MIOLO->uses('types/PrtCronogramaProfessor.class.php', $module);
            $disciplinas = new PrtDisciplinas();

            $descricao = $disciplinas->obterCronogramaDescricao(MIOLO::_REQUEST('groupid'), $dia, null, $scheduleId);
            $anterior = null;
            $diaAnt = $disciplinas->obterDiaAnterior(MIOLO::_REQUEST('groupid'), $dia);

            if ( $diaAnt )
            {
                $anterior = $disciplinas->obterCronogramaDescricao(MIOLO::_REQUEST('groupid'), $diaAnt, null, $scheduleId);
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
                $cronAnterior = new MBaseGroup('bgr' . rand(), _M('Conteúdo da aula anterior (@1)', null, $diaAnt), $controls);
                $cronAnterior->addStyle('margin', '0px !important');
                $cronAnterior->addStyle('border-width', '1px !important');
            }
            
            //Cronograma previsto
            $prtCronograma = new PrtCronogramaProfessor(MIOLO::_REQUEST('groupid'), $personId, null, $dia);
            $previsto = $prtCronograma->obterConteudoCronograma();
            
            if ( strlen($previsto) > 0 )
            {
                $labelPrevisto = new MLabel(str_replace("\n", '<br>', $previsto));
                
                $cronPrevisto = new MBaseGroup('bgr' . rand(), _M('Cronograma previsto', $module), array($labelPrevisto));
                $cronPrevisto->addStyle('margin', '0px !important');
                $cronPrevisto->addStyle('border-width', '1px !important');
            }

            $campos[] = new MDiv('', array($cronograma, $info, $spacer, $buttons));
            $campos[] = $cronAnterior;
            $campos[] = $cronPrevisto;

            $container = new MHContainer('hct'.rand(), $campos);
        }
        
        return $container;
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
            $divFoto = new MDiv('', '<div><img style="width:64px; height:64px; margin-top:0px;" src="'.$ui->getImageTheme($module, 'sem_foto.png').'" /></div>');
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