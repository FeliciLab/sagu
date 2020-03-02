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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
 * Class created on 06/01/2009
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
class FrmConfigReport extends GForm
{
    /** @var BusinessGnuteca3BusReport  */
    public $business;

    public function __construct()
    {
        $this->setAllFunctions('Report',null, 'reportId', array('reportId','Title','permission','isActive') );
        $this->setTransaction('gtcConfigReport');

        parent::__construct();
        
        if  ( $this->primeiroAcessoAoForm() && ($this->function != 'update') )
        {
        	GRepetitiveField::clearData('parameters');
            GFileUploader::clearData('odtModel');
        }
    }


    public function mainFields()
    {
        $tabControl = new GTabControl('tabControlReport');

 		$fields[] = $reportId = new MTextField('reportId', MIOLO::_REQUEST('reportId') , _M('Código', $this->module), FIELD_DESCRIPTION_SIZE, _M('Código será formatado após inserção.'), null, ( $this->function == 'update') );
        $reportId->addStyle('text-transform','uppercase');
        $fields[] = new MTextField('Title','',_M('Título', $this->module), FIELD_DESCRIPTION_SIZE );
        $fields[] = new MMultiLineField('description',null, _M('Descrição', $this->module),FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE  );
        $fields[] = new GSelection('permission', 'basic', _M('Permissão', $this->module), BusinessGnuteca3BusDomain::listForSelect('REPORT_PERMISSION') , null, null, nul, true);
        $fields[] = new GSelection('reportGroup', null, _M('Grupo', $this->module), BusinessGnuteca3BusDomain::listForSelect('REPORT_GROUP'));
        $fields[] = new GRadioButtonGroup('isActive', _M('Está ativo', $this->module) , GUtil::listYesNo(1), DB_TRUE, null, MFormControl::LAYOUT_HORIZONTAL, null);
        $fields[] = new GFileUploader( _M('Modelo Odt'), false, null, 'odtModel' );
        GFileUploader::setExtensions( array('odt','odg'), array('php', 'class', 'js'), 'odtModel'); //somente upload de odt
        GFileUploader::setLimit(1, 'odtModel'); // Limitar para upload de apenas um arquivo.

        $types['string'] = _M('String', $this->module);
        $types['date']   = _M('Data', $this->module);
        $types['int']    = _M('Inteiro', $this->module);
        $types['select'] = _M('Selecione', $this->module);
        $types['itemNumber'] = _M('Número de exemplar', $this->module);

        $lines[] = new MTextField('label', null, _M('Etiqueta', $this->module), FIELD_DESCRIPTION_SIZE);
        $lines[] = new MTextField('identifier', null, _M('Identificador', $this->module) , FIELD_DESCRIPTION_SIZE);
        $lines[] = new GSelection('type',null, _M('Tipo', $this->module), $types);
        $lines[] = new MTextField('defaultValue', null, _M('Valor padrão', $this->module) , FIELD_DESCRIPTION_SIZE);
        $lines[] = new MTextField('lastValue', null, _M('Último valor', $this->module) , FIELD_DESCRIPTION_SIZE);
        $lines[] = new MMultiLineField('options', null, _M('Opções', $this->module) ,FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE );
        $lines[] = new MDiv( 'helpDiv', array( new MButton('btnHelp', _M('Ajuda', $this->module), ':showFunctionHelp'),new MLabel(_M('Funciona em qualquer dos campos', $this->module)) ) ) ;

        $columns[] = new MGridColumn( _M('Etiqueta', $this->module), 'left', true, true, true, 'label');
        $columns[] = new MGridColumn( _M('Identificador', $this->module), 'left', true, true, true, 'identifier');
        $columns[] = new MGridColumn( _M('Tipo', $this->module), 'left', true, true, true, 'type');
        $columns[] = new MGridColumn( _M('Valor padrão', $this->module), 'left', true, true, true, 'defaultValue');
        $columns[] = new MGridColumn( _M('Último valor', $this->module), 'left', true, true, true, 'lastValue');
        $columns[] = new MGridColumn( _M('Opções', $this->module), 'left', true, true, true, 'options');

        $paramValidators[] = new MRequiredValidator('label', _M('Etiqueta', $this->module));
        $paramValidators[] = new MRequiredValidator('identifier', _M('Identificador', $this->module));
        $paramValidators[] = new GnutecaUniqueValidator('identifier', _M('Identificador', $this->module));

        $paramValidators[] = new MRequiredValidator('type', _M('Tipo', $this->module));
        $parameters = new GRepetitiveField('parameters', _M('Parâmetro', $this->module), $columns, $lines , array('edit','remove','up','down'), 'vertical');
        $parameters->setValidators( $paramValidators );

        $tabControl->addTab('tabReport', _M('Relatório', $this->module), $fields);
        $tabControl->addTab('tabParam',_M('Parâmetro', $this->module), array( $parameters ) );

        $sqlContent[0] = new MMultiLineField('reportSql',null, _M('Sql', $this->module),FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE*2, FIELD_MULTILINE_COLS_SIZE  );
        $sqlContent[0]->_addStyle('width','99%');
        $sqlContent[1] = new MMultiLineField('reportSubSql',null, _M('Sub sql', $this->module),FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE*2, FIELD_MULTILINE_COLS_SIZE  );
        $sqlContent[1]->_addStyle('width','99%');

        $tabControl->addTab('tabSql', _M('Sql', $this->module), $sqlContent);

        $scriptContent[0] = new MMultiLineField('script',null, _M('Script', $this->module),FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE*4, FIELD_MULTILINE_COLS_SIZE  );
        $scriptContent[0]->_addStyle('width','99%');

        $tabControl->addTab('tabScript', _M('Script', $this->module), $scriptContent);

        $this->setFields(array($tabControl));

        $valids[] = new MRequiredValidator('Title');
        $valids[] = new MRequiredValidator('reportId', null, 20); //limita a 20 caracteres
        $this->setValidators($valids);
    }

