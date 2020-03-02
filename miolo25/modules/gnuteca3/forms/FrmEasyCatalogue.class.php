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
 * Easy catalogue
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
  *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Class created on 08/09/2011
 *
 **/

$MIOLO->getClass('gnuteca3', 'gMarc21Record');

class FrmEasyCatalogue extends GForm
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
        $this->setTransaction('gtcPreCatalogue');

        parent::__construct(_M("Catalogação facilitada", $this->module));
    }

    public function mainFields()
    {
        $module = $this->module;
        $fields[] = new MTextField('fieldDelimiter', MARC_FIELD_DELIMITER, _M('Limitador de campo', $module),FIELD_DESCRIPTION_SIZE, _M('Caractere que separa os campos', $this->module));
        $fields[] = new MTextField('subFieldDelimiter', MARC_SUBFIELD_DELIMITER, _M('Limitador de subcampo', $module),FIELD_DESCRIPTION_SIZE, _M('Caractere que separa os subcampos. Valores comuns "$", "^" e "|"'));
        $fields[] = new MTextField('emptyIndicator', MARC_EMPTY_INDICATOR, _M('Indicador vazio', $module),FIELD_DESCRIPTION_SIZE, _M('Caractere que indica quando o indicador está vazio', $this->module));
        $fields[] = new MMultiLineField('content','',_M('Conteúdo',$this->module), null, 20, 90);

        $this->setFields($fields);

        $validators[] = new MRequiredValidator('content');
        $this->setValidators($validators);
        
        $this->toolBar->enableButtons('tbBtnSave');
        $this->toolBar->disableButton( array('tbBtnReset', 'btnFormContent', 'tbBtnSearch'));
    }

    public function tbBtnSave_click($sender=NULL, $confirm = DB_FALSE)
    {
        $this->mainFields(); //necessário para validar formulário
        $data = $this->getData();
        
        //valida formulário
        if ( !$this->validate($data) )
        {
            return false;
        }

        try
        {
            $objectMarcRecord = new gMarc21Record($data->content, $data->fieldDelimiter, $data->subFieldDelimiter, $data->emptyIndicator);

            $list = $objectMarcRecord->getTags(); //obtém as tags

            $controlNumber = $this->business->getNextControlNumber();

            //grava na pré-catalogação
            $this->business->beginTransaction();
            
            foreach ( $list as $line => $materialItem )
            {
                if ( $materialItem->fieldid == '000' )
                {
                    $materialItem->content = str_replace(' ', '#', $materialItem->content);
                }

                $materialItem->controlNumber = $controlNumber; //seta o número de controle
                $this->business->setData($materialItem); //seta os dados
                $ok[] = $this->business->insertMaterial(); //insere na pré-catalogação
            }
            
            if( !in_array(false, $ok) )
            {
                $this->business->commitTransaction();
                $urlYes = $this->MIOLO->getActionURL($this->module, 'main:catalogue:preCatalogue', null, array ('function' => 'update','controlNumber' => $controlNumber));
                $this->question( _M('Registro importado com sucesso com o número de controle "@1" para a pré-catalogação. Deseja editar o registro?', $this->module, $controlNumber), $urlYes);
            }
            else
            {
                $this->error(_M('Erro na importação do material', $this->module), 'javascript:' . GUtil::getCloseAction());
            }
            
        }
        catch ( Exception $e)
        {
            $this->error( $e->getMessage() );
        }
    }
}
?>