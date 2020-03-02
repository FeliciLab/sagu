<?php
/**
* @description		  Object for Download of files [Object for Download of files]
* @author			  Olavo Alexandrino - oalexandrino@yahoo.com.br
* @since			  May/2004
* @otherInformation	  Other properties can be added header.  It makes!
*/
class EasyDownload
{
    public $contentType;
    public $contentLength;
    public $contentDisposition;
    public $contentTransferEncoding;
    public $path;
    public $fileName;
    public $fileNameDown;

    /**
    * Constructor
    * @access		public	
    */
    public function easyDownload()
    {
        $this->contentType = "application/save";
        $this->contentLength = "";
        $this->contentDisposition = "";
        $this->contentTransferEncoding = "";
        $this->path = "";
        $this->fileName = "";
        $this->fileNameDown = "";
    }

    /**
    * It configures value Header 'ContentType'
    *
    * @access		public
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
    public function _setContentLength()
    {
        $this->contentLength = filesize($this->path . "/" . $this->fileName);
    }

    /**
    * It configures value Header 'ContentDisposition' 
    * @access		public
    */
    public function setContentDisposition($strValue)
    {
        $this->contentDisposition = $strValue;
    }

    /**
    * It configures value Header 'ContentTransferEncoding' 
    * @access		public
    */
    public function setContentTransferEncoding($strValue)
    {
        $this->contentTransferEncoding = $strValue;
    }

    /**
    * It configures the physical place where the file if finds in the server
    * @access		public
    */
    public function setPath($strValue)
    {
        /**
         * Prevents unwanted access ../* 
         */
        if (strpos($strValue, "..") === false)
        {
            $this->path = $strValue;
        }
        else
        {
            die ("Trying to access an URL with '..'");
        }
    }

    /**
    * It configures the real name of the archive in the server
    * @access		public
    */
    public function setFileName($strValue)
    {
        /**
         * Prevents unwanted access ../* 
         */
        if (strpos($strValue, "..") === false)
        {
            $this->fileName = $strValue;
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
    public function setFileNameDown($strValue)
    {
        $this->fileNameDown = $strValue;
    }

    /**
    * Init Download
    * @access		public
    */
    public function send()
    {
        $this->_setContentLength();
        header ("Content-Type: " . $this->contentType);
        header ("Content-Length: " . $this->contentLength);

        if ($this->fileNameDown == "")
            header ("Content-Disposition: attachment; filename=" . $this->fileName);
        else
            header ("Content-Disposition: attachment; filename=" . $this->fileNameDown);

        header ("Content-Transfer-Encoding: binary");
        $fp = fopen($this->path . "/" . $this->fileName, "r");
        fpassthru ($fp);
        fclose ($fp);
        exit();
    }
}
?>
