<?php
/**
 * <--- Copyright 2005-2013 de Solis - Cooperativa de Soluções Livres Ltda. e
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
 * Classe para Busca de Equipamento SIP
 *
 * @author Tcharles Silva [tcharles@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Tcharles Silva [tcharles@solis.coop.br]
 *
 * @since
 * Class created on 14/11/2013
 * 
 **/

class FrmSipEquipamentSearch extends GForm
{
    public $busLibraryUnit;
    public $busLocationForMaterialMovement;

    public function __construct()
    {
        $MIOLO  = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();

        //Instancia os atributos com o objeto Bus$Parametro respectivamente
        $this->busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busLocationForMaterialMovement = $MIOLO->getBusiness($module, 'BusLocationForMaterialMovement');

        $this->setAllFunctions('SipEquipament', 'sipEquipamentId', 'sipEquipamentId', 'sipEquipamentId');
        //$this->setGrid('GrdPerson');

        parent::__construct();
    }

     /*
     * Criado por Tcharles Silva
     * Em: 13/11/2013
     */
    public function mainFields()
    {
        // Descricao.
        $fields[] = new MTextField('descriptionS', $this->description->value, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);        

        //Campo de Usuario
        $fields[] = new MIntegerField('sipEquipamentIdS', $this->sipEquipamentId->value, _M('Usuário',$this->module));

        //Unidades de Biblioteca
        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = $libraryUnitId  = new GSelection('libraryUnitIdS',   $this->libraryUnitId->value, _M('Unidade de biblioteca', $this->module), $this->busLibraryUnit->listLibraryUnit());
        //$libraryUnitId->addAttribute('onchange', 'javascript:'.GUtil::getAjax('libraryUnitOnChange') );

        //Radio Buttons
        $fields[] = new GSelection('makeLoanS', NULL, _M('Realiza empréstimo', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('makeReturnS',  NULL, _M('Realiza devolução', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('makeRenewS', NULL, _M('Realiza renovação', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('denyUserCardS', NULL, _M('Bloqueia cartão de usuário', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('offlineModeS', NULL, _M('Trabalha em modo off-line', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('requiredpasswordS', NULL, _M('Autentica apenas com senha', $this->module), GUtil::listYesNo());
        
        //Fim dos Radio Buttons

        //Local da circulação de material
        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = $libraryUnitId  = new GSelection('locationformaterialmovementidS',
                                                     $this->busLocationForMaterialMovement->value,
                                                     _M('Local da circulação de material',
                                                     $this->module),
                                                     $this->busLocationForMaterialMovement->listLocationForMaterialMovement());

        $fields[] = new GSelection('psLoanlimitS', NULL, _M('Exceder limite de empréstimos', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('psOverduelimitS', NULL, _M('Exceder limite de atrasos', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('psPenaltylimitS', NULL, _M('Exceder limite de penalidades', $this->module), GUtil::listYesNo());
        $fields[] = new GSelection('psFinelimitS', NULL, _M('Exceder limite de multas', $this->module), GUtil::listYesNo());
        
        // Fim dos campos

        //Coloca os campos e os validatores
        $this->setFields($fields);
    }
    
    public function showDetail()
    {
        $MIOLO  = MIOLO::getInstance();
    	$module = MIOLO::getCurrentModule();

    	$busSipEquipamentStatusHistory = $MIOLO->getBusiness($module, 'BusSipEquipamentStatusHistory');
    	$busSipEquipamentStatusHistory->sipEquipamentId = MIOLO::_REQUEST('sipEquipamentId');

        $search = $busSipEquipamentStatusHistory->searchSipEquipamentStatusHistory(TRUE);
        

        if ($search)
        {
            $tbData = array();
            $date = new GDate();
            
            foreach ($search as $value)
            {
                /*
                 * Variavel $res, pega a mensagem referente ao Status
                 * 0 - OK
                 * 1 - SEM PAPEL
                 * 2 - DESLIGADO
                 */
                $res = BusinessGnuteca3BusSipEquipamentStatusHistory::getConstants($value->status);              
                
                $date->setDate($value->datetime);
                $tbData[] = array(
                    $res,
                    //$date->getDate(GDate::MASK_DATE_USER)
                    $date->getDate(GDate::MASK_TIMESTAMP_USER)
                );
                
            }
            
            $tbColumns = array(
                _M('Estado', $this->module),
                _M('Data', $this->module)
            );
            $tb = new MTableRaw(_M('Histórico', $this->module), $tbData, $tbColumns);
            $tb->zebra = TRUE;
            $fields[] = new MDiv(null, $tb);
        }
        else
        {
            $tb = new MLabel(_M('Nenhum histórico para este registro.', $this->module));
            $tb->addStyle('width', '100%');
            $tb->addStyle('text-align', 'center');
            $fields[] = new MDiv(NULL, $tb);
        }

        $this->injectContent( $tb, true, _M('Histórico do estado do Equipamento Sip', $this->module) . ' '. MIOLO::_REQUEST('sipEquipamentId') );
    }
    
    /*
     * Método que seria chamado ao clicar em confirmar no excluir
     * 
     * Não esta sendo chamado e deverá ser implementado em breve para tela mais amigável.
     */
    /*
    public function tbBtnDelete_confirm($sender=NULL)
    {
        $gridData = explode(',',$_REQUEST['gridData']);
        
        //$verifica = $this->busSipEquipamento->verificacao();
        
        if($pode_deletar)
        {
            parent::tbBtnDelete_confirm();
        }
        else
        {
            $this->alert( _naop é possivel....
        }
    }
     */
    
}
?>