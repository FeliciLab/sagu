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
 * Processo para apagar os valores
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers \n
 * Jader Osvino Fiegenbaum [jader@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 25/11/2010
 *
 **/
class FrmDeleteValuesOfSpreadSheet extends GForm
{
    public $MIOLO;
    public $module;
    private $busSpreadSheet;

    public function __construct()
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        
        $this->busSpreadSheet = $this->MIOLO->getBusiness($this->module, 'BusSpreadsheet');
        $this->setBusiness('BusDeleteValuesOfSpreadSheet');
        $this->setTransaction('gtcDeleteValuesOfSpreadSheet');
        parent::__construct(_M('Apagar valores das planilhas', $this->module));
    }


    public function mainFields()
    {
    	$fields[] = new MDiv('divDescription', _M('Quando os campos de uma planilha são removidos, eles permanessem guardados nos materiais que já tenham esse campo registrado.<br/>
            Esta ação apaga valores das planilhas de catalogação que não estão mais sendo utilizados.', $this->module), 'reportDescription');
    	
    	$spreadSheet = $this->busSpreadSheet->searchSpreadsheet(true);
    	$newValues = array();
        
    	if ( is_array($spreadSheet) )
    	{
    		foreach( $spreadSheet as $key=>$values )
    		{
    			$value = $values->category . '.' . $values->level;
    			$newValues[$value] = $value;
    		}
    	}
    	
        $fields[] = new GSelection('spreadSheet', null, _M('Planilha', $this->module), $newValues);
        $fields[] = new MButton('btnSearch', _M('Processar', $this->module), ':doAction',Gutil::getImageTheme('accept-16x16.png'));

        $fields[] = new MSeparator('<br>');
        $fields[] = new MDiv('divGrid');
        $this->setFields($fields);
    }


    /**
     * Método chamado via ajax para buscar a quantidade de registros que serão afetados por planilhas
     
     * @param $args
     */
    public function doAction($args)
    {
    	$spreadSheet = explode('.', $args->spreadSheet );
    	$category = $spreadSheet[0];
    	$level = $spreadSheet[1];
    	$tags = $this->busSpreadSheet->getTagsOfSpreadSheet($category, $level);
        $result = $this->business->countDeleteLinesOfGtcMaterial($tags);
        $grid = $this->MIOLO->getUI()->getGrid($this->module, 'GrdDeleteValuesOfSpreadSheet');
        $grid->setData($result);
        $fields[] = new MDiv('divGr', $grid);
            
        //só mostra o botão apagar registros quando realmente tiver registros para excluir
        if ( sizeof($result) > 0 )
        {
	        $fields[] = new MDiv( '', new MButton('btnDel', _M('Apagar registros', $this->module), ':deleteValuesBefore' ,Gutil::getImageTheme('delete-16x16.png') ) );
        }

        $this->setResponse( new MFormContainer('', $fields), 'divGrid');
    }


    public function deleteValuesBefore()
    {
        $this->question( _M('Tem certeza que deseja apagar o(s) registro(s)?', $this->module), GUtil::getAjax('deleteValues') );
    }
    
    /**
     * Método chamado via ajax para apagar os valores apagados da(s) planilha(s)
     
     * @param $args
     */
    public function deleteValues($args)
    {
    	$spreadSheet = explode('.', $args->spreadSheet );
        $category = $spreadSheet[0];
        $level = $spreadSheet[1];
        $tags = $this->busSpreadSheet->getTagsOfSpreadSheet($category, $level);
        $this->business->beginTransaction();
        $ok = $this->business->deleteLinesOfGtcMaterial($tags);
        $this->business->commitTransaction();
        $goto = $this->MIOLO->getActionURL($this->module, $this->action);

        if ( $ok )
        {
            $this->information('Registros apagados com sucesso!', $goto);
        }
        else 
        {
            $this->error('Não foi possível apagar os registros!', $goto);
        }
    }
}
?>
