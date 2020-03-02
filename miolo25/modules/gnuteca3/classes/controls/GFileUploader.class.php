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
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Guilherme Soldateli [guilherme@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 16/03/2011
 *
 * */
GFileUploader::ajaxEventHandler();

class GFileUploader extends GRepetitiveField
{

    public function __construct($caption, $validator = false, $opts = null, $name=NULL)
    {
        $controls[] = new MFileField('uploadedfile', null, _M('Selecione o arquivo', $module), null, _M('O arquivo deve ter no máximo ' . ini_get('upload_max_filesize'), $module));
        $columns[] = new MGridColumn(_M('Arquivo', 'gnuteca3'), 'left', true, null, true, 'basename');
        $columns[] = new MGridColumn(_M('Tipo', 'gnuteca3'), 'left', true, null, true, 'type');
        $columns[] = new MGridColumn(_M('Mime', 'gnuteca3'), 'left', true, null, true, 'mimeContent');
        $columns[] = new MGridColumn(_M('Nome temporário', 'gnuteca3'), 'left', true, null, false, 'tmp_name');
        $columns[] = new MGridColumn(_M('Tamanho', 'gnuteca3'), 'left', true, null, true, 'size');
        $columns[] = new MGridColumn(_M('Link', 'gnuteca3'), 'left', true, null, false, 'mioloLink');
        $columns[] = new MGridColumn(_M('Última modificação', 'gnuteca3'), 'left', true, null, true, 'lastChange');
        
        if ( is_null($name) )
        {
            $name = 'generalUploader';
        }
        
        parent::__construct($name, $caption, $columns, $controls, $opts ? $opts : array('remove', 'download'));
        //bloqueia de cara envio de php, class e javascript
        $this->setExtensions(null, array('php', 'class', 'js'));
    }

    public static function setLimit($limit, $repetitive = NULL)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';

        if ($limit == FALSE || $limit == NULL)
        {
            GRepetitiveField::setSessionValue('uploadFileLimit', nulll, $repetitive);
        }
        else
        {
            GRepetitiveField::setSessionValue('uploadFileLimit', $limit, $repetitive);
        }
    }

    public static function getLimit($repetitive)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        return GRepetitiveField::getSessionValue('uploadFileLimit', $repetitive);
    }

    public static function setExtensions($allowed, $deny = array('php', 'class', 'js'), $repetitive = 'generalUploader')
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        GRepetitiveField::setSessionValue('uploadFileAllowedExt', $allowed, $repetitive);
        GRepetitiveField::setSessionValue('uploadFileDenyExt', $deny, $repetitive);
    }

    public static function getAllowedExtensions($repetitive)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        return GRepetitiveField::getSessionValue('uploadFileAllowedExt', $repetitive);
    }

    public static function getDenyExtensions($repetitive)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        return GRepetitiveField::getSessionValue('uploadFileDenyExt', $repetitive);
    }

    public function getData($repetitive = null)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';

        return parent::getData($repetitive);
    }

    public function setData($data, $repetitive = null)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        parent::setData($data, $repetitive);
    }

    public function clearData($repetitive = null)
    {
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        parent::clearData($repetitive);
    }

    public static function downloadFile($file)
    {
        $MIOLO = MIOLO::getInstance();
        $module = 'gnuteca3';

        if (!is_object($file))
        {
            $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');
            $relative = GUtil::getAjaxEventArgs();
            $file = $busFile->getFile($file);
        }

        $link = "window.open('$file->mioloLink');";

        if ($file->type == 'image' || $file->type == 'text')
        {
            $content[] = $info = new MDiv('info', '', 'reportDescription');

            $infos[] = new MDiv('', _M('Tamanho: @1 Tipo: @2', 'gnuteca3', $file->size, $file->mimeContent));
            $infos[] = new MDiv('', _M('Última modificação: @1', 'gnuteca3', $file->lastChange));

            if ($file->type == 'image')
            {
                $content[] = new MDiv('image', "<img src='{$file->mioloLink}&width=600px'></img>");
            }
            else
            {
                $fileContent = file_get_contents($file->absolute);
                $editable = is_writable($file->absolute);
                $infos[] = new MDiv('', _M('Codificação: @1', 'gnuteca3', GString::detectEncoding($fileContent)));
                $content[] = $absolute = new MTextField('absolute', $file->absolute);
                $absolute->addStyle("display", 'none');

                if ($editable)
                {
                    $content[] = $fileContentField = new MultiLineField('fileContent', $fileContent, '', 30, 40, 40);
                    $fileContentField->addStyle("width", "99%");
                }
                else
                {
                    $content[] = new MDiv('image', '<pre>' . $fileContent . '</pre>', 'receiptBox');
                    $infos[] = new MDiv('', _M('O arquivo não possui permissões suficientes para edição.', 'gnuteca3'));
                }
            }

            $info->setInner($infos);

            if ($editable)
            {
                $buttons[] = new MButton('saveFile', _M('Salvar', $module), GUtil::getAjax('saveFile'), GUtil::getImageTheme('save-16x16.png'));
            }

            $buttons[] = new MButton('downloadFile', _M('Download', $module), $link, GUtil::getImageTheme('table-down.png'));
            $buttons[] = GForm::getCloseButton();

            $content[] = new MDiv(null, $buttons);

            $title = $editable ? _M('Edição', 'gnuteca3') : _M('Visualização', 'gnuteca3');
            GForm::injectContent($content, false, $title . ' - ' . $file->basename);
        }
        else
        {
            $MIOLO->page->onLoad($link);
            $MIOLO->ajax->setResponse(null, GForm::GDIV);
        }
    }

    /**
     * Salva arquivo de texto simples
     */
    public function saveFile()
    {
        $args = (Object) $_REQUEST;
        if ($args->absolute && is_writable($args->absolute))
        {
            $ok = file_put_contents($args->absolute, $args->fileContent);

            if ($ok > 0)
            {
                GForm::information(_M("Arquivo atualizado com sucesso", 'gnuteca3'));
            }
            else
            {
                throw new Exception(_M("Erro ao salvar o arquivo.", 'gnuteca3', $args->absolute));
            }
        }
        else
        {
            throw new Exception(_M("Sem permisssão para salvar o arquivo @1", 'gnuteca3', $args->absolute));
        }
    }

    public static function generateTable($repetitive = null)
    {
        $MIOLO = MIOLO::getInstance();
        $repetitive = $repetitive ? $repetitive : 'generalUploader';
        $MIOLO->ajax->setResponse(GRepetitiveField::getTable($repetitive), 'divgeneralUploader');
    }

    public static function ajaxEventHandler()
    {
        $event = GUtil::getAjaxFunction();
        $events = array('removeUploadedFile', 'removeFile', 'downloadFile', 'saveFile');

        if (in_array($event, $events))
        {
            GFileUploader::$event();
        }
    }

}

?>
