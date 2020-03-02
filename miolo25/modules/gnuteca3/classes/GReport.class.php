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
 * Class GReport
 *
 * @author Guilherme Soldateli [guilherme@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Guilherme Soldateli [guilherme@solis.com.br]
 * Jader Osvino Fiegenbaum [jader@solis.com.br]
 * Jonas Rosa [jonas_rosa@solis.com.br]
 *
 * @since
 * Class created on 05/03/2013
 *
 **/
$MIOLO->uses('forms/FrmAdminReport.class.php', 'gnuteca3');
class GReport
{

    public $columns;
    public $result;
    public $reportData;
    public $reportArguments;
    public $output;
    
    const REPORT_TYPE_GRID = 'GRID';
    const REPORT_TYPE_CSV = 'CSV';
    const REPORT_TYPE_PDF = 'PDF';
    const REPORT_TYPE_ODT = 'ODT';
    
    public function getReportData( $reportId )
    {
        $MIOLO = MIOLO::getInstance();
        $busReport = $MIOLO->getBusiness( 'gnuteca3', 'BusReport');
    	$this->reportData = $busReport->getReport( $reportId );
    	return $this->reportData;        
    }
    
    /**
     * Metodo que executa relatorio e retorna-o conforme o tipo de retorno solicitado
     * pelo parametro $reportType.
     * 
     * @param string $reportId
     * @param stdClass $reportArguments
     * @param integer $reportType alguma das constantes do GReport : REPORT_TYPE_GRID,REPORT_TYPE_CSV,REPORT_TYPE_PDF
     * @param string $totalColumn o indice da coluna do relatorio que sera totalizada.
     */
    public function executeReport($reportId, stdClass $reportArguments, $reportType, $totalColumn = null)
    {
        $MIOLO = MIOLO::getInstance();
        $busReport = $MIOLO->getBusiness( 'gnuteca3', 'BusReport');
        
        $this->reportData = $this->getReportData($reportId);
        $sql = $this->reportData->reportSql;
    	$subSql = $this->reportData->reportSubSql;

        $this->result = $busReport->executeSelect( $sql , $subSql, $reportArguments);
        $this->columns = $busReport->getResultFields();

        //Adiciona total se tiver $totalColumn nao vazio.
        $this->addTotal( $totalColumn );
        
        //Gera o relatorio conforme no formato desejado.
        $this->output = $this->generateReportAs($reportType);
        
        return $this->output;


    }
    
    /**
     * Retorna relatorio no formato de grid.
     * @return \GGrid
     */
    public function getGrid()
    {
        if ( $this->columns )
        {
            foreach ( $this->columns as $line => $info )
            {
            	$gridColumns[] = new MGridColumn( $info, MGrid::ALIGN_LEFT, null, null, true, null, true);
            }
        }
        
        $grid = new GGrid(null, $gridColumns );
       
        $grid->setData( $this->result );
        $grid->setIsScrollable();

        return $grid;        
    }
    /**
     * Retorna o relatorio em formato CSV
     * 
     * @param string $separator separado que sera utilizado para gerar o relatorio.
     * @return string (string no formato CSV)
     */
    public function getCSV( $separator = ';' )
    {
        $csv = null;
        if ($this->result && $this->columns)
        {
            foreach ($this->columns as $line => $info)
            {
                $csv .= $info.$separator;
            }

            $csv .=  "\n";

            foreach ( $this->result as $line => $info )
            {
                foreach ( $info as $l => $i )
                {
                        $csv .= $i.$separator;
                }

                $csv .= "\n";
            }
        }
        
        return $csv;
        
    }
    
    /**
     * Retorna o relatorio em formado PDF.
     * 
     * @param type $pageOrientation Define se vai ser retrato 'P' ou paisagem 'L'
     * @return type
     */
    public function getPDF($pageOrientation = null)
    {
        $MIOLO = MIOLO::getInstance();
        $MIOLO->getClass('gnuteca3', 'GPDFTable');    
        
        $output = '';
        $orientation = $pageOrientation ? $pageOrientation : 'P';
        
        if ( $this->result && $this->columns )
        {
            $pdf = new GPDFTable( $orientation, 'pt');
            $pdf->addTable( new MTableRaw($this->reportData->Title, $this->result, $this->columns) );
            
            $output = $pdf->Output(null, 'S');
        }
        
        
        return $output;        
    }
    
    /**
     * Exibe relatorio em formato odt.
     * O metodo gera o relatorio e retorna o caminho deste no servidor para ser
     * baixado.
     * 
     * @return string (Caminho do arquivo).
     */
    public function getODT()
    {
        $MIOLO = MIOLO::getInstance();
        
        $MIOLO->getClass('gnuteca3', 'GOdt');
        $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');

        $busFile->folder = 'odt';
        $busFile->fileName = BusinessGnuteca3BusFile::getValidFilename( $this->reportData->reportId ).'.';
        $file = $busFile->searchFile(true);
        $ext = $file[0]->extension;

        $odt = new GOdt( $file[0]->absolute );
        $odt->setVars('GREPORT_ID', $this->reportData->reportId );
        $odt->setVars('GREPORT_TITLE', $this->reportData->Title );
        $odt->setVars('GREPORT_DESC', $this->reportData->description );
        $odt->setVars('GREPORT_PERMISSION', $this->reportData->permission );
        $odt->setVars('GREPORT_SQL', $this->reportData->reportSql );
        $odt->setVars('GREPORT_SCRIPT', $this->reportData->script );
        $odt->setVars('GREPORT_ACTIVE', $this->reportData->isActive );
        $odt->setVars('GREPORT_GROUP', $this->reportData->reportGroup );

        $params = $this->reportData->parameters;

        //define variáveis de todos parametros
        if ( is_array( $params ) )
        {
            foreach ( $params as $line => $param )
            {
                $odt->setVars('GREPORT_PARAM_LABEL_'.$line, $param->label );
                $odt->setVars('GREPORT_PARAM_TYPE_'.$line, $param->type );
                $odt->setVars('GREPORT_PARAM_ID_'.$line, $param->identifier );
            }
        }

        $postData = $this->reportArguments;

        //criar variáveis de post no relatório, para poder mostrar os filtros
        if ( is_array( $postData ) )
        {
            foreach ( $postData as $line => $info )
            {
                //tira os dados padrão do miolo
                if ( ! ( stripos( $line, '__') === 0 ) && $line != 'cpaint_response_type' )
                {
                    $odt->setVars( $line, $info );
                }
            }
        }


        try
        {
            $content = $odt->setSegment('content');
            $this->setOdtContent($content, $this->result, $this->columns); //seta o conteúdo no segmento
            $odt->mergeSegment( $content );

            //salva o arquivo
            $filename = BusinessGnuteca3BusFile::getValidFilename( 'report_'.uniqid( $this->reportData->reportId,true)).'.odt';
            $odt->output( 'report', BusinessGnuteca3BusFile::getAbsoluteFilePath('report', $filename) );

            return $filename;

        }
        catch (Exception $exc)
        {
            echo Gform::error( $exc->getMessage()  );
            return false;
        }        
    }
    
    /**
     * Adiciona o total ao array de dados, caso for necessário
     *
     * @param int $totalColumn a coluna que deve ser totalizada.
     * @return array dados com adição de total
     */
    public function addTotal( $totalColumn = null )
    {
        $total = MIOLO::_REQUEST('total');
        
        if ( $total == FrmAdminReport::TOTAL_CONTAGEM )
        {
            $totalCount = count($this->result);
            if ( $totalCount > 0 )
            {
                $colCount = count($this->result[0]);
                
                if ( $colCount == 1 )
                {
                    $totalLine[] = _M('Total: ' . $totalCount, $this->module);
                }
                else
                {
                    $totalLine[] = _M('Total', $this->module);
                
                    //variável utilizada para alinhamento perfeito do total
                    $extrasCol = $colCount - 2;

                    if ( $extrasCol > 0 )
                    {
                        for ( $i = 0; $i < $extrasCol ; $i++ )
                        {
                            $totalLine[] = '';
                        }
                    }

                    //adiciona o total na última linha
                    $totalLine[] = $totalCount;
                }

                $this->result[] = $totalLine;
            }
        }
        else
        {
            if ( $totalColumn === null )
            {
                return false;
            }

            if ( is_array($this->result) && $totalColumn >= 0 )
            {
                //coloca a contagem de acordo com array considerando 0 como primeiro
                $collumCount = count( $this->result[0] ) - 1;

                if ( $collumCount >= $totalColumn)
                {
                    foreach ( $this->result as $line => $info )
                    {
                        $totalCount += $info[$totalColumn];
                    }
                }

                $totalLine[] = _M('Total geral da coluna', $this->module) . ' ' . $this->columns[$totalColumn];

                //variável utilizada para alinhamento perfeito do total
                $extrasCol = count( $this->result[0] ) - 2;

                if ( $extrasCol > 0 )
                {
                    for ( $i = 0; $i < $extrasCol ; $i++ )
                    {
                        $totalLine[] = '';
                    }
                }

                //adiciona o total na última linha
                $totalLine[] = $totalCount;

                $this->result[] = $totalLine;
            }
        }
    }
    
    
    /**
     * Seta o conteúdo no segmeto "content"
     * @param Segment $content objeto do segmento
     * @param array $result resultado
     * @param array $columns colunas do arquivo
     */
    protected function setOdtContent(Segment $content, $result, $columns )
    {
        $MIOLO = MIOLO::getInstance();
        
        //defini dados para multiplicação de seguimentos
        if ( is_array($result) && is_array( $columns ) )
        {
            foreach ( $result as $line => $info )
            {
                foreach ( $columns as $l => $column )
                {
                    try
                    {
                        if ( strtolower($column) == 'image' )
                        {
                            $parts = explode('/', $info[$l]); //separa o arquivo do diretório

                            $busFile = $MIOLO->getBusiness('gnuteca3','BusFile');
                            $busFile->folder= $parts[0]; //seta o diretório
                            $busFile->fileName = $parts[1]; //seta o arquivo
                            $pathFile = $busFile->searchFile(true);

                            //procura imagem default caso não tenha encotrado a imagem
                            if ( count($pathFile) == 0 )
                            {
                                $busFile->fileName = 'default.';
                                $pathFile = $busFile->searchFile(true);
                            }

                            $pathFile = $pathFile[0]->absolute; //obtém caminho absoluto da imagem

                            $content->setImage('image', $pathFile, 60, 75); //seta a imagem 3x4
                        }
                        else if ( strtolower($column) == strtolower('codebar') ) //código de barras 
                        { 
                            $tmpPath = BusinessGnuteca3BusFile::getAbsoluteFilePath('tmp', 'codabar_' . $info[$l], 'png' ); //obtém o path completo para arquivo 
                            $barcode = new codabar( $info[$l] ); //gera o código de barras do código de aluno 
                            $barcode->output(0.04, 1.27, 2, $tmpPath); //faz output para o tmp do Gnuteca 
                            $content->setImage('barcode', $tmpPath, 140, 30);
                            //seta código de barras 
                        }
                        else
                        {
                            $content->$column( utf8_decode( $info[$l] ) );
                        }
                    }
                    catch (Exception $exc)
                    {
                        //caso o parametro não exista no content
                    }
                }

                $content->merge();
            }
        }  
    }
    
    /**
     * Metodo que salva relatorio gerado em uma pasta temporaria para poder anexa-lo
     * ao envio de e-mails.
     * 
     * @param string $folder O folder em que o relatorio sera salvo.
     * @return string $filePath caminho onde foi salvo o relatorio gerado.
     */
    public function getReportFilePath($reportExtension,$folder = 'tmp')
    {
        //O odt gera automaticamente o caminho para o arquivo que e passado ao output no momento da geracao.
        if( strtoupper($reportExtension) == self::REPORT_TYPE_ODT )
        {
            //Quando for ODT, sempre eh gerado no diretorio files/report/nome_do_relatorio. OBS: nome_do_relatorio = $this->output
            $fileName = BusinessGnuteca3BusFile::getAbsoluteFilePath('report', $this->output);
            return $fileName;
        }

        
        //demais arquivos sao salvos.
        $filename = BusinessGnuteca3BusFile::getValidFilename( 'report_'.uniqid( $this->reportData->reportId,true)) .'.' . strtolower($reportExtension);
        $filePath = BusinessGnuteca3BusFile::getAbsoluteFilePath($folder, $filename);
        BusinessGnuteca3BusFile::streamToFile($this->output, $filePath );
        
        //retorna o caminho fisico dele.
        return $filePath;
    }
    
    public function generateReportAs ( $reportType = null, $pageOrientation = 'P', $csvSeparator = ';')
    {
        //Formatos estao dentro das constantes em maiusculo
        $reportType = strtoupper($reportType);
        
        switch ( $reportType ) 
        {
            case self::REPORT_TYPE_GRID :
                $this->output = $this->getGrid();
            break;
            
            case self::REPORT_TYPE_CSV:
                $this->output = $this->getCSV($csvSeparator);
            break;
            
            case self::REPORT_TYPE_PDF:
                $this->output = $this->getPDF($pageOrientation);
            break;
            
            case self::REPORT_TYPE_ODT:
                $this->output = $this->getODT();
            break;            
            default:
                $this->output = null;
            break;
        }
        
        return $this->output;
    }
    
}

?>
