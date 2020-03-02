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
 * Dictionary form
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
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 03/12/2008
 *
 **/
class FrmDictionarySearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Dictionary', array('dictionaryIdS', 'description'), array('dictionaryId'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MTextField('dictionaryIdS', null, _M('Código', $this->module), FIELD_ID_SIZE);
        $fields[] = $description = new MTextField('description', NULL, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTextField('tags', null, _M('Etiquetas', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GRadioButtonGroup('readOnly', _M('Somente leitura', $this->module), GUtil::listYesNo(1), $isRestrictedValue, null, MFormControl::LAYOUT_HORIZONTAL);

        $this->setFields($fields);
    }

    public function addDictionaryContent($args)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $busDictionary = $MIOLO->getBusiness($module, 'BusDictionary');
        $dictionaryId = MIOLO::_REQUEST('dictionaryId');
        
        $dictionary = $busDictionary->getDictionary($dictionaryId);
        
        if ( MUtil::getBooleanValue($dictionary->readOnly) )
        {
            $this->information(_M("Este dicionário é somente leitura!", $this->module));
        }
        else
        {
            $this->question( _M("Tem certeza que deseja adicionar o conteúdo faltante neste dicionário?", $this->module), GUtil::getAjax('doAddDictionaryContent', $args));
        }
    }

    public function doAddDictionaryContent($args)
    {
        $this->business->addContentMaterials( MIOLO::_REQUEST('dictionaryId') );
        $this->information(_M("Registros inseridos com sucesso!", $this->module), GUtil::getCloseAction(true));
    }
}
?>
