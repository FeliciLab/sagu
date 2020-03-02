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
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 19/02/2009
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
class FrmMaterialPhysicalType extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('MaterialPhysicalType', 'description', 'materialPhysicalTypeId', array('description'));
        parent::__construct();
        
        if ( $this->primeiroAcessoAoForm() && ! $this->function == 'update' )
        {
            GFileUploader::clearData('odtModel');
        }
    }
    
    public function mainFields()
    {
        if ( $this->function == 'update' )
        {
            $fields[] = new MTextField('materialPhysicalTypeId', null, _M('Código', $this->module), FIELD_ID_SIZE,null, null, true);
            $validators[] = new MRequiredValidator('materialPhysicalTypeId');
        }

        $fields[] = new MTextField('description', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new MMultiLIneField ('observation', NULL, _M('Observação', $this->module), NULL, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $fields[] = new GFileUploader(_M('Imagem',$this->module), false, null, 'image' );
        GFileUploader::setLimit(1, 'image'); //somente uma por material
        GFileUploader::setExtensions(array('png','jpg','jpeg','gif'), array('php', 'class', 'js'), 'image');

        $this->setFields($fields);
        
        $validators[] = new MRequiredValidator('description');
        $this->setValidators($validators);
    }
    
    /**
     * Método reescrito para salvar a imagem
     * 
     */
    public function tbBtnSave_click($sender=NULL)
    {
        $coverData = GFileUploader::getData('image');
        parent::tbBtnSave_click( $sender, $data );
        //grava a imagem
        $this->saveImage( $this->business->materialPhysicalTypeId, $coverData );
    }
    
   /**
    * Método que faz a gravação da imagem
    * @param integer código do material 
    */ 
    public function saveImage( $materialPhysicalTypeId , $coverData )
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile = new BusinessGnuteca3BusFile();
        $folder  = 'materialType';

        if ( $coverData )
        {
            //converte o nome do arquivo para o código da pessoa, foreach caso o id seja diferente de i
            foreach ( $coverData as $line => $info)
            {
                if ( $info->tmp_name )
                {
                    $coverData[$line]->basename = $materialPhysicalTypeId.'.png';
                }
            }

            //caso já exista uma photo estocada, remove-a, só pode existir uma capa por arquivo
            if ( $busFile->fileExists( $folder, $materialPhysicalTypeId, 'png') && is_array($coverData[0]) )
            {
                $filePath = $busFile->getAbsoluteFilePath( $folder, $materialPhysicalTypeId, 'png');
                $busFile->deleteFile($filePath);
            }

            $busFile->folder = $folder;
            $busFile->files = $coverData;
            $busFile->insertFile(); //insere o arquivo
            GFileUploader::clearData('image'); //limpa o sessão para evitar fazer 2 vezes a mesma coisa
        }
    }
    
    /**
     *  Método reescrito para carregar a imagens
     */
    public function loadFields() 
    {
        $busFile = $this->MIOLO->getBusiness('gnuteca3','BusFile');
        $busFile->folder    = 'materialType';
        $busFile->fileName  = MIOLO::_REQUEST('materialPhysicalTypeId') . '.';
        GFileUploader::setData( $busFile->searchFile(true), 'image');
        
        parent::loadFields();
    }
}
?>
