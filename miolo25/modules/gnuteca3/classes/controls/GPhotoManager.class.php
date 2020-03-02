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
 * Componente de upload e de tirar foto
 * 
 * @author Luís Augusto Weber Mercado [luis_augusto@solis.com.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Luis Augusto Weber Mercado [luis_augusto@solis.coop.br]
 *
 * @since
 * Class created on 11/04/2014
 *
 * */
$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('webcamCapture.js', 'gnuteca3');

GPhotoManager::ajaxEventHandler();

class GPhotoManager extends MContainer
{

    public $MIOLO;
    public $module;
    public $id;
    public $tab;
    private static $defaultPrefix = 'photo_';
    private static $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');

    /**
     * Construtor do componente.
     * 
     * @param $id Identificador do componente.
     */
    public function __construct($id, $destFolder = null)
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->id = $id;

        // Escreve o folder a ser gravado a foto na sessão.
        $_SESSION['GPhotoManager'][$this->id] = $destFolder;

        $uploadMenu = array();
        $takePhotoFields = array();
        $photoManagerFields = array();

        // Fields da aba de Upload.
        $uploadMenu[] = $fileField = new MFileField('uploadedFile', null, _M('Selecione o arquivo'), null, _M('O arquivo deve ter no máximo ' . ini_get('upload_max_filesize') . ' de tamanho.'));

        $fileField->addAttribute('onchange', MUtil::getAjaxAction('uploadConfirmation'));

        // Fields da aba de Webcam.
        $takePhotoFields[] = $divTakePhoto = new MDiv('divTakePhoto');

        // Estilo da div que exibe a stream de vídeo.
        $divTakePhoto->addStyle("width", "360px");
        $divTakePhoto->addStyle("height", "480px");
        $divTakePhoto->addStyle("overflow", "hidden");
        $divTakePhoto->addStyle("background-image", 'url(' . GUtil::getImageTheme('aviso_webcam.png') . ')');

        $takePhotoFields[] = $btnTakePhoto = new MDiv('', new MButton('btnTakePhoto', _M('Tirar foto', $this->module), MUtil::getAjaxAction('takePhoto'), GUtil::getImageTheme('photo-16x16.png')));
        $btnTakePhoto->addStyle('margin-left','100px');
        
        $takePhotoMenu[] = $divTakePhotoMenu = new MDiv('divTakePhotoMenu', $takePhotoFields);
        $divTakePhotoMenu->addStyle("margin-left", "auto");
        $divTakePhotoMenu->addStyle("margin-right", "auto");
        $divTakePhotoMenu->addStyle("width", "360px");
        
        // Fields da aba de gerenciamento de foto.
        $photoManagerFields[] = $labelCurrentPhoto = new MTextLabel('labelCurrentPhoto',_M('Foto atual'));
        $photoManagerFields[] = $labelTmpPhoto = new MTextLabel('labelTmpPhoto',_M('Foto recém tirada/carregada'));
        
        $labelCurrentPhoto->addStyle('margin-left', '60px');
        $labelTmpPhoto->addStyle('margin-left', '55px');
        
        $photoManagerFields[] = $divShowPhoto = new MDiv('divShowPhoto');
        $divShowPhoto->addStyle('border', '2px solid #fff');
        $divShowPhoto->addStyle('width' , '180px');
        $divShowPhoto->addStyle('height' , '240px');
        $divShowPhoto->addStyle('overflow', 'hidden');
        
        $photoManagerFields[] = $divShowTmpPhoto = new MDiv('divShowTmpPhoto');
        $divShowTmpPhoto->addStyle('border', '2px solid #fff');
        $divShowTmpPhoto->addStyle('width' , '180px');
        $divShowTmpPhoto->addStyle('height' , '240px');
        $divShowTmpPhoto->addStyle('margin-left', '190px');
        $divShowTmpPhoto->addStyle('margin-top', '-243.7px'); // Vai entender...
        $divShowTmpPhoto->addStyle('overflow', 'hidden');
        
        $photoManagerFields[] = $btnRemovePhoto = new MDiv('', new MButton('btnRemovePhoto', _M('Remover foto', $this->module), ':removePhoto', GUtil::getImageTheme('delete-16x16.png')));
        $photoManagerFields[] = $btnRemoveTmpPhoto = new MDiv('', new MButton('btnRemoveTmpPhoto', _M('Descartar foto', $this->module), ':removeTmpPhoto', GUtil::getImageTheme('delete-16x16.png')));
        
        $btnRemovePhoto->addStyle('margin-left','5px');
        $btnRemoveTmpPhoto->addStyle('margin','-40px 0px 0px 195px');
        
        $photoManagerFields[] = $divMessage = new MDiv('', '<p style="position: absolute; padding: 20px; border: 2px solid grey; width: 300px; font-weight: bold; font-size: 12px; margin-left: 500px; top: 100px; background-color: #f4f4f4; text-align: justify;"> ' . _M('Após realizar as demais operações de cadastro, não se esqueça de clicar no botão de salvar registro!') .' </p>');
        
        $photoManagerMenu[] = $divPhotoManager = new MDiv('divPhotoManager', $photoManagerFields);
               
        $divPhotoManager->addStyle('margin-left', '30px');
        
        $tabGroup = new MTabbedBaseGroup('photoOperations');
        $tabGroup->createTab('tabPhotoManager', _M('Gerenciar fotos', $this->module), $photoManagerMenu, 'loadTmpPhoto', FALSE, TRUE);
        $tabGroup->createTab('tabUpload', _M('Upload', $this->module), $uploadMenu, 'shutdownWebcam');
        $tabGroup->createTab('tabWebcam', _M('Webcam', $this->module), $takePhotoMenu, 'attachWebcam');
        
        $this->tab = $tabGroup;

        parent::__construct($id);
    }

    /**
     * Gera o componente.
     */
    public function generate()
    {
        $fields = array();
        
        $fields[] = $this->tab;
        
        $mainContainer = new MVContainer('photoManagerContainer', $fields);
             
        return $mainContainer->generate();
    }

    /**
     * Handler ajax do componente.
     */
    public static function ajaxEventHandler()
    {
        $event = GUtil::getAjaxFunction();
        $events = array('attachWebcam', 'shutdownWebcam', 'takePhoto', 'receivePhotoInfo', 'uploadConfirmation', 'uploadPhoto', 'loadTmpPhoto', 'removePhoto', 'removeTmpPhoto');

        if (in_array($event, $events)) {
            GPhotoManager::$event(GUtil::getAjaxEventArgs());
        }
        
    }

    /* MÉTODOS REFERENTES AO RECURSO DE TIRAR FOTOS PELA WEBCAM. */

    /**
     * Método AJAX:
     * Atribui a stream de video da webcam ao elemento.
     */
    public static function attachWebcam()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("
            WebcamCapture.attach('divTakePhoto');

        ");

        $MIOLO->ajax->setResponse(null, 'limbo');
    }

    /**
     * Método AJAX:
     * Desliga a webcam. Utilizar quando se quer desativar a mesma.
     */
    public static function shutdownWebcam()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("
            WebcamCapture.shutdown();

        ");

        $MIOLO->ajax->setResponse(null, 'limbo');
    }

    /**
     * Método AJAX:
     * Pega o frame atual da stream de video e retorna a imagem no formato base64 para o método receivePhotoInfo().
     */
    public static function takePhoto()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->page->onload("
            if(WebcamCapture.live)
            {
                var imgData = WebcamCapture.snap();

                var auxString = imgData.substring(23);
                
                miolo.doAjax('receivePhotoInfo', auxString, '__mainForm');

            }

        ");

        $MIOLO->ajax->setResponse(null, 'limbo');
    }

    /**
     * Método AJAX:
     * Salva na pasta temporária do gnuteca a imagem.
     * 
     * @params $base64: sequencia de bytes da imagem tirada.
     */
    public static function receivePhotoInfo($base64)
    {
        $MIOLO = MIOLO::getInstance();
        $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');

        // Id do operador.
        $loginId = $MIOLO->getLogin()->id;

        // Decode no base64 da imagem.
        $imgData = base64_decode($base64);

        $fileName = GPhotoManager::$defaultPrefix . $loginId . '.jpg';
        $filePath = $busFile->getAbsoluteServerPath(true) . '/tmp/';

        $targetPath = $filePath . $fileName;

        // Limpa a pasta temporária no caso de já haver alguma que não foi salva.
        GPhotoManager::deletePhoto(GPhotoManager::$defaultPrefix . $loginId, 'tmp');

        file_put_contents($targetPath, $imgData);

        GPhotoManager::setData('jpg', $fileName);

        $MIOLO->ajax->setResponse(null, 'limbo');
    }

    /* MÉTODOS REFERENTES AO UPLOAD DE FOTO. */

    /**
     * Método AJAX:
     * Confirma a escolha de foto do usuário.
     */
    public static function uploadConfirmation()
    {
        $msg = _M("Desejas utilizar o arquivo selecionado como sua foto?");

        // Ação + fechar o popup.
        $goToYes = "javascript:gnuteca.closeAction();" . " miolo.doPostBack('uploadPhoto', '', '__mainForm'); return false;";

        GPrompt::question($msg, $goToYes);
    }

    /**
     * Método AJAX:
     * Faz o upload da foto.
     */
    public static function uploadPhoto()
    {
        $MIOLO = MIOLO::getInstance();
        $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');

        // Login do operador.
        $loginId = $MIOLO->getLogin()->id;
        
        // Informações enviadas via Post.
        $uploadInfo = utf8_decode(MIOLO::_REQUEST('uploadInfo')); // Forçar o nome do arquivo com acento a ter caracteres válidos.
        
        // Informações do arquivo.
        $tmp_fileInfo = explode(';', $uploadInfo);
        
        $ext = explode('.', $tmp_fileInfo[0]);
        
        // Extensão do arquivo.
        $extension = $ext[sizeof($ext) - 1];

        $fileName = GPhotoManager::$defaultPrefix . $loginId;
        $filePath = $busFile->getAbsoluteServerPath(true) . '/tmp/';

        $fileBaseName = $fileName . '.' . $extension;
        
        $targetPath = $filePath . $fileBaseName;
        
        $tmpFile = $MIOLO->getConf('home.html') . "/files/tmp/" . $tmp_fileInfo[1]; // Nome temporário do arquivo.
                
        if (!in_array($extension, GPhotoManager::$allowedExtensions))
        {
            $allowedExt = implode(', ', GPhotoManager::$allowedExtensions);

            $msg = _M("Exstensão do arquivo não permitida. Tente novamente com outro arquivo que contenha uma das seguintes extensões: @1", 'gnuteca3', $allowedExt);

            unlink($tmpFile);

            throw new Exception($msg);
        }
        else
        {
            // Limpa a pasta temporária no caso de já haver alguma que não foi salva.
            GPhotoManager::deletePhoto($fileName, 'tmp');

            if (copy($tmpFile, $targetPath))
            {
                unlink($tmpFile);

                GPhotoManager::setData($extension, $fileBaseName);

                $msg = _M("Foto carregada com sucesso!");
                
                GPrompt::information($msg);
            }
            
        }
        
        $MIOLO->page->onload("
            document.getElementById('uploadedFile').value = '';

        ");

        $MIOLO->ajax->setResponse(null, 'limbo');
    }
    
    /* MÉTODOS REFERENTES A ABA DE GERENCIAMENTO DE FOTO */
    
    /**
     * Atribui a foto da pessoa a div principal.
     * 
     * @param $photoId Nome do arquivo
     * @param $folder Pasta onde irá procurar
     */
    public static function loadPhoto($photoId, $folder = null)
    {
        $result = null;
        
        if($photoId)
        {
            $result = GPhotoManager::searchForPhoto($photoId, $folder);
            
        }
                
        MUtil::clog($result);
        
        GPhotoManager::addImageToDiv($result[0], _M('Não há nenhuma foto cadastrada.'), 'divShowPhoto');
        
    }
    
    /**
     * MÉTODO AJAX:
     * 
     * Atribui a foto da pessoa a div temporária.
     * 
     */
    public static function loadTmpPhoto()
    {
        $MIOLO = MIOLO::getInstance();
            
        GPhotoManager::shutdownWebcam();
        
        $fileName = GPhotoManager::$defaultPrefix . $MIOLO->getLogin()->id;
        $folder = 'tmp';
                        
        $result = GPhotoManager::searchForPhoto($fileName, $folder);
        
        GPhotoManager::addImageToDiv($result[0], _M('Nenhuma foto foi tirada/carregada.'), 'divShowTmpPhoto');
        
        $MIOLO->ajax->setResponse(null, 'limbo');
        
    }
    
    /**
     * Atribui a foto da pessoa a div indicada.
     * 
     * @param $file GFile contendo as informações do arquivo.
     * @param $msg Mensagem a ser mostrada em caso de falha
     * @param $divName Nome da div a ser atribuida a imagem
     */
    private static function addImageToDiv($file, $msg, $divName)
    {
        $MIOLO = MIOLO::getInstance();
                
        clearstatcache(true, $file->filename . '.*');
        
        $inner = "";
        
        // Mensagem caso não haja foto.
        if(!$file)
        {
            $inner = '<p style="position: relative; margin-top: 50%; text-align: center; font-weight: bold; color: #f00;">' . $msg . '</p>';
            
        }
        else
        {
            $att = '&height=240'; // Tamanho padrão.
            
            if(substr_compare($file->dirname, '/html/files', strlen($file->dirname) - 11, 11, true) !== 0)
            {
                $urlComplement = 'folder='. $file->dirname . '&';
                
            }
            
            $url = 'file.php?'. $urlComplement . 'file=' . $file->basename . $att;

            $inner = '<img id="img' . $divName . '\" src=\"' . $url . '?' . rand() . '\" />';
                        
        }
        
        $MIOLO->page->onload("
            var element = document.getElementById('{$divName}');
            
            element.innerHTML = '{$inner}';
            
            var img = document.getElementById('img{$divName}');
            
            setTimeout(function()
            {
                if(img !== null)
                {
                    if(img.clientWidth > 180)
                    {
                        var margin = Math.floor(((img.clientWidth -180)/ 2) * (-1));

                        img.style.marginLeft = margin + 'px';

                    }
                }
            }, 2000);
                        
        ");
                
    }
    
    /**
     * Método AJAX:
     * Remove a foto cadastrada da pessoa.
     */
    private static function removePhoto()
    {
        $MIOLO = MIOLO::getInstance();
        
        $fileName = MIOLO::_REQUEST('personId');
                
        GPhotoManager::deletePhoto($fileName, 'person', true);
        
        GPhotoManager::loadPhoto();
        
        $MIOLO->ajax->setResponse(null, 'limbo');
        
    }
    
    /**
     * Método AJAX:
     * Remove a foto temporária da pessoa.
     */
    private static function removeTmpPhoto()
    {
        $MIOLO = MIOLO::getInstance();
        
        $fileName = GPhotoManager::$defaultPrefix . $MIOLO->getLogin()->id;
        $folder = 'tmp';
        
        GPhotoManager::deletePhoto($fileName, $folder);
        
        GPhotoManager::clearData();
        
        GPhotoManager::loadTmpPhoto();
        
        $MIOLO->ajax->setResponse(null, 'limbo');
        
    }

    /* MÉTODOS GERAIS */

    /**
     * Método que move a imagem da pasta temporária para a informada no construtor com o nome informado.
     * 
     * @param $id Identificador do componente
     * @param $fileName Nome da imagem que será salva
     */
    public static function savePhoto($id, $fileName)
    {
        $MIOLO = MIOLO::getInstance();
        $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');

        $info = GPhotoManager::getData($id);
        
        $serverPath = $busFile->getAbsoluteServerPath(true);
            
        // Se não foi informado um diretório, 
        if (!is_null($info->folder))
        {
            $destPath = $serverPath . '/' . $info->folder . '/' . $fileName . '.' . $info->extension;

        }
        else
        {
            $destPath = $serverPath . '/' . $fileName . '.' . $info->extension;
            
        }
        
        $srcPath = $serverPath . '/tmp/' . $info->tmpFile; 
            
        // Exclui qualquer arquivo que já esteja salvo com o id da pessoa.
        // Só exclui se tiver algum arquivo para substituir.
        if( file_exists($srcPath) && strlen($info->tmpFile) > 0 )
        {
            GPhotoManager::deletePhoto($fileName, $info->folder);
        }
        
        if (copy($srcPath, $destPath))
        {
            unlink($srcPath);
        }
    }

    /**
     * Escreve os dados necessários para salvar na seção.
     * 
     * @param $ext Extensão do arquivo
     * @param $tmpFile Local onde está armazenada a imagem temporária
     */
    public static function setData($extension, $tmpFile)
    {
        $_SESSION['GPhotoManager']['fileExt'] = $extension;
        $_SESSION['GPhotoManager']['tmpFile'] = $tmpFile;
        
        $MIOLO = MIOLO::getInstance();
        
        $MIOLO->page->onload("
            javascript:miolo.doAjax('loadTmpPhoto','','__mainForm');; mtabbedbasegroup.changeTab('tabPhotoManager','photoOperations');

        ");
        
    }

    /**
     * Retorna os dados necessários para mover o arquivo temporário para a pasta de destino.
     * 
     * @return stdClass contendo as informações de destino e do arquivo temporário.
     */
    public static function getData($id) {
        $info = new stdClass();

        $info->extension = $_SESSION['GPhotoManager']['fileExt'];
        $info->tmpFile = $_SESSION['GPhotoManager']['tmpFile'];
        $info->folder = $_SESSION['GPhotoManager'][$id];

        return $info;
        
    }

    /**
     * Limpa os itens da seção.
     */
    public static function clearData() {
        $MIOLO = MIOLO::getInstance();
        
        $session = $MIOLO->getSession();
        $session->setValue("uploadedFile", null);
        $session->setValue("uploadInfo", null);
        $session->setValue("uploadErrors", null);
        $session->setValue("GPhotoManager", null);
        
        // Limpa qualquer arquivo não salvo.
        GPhotoManager::deletePhoto(GPhotoManager::$defaultPrefix . $MIOLO->getLogin()->id, 'tmp');
        
    }
    
    /**
     * Procura por fotos arquivos que não foram salvos.
     * 
     * @param $photoId Nome do arquivo
     * @param $folder Diretório em que se deja procurar
     */
    public static function searchForPhoto($photoId, $folder)
    {
        $MIOLO = MIOLO::getInstance();
        
        $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');
        $busFile->folder = $folder;
        $busFile->fileName = $photoId . '.';

        return $busFile->searchFile(true);
        
    }
    
    /**
     * Exclui possíveis arquivos que não foram salvos.
     * 
     * @param $fileName Nome do arquivo
     * @param $filePath Diretório em que se deja procurar
     */
    private static function deletePhoto($fileName, $folder = null, $onlyfirst = false)
    {
        $result = GPhotoManager::searchForPhoto($fileName, $folder);
        
        if($onlyfirst)
        {
            unlink($result[0]->absolute);
                        
        }
        else
        {
            foreach ($result as $fileInfo)
            {
                // Exclui a foto.
                unlink($fileInfo->absolute);
                
            }
            
        }
                
    }
    
    public function setReadOnly($set)
    {
        $MIOLO = MIOLO::getInstance();
        
        if($set)
        {
            $js = "
                mtabbedbasegroup.disableTab('tabUpload','photoOperations');
                mtabbedbasegroup.disableTab('tabWebcam','photoOperations');
                mtabbedbasegroup.disableTab('tabPhotoManager','photoOperations');
                document.getElementById('photoOperations').disabled = true;

            ";

            $MIOLO->page->onload($js);
            
        }
                
    }
                    
}

?>