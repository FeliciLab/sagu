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
 *  Teste unitário
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @since
 * Creation date 25/10/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestSearchableField extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('SearchableField');
        
        $data = new stdClass();
        $data->description = $data->descriptionS = 'Desc';
        $data->field = $data->fieldS = '090.a';
        $data->identifier = $data->identifierS = 'cdd';
        $data->observation = $data->observationS = '';
        $data->isRestricted = $data->isRestrictedS = 'f';
        $data->level = $data->levelS = '3';
        $data->fieldType = $data->fieldTypeS = '1';
        $data->helps = $data->helpsS = '';
        $data->linkId = $data->linkIdS = '1';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'search';

        $this->business->setData($data);
    }
}
?>