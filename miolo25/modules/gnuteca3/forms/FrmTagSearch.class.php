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
 * Tag search form
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 25/09/2008
 *
 **/
class FrmTagSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Tag', array('fieldIdS','subfieldIdS'),array('fieldId','subfieldId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MTextField('fieldIdS', null, _M('Campo', $this->module), 3);
        $fields[] = new MTextField('subfieldIdS', null, _M('Subcampo', $this->module), 3);
        $fields[] = new MTextField('descriptionS', null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);

        $fields[] = new GSelection('isRepetitiveS', null, _M('É repetitivo',$this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('hasSubfieldS', null, _M('Tem subcampos',$this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('isActiveS', null, _M('Está ativo',$this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('inDemonstrationS', null, _M('Em demonstração',$this->module), GUtil::listYesNo(0));
        $fields[] = new GSelection('isObsoleteS', null, _M('É obsoleto',$this->module), GUtil::listYesNo(0));

        $this->setFields( $fields );
    }

    public function deleteTagConfirm()
    {
        try
        {
            $ok = $this->business->deleteTag( MIOLO::_REQUEST('fieldId'), MIOLO::_REQUEST('subfieldId') );
            if ( $ok )
            {
                $goto = "javascript:gnuteca.closeAction(); " . GUtil::getAjax('searchFunction');
                $this->information(MSG_RECORD_DELETED, $goto);
            }
            else
            {
                $this->error(MSG_RECORD_ERROR);
            }
        }
        catch( EDatabaseException $e )
        {
            $this->error( $e->getMessage() );
        }
    }


    public function deleteTag()
    {
        $data = $this->getData();

        //%23 é o # codificado
        if ( $data->subfieldId == '%23' )
        {
            $this->error( _M('Só é permitido excluir subcampos.', $this->module) );
            return false;
        }
        else
        {
            $gotoYes = 'javascript:'.GUtil::getAjax($function, array( 'event' => 'deleteTagConfirm', 'function'=>'detail', 'fieldId'  => MIOLO::_REQUEST('fieldId'), 'subfieldId'  => MIOLO::_REQUEST('subfieldId') ) );
            $this->question( MSG_CONFIRM_RECORD_DELETE, $gotoYes );
        }
    }
}
?>