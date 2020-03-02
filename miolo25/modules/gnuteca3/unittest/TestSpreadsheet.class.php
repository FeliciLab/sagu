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
 * Creation date 28/10/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestSpreadsheet extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('Spreadsheet');
        
        $data = new stdClass();
        $data->category = $data->categoryS = 'BK';
        $data->level = $data->levelS = '#';
        $data->field = $data->fieldS = 'Empty.a';
        $data->required = $data->requiredS = '';
        $data->repeatFieldRequired = $data->repeatFieldRequiredS = '';
        $data->defaultValue = $data->defaultValueS = '';
        $data->menuName = $data->menuNameS = '';
        $data->menuOption = $data->menuOptionS = '';
        $data->menuLevel = $data->menuLevelS = '';
        $data->isModified = $data->isModifiedS = '';
        $data->functionMode = $data->functionModeS = 'manage';

        $this->business->setData($data);
    }
}
?>