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
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 31/05/2007
 *
 **/
class rptBarCode extends GPDFLabel
{
    public $MIOLO, $module;

    const BARCODE_FORMAT_EXEMPLARY = 1;
    const BARCODE_FORMAT_PERSON = 2;
    const BARCODE_FORMAT_OTHER = 3;
    
    
    function __construct($labelLayout, $data, $codes)
    {

        $this->MIOLO = MIOLO::getInstance();
        $this->module = $this->MIOLO->getCurrentModule();
        
        $logoPath = BusinessGnuteca3BusFile::getAbsoluteFilePath('images', 'logo', 'jpg' ) ;

        parent::__construct($labelLayout);
        $this->setBeginLabel($data->beginLabel ? $data->beginLabel : 1);

        // Início da configuração do relatório
        $this->setSubject(_M('Código de barras', $this->module));
        $this->setTitle(_M('Código de barras', $this->module));
        $this->setFont('Arial', '', $data->fontSize ? $data->fontSize : 10);
        
        $size = $data->size; // Tamanho do codigo de barras
        
        $fontHeight = $data->fontSize / 20; //tamanho da altura da célula

        //lista variáveis digitadas no texto do código de barras
        $marcVariables = GUtil::extractMarcVariables( $data->text );
        //utilizado para obter dados do exemplar
        $busExemplaryControl =  $this->MIOLO->getBusiness('gnuteca3', 'BusExemplaryControl');
        $busMaterial =  $this->MIOLO->getBusiness('gnuteca3', 'BusMaterial');

        foreach ( $codes as $itemNumber )
        {
            $exemplary = $busExemplaryControl->getExemplaryControl( $itemNumber ,false);
            
            //caso tenha número de controle obtem as varíaveis do material
            $controlNumber = '';
            
            if ( $exemplary->controlNumber )
            {
                $controlNumber = $exemplary->controlNumber;
            }
            
            //texto simples passado no form
            $text = $data->text;
                
            //caso tenha variáveis e for formatação de exemplar, interpreta
            if ( is_array( $marcVariables ) && $data->formatType == self::BARCODE_FORMAT_EXEMPLARY )
            {
                $gFunc = new GFunction();
                
                foreach ( $marcVariables as $line => $variable )
                {
                    $tag = GString::construct($variable)->replace('$', ''); //tira $ da frente
                    
                    $content = '';
                    
                    $tag = $tag->getString();
                    
                    if ( $controlNumber )
                    {
                        if ( strpos($tag, '949') !== false )
                        {
                            $line = $exemplary->line;
                        }
                        else
                        {
                            $line = 0;
                        }
                                                
                        $content = $busMaterial->getContentTag( $controlNumber , $tag, $line );
                    }
                    
                    $gFunc->setVariable( $variable, $content );
                }
                
                $text = $gFunc->interpret( $data->text );
            }
            //Caso seja formatação de código de barras para pessoa
            elseif ( $data->formatType == self::BARCODE_FORMAT_PERSON )
            {
                $busPerson = $this->MIOLO->getBusiness('gnuteca3', 'BusPerson');
                $person= $busPerson->getPerson($itemNumber);

                //Para cada atributo da classe que estiver na PHOTO_URL 
                foreach ( $person as $attrib => $value)
                {
                    if ( !is_object($value) )
                    {
                        $text = str_replace('$'.$attrib,$value, $text);
                    }
                }                
            }

            $this->checkBreakLine();
            $this->checkBreakPage();

            //Coloca o ponteiro para desenhar a etiqueta
            $this->setBeginPositionOfTheLabel();

            //Largura e altura da etiqueta
            list ($w, $h) = $this->lengthLabel();

            $x2 = $x = $this->x;
            $y2 = $y = $this->y;
            
            //Escreve o texto, se tiver
            if ($data->text)
            {
                $this->cell($w, $fontHeight, $text, 0, 0, 'C');
                
                $text = '';
                
            }

            //Chama classe de geracao de codigo barra (a informacao do nome da classe vem do form FrmBarCode)
            $className = $data->type;

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
            
            $barcode = new $className( $itemNumber );
            $barcode->_compute_pattern();

            $cellSize = 0.03;
            
            //tamanho que a barra deveria ocupar, sem considerar o espaço da foto
            $fixedBarWidth = $cellSize * $size;

            if ( $data->logo )
            {
                $cellSize = 0.015;
            }
            
            //tamanho da barra
            $barWidth = $cellSize * $size;
            
            //diferença entre o tamanho proposto e o tamanho real
            $widthDiff = $barcode->getWidth($fixedBarWidth)  - $barcode->getWidth($barWidth);
            
            $barHeight = 0.5;
            $barH = $h*$barHeight;

            //Executa calculos para centralizar o código de barras
            $centerBarY = ($h-$barH)/2;
            $centerBarX = ($w-$barcode->getWidth( $fixedBarWidth ))/2;

            //Descrição
            $this->SetXY( $this->x, $this->y+$centerBarY-0.5 );
            
            if ( $data->logo )
            {
                $this->Image( $logoPath, $centerBarX +$x , $centerBarY+$y, $barH, $barH );
            }
            
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
                    $this->Rect( $centerBarX + $x + $widthDiff, $centerBarY+$y, $n*$barWidth, $barH, 'FD' );
                    $x += ($n*$barWidth)+0.005;
                    $bar = !$bar;
                }
            }
            
            //Escreve o código
            $this->SetXY($x2+( $widthDiff / 2 ), $y2+$centerBarY+$barH);
            $this->Cell($w, 0.5, $itemNumber, 0, 0, 'C');
        }

        $this->setFilename('barcode_'.date('Ymd').'.pdf');
        $this->generate(false);
    }
}
?>
