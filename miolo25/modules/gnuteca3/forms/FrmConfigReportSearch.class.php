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
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
 * Class created on 06/01/2009
 *
 **/
class FrmConfigReportSearch extends GForm
{
    /** @var BusinessGnuteca3BusReport  */
    public $business;

    public function __construct()
    {
    	$this->setAllFunctions('Report',array('reportIdS'),array('reportId'));
    	$this->setGrid('GrdConfigReport');
    	$this->setTransaction('gtcConfigReport');
        parent::__construct();
    }


    public function mainFields()
    {
    	$fields[] = $reportId = new MTextField('reportIdS','',_M('Código', $this->module), FIELD_DESCRIPTION_SIZE );
        $reportId->addStyle('text-transform','uppercase');
    	$fields[] = new MTextField('titleS','',_M('Título', $this->module), FIELD_DESCRIPTION_SIZE );
        $fields[] = new MTextField('descriptionS',null, _M('Descrição', $this->module), FIELD_DESCRIPTION_SIZE);
        $fields[] = new GSelection('permissionS', '', _M('Permissão', $this->module), BusinessGnuteca3BusDomain::listForSelect('REPORT_PERMISSION') );
        $fields[] = new GSelection('reportGroupS', null, _M('Grupo', $this->module), BusinessGnuteca3BusDomain::listForSelect('REPORT_GROUP'));
        $fields[] = new GSelection('isActiveS', null, _M('Ativo', $this->module) , GUtil::listYesNo(0));

        $this->setFields( $fields );
    }


    /**
     * Método que disponibiliza para download os SQL's do report
     */
    public function exportReport($args)
    {
        $sql = $this->business->exportReport(MIOLO::_REQUEST('reportId'));
        BusinessGnuteca3BusFile::openDownload('report', $this->name . 'relatorio.grpt', $sql);

        $this->setResponse(null, 'limbo');
    }
}
?>