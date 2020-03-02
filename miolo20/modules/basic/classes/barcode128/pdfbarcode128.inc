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
 *
 * This class 
 *
 * @author GuinuX <guinux@cosmoplazza.com>
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 * Arthur Lehdermann [arthur@solis.coop.br]
 * Daniel Afonso Heisler [daniel@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Leovan Tavares da Silva [leovan@solis.coop.br]
 * Samuel Koch [samuel@solis.coop.br]
 * William Prigol Lopes [william@solis.coop.br]
 * 
 * @since
 * Class created on 30/05/2002
 *
 **/
 
	class pdfbarcode128 extends barcode128 {
		var $_pdf;
		var $_char_width;
		var $_bar_width;
		var $_data;
		
		function pdfbarcode128( $data, $char_width ) {
			$this->_bar_width = round($char_width/11, 1);
			$this->_char_width = $this->_bar_width * 11;
			$this->_data = $data;
			$this->barcode128($data);
		}
		
		function get_width() {
			return barcode128::get_width($this->_char_width);
		}
	
		function set_pdf_document( &$pdf ) {
			$this->_pdf = &$pdf;
		}
		
		function _draw_line( $x, $y, $nb, $height, $bar ) {
			if ($bar) {
				$this->_pdf->SetDrawColor(0, 0, 0);
				$this->_pdf->SetFillColor(0, 0, 0);
			} else {
				$this->_pdf->SetDrawColor( 255, 255, 255);
				$this->_pdf->SetFillColor( 255, 255, 255);
			}
			$this->_pdf->SetLineWidth(0.000);
			$this->_pdf->Rect( $x, $y, $nb*$this->_bar_width, $height, 'FD');
		}
	
		function draw_barcode( $x, $y, $bar_height, $print_text = false ) {
			$start_x = $x;
			$start_y = $y;
			
			$this->_compute_pattern();
			
			// Quiet Zone
			$this->_pdf->SetLineWidth(0.0000);
			$this->_pdf->SetDrawColor(255, 255, 255);
			$this->_pdf->SetFillColor(255, 255, 255);
			$this->_pdf->Rect($x, $y, $this->_bar_width*10, $bar_height, 'FD');
			$x += $this->_bar_width*10;

			foreach($this->_pattern as $pos=>$digit) {
				$digit = split( ' ', $digit);
				$bar = true;
				foreach ($digit as $tmp=>$nb) {
					$this->_draw_line( $x, $y, $nb, $bar_height, $bar );
					$x += ($this->_bar_width*$nb)+0.05;
					$bar = ($bar==true?false:true);
				}
			}

			// Quiet Zone
			$this->_pdf->SetLineWidth(0.0000);
			$this->_pdf->SetDrawColor(255, 255, 255);
			$this->_pdf->SetFillColor(255, 255, 255);
			$this->_pdf->Rect($x, $y, $this->_bar_width*10, $bar_height, 'FD');
			$x += $this->_bar_width*10;

			if ($print_text) {
				$font_size = floor($this->get_width() / strlen($this->_data)*1.5);
				//$this->_pdf->SetFont('arial', '', $font_size);
				$this->_pdf->SetXY( $start_x, $start_y + $bar_height );
				$this->_pdf->Cell( $x - $start_x, $font_size/2, $this->_data, 0, 0, 'C' );
			}
		}
	} // End of Class
	
?>
