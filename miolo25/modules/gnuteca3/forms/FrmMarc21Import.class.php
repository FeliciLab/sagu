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
 * Marc21 importation
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
  *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
  *
 * @since
 * Class created on 03/12/2008
 *
 **/

$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
$MIOLO->getClass('gnuteca3', 'GMarc21');
$MIOLO->getClass('gnuteca3', 'gMarc21Record');

class FrmMarc21Import extends GForm
{
    public $busMaterial;

    public function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        $this->setBusiness('BusPreCatalogue');
        $this->setInsertFunction('insertMaterial');
        $this->setGetFunction('getPreCatalogue');
        $this->setDeleteFunction('deletePreCatalogue');
        $this->setUpdateFunction('updatePreCatalogue');
        $this->setTransaction('gtcMarc21Import');

        parent::__construct(_M("Importar Marc21", $this->module));
    }

    /**
     * Classe Fields
     *
     */
    public function mainFields()
    {
        $module = $this->module;
        $fields[] = new MTextField('fieldDelimiter', MARC_FIELD_DELIMITER, _M('Limitador de campo', $module),FIELD_DESCRIPTION_SIZE, _M('Caractere que separa os campos', $this->module));
        $fields[] = new MTextField('subFieldDelimiter',MARC_SUBFIELD_DELIMITER, _M('Limitador de subcampo', $module),FIELD_DESCRIPTION_SIZE, _M('Caractere que separa os subcampos. Valores comuns "$", "^" e "|"'));
        $fields[] = new MTextField('emptyIndicator', MARC_EMPTY_INDICATOR, _M('Indicador vazio', $module),FIELD_DESCRIPTION_SIZE, _M('Caractere que indica quando o indicador está vazio', $this->module));
        $fields[] = new MTextField('recordDelimiter','---', _M('Limitador de registro', $module),FIELD_DESCRIPTION_SIZE);
        $fields[] = $uploader = new GFileUploader('Envie seu arquivo', FALSE, NULL, 'fileText');
        GFileUploader::setExtensions(array('txt'), array('php', 'class', 'js'), 'fileText' ) ;
        GFileUploader::setLimit(10, 'fileText');

        $fields[] = new MSeparator('<br>');
        $fields[] = new MDiv('divGrid');
        
        $this->setFields($fields);

        $this->toolBar->disableButton( array('tbBtnReset', 'btnFormContent', 'tbBtnSearch', 'tbBtnDelete'));
        $this->toolBar->enableButtons('tbBtnSave');
        
    }

    public function tbBtnSave_click($sender=NULL, $confirm = DB_FALSE)
    {
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();
        
        $data = $this->getData();
        
        $data->files = GFileUploader::getData('fileText');
        
        if( !$data->files )
        {
            throw new Exception( _M("Um arquivo deve ser selecionado!", $this->module) );
        }
        
        if( $data->files[0]->removeData && !$data->files[1] )
        {
            throw new Exception( _M("Um arquivo deve ser selecionado!", $this->module) );
        }
        
        GFileUploader::clearData('fileText');//após pegar limpa para evitar reuso

        $ok = array();
        //percorre todos os arquivos

        $confirm = (strlen(MIOLO::_REQUEST('confirm')) > 0) ? MIOLO::_REQUEST('confirm') : $confirm;

        $arrayContent = array();
        foreach ( $data->files as $file )
        {
            $fileContent = file_get_contents($file->tmp_name);
            
            if ( strlen($fileContent) > 0 )
            {
                $arrayContent[] = $fileContent;
            }
        }
        
        $content = implode($data->recordDelimiter . "\n", $arrayContent);
        
        //instancia objeto GMarc21
        $marc21Object = new GMarc21($content, $data->fieldDelimiter, $data->subFieldDelimiter, $data->emptyIndicator, $data->recordDelimiter);
        $records = $marc21Object->getRecords();
        $ok = array();
        
        $first = 0;
        
        if ( is_array($records) )
        {
            $this->business->beginTransaction();
            
            foreach ( $records as $i => $record )
            {
                $data = $record->getTags();
                $controlNumber = $this->business->getNextControlNumber();
                
                if ( $first == 0 )
                {
                    $first = $controlNumber;
                }
                
                foreach ( $data as $line => $materialItem )
                {
                    if ( $materialItem->fieldid == '000' )
                    {
                        $materialItem->content = str_replace(' ', '#', $materialItem->content);
                    }

                    $materialItem->controlNumber = $controlNumber; //seta o número de controle
                    $this->business->setData($materialItem); //seta os dados
                    $ok[] = $this->business->insertMaterial(); //insere na pré-catalogação
                }
            }
            
            $this->business->commitTransaction();
        }
        
        if( !in_array(false, $ok) )
        {
            $urlConfirm = $MIOLO->getActionURL('gnuteca3', $action);
            $this->information( _M('Registros importados com sucesso com o número de controle "@1" até "@2"', $this->module, $first, $controlNumber), $urlConfirm);
        }
        else
        {
            $this->error(_M('Erro na importação do material', $this->module), 'javascript:' . GUtil::getCloseAction());
        }
    }
}
?>