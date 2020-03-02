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
 * Preference search form
 *
 * @author Sandro Roberto Weisheimer [sandrow@solis.coop.br]
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
 * Class created on 13/03/2009
 *
 **/
class FrmPrefixSuffixSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('PrefixSuffix', array('prefixSuffixIdS','fieldIdS', 'subFieldIdS', 'contentS', 'typeS'), array('prefixSuffixId'));
        parent::__construct();
    }


    public function mainFields()
    {
        $fields[]    = new MTextField('prefixSuffixIdS', $this->prefixSuffixIdS->value, _M('Código', $this->module), 3);
        $fields[]    = new MTextField('fieldIdS', $this->fieldIdS->value, _M('Campo', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]    = new MTextField('subFieldIdS', $this->subFieldIdS->value, _M('Subcampo',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]    = new MTextField('contentS', $this->contentS->value, _M('Conteúdo',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[]    = new GSelection('typeS', $this->typeS->value, _M('Tipo', $this->module), $this->business->listTypes(), null, null, null, FALSE);

        $this->setFields($fields);
    }
}
?>