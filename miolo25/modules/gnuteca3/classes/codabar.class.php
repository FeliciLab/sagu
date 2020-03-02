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
 * Class
 *
 * @author Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 26/01/2007
 *
 **/

class codabar
{
	var $bar;
    var $code;
    var $_codeset;
    var $_pattern;

    function codabar($code)
    {
    	$this->code = $code;

        //Relação dos números com o binário.
        //Padrão Codabar - Cbr
        $bar[0] = 'NnNnNwW';
        $bar[1] = 'NnNnWwN';
        $bar[2] = 'NnNwNnW';
        $bar[3] = 'WwNnNnN';
        $bar[4] = 'NnWnNwN';
        $bar[5] = 'WnNnNwN';
        $bar[6] = 'NwNnNnW';
        $bar[7] = 'NwNnWnN';
        $bar[8] = 'NwWnNnN';
        $bar[9] = 'WnNwNnN';
        $bar['-'] = 'NnNwWnN';
        $bar['$'] = 'NnWwNnN';
        $bar[':'] = 'WnNnWnW';
        $bar['/'] = 'WnWnNnW';
        $bar['.'] = 'WnWnWnN';
        $bar['+'] = 'NnWnWnW';
        $bar[A] = 'NnWwNwN';
        $bar[B] = 'NwNwNnW';
        $bar[C] = 'NnNwNwW';
        $bar[D] = 'NnNwWwN';
        $this->bar = $bar;
    }


    function _compute_pattern()
    {
    	$code = $this->code;
    	$bar  = $this->bar;
        $bin  = $bar[A] . 'n';//o n adiciona um espaço entre os caractes

        //forma o código conforme tabela acima:
        for($p = 0; $p < strlen($code); $p++)
        {
            $bin = $bin . $bar[substr($code,$p,1)] . 'n';
        }

        //Fim do Código
        $bin = $bin . $bar[B];

        if ($bin)
        {
            for ($c=0;$c<strlen($bin);$c++)
            {
                switch (substr($bin,$c,1))
                {
                    case 'W':
                        $largura = 3;
                        $barra = true;
                        break;
                    case 'N':
                        $largura = 1;
                        $barra = true;
                        break;
                    case 'w':
                        $largura = 3;
                        $barra = false;
                        break;
                    case 'n':
                        $largura = 1;
                        $barra = false;
                        break;
                }
                $this->_pattern[$c] = $largura;
            }
        }
    }


    function get_pattern()
    {
        return array(implode(' ', $this->_pattern));
    }


    function _dump_pattern()
    {
        header('Content-Type: text/plain');
        print_r($this->_pattern);
    }


    function getWidth( $barWidth )
    {
        $width = 0;
        foreach ($this->get_pattern() as $digit)
        {
            $digit = split( ' ', $digit);
            foreach ($digit as $n)
            {
                $width += ($n*$barWidth)+0.005;
            }
        }
        
        return $width;
    }
    
    /**
     * Gera uma imagem com o código de barras
     * 
     * @param numeric $columnWidth largura da coluna
     * @param numeric $columnHeight altura da coluna
     * @param int $scale escala da largura
     * @param String $outputPath path onde será gerada a imagem
     */
    public function output($columnWidth, $columnHeight, $scale = 1, $outputPath)
    {
        $this->_compute_pattern();
        
        //Gerar as barras
        foreach ($this->get_pattern() as $digit)
        {
            $digit = split( ' ', $digit);
            $total = array_sum($digit);
            $total = ($total * $columnWidth) * 38 * $scale; //largura total ncm * 38 * escala

            //O GD trabalha em pixels, é necessário converter todas medidas para pixels
            // 1cm -> 38pixels

            $img = ImageCreate($total,($columnHeight * 38 * $scale)); //cria a imagem
            $black = ImageColorAllocate($img, 0, 0, 0); //cor preta
            $white = ImageColorAllocate($img, 255, 255, 255); //cor branca

            $widht = ($columnWidth * 38 * $scale); //largura da coluna
            $height = ($columnHeight * 38 * $scale); //altura da coluna

            $x1 = 0;
            $y1 = 0;

            $x2 = $widht + $x1;
            $y2 = $height;

            $bar = true;
            
            foreach ($digit as $n)
            {
                $color = $bar ? $black : $white; //define se cor será preta ou branca
                $x2 = $x1 + ($widht * $n);
                
                ImageFilledRectangle($img, $x1, $y1, $x2, $y2, $color); //cria as barras

                $x1 = $x2;
                $bar = !$bar;
            }
        }

        ImagePNG($img, $outputPath);  //gera a imagem
    }
    
    
}

?>
