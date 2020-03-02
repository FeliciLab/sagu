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
 * Class Layout form
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 28/jul/08
 *
 **/
class FrmLabelLayout extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('LabelLayout', null, 'labelLayoutId', array('description'));
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[] = new MTextField("description", null, _M("Descrição", $this->module), FIELD_DESCRIPTION_SIZE);

        $lblTopMargin = new MLabel(_M('Margem superior', $this->module) . ':');
        $lblTopMargin->setWidth(FIELD_LABEL_SIZE);
        $topMargin    = new MTextField('topMargin', $this->topMargin->value, null, FIELD_ID_SIZE);
        $lblAjuda     = new MLabel(_M('Utilize ponto para separar casas decimais', $this->module) );
        $fields[]     = new GContainer('hctTopMargin', array($lblTopMargin, $topMargin, $lblAjuda));

        $fields[] = new MTextField("leftMargin", null, _M("Margem esquerda", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("verticalSpacing", null, _M("Espaco Vertical", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("horizontalSpacing", null, _M("Espaço horizontal", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("height", null, _M("Altura", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("width_", null, _M("Largura", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("lines", null, _M("Linhas", $this->module), FIELD_ID_SIZE);
        $fields[] = new MTextField("columns_", null, _M("Colunas", $this->module), FIELD_ID_SIZE);
        $fields[] = new GSelection("pageFormat", null, _M("Formato da página", $this->module), BusinessGnuteca3BusDomain::listForSelect('PAGE_FORMAT') );

        if ($this->function == 'update')
        {
            $fields[] = new MHiddenField("labelLayoutId", MIOLO::_REQUEST('labelLayoutId'));
        }

        $validators[] = new MRequiredValidator('description');
        $validators[] = new MFloatValidator("topMargin", null, '.', null, 'required');
        $validators[] = new MFloatValidator("leftMargin", null, '.', null, 'required');
        $validators[] = new MFloatValidator("verticalSpacing", null, '.', null, 'required');
        $validators[] = new MFloatValidator("horizontalSpacing", null, '.', null, 'required');
        $validators[] = new MFloatValidator("height", null, '.', null, 'required');
        $validators[] = new MFloatValidator("width_", null, '.', null, 'required');
        $validators[] = new MFloatValidator("lines", null, '.', null, 'required');
        $validators[] = new MFloatValidator("columns_", null, '.', null, 'required');

        $this->setFields($fields);
        $this->setValidators($validators);
    }
}
?>