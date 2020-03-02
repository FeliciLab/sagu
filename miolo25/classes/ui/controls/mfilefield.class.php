<?php

/**
 * File upload component
 * Example at module=example&action=main:controls:upload
 *
 * TODO: error handling
 * TODO: cross-browser style http://www.quirksmode.org/dom/inputfile.html
 *
 * @author Daniel Hartmann [daniel@solis.coop.br]
 * @author Ely Edison Matos [ely.matos@ufjf.edu.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2011/02/14
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 */

$MIOLO = MIOLO::getInstance();
$MIOLO->page->addScript('m_filefield.js');

class MFileField extends MTextField
{
    /**
     * Dojo textarea id for upload information.
     */
    const UPLOAD_INFO = 'uploadInfo';

    /**
     * Errors JSON id.
     */
    const UPLOAD_ERRORS = 'uploadErrors';

    /**
     * CSS class for add and remove buttons.
     */
    const MULTIPLE_UPLOAD_BUTTON_STYLE = 'mFileFieldMultipleButton';

    /**
     * @var boolean Define if the component must support the upload of multiple files.
     */
    private $isMultiple = false;

    /**
     * @var object MButton instance to add more files.
     */
    private $addButton;

    /**
     * @var object MButton instance to remove files.
     */
    private $removeButton;

    /**
     * MFileField constructor.
     *
     * @param string $name Field id and name.
     * @param string $value Default value.
     * @param string $label Field label.
     * @param integer $size Field size.
     * @param string $hint Hint.
     */
    public function __construct($name, $value='', $label='', $size=40, $hint='')
    {
        parent::__construct($name, $value, $label, $size, $hint);
        $this->type = 'file';
        $this->setClass('mFileField');

        $this->page->addDojoRequire("dojo.parser");
        $this->page->addDojoRequire("dojo.io.iframe");
        $this->page->onLoad("miolo.page.fileUpload = 'yes';");
        $this->page->setEnctype('multipart/form-data');

        if ( MUtil::isAjaxEvent() )
        {
            $this->page->onload("miolo.getForm('{$this->page->getFormId()}').setEnctype('multipart/form-data');");
        }
    }

    /**
     * @param boolean $isMultiple Set if the component must support the upload of multiple files.
     */
    public function setIsMultiple($isMultiple)
    {
        $this->isMultiple = $isMultiple;

        if ( $isMultiple )
        {
            if ( !is_object($this->addButton) )
            {
                $this->addButton = new MButton("{$this->name}_addButton", _M('Add another file'), "mfilefield.addFile('$this->name');");
            }

            if ( !is_object($this->removeButton) )
            {
                $this->removeButton = new MButton("{$this->name}_removeButton", _M('Remove'), "mfilefield.removeFile('$this->name');");
                $this->removeButton->addStyle('display', 'none');
            }

            $this->addButton->setClass(self::MULTIPLE_UPLOAD_BUTTON_STYLE);
            $this->removeButton->setClass(self::MULTIPLE_UPLOAD_BUTTON_STYLE);

            $this->id = "$this->id[]";
            $this->name = "$this->name[]";
        }
        else
        {
            $this->addButton = NULL;
            $this->removeButton = NULL;
            $this->id = str_replace('[]', '', $this->id);
            $this->name = str_replace('[]', '', $this->name);
        }
    }

    /**
     * @param object $addButton Set the MButton instance which will add more files.
     */
    public function setAddButton($addButton)
    {
        $this->addButton = $addButton;
    }

    /**
     * @return object Get the MButton instance which adds more files.
     */
    public function getAddButton()
    {
        return $this->addButton;
    }

    /**
     * @param object $removeButton Set the MButton instance which will remove files.
     */
    public function setRemoveButton($removeButton)
    {
        $this->removeButton = $removeButton;
    }

    /**
     * @return object Get the MButton instance which removes files.
     */
    public function getRemoveButton()
    {
        return $this->removeButton;
    }

    /**
     * Generate inner content.
     */
    public function generateInner()
    {
        parent::generateInner();
        $hidden = new MHiddenField('__ISFILEUPLOADPOST', 'yes');
        $this->inner .= $hidden->generate();
    }

    /**
     * @return string The whole component in string (HTML) format.
     */
    public function generate()
    {
        $generated = parent::generate();

        if ( $this->isMultiple )
        {
            $this->removeButton->addAttribute('data-enhance', 'false');
            $generated .= $this->addButton->generate();
            $generated .= $this->removeButton->generate();
        }

        return $generated;
    }

    /**
     * @return array List with the names of the uploaded files
     */
    public static function getUploadInfo()
    {
        $uploadInfo = MIOLO::_REQUEST(self::UPLOAD_INFO);
        if ( $uploadInfo )
        {
            $uploadInfo = explode(',', $uploadInfo);
        }

        return $uploadInfo;
    }

    /**
     * @return array List with the upload errors.
     */
    public static function getUploadErrors()
    {
        $errors = array();
        $errorsString = MIOLO::_REQUEST(self::UPLOAD_ERRORS);

        if ( $errorsString )
        {
            $errorLines = explode(',', $errorsString);

            foreach ( $errorLines as $error )
            {
                list($fileName, $errorCode) = explode(';', $error);

                $errors[$fileName] = (int) $errorCode;
            }
        }

        return $errors;
    }

    /**
     * Move the uploaded files to the specified path
     *
     * @param string $path Path where the files must be stored
     * @return array Array with name and path of the uploaded files
     */
    public static function uploadFiles($path)
    {
        $MIOLO = MIOLO::getInstance();
        $uploadInfo = self::getUploadInfo();

        if ( !is_array($uploadInfo) )
        {
            return;
        }

        $uploadedFiles = array();
        
        foreach ( $uploadInfo as $fileInfo )
        {
            list($fileName, $tmpFile) = explode(';', $fileInfo);

            $tmpFile = $MIOLO->getConf('home.html') . "/files/tmp/$tmpFile";

            // If a file with the same name exists, change its name
            if ( file_exists("$path/$fileName") )
            {
                $fData = pathinfo($fileName);

                $fileName = $fData['filename'] . '-1';

                if ( $fData['extension'] )
                {
                    $fileName .= ".{$fData['extension']}";
                }

                if ( file_exists("$path/$fileName") )
                {
                    // Get all the file names based on this file name. E.g. filename-1.ext, filename-2.ext, ...
                    if ( $fData['extension'] )
                    {
                        $files = glob("$path/{$fData['filename']}-*.{$fData['extension']}");
                    }
                    else
                    {
                        $files = glob("$path/{$fData['filename']}-*");
                    }

                    natsort($files);
                    $lastFile = pathinfo(end($files));

                    $counter = end(explode('-', $lastFile['filename']));

                    $i = ((int) $counter) + 1;

                    $fileName = $fData['filename'] . "-$i";

                    if ( $fData['extension'] )
                    {
                        $fileName .= ".{$fData['extension']}";
                    }
                }
            }

            if ( copy($tmpFile, "$path/$fileName") )
            {
                unlink($tmpFile);
                $uploadedFiles[] = array(
                    'name' => $fileName,
                    'path' => "$path/$fileName"
                );
            }
        }

        return $uploadedFiles;
    }
}
?>