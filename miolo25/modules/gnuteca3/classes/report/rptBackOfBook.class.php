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
 * Back of book report
 *
 * @author Moises Heberle [moises@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Rafael Luís Spengler [rafael@solis.coop.br]
 * Tiago Gossmann [tiagog@solis.coop.br]
 * Sandro R. Weisheimer [sandrow@solis.coop.br]
 *
 * @since
 * Class created on 06/11/2008
 *
 **/

class rptBackOfBook extends GPDFLabel
{
    public $MIOLO, $module;
    public $busFormatBackOfBook;
    public $busLibraryUnitConfig;
    public $busMaterial;


    function __construct($data, $codes)
    {
        $this->MIOLO  = MIOLO::getInstance();
        $this->module = MIOLO::getCurrentModule();
        $this->busFormatBackOfBook = $this->MIOLO->getBusiness($this->module, 'BusFormatBackOfBook');
        $this->busMaterial         = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
        $this->MIOLO->getClass($this->module, 'GFunction');

        parent::__construct($data);
        $this->setBeginLabel($data->beginLabel ? $data->beginLabel : 1);

        // Início da configuração do relatório
        $this->setSubject(_M('Lombada', $this->module));
        $this->setTitle(_M('Lombada', $this->module));
        $this->setFont('Arial', '', $data->fontSize ? $data->fontSize : 10);
        $cellSize = $data->fontSize / 20; //tamanho da célula
        $fontHeight = 10 / 20; //Tamanho da celula do texto do codigo de barra. FIX-ME: Esta hardcode

        for ( $i=1; $i < $data->initialLabel; $i++ )
        {
            $this->setBeginPositionOfTheLabel();
        }

        $gf         = new GFunction();
        $gf->SetExecuteFunctions(true);
        $format     = $this->busFormatBackOfBook->getFormatBackOfBook($data->formatBackOfBookId);
        $tagsFormat = GUtil::extractMarcVariables($format->format);
        $tagsInternalFormat = GUtil::extractMarcVariables($format->internalFormat);
        $tags       = array_unique( array_merge($tagsFormat, $tagsInternalFormat) );

        foreach ($codes as $itemNumber => $ec)
        {
            $myFormat = $format->format;

            if (!$ec->controlNumber)
            {
                    continue;
            }

            $this->checkBreakLine();
            $this->checkBreakPage();

            //Coloca o ponteiro para desenhar a etiqueta
            $this->setBeginPositionOfTheLabel();

            $yInicial = $this->getY(); //Pega o valor inicial do Y para gerar a etiqueta interna na mesma linha   
            $xInicial = $this->getX(); //Pega o valor inicial do X para gerar a etiqueta interna na mesma coluna

            //Se for para imprimir codigo de barras
            if ( in_array($data->barCodeType, array(OPTION_BARCODE_YES_DIFF, OPTION_BARCODE_YES_SAME)) ) 
            {
                $size = 1; //$data->size; //tamanho do codigo de barras que vem do form
                        
                //Largura e altura da etiqueta
                list ($w, $h) = $this->lengthLabel();

                //Escreve o texto, se tiver
                if ($data->barCodeText)
                {
                    $this->cell($w, $fontHeight, $data->barCodeText, 0, 0, 'C');
                    $yInicial = $this->y+$fontHeight;
                    $this->setX($xInicial);
                }
            
                //Chama classe de geracao de codigo barra (a informacao do nome da classe vem do form FrmBarCode)
                $className = $data->barCodeStyle;

                if ( !$className )
                {
                    throw new Exception("É necessário selecionar o tipo do código de barras.");
                }

                $this->MIOLO->GetClass($this->module, $className);
                
                //caso definição de adição de unidade no código do exemplar esteja acionada, retira os números do itemNumber e seja a formatação de EXEMPLAR
                if ( defined('EXEMPLAR_PLUS_UNIT') && EXEMPLAR_PLUS_UNIT == DB_TRUE && ($data->formatType == self::BARCODE_FORMAT_EXEMPLARY) )
                {
                    $itemNumber = substr( $itemNumber, strlen( GOperator::getLibraryUnitLogged() ) , strlen( $itemNumber ) );
                }
                
                $barcode = new $className( $ec->itemNumber );
                $barcode->_compute_pattern();
                                
                $cellSizeBar = 0.03;
            
                //tamanho que a barra deveria ocupar, sem considerar o espaço da foto
                $fixedBarWidth = $cellSizeBar * $size;

                /*if ( $data->logo )
                {
                    $cellSize = 0.015;
                }*/

                //tamanho da barra
                $barWidth = $cellSizeBar * $size;
                
                //diferença entre o tamanho proposto e o tamanho real
                $widthDiff = $barcode->getWidth($fixedBarWidth)  - $barcode->getWidth($barWidth);

                $barHeight = 0.5;
                $barH = $h*$barHeight;

                //na mesma etiqueta
                $xInicial = $this->x;

                //na mesma etiqueta
                $x2 = $x = $xInicial; //$this->x;
                $y2 = $y = $yInicial; //$this->y;   
                                
                if ( $data->barCodeType == OPTION_BARCODE_YES_DIFF )
                {
                    //em etiqueta diferentes
                    $x = $this->x + ($w-$barcode->getWidth( $fixedBarWidth ))/2;
                }                

                //TODO: Calculo para quando tiver o logo
                /*if ( $data->logo )
                {
                    $this->Image( $logoPath, $centerBarX +$x , $centerBarY+$y, $barH, $barH );
                }*/
                
                //Gerar as barras de um exemplar
                foreach ( $barcode->get_pattern() as $digit )
                {
                    $digit = split( ' ', $digit);
                    $bar = true;

                    foreach ($digit as $n)
                    {
                        if ( $bar ) //preto
                        {
                            $this->SetDrawColor(0, 0, 0);
                            $this->SetFillColor(0, 0, 0);
                        }
                        else //branco
                        {
                            $this->SetDrawColor( 255, 255, 255);
                            $this->SetFillColor( 255, 255, 255);
                        }

                        $this->SetLineWidth(0.000);
                        $this->Rect( $x + $widthDiff, $y, $n*$barWidth, $barH, 'FD' );
                        $x += ($n*$barWidth)+0.005;
                        $bar = !$bar;
                    }
                }

                //Escreve o código
                $this->SetXY($x2+( $widthDiff / 2), $y2+$barH);
                
                //Se barra for em etiqueta diferente da lombada
                if ( $data->barCodeType == OPTION_BARCODE_YES_DIFF )                
                {
                    $xInicial = $this->x;
                    $yInicial = $this->y;

                    //Centraliza descricao
                    //Executa calculos para centralizar a descricao do codigo de barras
                    $centerBarY = ($h-$barH)/2;
                   
                    //Descrição
                    $this->SetXY($xInicial,$this->y+$centerBarY-0.5 );
                    
                    //Quando for lombada separada da barra, o tamanho do texto vai ser igual ao tamanho da etiquetas.
                    $x = $w;
                    $x2 = 0;
                }

                $this->Cell($x-$x2, 0.5, $ec->itemNumber, 0, 0, 'C');

                
                if ( $data->barCodeType == OPTION_BARCODE_YES_SAME ) //Trata a posiçao da lombada
                {
                    $this->setXY($this->x+0.2, $yInicial);
                }
                else if ( $data->barCodeType == OPTION_BARCODE_YES_DIFF )
                {
                    $this->SetXY($xInicial, $yInicial);
                    //em etiqueta diferentes
                    $this->checkBreakLine();
                    $this->checkBreakPage();
                    $this->setBeginPositionOfTheLabel();
                }
                
                
                //$this->SetY($yInicial); //Volta o linha para o inicio
            }
            
            $gf->clearVariables();
            $gf->setVariable('$CONTROL_NUMBER', $ec->controlNumber);

            //troca cada uma das variaveis de tag, pelo valor pelo buscado no banco
            foreach ($tags as $tag)
            {
                $tagMarc = substr($tag, 1);

                list ($field, $subfield) = explode('.', $tagMarc);

                $line = null;

                if ( $field == MARC_EXEMPLARY_FIELD )
                {
                    $line = $ec->line;
                }

               	$gf->setVariable($tag, $this->busMaterial->getContentTag($ec->controlNumber, $tagMarc, $line));
            }

            //isso passa a linha para o GFunction, dessa forma ele sabe como pegar o getTagDescription corretamente
            $gf->line = $ec->line;

            if ($data->internalLabel != OPTION_ILABEL_ONLY)
            {
                $content = explode("\n", $gf->interpret($myFormat ,false) );

                $newContent = null;

                //remove linhas sem conteúdo
                if (is_array($content))
                {
                    foreach ($content as $i => $line)
                    {
                        if ( strlen(trim($line)) )
                        {
                            //pega o tamanho da string
                            $stringWidth = $this->getStringWidth( $line );
                            $labelWidth  = $data->labelWidth;

                            //se o tamanho do texto for maior que o tamanho permitido pela etiqueta
                            if ( $stringWidth > $labelWidth )
                            {
                                $temp = $this->lineBreak($line, $stringWidth, $labelWidth);

                                //e adiciona todas elas ao novo conteúdo
                                foreach ( $temp as $y )
                                {
                                    $newContent[] = $y;
                                }
                            }
                            else
                            {
                                //só adiciona a linha normal
                                $newContent[] = $line;
                            }
                        }
                    }
                }

                $tamMax = 0;

                //define as células de conteúdo e desenha a etiqueta da lomabda
                foreach ($newContent as $value)
                {
                    $this->Cell($this->x, $cellSize, $value, 0, 2, 'L');

                    //Pega o maior tamanho para alinhar a etiqueta interna na mesma etiqueta
                    $tam = $this->GetStringWidth($value);
                    if ($tamMax < $tam )
                    {
                        $tamMax = $tam;
                    }
                }
            }
            
            $itext = array();

            if (in_array($data->internalLabel, array(OPTION_ILABEL_YES_SAME, OPTION_ILABEL_YES_DIFF, OPTION_ILABEL_ONLY)))
            {
                $itext = explode("\n", $gf->interpret($format->internalFormat, false));
            }
            
            if (count($itext)) //Se tiver etiqueta interna desenha a etiqueta interna
            {
            	//calcula a largura certa para cada linha da etiqueta interna
                $internalLabel = array();
            	foreach ($itext as $line)
            	{
	            	//pega o tamanho da string
	                $stringWidth = $this->getStringWidth( $line );
	                $labelWidth  = $data->labelWidth;
	
	                //se o tamanho do texto for maior que o tamanho permitido pela etiqueta
	                if ( $stringWidth > $labelWidth )
	                {
	                    $temp = $this->lineBreak($line, $stringWidth, $labelWidth);
	                    //e adiciona todas elas ao novo conteúdo
	                    foreach ( $temp as $y )
	                    {
	                        $internalLabel[] = $y;
	                    }
	                }
	                else
	                {
	                    //só adiciona a linha normal
	                    $internalLabel[] = $line;
	                }
            	}   
            	
                if ($data->internalLabel == OPTION_ILABEL_YES_SAME)
                {
                      $this->setXY($this->x+$tamMax+0.2, $yInicial);
                }
                else if ($data->internalLabel == OPTION_ILABEL_YES_DIFF)
                {
                      $this->checkBreakLine();
                      $this->checkBreakPage();
                      $this->setBeginPositionOfTheLabel();
                }

                foreach ($internalLabel as $text)
                {

                    $this->Cell($this->x, $cellSize, $text, 0, 2, 'L');
                }
            }
        }

        $this->setFilename( 'backofbook_' . date('Ymd') . '.pdf' );
        $this->generate(false);
    }
   
    /**
     * Método para quebar a linha conforme o limite de caractéres
     * 
     * @param $string a quebrar
     * @param limite de caractéres
     * @return array
     */
    private function lineBreak($string, $stringSize, $limit)
    {
    	if ( strlen($stringSize) == 0 )
    	{
    		$stringSize = $this->getStringWidth( $string );
    	}

        //calcula o tamanho certo
        $cutLimit = ( strlen( $string ) * $limit ) / $stringSize;
        
        //quebra em linhas
        $lines = explode( "\n" , wordwrap( $string, $cutLimit ) );
    	
        return $lines;
    }
}
?>