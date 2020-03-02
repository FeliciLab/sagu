<?php

/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Solu��es Livres Ltda.
 *
 * Este arquivo � parte do programa Sagu.
 *
 * O Sagu � um software livre; voc� pode redistribu�-lo e/ou modific�-lo
 * dentro dos termos da Licen�a P�blica Geral GNU como publicada pela Funda��o
 * do Software Livre (FSF); na vers�o 2 da Licen�a.
 *
 * Este programa � distribu�do na esperan�a que possa ser �til, mas SEM
 * NENHUMA GARANTIA; sem uma garantia impl�cita de ADEQUA��O a qualquer MERCADO
 * ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU/GPL em
 * portugu�s para maiores detalhes.
 *
 * Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo
 * "LICENCA.txt", junto com este programa, se n�o, acesse o Portal do Software
 * P�blico Brasileiro no endere�o www.softwarepublico.gov.br ou escreva para a
 * Funda��o do Software Livre (FSF) Inc., 51 Franklin St, Fifth Floor, Boston,
 * MA 02110-1301, USA --->
 *
 * Usuario portal
 *
 * @author Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * \b Maintainers: \n
 * Jonas Guilherme Dahmer [jonas@solis.coop.br]
 *
 * @since
 * Class created on 24/09/2012
 *
 */
class PrtHistoricoEscolar extends MForm
{
    public function __construct(){}
    
    public function obterContratos($personid)
    {
        $MIOLO = MIOLO::getInstance();
        
        $busDiverseConsultation = $MIOLO->getBusiness('academic', 'BusDiverseConsultation');
        
        $dataContracts = $busDiverseConsultation->getPersonContracts($personid);
        
        if ( count($dataContracts) > 0 )
        {
            foreach ( $dataContracts as $contractRow )
            {
                $contrato = new stdClass();
                $contrato->id = $contractRow[0];
                $contrato->curso = $contractRow[1];
                $contrato->versao = $contractRow[2];
                $contrato->turno = $contractRow[3];
                $contrato->unidade = $contractRow[4];
                $data[] = $contrato;
            }
        }
        
        return $data;
    }
    
    public function obterHistoricoEscolar($contractId, $periodId=null)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->notInPeriodId = $periodId?$periodId:SAGU::getParameter('BASIC', 'CURRENT_PERIOD_ID');
        $filter->contractId = $contractId;
        
        $busDiverseConsultation = $MIOLO->getBusiness('academic', 'BusDiverseConsultation');        
        $data = $busDiverseConsultation->getCurricularComponentCoursed($filter);
        
        return $data;
    }
    
    public function obterAproveitamentos($contractId, $periodId=null)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->contractId = array( $contractId );
        $filter->periodId   = $periodId?$periodId:SAGU::getParameter('BASIC', 'CURRENT_PERIOD_ID');
        
        $busDiverseConsultation = $MIOLO->getBusiness('academic', 'BusDiverseConsultation');
        $data = $busDiverseConsultation->getCurricularComponentExploited($filter);
        
        return $data;
    }
    
    public function obterProficiencias($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->contractId  = $contractId;
        $filter->proficiency = true;
        
        $busDiverseConsultation = $MIOLO->getBusiness('academic', 'BusDiverseConsultation');
        $data = $busDiverseConsultation->getCurricularComponentCoursed($filter);
        
        return $data;
    }
    
    public function obterAtividadesComplementares($contractId, $compActivCategoryId = null)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->contractId = $contractId;
        $filter->changeAction = true;
        $filter->complementaryActivitiesCategoryId = $compActivCategoryId;
        
        $busComplementaryActivities = $MIOLO->getBusiness('academic','BusComplementaryActivities');
        $data = $busComplementaryActivities->searchComplementaryActivities($filter);
        
        return $data;
    }
    
    public function obterMovimentacoesContratuais($contractId)
    {
        $MIOLO = MIOLO::getInstance();
        
        $filter = new stdClass();
        $filter->contractId = $contractId;
        $filter->changeAction = true;
        
        $busMovementContract = $MIOLO->getBusiness('academic','BusMovementContract');        
        $data = $busMovementContract->searchMovementContract($filter);
        
        return $data;
    }
    
    public function obterNomeDoCurso($courseId)
    {
        $sql = new MSQL();
        $sql->setTables('acdcourse');
        $sql->setColumns('name');
        $sql->setWhere('courseid = ?');
        $sql->addParameter($courseId);
        
        $result = bBaseDeDados::consultar($sql);
        
        return $result[0][0];
    }

}


?>
