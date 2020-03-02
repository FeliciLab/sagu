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
 * @author Tiago Gossmann [tiagog@solis.coop.br]
 *
 * $version: $Id$
 *
 * \b Maintainers \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 02/07/2007
 *
 **/
$MIOLO->uses('classes/GPDF.class.php','gnuteca3');

class GPDFLabel extends GPDF
{
    public $x, $y, $column, $line, $labelLayout;

    public function __construct($labelLayout)
    {
        $this->labelLayout = $labelLayout;
        $this->column      = 0;
        $this->line        = 0;

        $pFormat = $this->labelLayout->pageFormat;
        
        if (strtolower($pFormat) == 'automatic')
        {
        	$pFormat = 'A4';
        }

        parent::__construct(null, 'P', 'cm', $pFormat);
        //Ajusta a margem esquerda e direita
        $this->setMargins($this->labelLayout->leftMargin, $this->labelLayout->topMargin, 0);
        $this->setAutoPageBreak(false, 0);
        $this->addPage();
    }


    public function checkBreakLine()
    {
        if ($this->column >= $this->labelLayout->columns)
        {
            $this->addBreakLine();
            return true;
        }
        
        return false;
    }


    public function addBreakLine()
    {
        $this->column = 0;
        $this->line++;
    }


    public function checkBreakPage()
    {
        if ($this->line >= $this->labelLayout->lines)
        {
            $this->line = 0;
            $this->column = 0;
            $this->addPage();
            return true;
        }
        return false;
    }

    public function setBeginPositionOfTheLabel()
    {
        $this->x = $this->labelLayout->leftMargin+($this->labelLayout->horizontalSpacing*$this->column);
        $this->y = $this->labelLayout->topMargin+($this->labelLayout->verticalSpacing*$this->line);
        $this->setXY($this->x, $this->y);

        $this->column++;
    }

    public function setBeginLabel($beginLabel)
    {
        $this->column = (int) ($beginLabel%($this->labelLayout->columns))-1;
        $this->line   = (int) ($beginLabel/($this->labelLayout->columns));
        if ($this->column < 0)
        {
            $this->column = $this->labelLayout->columns-1;
            $this->line--;
        }
    }

    public function lengthLabel()
    {
        $w = $this->labelLayout->labelWidth;
        $h = $this->labelLayout->labelHeight;

        return array($w, $h);
    }
}
?>