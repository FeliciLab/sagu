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
class FrmSearchFormat extends GForm
{
	public $MIOLO;
	public $module;
    public $busMarcTagListingOption;
    public $busSearchFormatColumn;
    private $busBond;
    
    public function __construct()
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();
        $this->busMarcTagListingOption  = $this->MIOLO->getBusiness($this->module, 'BusMarcTagListingOption');
        $this->busSearchFormatColumn    = $this->MIOLO->getBusiness($this->module, 'BusSearchFormatColumn');
        $this->busBond                  = $this->MIOLO->getBusiness($this->module, 'BusBond');
        $this->setAllFunctions('SearchFormat', null, array('searchFormatId'), array('description', 'isRestricted'));
        parent::__construct();

        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
            GRepetitiveField::clearData('searchFormatAccess');
            GRepetitiveField::clearData('searchPresentationFormat');
        }
    }


    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[]       = new MTextField('searchFormatId', null, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
            $validators[]   = new MRequiredValidator('searchFormatId');
        }

        $fields[] = new MTextField('description', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GRadioButtonGroup('isRestricted', _M('É restrita', $this->module), GUtil::listYesNo(1), DB_FALSE, null, MFormControl::LAYOUT_HORIZONTAL);
        $fields[] = new MMultiSelection('searchFormatColumn', array(null), _M('Colunas ocultas', $this->module), $this->busSearchFormatColumn->listColumns());
        $fields[] = $date = new MTextField('date', date(GDate::MASK_TIMESTAMP_USER), _M('Data', $this->module), 50);
        $date->addStyle('display', 'none');

        //GRepetitiveField gtcSearchFormatAccess
        $flds           = null;
        $columns        = null;
        $fields[]       = new MSeparator();

        $flds[] = new GSelection('linkId', '', _M('Código do vínculo', $this->module), $this->busBond->listBond(true));
       
        
        $columns[]      = new MGridColumn(_M('Código', $this->module),    'left', true, null, true, 'linkId' );
        $columns[]      = new MGridColumn(_M('Grupo',   $this->module), 'left', true, null, true, 'linkIdDescription' );
        $valids[]       = new GnutecaUniqueValidator('linkId', _M('Código do vínculo', $this->module));
        $valids[]       = new MRequiredValidator('linkId', _M('Código do vínculo', $this->module));
        
        $searchFormatAccess = new GRepetitiveField('searchFormatAccess', _M('Grupos com acesso', $this->module), null, null, array('edit', 'remove'));
        $searchFormatAccess->setFields($flds);
        $searchFormatAccess->setColumns($columns);
        $searchFormatAccess->setValidators($valids);
        $fields[] = $searchFormatAccess;

        //GRepetitiveField gtcSearchPresentationFormat
        $fields[] = new MSeparator();
        unset($flds, $columns);

        $list = $this->busMarcTagListingOption->listMarcTagListingOption('CATEGORY', TRUE);
        $flds[] = new GSelection('category', null, _M('Categoria', $this->module), $list);

        $flds[] = new GContainer( '', array( new MMultiLIneField('searchFormat', null, _M('Formato da pesquisa', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE) ) );
        $flds[] = new MHContainer('hctSF', array($searchFormatLabel,$searchFormat, $btnHelp));
        $flds[] = new MMultiLIneField('detailFormat', null, _M('Formato de detalhes', $this->module), null, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $flds[]= new MButton('btnHelp', _M('Ajuda', $this->module), ':showFunctionHelp');

        $columns[] = new MGridColumn(_M('Categoria', $this->module), 'left', true, null, true, 'category');
        $columns[] = new MGridColumn(_M('Formato da pesquisa', $this->module), 'left', true, null, true, 'searchFormat');
        $columns[] = new MGridColumn(_M('Formato de detalhes', $this->module), 'left', true, null, true, 'detailFormat');

        $valid[] = new MRequiredValidator('category');
        $valid[] = new MRequiredValidator('detailFormat', _M('Formato de detalhes', $this->module));
        $valid[] = new MRequiredValidator('searchFormat', _M('Formato da pesquisa', $this->module));

        $fields[] = $searchPresentationFormat = new GRepetitiveField('searchPresentationFormat', _M('Formato de apresentação', $this->module), $columns, $flds, array('edit', 'remove'));
        $searchPresentationFormat->setValidators($valid);
      
        $validators[] = new MRequiredValidator('description');
        $validators[] = new MRequiredValidator('searchPresentationFormat'); //pelo menos 1 registro na GnutecaRepetitiveField

        $this->setFields($fields);

        $this->setValidators($validators);
    }

    public function tbBtnSave_click($sender=NULL)
    {
        $data = $this->getData();
        $data->searchFormatAccess       = GRepetitiveField::getData('searchFormatAccess');
        $data->searchPresentationFormat = GRepetitiveField::getData('searchPresentationFormat');
        parent::tbBtnSave_click($sender, $data);
    }

    public function setData($data)
    {
        parent::setData($data, true);
    }

    public function showFunctionHelp()
    {
    	parent::_showFunctionHelp();
    }
    
    /**
     * FIXME método reescrito para substituir </style > por </style>. Ticket #9332
     * @param type $args
     * @param type $forceMode 
     */
    public function addToTable($args, $forceMode = FALSE)
    {
        $repetetive = $args->GRepetitiveField;
        
        if ( $repetetive == 'searchPresentationFormat' )
        {
            if ( is_object($args) )
            {
                $args->searchFormat = str_replace('</style >', '</style>', $args->searchFormat);
                $args->detailFormat = str_replace('</style >', '</style>', $args->detailFormat);
            }
        }
        else
        {
            $args = $this->groupParse($args); //faz parse da descrição do linkId
        }
        
    	($forceMode) ? parent::forceAddToTable($args) : parent::addToTable($args);
    }


    public function forceAddToTable($args)
    {
        $this->addToTable($args, TRUE);
    }
    
     /**
     * FIXME método reescrito para substituir </style> por </style >. Ticket #9332
     * @param type $args
     * @param type $forceMode 
     */
    function editFromTable($args)
	{
        $repetetive = $args->GRepetitiveField;
        
        if ( $repetetive == 'searchPresentationFormat' )
        {
            //obtém os valor da repetitive
            $values = GRepetitiveField::getDataItem( $args->arrayItemTemp, $args->GRepetitiveField );

            //altera os valores fazendo a troca de </style> por </style >
            $values->searchFormat = str_replace('</style>', '</style >', $values->searchFormat);
            $values->detailFormat = str_replace('</style>', '</style >', $values->detailFormat);

            //define novamente o dado na repetetitive
            GRepetitiveField::defineData($args->GRepetitiveField, $values, $args->arrayItemTemp);
        }
        
		parent::editFromTable($args);
	}
    
    /**
     * Método que trata os dados da repetitive de vínculos
     */
    public function groupParse($data)
    {
        if (is_array($data))
        {
            $arr = array();
            foreach ($data as $val)
            {
                $arr[] = $this->groupParse($val);
            }

            return $arr;
        }
        else if (is_object($data))
        {
            $link = $this->busBond->listBond();
            
            if ( is_array($link) )
            {
                foreach( $link as $key => $values )
                {
                    if ( $values[0] == $data->linkId )
                    {
                        $data->linkIdDescription = $values[1];
                        break;
                    }
                }
            }

            return $data;
        }
    }
   
    /**
     * Método reescrito para fazer o parser da descrição do vínculo na repetitive de grupos
     */
    public function loadFields()
    {
        $this->business->getSearchFormat( MIOLO::_REQUEST('searchFormatId') );
        $this->business->date = date(GDate::MASK_TIMESTAMP_USER);
        
        MUtil::clog('Date: ' . $this->business->date);
        $this->setData($this->business);
        GRepetitiveField::setData($this->groupParse($this->business->searchFormatAccess), 'searchFormatAccess');
    }
    
    /**
     *  Método sobreescrito para adicionar mensagem ao atualizar.
     * 
     * @param string $msg Mensagem do information.
     * @param string $goto URL para confirmação.
     */
    public static function information($msg, $goto)
    {
        if ( $msg == MSG_RECORD_UPDATED )
        {
            $msg .= ' ' . _M('O cache será atualizado sob demanda ou através da tarefa do agendador de tarefas!', 'gnuteca3');
        }
        
        GForm::information($msg, $goto);
    }
    
    /**
     *  Método sobreescrito para adicionar mensagem ao inserir.
     * 
     * @param string $msg Mensagem do quention.
     * @param string $gotoYes URL para quando usuário clicar em "Sim".
     * @param string $gotoNo URL para quando usuário clicar em "Não".
     */
    public static function question($msg, $gotoYes, $gotoNo)
    {
        if ( $msg == MSG_RECORD_INSERTED )
        {
            $msg .= ' ' . _M('O cache será atualizado sob demanda ou através da tarefa do agendador de tarefas!', 'gnuteca3');
        }
        
        GForm::question($msg, $gotoYes, $gotoNo);
    }
}
?>