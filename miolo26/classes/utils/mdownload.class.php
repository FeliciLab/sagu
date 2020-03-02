<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MDownload
{
    /**
     * Attribute Description.
     */
    private
        $mimeType = array('ai' => 'application/postscript',         'aif' => 'audio/x-aiff',
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
                          'z' => 'application/x-compress',          'zip' => 'application/zip');

    /**
     * Attribute Description.
     */
    public $contentType;

    /**
     * Attribute Description.
     */
    public $contentLength;

    /**
     * Attribute Description.
     */
    public $contentDisposition;

    /**
     * Attribute Description.
     */
    public $contentTransferEncoding;

    /**
     * Attribute Description.
     */
    public $fileName;

    /**
     * Attribute Description.
     */
    public $fileNameDown;

    /**
     * Attribute Description.
     */
    public $baseName;

    /**
    * Constructor
    * @access		public	
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function __construct()
    {
        $this->contentType = "application/save";
        $this->contentLength = "";
        $this->contentDisposition = "";
        $this->contentTransferEncoding = "";
        $this->fileName = "";
        $this->fileNameDown = "";
    }

    /**
    * It configures value Header 'ContentType'
    *
    * @access		public
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $strValue (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setContentType($strValue)
    {
        $this->contentType = $strValue;
    }

    /**
    * It configures value Header 'ContentLength' with the size of the informed file
    * @return		void
    * @access		private
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function _setContentLength()
    {
        $this->contentLength = filesize($this->fileName);
    }

    /**
    * It configures value Header 'ContentDisposition' 
    * @access		public
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $strValue (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setContentDisposition($strValue)
    {
        $this->contentDisposition = $strValue;
    }

    /**
    * It configures value Header 'ContentTransferEncoding' 
    * @access		public
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $strValue (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setContentTransferEncoding($strValue)
    {
        $this->contentTransferEncoding = $strValue;
    }

    /**
    * It configures the real name of the archive in the server
    * @access		public
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $strValue (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setFileName($strValue)
    {
        /**
         * Prevents unwanted access ../* 
         */
        if (strpos($strValue, "..") === false)
        {
            $this->fileName = $strValue;
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
            die ("Trying to access an URL with '..'");
        }
    }

    /**
    * It configures the personalized name of the file 
    * (therefore it can be different of the located real name in the server)
    * @access		public
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $strValue (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setFileNameDown($strValue)
    {
        $this->setFileName($strValue);
        $this->fileNameDown = $strValue;
    }

    /**
    * Init Download
    * @access		public
    */
    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
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

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $text (tipo) desc
     * @param $type (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
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
}
?>
