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
 * Classe para Cadastro de Equipamento SIP
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

class FrmSipEquipament extends GForm
{
    public $busLibraryUnit;
    public $busLocationForMaterialMovement;
    public $busExemplaryStatus;
        
    public function __construct()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        //Instancia os atributos com o objeto Bus$Parametro respectivamente
        $this->busLibraryUnit = $MIOLO->getBusiness($module, 'BusLibraryUnit');
        $this->busLocationForMaterialMovement = $MIOLO->getBusiness($module, 'BusLocationForMaterialMovement');
        $this->busExemplaryStatus = $MIOLO->getBusiness($module, 'BusExemplaryStatus');
        
        $this->setAllFunctions('SipEquipament', 'sipEquipamentId', 'sipEquipamentId', 'sipEquipamentId');
        
        parent::__construct();
    }

    /*
     * Criado por Tcharles Silva
     * Em: 13/11/2013
     * Ultima atualização: 18/11/2013
     * Motivo:
     *      Implementar as opções de Bin.
     */
    public function mainFields()
    {
        // Limpa a repetitive field de regras.
        if ( GForm::primeiroAcessoAoForm() )
        {
            GRepetitiveField::clearData('sipEquipamentBinRules');
        }

        // Descrição.
        $fields[] = new MTextField('description', $this->description->value, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);        

        //Campo de Usuario
        $fields[] = $fieldId = new MTextField('sipEquipamentId', $this->sipEquipamentId->value, _M('Usuário',$this->module));
        
        if ( $this->function == 'update' )
        {
            $fieldId->setReadOnly(true);
        }
        
        //Campo de senha
        $fields[] = new MPasswordField('password', $this->password->value, _M('Senha',$this->module));
        
        
        //Unidades de Biblioteca
        $this->busLibraryUnit->filterOperator = TRUE;
        $fields[] = $libraryUnitId  = new GSelection('libraryUnitId',
                                                     $this->busLibraryUnit->value, 
                                                     _M('Unidade de biblioteca', 
                                                     $this->module), 
                                                     $this->busLibraryUnit->listLibraryUnit(), 
                                                     null, null, null, true);
        //$libraryUnitId->addAttribute('onchange', 'javascript:'.GUtil::getAjax('libraryUnitOnChange') );       
        
        /*
         * Inicio dos Radio Buttons
         * Abaixo adicionamos o componente, logo após,
         * setamos o campo GRadioButtonGroup como requerido.
         */
        //Campo se permite Realizar Empréstimo
        $fields[] = $radio = new GRadioButtonGroup('makeLoan', _M('Realiza empréstimo', $this->module), GUtil::listYesNo(1), DB_FALSE );
        $controls = $radio->getControls();
        $controls[0]->setClass('mCaptionRequired');
        
        //Campo se permite Realizar Devolução
        $fields[] = $radio = new GRadioButtonGroup('makeReturn', _M('Realiza devolução', $this->module) , GUtil::listYesNo(1), DB_FALSE );
        $controls = $radio->getControls();
        $controls[0]->setClass('mCaptionRequired');
        
        //Campo se permite Realizar Renovação
        $fields[] = $radio = new GRadioButtonGroup('makeRenew', _M('Realiza renovação', $this->module) , GUtil::listYesNo(1), DB_FALSE );
        $controls = $radio->getControls();
        $controls[0]->setClass('mCaptionRequired');
        
        //Campo se permite negar o cartão do usuário
        $fields[] = $radio = new GRadioButtonGroup('denyUserCard', _M('Bloqueia cartão de usuário', $this->module) , GUtil::listYesNo(1), DB_FALSE );
        $controls = $radio->getControls();
        $controls[0]->setClass('mCaptionRequired');
        
        //Campo se permite trabalhar em modo offline
        $fields[] = $radio = new GRadioButtonGroup('offlineMode', _M('Trabalha em modo off-line', $this->module) , GUtil::listYesNo(1), DB_FALSE );
        $controls = $radio->getControls();
        $controls[0]->setClass('mCaptionRequired');
        
        //Campo se permite equipamento logar sem senha
        $fields[] = $radio = new GRadioButtonGroup('requiredpassword', _M('Autentica usuário apenas com senha', $this->module) , GUtil::listYesNo(1), DB_TRUE );
        $controls = $radio->getControls();
        $controls[0]->setClass('mCaptionRequired');
        
        //Fim dos Radio Buttons
        
        //Campo de Periodo de tempo
        $fields[] = new MIntegerField('timeOutPeriod', $this->timeOutPeriod->value, _M('Tempo de tentativa de conexão',$this->module));
        
        //Campo de Permitir tentativas
        $fields[] = new MIntegerField('retriesAllow', NULL, _M('Numero de tentativas',$this->module));
        
        //Local da circulação de material
        $fields[] = new GSelection('locationformaterialmovementid',
                                                     $this->busLocationForMaterialMovement->value,
                                                     _M('Local da circulação de material',
                                                     $this->module),
                                                     $this->busLocationForMaterialMovement->listLocationForMaterialMovement(),
                                                     null, null, null, true);
        
        //Campo de Bin
        $fields[] = new MIntegerField('binDefault', $this->sipEquipamentId->value, _M('Bin Padrão',$this->module));
        
        //Campo para Mensagem da tela
        $fields[] = new MMultiLineField('screenMessage', $this->screenMensage->value, _M('Mensagem em tela',$this->module), 20, 5, 40);
        
        //Campo para Mensagem impressa
        $fields[] = new MMultiLineField('printMessage', $this->printMessage->value, _M('Mensagem impressa',$this->module), 20, 5, 40);
        
        //Campo Repetitivo para Bin
        $statusList = $this->busExemplaryStatus->listExemplaryStatus(null, true);

        $fields[] = $binToExemplaryStatus = new GRepetitiveField('sipEquipamentBinRules', _M('Regras para Bin', $this->module), NULL, NULL, array('remove'));
        
        //$fldBinToExemplaryStatus[] = new GSelection('exemplaryStatusId', $this->exemplaryStatusId->value, _M('Estado do exemplar', $this->module), $statusList, false, false, false, false);
        $label = new MLabel(_M('Estado do exemplar', $this->module));
        $field = new GSelection('exemplaryStatusId', $this->exemplaryStatusId->value, NULL, $statusList, false, false, false, false);
        $fldBinToExemplaryStatus[] = new GContainer('', array($label, $field));
        $fldBinToExemplaryStatus[] = new MIntegerField('bin', $this->bin->value, _M('Bin',$this->module));
        
        $binToExemplaryStatus->setFields($fldBinToExemplaryStatus);

        $tableBinValidators[] = new GnutecaUniqueValidator('exemplaryStatusId', _M('Tipo de Material', $this->module), 'required');
        //$tableBinValidators[] = new GnutecaUniqueValidator('bin', _M('Bin', $this->module), 'required');
        $tableBinValidators[] = new MIntegerValidator('bin', _M('Bin', $this->module), 'required');
        $binToExemplaryStatus->setValidators( $tableBinValidators );

        $columns[] = new MGridColumn( _M('Estado', $this->module), 'left', true, '', false, 'exemplaryStatusId');
        $columns[] = new MGridColumn( _M('Estado', $this->module), 'left', true, '', true, 'exemplaryStatusIdDesc');
        $columns[] = new MGridColumn( _M('Bin', $this->module), 'left', true, '', true, 'bin');
        
        $binToExemplaryStatus->setColumns($columns);
        //<----- Fim do Campo Repetitivo ------>
        
        
        /* Campos para validação do patronInfo
         * Utilizado para restringir acessos aos terminais, mediante cadastrado por aqui
         * 
         * Por padrão, os campos estão desabilitados, ou seja, sempre irá verificar.
         */
        
        $campos[] = $radio = new GRadioButtonGroup('psLoanlimit', _M('Exceder limite de empréstimos', $this->module),
                                                   GUtil::listYesNo(1), DB_FALSE );
        
        $campos[] = $radio = new GRadioButtonGroup('psOverduelimit', _M('Exceder limite de atrasos', $this->module),
                                                   GUtil::listYesNo(1), DB_FALSE );
        
        $campos[] = $radio = new GRadioButtonGroup('psPenaltylimit', _M('Exceder limite de penalidades', $this->module),
                                                   GUtil::listYesNo(1), DB_FALSE );
        
        $campos[] = $radio = new GRadioButtonGroup('psFinelimit', _M('Exceder limite de multas', $this->module),
                                                   GUtil::listYesNo(1), DB_FALSE );
        
        $fields[] = new MBaseGroup('patroninfo', 'Permitir acesso ao terminal se:', $campos);
        
        //<----- Fim dos campos ------->
        
        //Inicio dos validatores
        $validators[] = new MRequiredValidator('sipEquipamentId');//, _M('Usuário', $this->module), 'required');
        $validators[] = new MRequiredValidator('description');
        $validators[] = new MRequiredValidator('libraryUnitId');
        $validators[] = new MRequiredValidator('makeLoan');
        $validators[] = new MRequiredValidator('makeReturn');
        $validators[] = new MRequiredValidator('makeRenew');
        $validators[] = new MRequiredValidator('denyUserCard');
        $validators[] = new MRequiredValidator('offlineMode');
        $validators[] = new MRequiredValidator('timeOutPeriod');
        $validators[] = new MRequiredValidator('retriesAllow');
        $validators[] = new MRequiredValidator('locationformaterialmovementid');
        $validators[] = new MRequiredValidator('binDefault');
        //Fim dos validatores


        $this->setFields($fields);
        $this->setValidators($validators);
    }
    
    public function getData()
    {
        
        
        $data = parent::getData(true);
        
        if ( $data->sipEquipamentBinRules )
        {
            foreach ( $data->sipEquipamentBinRules as $key => $value )
            {
                if ( $value->removeData )
                {
                    unset($data->sipEquipamentBinRules[$key]);
                }
            }
        }
        
        return $data;
        
    }  

    public function addToTable($args) 
    {
        $this->forceAddToTable($args);
    }
    
    //Função que é chamada ao adicionar uma Bin
    public function forceAddToTable($args)
    {
        $args->exemplaryStatusIdDesc = $this->busExemplaryStatus->getDescription($args->exemplaryStatusId);
        parent::forceAddToTable($args);
    }

    
    public function setData( $data )
    {
        parent::setData($data, true);
    }
}
?>