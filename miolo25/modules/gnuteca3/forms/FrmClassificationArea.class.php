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
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 22/10/2008
 *
 **/
class FrmClassificationArea extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('ClassificationArea', null, 'classificationAreaId', 'areaName');
        parent::__construct();
    }


    public function mainFields()
    {
        if ($this->function == 'update')
        {
            $fields[] = new MTextField('classificationAreaId', null, _M('Código', $this->module),FIELD_ID_SIZE, null, null, true);
            $valids[] = new MRequiredValidator('classificationAreaId');
        }
        
        $fields[]   = new MTextField('areaName', null, _M('Nome da área', $this->module),FIELD_DESCRIPTION_SIZE);
        $valids[]   = new MRequiredValidator('areaName');
        
        $label              = new MLabel(_M('Classificação', $this->module) . ':');
        $label->setWidth(FIELD_LABEL_SIZE);
        $classification     = new MMultilineField('classification', null, null, FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $lblHelp            = new MDiv('divIgnoreClassificationHint',new MLabel(_M('Utilizar vírgula para separar os valores.', $this->module) ));
        $fields[]           = new GContainer('hctClassification', array($label, $classification, $lblHelp));
        $valids[]           = new MRequiredValidator('classification');

        $labelIgnore        = new MLabel(_M('Ignorar classificação', $this->module) . ':');
        $labelIgnore->setWidth(FIELD_LABEL_SIZE);
        $ignClassification  = new MMultilineField('ignoreClassification', null, null, FIELD_DESCRIPTION_SIZE, FIELD_MULTILINE_ROWS_SIZE, FIELD_MULTILINE_COLS_SIZE);
        $lblHelpIgnore      = new MDiv('divIgnoreClassificationHint',new MLabel(_M('Utilizar vírgula para separar os valores.', $this->module) ));
        $fields[]           = new GContainer('hctClassification', array($labelIgnore, $ignClassification, $lblHelpIgnore));

        $this->setFields($fields);
        $this->setValidators($valids);
    }
}
?>
