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
 * Define handler
 * Define the preferences and mensagens
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
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
 * Class created on 21/07/2008
 *
 **/
$MIOLO  = MIOLO::getInstance();
$module = 'gnuteca3';

require_once("gnutecaClasses.inc.php");

//evite problemas com syncDB
if ( $MIOLO->getCurrentModule() != 'base')
{
    //carrega valores globais de preferências
    $business  = $MIOLO->getBusiness($module,'BusPreference');
    $constants = GOperator::getLibraryUnitLogged() ? $business->getParameterValues(GOperator::getLibraryUnitLogged()) : $business->getModuleValues('gnuteca3');

    if ( count($constants)>0 )
    {
        foreach ( $constants as $cnt )
        {
            define($cnt[0],$cnt[1]);
        }
    }
}

/**
 * Mensagens padrões
 */
define('MSG_RECORD_DELETED',            'Registro excluído.');
define('MSG_RECORD_UPDATED',            'Registro atualizado.');
define('MSG_RECORD_INSERTED',           'Registro inserido. Deseja inserir mais registros?');
define('MSG_CONFIRM_RECORD_DELETE',     'Tem certeza que deseja excluir o registro?');
define('MSG_RECORD_ERROR',              _M('Erro ao executar operação solicitada.', $module));
define('MSG_RECORD_PARSE_ERROR',        'Não foi possível inserir todos os itens, por favor, certifique-se da integridade dos dados.');
define('MSG_CONFIRM_RECORD_CANCEL',     'Tem certeza que deseja cancelar o registro?!');
define('MSG_RECORD_CANCELED',           'Registro cancelado.');
define('USUARIO_SEM_PERMISSAO',         'Você não tem permissão de acesso à este local. Contate o administrador do sistema.');

/**
 * CATALOGE CONSTANTS OPTIONS
 */
define('FIELD_TYPE_SELECT',     "MSelection"            );
define('FIELD_TYPE_COMBO',      "MComboBox"             );
define('FIELD_TYPE_TEXT',       "MTextField"            );
define('FIELD_TYPE_REPETITIVE', "GtcRepetitiveField"    );
define('FIELD_TYPE_DICTIONARY', "GtcDictionary"         );
define('FIELD_TYPE_LOOKUP',     "GtcLookUp"             );
define('FIELD_TYPE_DATE',       "GtcDateField"          );
define('FIELD_TYPE_MULTILINE',  "MMultiLineField"       );

define('LEADER_TAG_MATERIAL_TYPE',      '000-6'         );
define('LEADER_TAG_BIBLIOGRAPY_LEVEL',  '000-7'         );
define('LEADER_TAG_ENCODING_LEVEL',     '000-17'        );

define('LOGIN_TYPE_ADMIN', 1); //login do tipo admin
define('LOGIN_TYPE_USER', 2); //login do tipo usuario
define('LOGIN_TYPE_USER_AJAX', 3); //login do tipo usuario + tela ajax

//Seta o datestyle padrão para o gnuteca
GBusiness::setDateStyle();
?>
