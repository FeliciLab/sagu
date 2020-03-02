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
 * File search form
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 * Sandro Roberto Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 14/10/2010
 *
 **/
$MIOLO->getClass('gnuteca3', 'controls/GFileUploader');
class FrmFileSearch extends GForm
{
    public function __construct()
    {
        $this->setAllFunctions('File', array('filePath') ,array('filePath'));
        $this->business->recursiveSearch = true; //habilita pesquisa em subpastas
        parent::__construct();
        
    }


    public function mainFields()
    {
        $fields[] = new MTextField('fileName', '', _M('Nome do arquivo',$this->module),   FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTextField('extension', '', _M('Extensão',$this->module), FIELD_DESCRIPTION_SIZE );
        $fields[] = new GSelection('folder', null, _M('Pasta'), $this->business->listFolder(), null, null, null, null);

        $this->setFields( $fields );
    }


    /**
     * Visualiza os arquivos possíveis e permite o download dos não conhecidos.
     *
     *
     * @param <string> $relative
     *
     */
    public function gridDownloadFile($relative)
    {
        $file = $this->business->getFile($relative);
        GFileUploader::downloadFile($file);
    }
}
?>