    public function setData( $data )
    {
        parent::setData($data,true);
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile->folder = 'odt';
        $busFile->fileName = BusinessGnuteca3BusFile::getValidFilename( $data->reportId ) .'.';
        GFileUploader::setData( $busFile->searchFile(true), 'odtModel' );
    }

    /**
     * Retorna os dados do formulário, modificando o reportId.
     *
     * @return stdClass
     */
    public function getData()
    {
        $data = parent::getData( false );
        $data->reportId = strtoupper( BusinessGnuteca3BusFile::getValidFilename( $data->reportId ) );
        return $data;
    }

    public function tbBtnSave_click( )
    {
        $fileData = GFileUploader::getData('odtModel');
        parent::tbBtnSave_click( );
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
        $id = $this->business->reportId;

        if ( $fileData )
        {
            //converte o nome do arquivo para o número de controle, foreach caso o id seja diferente de i
            foreach ( $fileData as $line => $info)
            {
                if ( $info->tmp_name )
                {
                    $explode = explode('.', $fileData[$line]->basename);
                    $ext = $explode[count($explode)-1];
                    $fileData[$line]->basename = $id.'.'.$ext;
                }
            }

            if ( $busFile->fileExists( 'odt', $id, $ext ) && $fileData[0]->basename )
            {
                $busFile->deleteFile( $busFile->getAbsoluteFilePath( 'odt', $id, $ext ) );
            }

            $busFile->folder = 'odt';
            $busFile->files = $fileData;
            $busFile->insertFile(); //insere o arquivo
            GFileUploader::clearData('odtModel'); //limpa o sessão para evitar fazer 2 vezes a mesma coisa
        }
    }

    public function showFunctionHelp()
    {
    	parent::_showFunctionHelp( array('cut', 'upper', 'pad', 'replace', 'lower', 'date', 'ifexists', 'href', 'gtcgetmaterialcontent', 'gtcgettagname', 'gtcseparator', 'executeDB',  'executePHP', 'executeSQL') );
    }
}
?>