<?php
/**
 * <--- Copyright 2005-2011 de Solis - Cooperativa de Soluções Livres Ltda. e
 * Univates - Centro Universitário.
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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * 
 * @since
 * Class created on 06/01/2011
 *
 **/
set_time_limit(10000);
ini_set('max_execution_time', 10000);
ini_set('max_input_time',10000);
$theme->clearContent();
$MIOLO->uses( 'classes/bBaseDeDados.class.php','base');
$MIOLO->uses( 'classes/bSyncExecute.class.php','base');

$syncModule = MIOLO::_REQUEST('syncModule');

if ( !$syncModule )
{
    $theme->clearContent();
    
    $ui = $MIOLO->getUI();

    $painel = new MActionPanel('painelBase', _M('Base'));

    $imagem = $ui->getImage($module, 'imagem.png');
    $painel->addAction(_M('Pessoa'), $imagem, $module, 'main', NULL, array( 'chave' => 'baseperson' ));
 
    $theme->appendContent($painel);
   
    // Inclui o manipulador.
    include_once(dirname(__FILE__) . "/manipulador.inc.php");
}
else
{
    if ( !defined('DB_NAME') )
    {
        define('DB_NAME', $syncModule );
    }
    
    bBaseDeDados::iniciarTransacao();
    try
    {    
        //Executar sincroniza��o
        bSyncExecute::executeSync($syncModule); 

        bBaseDeDados::finalizarTransacao();
        
        $fields[] = MMessage::getStaticMessage('msgSucesso', _M("Sincroniza��o da base de dados realizada com sucesso!", $syncModule), MMessage::TYPE_SUCCESS);
        
        $theme->appendContent( $fields );
    }
    catch (Exception $e)
    {
        bBaseDeDados::reverterTransacao();
        
        $fields[] = MMessage::getStaticMessage('msgErro', _M("Erro ao sincronizar a base de dados."), MMessage::TYPE_ERROR);
        $fields[] = new MText('error', $msg, 'red');
        
        $theme->appendContent( $fields ); 
    }
}
?>
