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
 * Creation date 17/10/2011
 *
 **/
include_once '../classes/GBusinessUnitTest.class.php';
class TestFineStatus extends GBusinessUnitTest
{
    public function setUp()
    {
        parent::setUp('FineStatus');
        
        $data = new stdClass();
        $data->description = $data->descriptionS = 'Desc';
        $data->fineStatusId = $data->fineStatusIdS = '';
        $data->isModified = $data->isModifiedS = 't';
        $data->functionMode = $data->functionModeS = 'manage';
        $data->gtcRepeatField_949 = $data->gtcRepeatField_949S = array (  0 =>   stdClass::__set_state(array(     'module' => 'gnuteca3',     'action' => 'main:catalogue:material',     'function' => 'new',     'controlNumber' => '',     'marcLeader' => '00000nam##22000004##4500',     'entryDate' => '17/10/2011',     'firstOperator' => 'guilherme',     'lastChange' => '',     'lastOperator' => '',     'wasSaved' => '',     'spreeadsheetField_008_0-BK' => '111017',     'spreeadsheetField_008_6-BK' => 'b',     'spreeadsheetField_008_7-BK' => '',     'spreeadsheetField_008_11-BK' => '',     'spreeadsheetField_008_15-BK' => 'bl',     'spreeadsheetField_008_18-BK' => '#',     'spreeadsheetField_008_19-BK' => '#',     'spreeadsheetField_008_20-BK' => '#',     'spreeadsheetField_008_21-BK' => '#',     'spreeadsheetField_008_22-BK' => '#',     'spreeadsheetField_008_23-BK' => '#',     'spreeadsheetField_008_24-BK' => '#',     'spreeadsheetField_008_25-BK' => '#',     'spreeadsheetField_008_26-BK' => '#',     'spreeadsheetField_008_27-BK' => '#',     'spreeadsheetField_008_28-BK' => '#',     'spreeadsheetField_008_29-BK' => '0',     'spreeadsheetField_008_30-BK' => '0',     'spreeadsheetField_008_31-BK' => '0',     'spreeadsheetField_008_33-BK' => '0',     'spreeadsheetField_008_34-BK' => '#',     'spreeadsheetField_008_35-BK' => 'por',     'spreeadsheetField_008_38-BK' => '#',     'spreeadsheetField_008_39-BK' => '#',     'spreeadsheetField_020_a' => '',     'spreeadsheetField_040_a' => 'BR-LjCUU',     'spreeadsheetField_040_d' => '',     'spreeadsheetField_041_I1_ro' => '# - indefinido (obsoleto)',     'spreeadsheetField_041_I1' => '#',     'spreeadsheetField_041_a' => 'POR',     'spreeadsheetField_080_a' => '',     'spreeadsheetField_090_a' => '',     'spreeadsheetField_090_b' => '',     'spreeadsheetField_017_a' => '',     'spreeadsheetField_100_I1_ro' => '0 - Prenome simples e/ou composto',     'spreeadsheetField_100_I1' => '0',     'spreeadsheetField_100_a' => '',     'spreeadsheetField_100_a_Filter_DictionaryNumber' => '1',     'spreeadsheetField_100_a_Filter_DictionaryContent' => '',     'prefix_spreeadsheetField_100_a' => '',     'spreeadsheetField_110_I1_ro' => '0 - Nome invertido',     'spreeadsheetField_110_I1' => '0',     'spreeadsheetField_110_a' => '',     'spreeadsheetField_110_a_Filter_DictionaryNumber' => '3',     'spreeadsheetField_110_a_Filter_DictionaryContent' => '',     'spreeadsheetField_110_c' => '',     'spreeadsheetField_110_d' => '',     'spreeadsheetField_110_n' => '',     'spreeadsheetField_111_I1_ro' => '0 - Nome invertido',     'spreeadsheetField_111_I1' => '0',     'spreeadsheetField_111_a' => '',     'spreeadsheetField_111_a_Filter_DictionaryNumber' => '4',     'spreeadsheetField_111_a_Filter_DictionaryContent' => '',     'spreeadsheetField_111_c' => '',     'spreeadsheetField_111_d' => '',     'spreeadsheetField_111_n' => '',     'spreeadsheetField_240_a' => '',     'spreeadsheetField_245_I1_ro' => '0 - Não gerar entrada secundária de título',     'spreeadsheetField_245_I1' => '0',     'spreeadsheetField_245_I2_ro' => '0 - Zero',     'spreeadsheetField_245_I2' => '0',     'spreeadsheetField_245_a' => '',     'prefix_spreeadsheetField_245_a' => '',     'suffix_spreeadsheetField_245_a' => '',     'separator_spreeadsheetField_245_a' => '',     'spreeadsheetField_245_b' => '',     'spreeadsheetField_245_c' => '',     'spreeadsheetField_245_h' => '',     'spreeadsheetField_246_a' => '',     'spreeadsheetField_246_b' => '',     'spreeadsheetField_250_a' => '',     'spreeadsheetField_260_a' => '',     'spreeadsheetField_260_a_Filter_DictionaryNumber' => '6',     'spreeadsheetField_260_a_Filter_DictionaryContent' => '',     'spreeadsheetField_260_b' => '',     'spreeadsheetField_260_b_Filter_DictionaryNumber' => '7',     'spreeadsheetField_260_b_Filter_DictionaryContent' => '',     'spreeadsheetField_260_c' => '',     'spreeadsheetField_300_b' => '',     'spreeadsheetField_300_e' => '',     'spreeadsheetField_300_a' => '',     'suffix_spreeadsheetField_300_a' => '',     'spreeadsheetField_440_I2_ro' => '0 - Zero',     'spreeadsheetField_440_I2' => '0',     'spreeadsheetField_440_a' => '',     'spreeadsheetField_500_a' => '',     'spreeadsheetField_502_a' => '',     'spreeadsheetField_504_a' => '',     'spreeadsheetField_505_I1_ro' => '0 - Conteúdo',     'spreeadsheetField_505_I1' => '0',     'spreeadsheetField_505_I2_ro' => '# - Básico',     'spreeadsheetField_505_I2' => '#',     'spreeadsheetField_505_a' => '',     'spreeadsheetField_590_a' => '',     'spreeadsheetField_650_I1_ro' => '# - Informação não disponível',     'spreeadsheetField_650_I1' => '#',     'spreeadsheetField_650_I2_ro' => '0 - Cabeçalhos de assuntos da Library of Congress/lista de autoridades da LC',     'spreeadsheetField_650_I2' => '0',     'spreeadsheetField_650_a' => '',     'spreeadsheetField_650_a_Filter_DictionaryNumber' => '9',     'spreeadsheetField_650_a_Filter_DictionaryContent' => '',     'prefix_spreeadsheetField_650_a' => '',     'suffix_spreeadsheetField_650_a' => '',     'spreeadsheetField_700_I1_ro' => '0 - Prenome simples e/ou composto',     'spreeadsheetField_700_I1' => '0',     'spreeadsheetField_700_I2_ro' => '# - Não há informação',     'spreeadsheetField_700_I2' => '#',     'spreeadsheetField_700_t' => '',     'spreeadsheetField_700_a' => '',     'spreeadsheetField_700_e' => '',     'spreeadsheetField_700_4' => '',     'spreeadsheetField_710_I1_ro' => '0 - Nome Invertido',     'spreeadsheetField_710_I1' => '0',     'spreeadsheetField_710_I2_ro' => '# - Não há informação',     'spreeadsheetField_710_I2' => '#',     'spreeadsheetField_710_a' => '',     'spreeadsheetField_710_t' => '',     'spreeadsheetField_710_b' => '',     'spreeadsheetField_711_I1_ro' => '0 - Nome invertido',     'spreeadsheetField_711_I1' => '0',     'spreeadsheetField_711_I2_ro' => '# - Não há informação',     'spreeadsheetField_711_I2' => '#',     'spreeadsheetField_711_a' => '',     'spreeadsheetField_711_c' => '',     'spreeadsheetField_711_d' => '',     'spreeadsheetField_711_n' => '',     'spreeadsheetField_711_t' => '',     'spreeadsheetField_740_a' => '',     'spreeadsheetField_856_u' => '',     '__ISFILEUPLOADPOST' => 'yes',     'spreeadsheetField_901_a' => '1',     'spreeadsheetField_901_b' => '',     'spreeadsheetField_901_c' => '1',     'spreeadsheetField_901_e' => '',     'spreeadsheetField_950_a' => '',     'spreeadsheetField_949_3' => '1',     'spreeadsheetField_949_1' => '1',     'spreeadsheetField_949_a' => '00012345643274',     'spreeadsheetField_949_b' => '1',     'spreeadsheetField_949_c' => 'C',     'spreeadsheetField_949_d' => '1',     'spreeadsheetField_949_f' => '',     'spreeadsheetField_949_g' => '15',     'spreeadsheetField_949_h' => '',     'spreeadsheetField_949_i' => '41',     'spreeadsheetField_949_n' => '',     'spreeadsheetField_949_q' => '',     'lookUpDesc_spreeadsheetField_949_q_ro' => '',     'spreeadsheetField_949_t' => '',     'spreeadsheetField_949_v' => '',     'spreeadsheetField_949_w' => '',     'spreeadsheetField_949_y' => '17/10/2011',     'spreeadsheetField_949_z' => '',     'spreeadsheetField_041_a_defaultValue' => 'POR',     'spreeadsheetField_949_3_defaultValue' => '1',     'spreeadsheetField_949_1_defaultValue' => '1',     'spreeadsheetField_949_b_defaultValue' => '1',     'spreeadsheetField_949_c_defaultValue' => 'C',     'spreeadsheetField_949_d_defaultValue' => '1',     'spreeadsheetField_949_i_defaultValue' => '1',     'spreeadsheetField_949_y_defaultValue' => '17/10/2011',     'spreeadsheetField_949_g_defaultValue' => '15',     'GRepetitiveField' => 'gtcRepeatField_949',     'arrayItemTemp' => NULL,     'keyCode' => '',     'isModified' => 't',     'functionMode' => 'manage',     'frm4e9c2c0a41944_action' => 'http://gnutecatrunk.guilherme/index.php?module=gnuteca3&action=main:catalogue:material&function=new',     '__mainForm__VIEWSTATE' => '',     '__mainForm__ISPOSTBACK' => 'yes',     '__mainForm__EVENTTARGETVALUE' => 'forceAddToTable',     '__mainForm__EVENTARGUMENT' => '',     '__FORMSUBMIT' => '__mainForm',     '__ISAJAXCALL' => 'yes',     '__THEMELAYOUT' => 'dynamic',     '__MIOLOTOKENID' => '',     '__ISFILEUPLOAD' => 'no',     '__ISAJAXEVENT' => 'yes',     'cpaint_response_type' => 'json',     'insertData' => true,     'arrayItem' => 0,  )),);

        $this->business->setData($data);
    }
}
?>