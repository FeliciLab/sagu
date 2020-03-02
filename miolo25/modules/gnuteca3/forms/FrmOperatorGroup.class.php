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
 *
 * Holiday form
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 *
 * @since
 * Class created on 03/05/2011
 *
 **/
$MIOLO->uses('db/BusMaterial.class.php', 'gnuteca3');
$MIOLO->uses('db/BusOperatorGroup.class.php', 'gnuteca3');
class FrmOperatorGroup extends GForm
{
    public $MIOLO,
           $module;
    
    private $labels;

    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->setAllFunctions('OperatorGroup', null, array('idGroup', 'groupName'), 'groupName', 'groupName');

        $this->labels['ACESSAR'] = _M('Acessar', $this->module);
        $this->labels['INCLUIR'] = _M('Incluir', $this->module);
        $this->labels['ALTERAR'] = _M('Alterar', $this->module);
        $this->labels['REMOVER'] = _M('Remover', $this->module);
        
        parent::__construct();

        //limpa a repetitive de permissões
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('access');
        }
    }

    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[] = new MTextField('idGroup', null, _M('Código',$this->module), FIELD_ID_SIZE,null, null, $this->function == 'update');
        }

        $fields[] = $groupName = new MTextField('groupName', null, _M('Nome',$this->module), FIELD_DESCRIPTION_SIZE);
        $groupName->addStyle('text-transform', 'uppercase'); //converte texto em maiúsculas

        $fldTransaction[] = new GSelection('idTransaction', null, _M('Transação', $this->module), BusinessGnuteca3BusOperatorGroup::listTransactions()  );
        //colunas
        $transactionColumn[] = new MGridColumn( _M('Id',    $this->module), 'left', true, null, true, 'idTransaction' );
        $transactionColumn[] = new MGridColumn( _M('Transação',    $this->module), 'left', true, null, true, 'descTransaction' );

        $perms = $this->MIOLO->getPerms()->perms;
        
        if ( is_array($perms) )
        {
            $checkbox = array();
            foreach ($perms as $keyPerm=>$perm)
            {
                $fldTransaction[] = new GRadioButtonGroup($perm, $this->labels[$perm], GUtil::listYesNo(1), DB_TRUE, null, MFormControl::LAYOUT_HORIZONTAL);
                $fldTransaction[] = new MHiddenField($perm.'_defaultValue','t');
                $transactionColumn[] = new MGridColumn( $this->labels[$perm], 'left', true, null, false, $perm);
                $transactionColumn[] = new MGridColumn( $this->labels[$perm], 'left', true, null, true, "{$perm}Desc" );
            }
        }

        $validsTransaction[] = new MRequiredValidator('idTransaction');
        $validsTransaction[] = new GnutecaUniqueValidator('idTransaction', _M('Transação', $this->module));

        $fields[] = new MSeparator('<br>');

        $access = new GRepetitiveField('access', _M('Permissões', $this->module), NULL, NULL);
        $access->setFields( $fldTransaction );
        $access->setValidators( $validsTransaction );
        $access->setColumns($transactionColumn);
        $fields[] = $access;

        $this->setFields($fields);

        $validators[]   = new MRequiredValidator('groupName');
        $validators[]   = new MRequiredValidator('access');

        $this->setValidators($validators);

        //FIXME marca os campos radio button da repetitive como "Não"
        if ( $this->function == 'update' )
        {
            foreach ($perms as $keyPerm=>$perm)
            {
                $this->page->onLoad("var field = dojo.byId('{$perm}_0'); if ( field ) field.checked = true;");
            }
         }
    }

    /**
     * Método reescrito para adicionar dados na repetitive
     *
     * @param (object) $args
     * @param (boolean) $forceMode
     */
    public function addToTable($args, $forceMode = FALSE)
    {
        $args = $this->accessParse($args);

    	($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }

    /**
     * Método reescrito da GRepetitiveField
     *
     * @param (object) $args
     */
    public function forceAddToTable($args)
    {
        $this->addToTable($args, true);
    }

    /**
     * Trata os dados das transações
     *
     * @param $data
     */
    function accessParse($data)
    {
        $MIOLO = MIOLO::getInstance();
        
        if (is_array($data))
        {
            $arrData = array();

            foreach( $data as $i=> $value )
            {
                $arrData[] = $this->accessParse($value);
            }

            return $arrData;
        }
        else if (is_object($data))
        {
            $transactions = BusinessGnuteca3BusOperatorGroup::listTransactions();
            $data->descTransaction = $transactions[$data->idTransaction];
            
            //Se nao existir descricao para a transacao, ela nao deve aparecer.
            if ( empty($data->descTransaction) )
            {
               return false;
            }

            $perms = $MIOLO->getPerms()->perms;

            if ( is_array($perms) )
            {
                foreach( $perms as $permKey=>$perm)
                {
                    $posDesc = $perm . 'Desc'; //chave da descrição da permissão
                    $data->$posDesc = $data->$perm ? GUtil::getYesNo($data->$perm) : GUtil::getYesNo(DB_FALSE);

                    //quando vem do banco vazio, determinar como false
                    if ( !$data->$perm )
                    {
                        $data->$perm = DB_FALSE;
                    }
                }
            }

            return $data;
        }
    }

    /**
     * Método reescrito para salvar
     */
    public function tbBtnSave_click()
    {
        $MIOLO = MIOLO::getInstance();
        
        $data = $this->getData();

        $newAccess = array();
        $perms = $MIOLO->getPerms()->perms;
        if ( is_array($data->access) )
        {
            foreach($data->access as $i => $access)
            {
                if ( !$access->removeData )
                {
                    if ( is_array($perms) )
                    {
                        foreach( $perms as $permKey => $perm)
                        {
                            if ( $access->$perm == DB_TRUE )
                            {
                                $value = new stdClass();
                                $value->transaction = $access->idTransaction;
                                $value->rights = $permKey;
                                $newAccess[] = $value;
                            }
                        }
                    }
                }
            }
            $data->access = $newAccess;
        }

        parent::tbBtnSave_click(null, $data);
    }

    /**
     *  Método reecrito para poder tratar dados da repetitiveField
     */
    public function loadFields()
    {
        $this->business->getOperatorGroup( MIOLO::_REQUEST('idGroup') );
        $this->setData($this->business);

        //trata os dados da repetitive antes de preencher
        GRepetitiveField::setData($this->accessParse($this->business->access), 'access');
    }
}
?>