<?php
/**
 * <--- Copyright 2005-2010 de Solis - Cooperativa de Soluções Livres Ltda.
 *
 * Este arquivo é parte do programa Sagu.
 *
 * O Sagu é um software livre; você pode redistribuí-lo e/ou modificá-lo
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
 * Redireciona para tela de encontros do Residency
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 21/07/2011
 */

$action = MIOLO::getCurrentAction();
$module = SAGU::getFileModule(__FILE__);

$mioloUserName = trim($MIOLO->getLogin()->id);
$busPerson = new BusinessBasicBusPerson();
$personData = $busPerson->getPersonByMioloUserName($mioloUserName);

$oferta = new ResOfertaDeUnidadeTematica( MIOLO::_REQUEST('ofertaDeUnidadeTematicaId') );

//Verifica se a pessoa logada é responsável no núcleo e enfase da oferecida, caso sim, permite o acesso a tela de encontros
$verificaResponsavel = ResPreceptoria::responsavelNucleoEnfaseDaOferecida($personData->personId, $oferta->ofertaDeUnidadeTematicaId);

//Verifica permissao
if ( $verificaResponsavel == DB_FALSE )
{
    $MIOLO->error(_M('Somente o preceptor desta oferta de atividade pode acessar este conteúdo.', $module));
}

SAGU::handle('residency', $action, _M('Encontro', $module), 'FrmEncontro', 'FrmEncontro', array('checkAccess' => false));
?>