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
 * @author Moises Heberle [moises@solis.coop.br]
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
 * Class created on 28/07/2008
 *
 **/
class FrmPreferenceSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('Preference', array('moduleConfigS','parameterS, configValueS'),array('moduleConfig','parameter'));
        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MTextField('moduleConfigS', strtoupper($this->module), _M('Módulo',$this->module), 20,null, null, true);
        $fields[] = new MTextField('parameterS', $this->parameterS->value, _M('Parâmetro',$this->module), 20);
        $fields[] = new MTextField('configValueS', $this->configValueS->value, _M('Conteúdo',$this->module), 20);
        $fields[] = new MTextField('descriptionS', $this->descriptionS->value, _M('Descrição',$this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('typeS', $this->typeS->value, _M('Tipo do campo',$this->module), BusinessGnuteca3BusDomain::listForSelect('PREFERENCE_TYPE') );
        $fields[] = new GSelection('groupByS', $this->groupByS->value, _M('Grupo',$this->module), BusinessGnuteca3BusDomain::listForSelect('ABAS_PREFERENCIA' ));
        $fields[] = new MTextField('labelS', null, _M('Etiqueta',$this->module), FIELD_DESCRIPTION_SIZE);

        $this->setFields($fields);
    }
}
?>