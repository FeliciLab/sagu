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
 * Supplier form
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
 * Class created on 28/11/2008
 *
 **/
class FrmSupplier extends GForm
{
    public function __construct($data = NULL)
    {
        $this->setAllFunctions('Supplier', null, 'supplierId', array('supplierId'));
        parent::__construct( );
    }

    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[] = new MTextField('supplierId', null, _M('Código', $this->module), FIELD_ID_SIZE, null, null, true);
        }

        $fields[]   = new MTextField('_name', null, _M('Nome', $this->module), FIELD_DESCRIPTION_SIZE);
        $tabControl = new GTabControl('supplierTypeAndLocation');
        $tabControl->addTab('tabBuy', _M('Compra',       $this->module), $this->getTabFields("C"));
        $tabControl->addTab('tabInterchange',  _M('Permuta',  $this->module), $this->getTabFields("P"));
        $tabControl->addTab('tabDonation',  _M('Doação',  $this->module), $this->getTabFields("D"));

        $fields[] = $tabControl;

        $this->setFields( $fields );
        
        if ( !$this->function == 'update')
        {
            $validators[] = new MRequiredValidator('supplierId');
        }

        $this->setValidators($validators);
    }

    /**
     * Retorna os campos das tabs
     *
     * @param string type
     * @return array de campos
     */
    private function getTabFields($type)
    {
        $fields[] = new MTextField("tab[$type][companyName]",      null,  _M('Nome da companhia', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][cnpj]",             null,  _M("CNPJ", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][location]",         null, _M("Logradouro", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][neighborhood]",     null, _M("Bairro", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][city]",             null, _M("Cidade", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][zipCode]",          null, _M("CEP", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][phone]",            null, _M("Telefone", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][fax]",              null, _M("Fax", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][alternativePhone]", null, _M("Telefone alternativo", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][email]",            null, _M("E-mail", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][alternativeEmail]", null, _M("E-mail alternativo", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][contact]",          null, _M("Contato", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MTextField("tab[$type][site]",             null, _M("Site", $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MCalendarField("tab[$type][date]",         null, _M("Data", $this->module), FIELD_DATE_SIZE);
        $fields[] = new MMultiLineField("tab[$type][observation]", null, _M("Observação", $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = new MMultiLineField("tab[$type][bankDeposit]", null, _M("Depósito bancário", $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = new MSeparator();

        return $fields;
    }

    /**
     * Metodo chamado para carregar os valores da base e setar os campos
     *
     */
    public function loadFields()
    {
        $supplierId = MIOLO::_REQUEST('supplierId');

        $this->business->getSupplier($supplierId);
        $this->business->_name = $this->business->name;

        $this->setTabData( $supplierId, 'C' );
        $this->setTabData( $supplierId, 'D' );
        $this->setTabData( $supplierId, 'P' );

        $this->setData($this->business);
    }

    /**
     * Define os dados de uma aba no formulário
     * @param integer $supplierId código do fornecedor
     * @param string $type C = compra, D = doação, P = permuta
     */
    protected function setTabData( $supplierId, $type )
    {
        $busSupplierTypeAndLocation = $this->MIOLO->getBusiness($this->module, "BusSupplierTypeAndLocation");

        $tabData = (array) $busSupplierTypeAndLocation->getSupplierTypeAndLocationValueForm($supplierId, strtolower($type));

        foreach ( $tabData as $line => $value )
        {
            $field = $this->GetField( "tab[$type][$line]" );

            if ( $field )
            {
                $field->setValue($value);
            }
        }
    }

    /**
     * Metodo chamado ao clicar no botao save
     *
     * @param stdClass $args
     */
    public function tbBtnSave_click( $args )
    {
        $args = (object)$_REQUEST;
        
        $this->business->clean();
        $this->business->supplierId     = $args->supplierId;
        $this->business->name           = $args->_name;
        $this->business->companyName    = $args->companyName;

        //É necessário preencher pelo menos 1 campo Nome da compania
        foreach ($args->tab as $type => $fields)
        {
            foreach ($fields as $fieldsName => $value)
            {
                if ($fields[companyName])
                {  
                    $companyName = true;
                }
            }
        }
        if ($companyName)
        {
            parent::tbBtnSave_click($sender, $args);
        }
        else
        {
            $this->error(_M('É necessário preencher pelo menos 1 campo Nome da companhia.', $this->module) );
        }
    }
}
?>