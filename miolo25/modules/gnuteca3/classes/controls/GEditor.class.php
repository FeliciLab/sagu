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
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *         Eduardo Bonfadini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Eduardo Bonfadini [eduardo@solis.coop.br]
 *
 * @since
 * Class created on 05/10/2011
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
class GEditor extends MEditor
{
    public function __construct($name=null, $value='', $label='', $buttons=null) 
    {
        parent::__construct($name, $value, $label, $buttons);
        $this->disableElementsPath();
        $this->addCustomButton(_M('Adicionar imagem', $this->module), GUtil::getAjax('selectImagetoEditor', $this->id), GUtil::getImageTheme('addImage-16x16.png') );
        $this->setConfigValue('width', '625px');
        
        //necessário para fazer o connect
        
        $this->page->onload("meditor.connection['{$name}'] = dojo.connect(miolo.webForm, 'onSubmit', function () 
                                                         {
                                                             CKEDITOR.instances['{$name}'].updateElement(); return true;
                                                         });");
    }
    /**
     * Seleciona uma image para adiciona-la ao editor.
     *
     * @param stdClass $args
     */
    public function selectImagetoEditor($id)
    {
        $MIOLO = MIOLO::getInstance();
        
        $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile = new BusinessGnuteca3BusFile();
        $folders = $busFile->listFolder(true);
        $folders = array_merge($folders, array(array('theme', _M('Tema', 'gnuteca3'))));
        $selected = 'images';
        $folder = new GSelection('folder',$selected, _M('Pasta','gnuteca3'), $folders, null, null,null, true);
        $folder->addAttribute('onchange',Gutil::getAjax('changeFolder'));

        $btnAdd = new MDiv('divAddImage', new MImageLink('addImage', '', 'javascript:'.Gutil::getAjax('addImage') , GUtil::getImageTheme('add-16x16.png') ));
        $container = new GContainer('', array( $folder, $btnAdd) );
        
        $fields[] = $container;
        $fields[] = new MSeparator('</br>');
        $fields[] = new MDiv('images', self::listImageForFolder( $selected ) );
        $content = GUtil::alinhaForm($fields);

        GForm::injectContent( $content, true, _M('Selecionar imagem', 'gnuteca3') );
    }

    public function addImage()
    {
        $MIOLO = MIOLO::getInstance();
        
        if ( MIOLO::_REQUEST('folder') == 'theme' )
        {
            throw new Exception(_M('Não é possível adicionar nova imagem no tema', 'gnuteca3'));
        }
        
        $uploader = new GFileUploader('Envie uma imagem ao servidor');
        GFileUploader::clearData();
        GFileUploader::setExtensions(array('png','jpg','gif'), $deny);

        $fields[] = $uploader;
        $fields[] = new MButton('btnUploadImage',_M('Salvar', 'gnuteca3'),':uploadImage',Gutil::getImageTheme('save.png'));

        $MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
        $MIOLO->ajax->setResponse( $fields,'images');
    }

    public function uploadImage()
    {
        $MIOLO = MIOLO::getInstance();
        
        $folder = MIOLO::_REQUEST('folder');
        $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile = new BusinessGnuteca3BusFile();
        $busFile->folder = $folder;
        $busFile->files = GFileUploader::getData();
        $busFile->insertFile(); //insere o arquivo
        GFileUploader::clearData(); //limpa o sessão para evitar fazer 2 vezes a mesma coisa

        $images = self::listImageForFolder($folder);

        $MIOLO->ajax->setResponse( $images ,'images');
    }

    /**
     * Atualiza a lista de imagens para a pasta selecionada.
     */
    public function changeFolder()
    {
        $MIOLO = MIOLO::getInstance();
        $folder =  MIOLO::_REQUEST('folder');

        //esconde botão de adicionar imagem quando for pasta de imagens do tema
        if ( $folder == 'theme' )
        {
            $display = 'none';
        }
        else
        {
            $display = 'block';
        }
        
        $this->page->onLoad( "gnuteca.setDisplay( 'divAddImage', false, '{$display}');");
        
        $MIOLO->ajax->setResponse( self::listImageForFolder( $folder ), 'images');
    }

    /**
     * Lista as imagens para uma pasta
     *
     * @param string $folder
     * @return MImageButton array de MImageButton
     */
    public static function listImageForFolder($folder)
    {
        $MIOLO = MIOLO::getInstance();
        $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile = new BusinessGnuteca3BusFile();
        
        //lista imagens do tema quando pasta for "theme"
        if ( $folder == 'theme' )
        {
             $files = $busFile->listThemeImages();
        }
        else
        {
            $files = $busFile->listImages($folder, true);
        }
        
        if ( $files )
        {
            foreach ( $files as $line => $file )
            {
                list($width, $height, $type, $attr) = getimagesize( $file->absolute ) ;
                $link = "javascript:CKEDITOR.instances[dojo.byId('myEditorId').value].insertHtml('<img alt=\'\' src=\'{$file->mioloLink}\' style=\'width: {$width}px; height: {$height}px;\' />'); ";
               
                //atualiza meditor
                $link .= " updateEditor();" .
                GUtil::getCloseAction();
                
                $images[] = $image = new MImageButton( 'name', $file->filename, $link, $file->mioloLink.'&height=60px', null);
                $image->addAttribute('alt', $file->filename);
                $image->addAttribute('title', $file->filename);

                $image->setClass('selectImage');
            }
        }
        else
        {
            $images[] = new MDiv('',_M('Nenhuma imagem nesta pasta.', 'gnuteca3') );
        }
        
        return $images;
    }
    
    public function generateInner()
    {
        parent::generateInner();
        
        $this->page->addJsCode("function updateEditor()
                                {
                                    meditor.connection[dojo.byId('myEditorId').value] = dojo.connect(miolo.webForm, 'onSubmit', function () //função para fazer o connect
                                    { 
                                         CKEDITOR.instances[dojo.byId('myEditorId').value].updateElement(); 
                                    });
                                }");
        
        $text = new MTextField('myEditorId', $this->id);
        $text->addStyle('display', 'none');
        $this->inner .= $text->generate();
    }
}

/*controlador de eventos
 FIXME: foi necessário colocar na parte inferior do arquivo para reconhecer a classe GEditor*/
$possibleEvents = array('selectImagetoEditor','addImage','uploadImage','changeFolder');
$event = GUtil::getAjaxFunction();

if ( in_array( $event, $possibleEvents))
{
    try
    {
        GEditor::$event(GUtil::getAjaxEventArgs());
    }
    catch ( Exception $e )
    {
        GForm::error( $e->getMessage() );
    }
}

?>
