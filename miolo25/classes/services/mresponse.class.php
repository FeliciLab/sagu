<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MResponse extends MService
{
    private
        $mimeType = array(
           'ai'   => 'application/postscript',       'aif' => 'audio/x-aiff',
           'aifc' => 'audio/x-aiff',                 'aiff' => 'audio/x-aiff',
           'asf' => 'video/x-ms-asf',                'asr' => 'video/x-ms-asf',
           'asx' => 'video/x-ms-asf',                'au' => 'audio/basic',
           'avi' => 'video/x-msvideo',               'bin' => 'application/octet-stream',
           'bmp' => 'image/bmp',                     'css' => 'text/css',
           'doc' => 'application/msword',            'gif' => 'image/gif',
           'gz' => 'application/x-gzip',             'hlp' => ' application/winhlp',
           'htm' => 'text/html',                     'html' => 'text/html',
           'ico' => 'image/x-icon',                  'jpe' => 'image/jpeg',
           'jpeg' => 'image/jpeg',                   'jpg' => 'image/jpeg',
           'js' => 'application/x-javascript',       'lzh' => 'application/octet-stream',
           'mid' => 'audio/mid',                     'mov' => 'video/quicktime',
           'mp3' => 'audio/mpeg',                    'mpa' => 'video/mpeg',
           'mpe' => 'video/mpeg',                    'mpeg' => 'video/mpeg',
           'mpg' => 'video/mpeg',                    'pdf' => 'application/pdf',
           'png' => 'image/png',                     'pps' => 'application/vnd.ms-powerpoint',
           'ppt' => 'application/vnd.ms-powerpoint', 'ps' => 'application/postscript',
           'qt' => 'video/quicktime',                'ra' => 'audio/x-pn-realaudio',
           'ram' => 'audio/x-pn-realaudio',          'rtf' => 'application/rtf',
           'snd' => 'audio/basic',                   'tgz' => 'application/x-compressed',
           'tif' => 'image/tiff',                    'tiff' => 'image/tiff',
           'txt' => 'text/plain',                    'wav' => 'audio/x-wav',
           'xbm' => 'image/x-xbitmap',               'xpm' => 'image/x-xpixmap',
           'z' => 'application/x-compress',          'zip' => 'application/zip'
     );

    private $contentType;
    private $contentLength;
    private $contentDisposition;
    private $contentTransferEncoding;
    private $fileName;
    private $fileNameDown;
    private $baseName;

    public function __construct()
    {
        $this->contentType = "application/save";
        $this->contentLength = "";
        $this->contentDisposition = "";
        $this->contentTransferEncoding = "";
        $this->fileName = "";
        $this->fileNameDown = "";
    }

    public function __down()
    {
        $this->contentType = "application/save";
        $this->contentLength = "";
        $this->contentDisposition = "";
        $this->contentTransferEncoding = "";
        $this->fileName = "";
        $this->fileNameDown = "";
    }

    public function setContentType($value)
    {
        $this->contentType = $value;
    }

    public function _setContentLength()
    {
        $this->contentLength = filesize($this->fileName);
    }

    public function setContentLength($value)
    {
        $this->contentLength = $value;
    }

	function setContentDisposition($value)
    {
        $this->contentDisposition = $value;
    }

    public function setContentTransferEncoding($value)
    {
        $this->contentTransferEncoding = $value;
    }

    public function setFileName($value)
    {
        /**
         * Prevents unwanted access ../* 
         */
        if (strpos($value, "..") === false)
        {
            $this->fileName = $value;
            $this->baseName = basename($this->fileName);
            $extension = strstr($this->baseName, '.');

            if ($extension != '')
            {
                $extension = substr($extension, 1, 5);
                $this->setContentType($this->mimeType[$extension]);
            }
        }
        else
        {
            die ("MResponse: Trying to access an URL with '..'");
        }
    }

    public function setFileNameDown($value)
    {
        $this->setFileName($value);
        $this->fileNameDown = $value;
    }

    public function send()
    {
        $fileName = $this->fileName;
        $this->_setContentLength();

        /*
        echo $fileName;
        echo $this->baseName;
        echo $this->contentType;
        echo $this->contentLength;
        */

        header ("Content-Type: " . $this->contentType);
        header ("Content-Length: " . $this->contentLength);

        if ($this->fileNameDown == "")
            header ("Content-Disposition: inline; filename= " . $this->baseName);
        else
            header ("Content-Disposition: attachment; filename= " . $this->baseName);

        header ("Cache-Control: cache"); // HTTP/1.1 
        header ("Content-Transfer-Encoding: binary");

        $fp = fopen($fileName, "r");
        fpassthru ($fp);
        fclose ($fp);

        exit();
    }

    public function sendDownload($fileName)
    {
        if (!empty($fileName) && file_exists($fileName))
        {
            $this->setFileNameDown($fileName);
            $this->send();
        }
    }

    public function sendFile($fileName)
    {
        if (!empty($fileName) && file_exists($fileName))
        {
            $this->setFileName($fileName);
            $this->send();
        }
    }

    public function sendRedirect($url)
    {
	    header('Location:'.$url);
    }

    public function sendPage($page)
    {
        if ($page->redirect)
        {
            $this->sendRedirect($page->goTo);
        } 
        else
        {
            echo $page->generate();
        }
    }

    public function sendText($text, $type)
    {
        $length = strlen($text);
        header ("Content-Type: " . $type);
        header ("Content-Length: " . $length);
        header ("Content-Disposition: inline; filename=" . $this->baseName);
        header ("Content-Transfer-Encoding: binary");
        echo $text;
        exit();
    }

    public function sendBinary($binary)
    {
        header ("Content-Type: " . $this->contentType);
        header ("Content-Length: " . $this->contentLength);
        header ("Content-Disposition: inline; filename= " . $this->baseName);
        header ("Cache-Control: cache"); // HTTP/1.1 
        header ("Content-Transfer-Encoding: binary");
        echo $binary;
        exit();
    }


}
?>