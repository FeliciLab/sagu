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
 * Dictionary form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 03/12/2008
 *
 **/

$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
$MIOLO->getClass('gnuteca3', 'gIso2709Import');

class FrmISO2709Import extends GForm
{
    public $busMaterial;
    public $busPreCatalogue;

    public function __construct($data)
    {
        $this->MIOLO    = MIOLO::getInstance();
        $this->module   = MIOLO::getCurrentModule();

        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->busPreCatalogue = $this->MIOLO->getBusiness($this->module, 'BusPreCatalogue');

        if ( GForm::primeiroAcessoAoForm() )
        {
            GFileUploader::clearData('isoFile');
        }

        $this->setTransaction('gtcISO2709Import');

        parent::__construct(_M("Importar", $this->module));
    }

    public function mainFields()
    {
        $fields[] = new MTextField('recordDelimiter', '29', _M('Delimitador de registro', $this->module));
        $fields[] = new MTextField('fieldDelimiter', '30', _M('Delimitador de campo', $this->module));
        $fields[] = new MTextField('subFieldDelimiter', '31',  _M('Delimitador de subcampo', $this->module));
        $fields[] = new MCheckBox("ascii", DB_TRUE, _M('Valor ascii'), true);
        $fields[] = new MSeparator('<br>');
        $fields[] = new GFileUploader( _M('Envie seu arquivo', $this->module), false, $opts = array('remove'), 'isoFile' );
        GFileUploader::setExtensions(array('iso'), array('php', 'class', 'js'), 'isoFile') ;
        GFileUploader::setLimit(10, 'isoFile');

        $fields[] = new MSeparator('<br>');
        $fields[] = new MDiv('divGrid');

        //toolbar
        $this->getToolBar();
        $fields[] =  $this->_toolBar;
        $this->_toolBar->enableButtons('tbBtnSave', 'tbBtnNew');
        $this->_toolBar->disableButton( array('tbBtnSearch'));

        $this->setFields($fields);
    }


    public function tbBtnSave_click($sender=NULL, $confirm = DB_FALSE)
    {
        ini_set('memory_limit', '-1'); // define sem nenhuma limitação de memória
        //$this->mainFields(); //FIXME isso foi feito para funcionar validadores, analisar uma forma melhor
        $validators[] = new MRequiredValidator('recordDelimiter',_M('Delimitador de registro', 'gnuteca3') );
        $validators[] = new MRequiredValidator('fieldDelimiter',_M('Delimitador de campo', 'gnuteca3'));
        $validators[] = new MRequiredValidator('subFieldDelimiter',_M('Delimitador de subcampo', 'gnuteca3'));
        $this->setValidators($validators);

        $data = $this->getData();

        if ( !$this->validate($data, $errors) )
        {
            return false;
        }

        $data->files = GFileUploader::getData('isoFile');

        $ok = array();
        //percorre todos os arquivos
        //pega a quantidade de registros a importar
        $readRecords = 0;

        if ( $data->ascii )
        {
            $data->recordDelimiter = chr($data->recordDelimiter);
            $data->fieldDelimiter = chr($data->fieldDelimiter);
            $data->subFieldDelimiter = chr($data->subFieldDelimiter);
        }

        $readRecords = 0 ;
        
        //interpreta os arquivos a fim de verificar a quantidades de registros
        if ( is_array($data->files) )
        {
            foreach( $data->files as $file )
            {
                $content = file_get_contents($file->tmp_name);
                $importObject = new gIso2709Import($content , $data->recordDelimiter, $data->fieldDelimiter, $data->subFieldDelimiter);
                $readRecords += $importObject->size();
            }
        } else {
            return $this->information(_M('Não há registros para importar.', $this->module));
        }
        
        //caso existiam registros nos arquivos pede confirmação
        if ( $readRecords > 0 )
        {
            $this->MIOLO->getSession()->setValue('data', $data);
            return $this->question(_M('Tem certeza que deseja importar @1 registros?', $this->module, $readRecords), GUtil::getAjax('saveConfirmation'));
        } else {
            return $this->information(_M('Não há registros para importar.', $this->module));
        }
    }

    /**
     * Função chamada após a confirmação de importação de registros
     */
    public function saveConfirmation()
    {
        $data = $this->MIOLO->getSession()->getValue('data'); //pega os files da sessão
        GFileUploader::clearData('isoFile'); //limpa a repetitive de arquivos
        GFileUploader::generateTable('isoFile');
        $this->busPreCatalogue->beginTransaction();
        $controlNumbers = array();
        $import = array();

        $readRecords = $savedRecors = 0;

        if ( !is_array($data->files))
        {
            throw new Exception(_M('Impossível encontrar arquivo!','gnuteca3'));
        }

        foreach($data->files as $file )
        {
            $content = file_get_contents($file->tmp_name);
            $importObject = new gIso2709Import($content, $data->recordDelimiter, $data->fieldDelimiter, $data->subFieldDelimiter);
            $import[] = $importObject->execute();

            $savedRecors += $importObject->savedRecords;
            $readRecords += $importObject->size();
        }

        $this->busPreCatalogue->commitTransaction();

        if ( !in_array(false, $import)  )
        {
            $messages = new GMessages();
            $message = _M('Foram importados @1 de @2 materiais.', $this->module, $savedRecors, $readRecords);
            $messages->addInformation($message);
            $this->injectContent($messages->getMessagesTableRaw(), true, true);
        }
        else
        {
            $goto = $this->MIOLO->getActionURL($this->module, $this->_action);
            $this->error(_M('Ocorreu um erro na importação', $this->module), $goto);
        }
    }

}

?>
