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
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * @since
 * Class created on 13/09/2011
 *
 **/
class FrmHelpSearch extends GForm
{
    public $busFile;
    
    public function __construct()
    {
        $this->setAllFunctions('Help', array('helpId'),array('helpId'));
        $MIOLO  = MIOLO::getInstance();
        $this->busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');

        parent::__construct();
    }

    public function mainFields()
    {
        $fields[] = new MIntegerField('helpIdS', null, _M('Código',$this->module), FIELD_ID_SIZE);
        $fields[] = $formS = new GSelection('formS', null, _M('Formulário', $this->module), $this->busFile->listForms(), null, null, null, FALSE);
        $formS->addAttribute('onChange', GUtil::getAjax('changeForm'));
        $fields[] = new MDiv('divSubForm', null);
        $fields[] = new GSelection('isActiveS', null, _M('Ativo', $this->module), GUtil::listYesNo(), null, null, null, FALSE);
        $fields[] = new MTextField('helpS', null, _M('Conteúdo',$this->module), FIELD_DESCRIPTION_SIZE );
        $this->setFields( $fields );
    }

    /**
     * Função AJAX que mostra campo do subForm se a opção de formulário escolhida
     * for FrmSimpleSearch
     *
     * @param stdClass $args
     */
    public function changeForm ($args)
    {
        if ( $args->formS == 'FrmSimpleSearch' )
        {
            $lblSubForm= new MLabel(_M('Subformulário', $this->module).":");
            $subFormS = new GSelection('subFormS', null, _M('Subformulário', $this->module), $this->busFile->listForms(true) );
        }
        
        $fields[] = new MDiv('subFormField', array($lblSubForm,$subFormS));
        $this->setResponse($fields, 'divSubForm');
    }
}
?>