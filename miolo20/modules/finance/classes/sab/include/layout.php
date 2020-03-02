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
 * Class PDF
 *
 * @author Leovan Tavares da Silva [leovan] [leovan@solis.coop.br]
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
 * Class created on 14/06/2006
 *
 **/

$fpdf_fontpath = 'fpdf153/font/';
if ( $MIOLO->getConf("home.miolo") )
{
    $fpdf_fontpath = $MIOLO->getConf("home.miolo").'/modules/finance/classes/sab/include/'.$fpdf_fontpath;
}

define('FPDF_FONTPATH', $fpdf_fontpath);

require_once('fpdf153/fpdf.php');
require_once('barCode.class');

class PDF extends barCode
{
    //
    public function footer()
    {

    }

    //
    public function header()
    {

    }
}

class sabLayout
{
    private $pdf;
    private $sab;
    private $bodyPosition;
    private $outputType = 'I';
    private $fileName = 'boleto.pdf';

   /*
    * Constructor of sabLayout, call all functions needed to generate the invoice and returns a file containing the requested invoice
    *
    * @params: $sab (object sabStruct, see sabStruct on core.php) Contains the information about the structure.
    *
    * @returns: void, save a file or modify http headers to return a file containing the invoice if works
    *
    */
    public function __construct(sabStruct $sab, fieldsStruct $fields)
    {
        $this->pdf    = new PDF('P', 'mm', 'A4');
        $this->sab    = $sab;
        $this->fields = $fields;
        $this->pdf->aliasNbPages();
        $this->pdf->addPage('P');
        $this->pdf->setMargins('10', '2');
        if ($this->sab->getNumberOfBodies()>0)
        {
            for ($this->bodyPosition = 1; $this->bodyPosition<=$this->sab->getNumberOfBodies(); $this->bodyPosition++)
            {
                $this->generateInvoiceBody();
            }
        }
    }
    
   /*
    * Generate pdf output according to class attributes
    */
    public function generate()
    {
        $this->pdf->output($this->fileName, $this->outputType);
        
        // Corrige problema que ocorria em #26250, onde a impressao de boletos funcionava na primeira vez, mas apos a segunda em diante um erro de saida era ocasionado
        if ( $this->outputType == 'D' )
        {
           exit;
        }
    }

   /*
    * Set the FPDF's output type to change for a local file, or inline browser
    *
    * @param $type (char): F for local file, D for browser download
    *
    * @returns: (void) Nothing
    */
    public function setOutputType($type)
    {
        if (in_array($type, array('F', 'I', 'D')))
        {
            $this->outputType = $type;
        }
    }
    
    /**
     * Limpa o tipo de output para não executar o download do arquivo.
     */
    public function clearOutputType()
    {
        unset($this->outputType);
    }

    /*
     * Sets the file name for output
     *
     * @param $name (varchar): Name for file
     *
     * @return (void) Nothing
     */
    public function setFileName($name)
    {
        if (strlen($name)>0)
        {
            $this->fileName = $name;
        }
    }
    
    /*
     * Get the invoice image information
     *
     * @param: No parameters needed
     *
     * @returns: The path to image file
     *
     */
    private function getFileName()
    {
    	return $this->fileName;
    }

    /*
     * Get the invoice image information
     *
     * @param: No parameters needed
     *
     * @returns: The path to image file
     *
     */
    private function getLayoutImagePath()
    {
        $MIOLO = MIOLO::getInstance();
        $path = 'layouts/l'.$this->sab->getBankCode().'/layoutImage.png';
        if ( $MIOLO->getConf("home.miolo") )
        {
            $path = $MIOLO->getConf("home.miolo").'/modules/finance/classes/sab/layouts/l'.$this->sab->getBankCode().'/layoutImage.png';
        }
        return $path;
    }

    
    /*
     * Generate the invoice body structure with specific information
     *
     * @param: No parameters needed
     *
     * @returns: Nothing
     *
     */
    public function generateInvoiceBody()
    {       
        $x = $this->pdf->getX();
        $y = $this->pdf->getY();
        $this->pdf->image($this->getLayoutImagePath(), $x, $y-2, '33,92', '8,28');
        $this->pdf->setFillColor(181, 181, 181);
        $this->pdf->setFont('Arial', 'B', '16');
        $this->pdf->cell(60, 5, '| '.$this->sab->getBankCode().'-'.$this->sab->getBankDV().' |', '', '', 'R');
        $this->pdf->setFont('Arial', 'B', '10');
        $headerData = $this->sab->getHeaderInfo($this->bodyPosition);
        if (is_object($headerData))
        {
            if ($headerData->type == 'TEXT')
            {
                $this->pdf->cell(122, 7, $headerData->text, '','','R');
            }
            elseif ($headerData->type == 'DIGITABLE NUMBER')
            {
                $this->pdf->cell(122, 7, $this->fields->getDigitableNumber(), '', '', 'R');
            }
        }
        else
        {
            $this->pdf->cell(105, 7, '', '', '', '');
        }
        $this->pdf->cell(105, 7, '', '', '', 'R');
        $this->pdf->ln();
        $this->pdf->cell(182, 1, ' ', 'T');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 5);
        $this->pdf->cell(138, 2.4, _('Local de pagamento'), 'LR');
        $this->pdf->cell(44,  2.4, _('Vencimento'),         'LR');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 8);
        $this->pdf->cell(138, 2.95, $this->sab->getPaymentPlaceDescription(),   'LR');
        $this->pdf->cell(44,  2.95, $this->sab->getInvoiceMaturityDate().'        ', 'LR', 0, 'R');
        $this->pdf->ln();
        $this->pdf->cell(182, 1, ' ', 'T');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 5);
        $this->pdf->cell(138, 2.4, _('Cedente'),                       'LR');
        $this->pdf->cell(44,  2.4, _('Agência/Cód. Cedente'),          'LR');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 8);
        $this->pdf->cell(138, 2.95, $this->sab->getTransferorName().' - '.$this->sab->getTransferorCnpj(), 'LR');

        if ( (strlen($this->sab->getTransferorBankAccountDV()) != 0) && ($this->sab->getBankCode() != '104') )
        {
            $bankAccount = $this->sab->getTransferorBankAccount() . '-' . $this->sab->getTransferorBankAccountDV();
        }
        else
        {
            $bankAccount = $this->sab->getTransferorBankAccount();
        }

        if ( $this->sab->getBankCode() == '104' )
        {
            $this->pdf->cell(44,  2.95, $bankAccount.$this->sab->getTransferorCode().'-'.$this->sab->getTransferorCodeDv(), 'LR', 0, 'R');
        }
        else
        {
            $this->pdf->cell(44,  2.95, $bankAccount.' / '.$this->sab->getTransferorCode().'-'.$this->sab->getTransferorCodeDv(), 'LR', 0, 'R');
        }

        $this->pdf->ln();
        $this->pdf->cell(182, 1, ' ', 'T');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 5);
        $this->pdf->cell(26, 2.4, _('Data documento'),     'LR');
        $this->pdf->cell(45, 2.4, _('No. do documento'),   'LR');
        $this->pdf->cell(28, 2.4, _('Espécie DOC'),        'LR');
        $this->pdf->cell(13, 2.4, _('Aceite'),             'LR');
        $this->pdf->cell(26, 2.4, _('Data processamento'), 'LR');
        $this->pdf->cell(44, 2.4, _('Nosso número'),       'LR');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 8);
        $this->pdf->cell(26, 2.95, $this->sab->getInvoiceDate(),          'LR', 0, 'C');
        $this->pdf->cell(45, 2.95, $this->sab->getInvoiceNumber(),        'LR');
        $this->pdf->cell(28, 2.95, $this->sab->getInvoiceKind(),          'LR', 0, 'C');
        $this->pdf->cell(13, 2.95, $this->sab->getInvoiceAccepted(),      'LR', 0, 'C');
        $this->pdf->cell(26, 2.95, $this->sab->getInvoiceProcessDate(),   'LR', 0, 'C');

        if ( (($this->sab->getBankCode() == '104') || ($this->sab->getBankCode() == '237') || ($this->sab->getBankCode() == '341')) && ($this->sab->getBankCodeAux() != '320') )
        {
            $idWallet = $this->sab->getIsRegister();
            $idWallet .= $this->sab->getSentTransferor();
            if ( ($this->sab->getBankCode() == '237') || ($this->sab->getBankCode() == '341') )
            {
                $idWallet = $this->sab->getInvoiceWallet();
            }

            if ( $this->sab->getBankCode() == '104' )
            {
                $this->pdf->cell(44, 2.95, $idWallet . $this->sab->getInvoiceOurNumber() . '-' . $this->sab->getInvoiceOurNumberDv(), 'LR', 0, 'R');
            }
            else
            {
                $this->pdf->cell(44, 2.95, $idWallet . ' / ' . $this->sab->getInvoiceOurNumber() . '-' . $this->sab->getInvoiceOurNumberDv(), 'LR', 0, 'R');
            }
        }
        else if ( ($this->sab->getBankCode() == '001') && (strlen($this->sab->getTransferorAgreement()) == 7) )
        {
            $this->pdf->cell(44, 2.95, $this->sab->getTransferorAgreement().$this->sab->getInvoiceOurNumber(), 'LR', 0, 'R');
        }
        else if ( $this->sab->getBankCodeAux() == '320' )
        {
            $this->pdf->cell(44, 2.95, $this->sab->getInvoiceWallet() . ' / ' . $this->sab->getTransferorAgreement() . $this->sab->getInvoiceOurNumber() . '-' . $this->sab->getInvoiceOurNumberDv(), 'LR', 0, 'R');
        }
        else
        {
            $this->pdf->cell(44, 2.95, $this->sab->getInvoiceOurNumber(), 'LR', 0, 'R');
        }

        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 5);
        $this->pdf->cell(182, 1, ' ',                      'T');
        $this->pdf->ln();
        $this->pdf->cell(26,  2.4, _('Uso do Banco'),           'LR');
        $this->pdf->cell(22,  2.4, _('Carteira'),               'LR');
        $this->pdf->cell(23,  2.4, _('Espécie'),                'LR');
        $this->pdf->cell(41,  2.4, _('Quantidade'),             'LR');
        $this->pdf->cell(26,  2.4, _('Valor'),                  'LR');
        $this->pdf->cell(44,  2.4, _(' ( = ) Valor do Documento'), 'LR');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 8);
        if ($this->sab->checkSpecialFields())
        {
             $this->pdf->cell(26, 2.95,' ', 'LR');
        }
        else
        {
            $this->pdf->cell(26, 2.95,' ', 'LR');
        }
        
        if ( $this->sab->getBankCode() == '104' )
        {
            $this->pdf->cell(22, 2.95, 'SR', 'LR', 0, 'C');
        }
        else
        {
            if ( $this->sab->getBankCode() == '001' && $this->sab->getInvoiceWalletModel())
            {
                $this->pdf->cell(22, 2.95, $this->sab->getInvoiceWallet().'-'.$this->sab->getInvoiceWalletModel(), 'LR', 0, 'C');
            }
            else
            {
                $this->pdf->cell(22, 2.95, $this->sab->getInvoiceWallet(), 'LR', 0, 'C');
            }
        }
        
        $this->pdf->cell(23, 2.95, $this->sab->getMoneyKind(),       'LR', 0, 'C');
        $this->pdf->cell(41, 2.95, $this->sab->getInvoiceQuantity(), 'LR');
        $this->pdf->cell(26, 2.95, '',                               'LR');
        $this->pdf->setFont('Arial', 'B', 8);
        $this->pdf->cell(44, 2.95, $this->sab->getFormattedInvoiceValue().' ', 'LR', 0, 'R');
        $this->pdf->ln();
        $this->pdf->cell(182, 1, ' ', 'T');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 5);
        $this->pdf->cell(138, 2.4, _('Instruções '), 'LR');
        $this->pdf->cell(1, 2.4,   ' ', '');
        $this->pdf->cell(43, 2.4,  _(' ( - ) Desconto/Abatimento'), 'LRT');
        $this->pdf->ln();
        $fMessagePos[0]->x = $this->pdf->getX();
        $fMessagePos[0]->y = $this->pdf->getY();
        $this->pdf->multiCell(138, 2.95, implode("\r\n", $this->sab->getInvoiceMessage()), 'LR');
        $this->pdf->sety($fMessagePos[0]->y);
        $this->pdf->setx($fMessagePos[0]->x+138);
        $this->pdf->cell(1, 2.4,   ' ', '');
        $this->pdf->cell(43, 2.95, '', 'LRB');
        $this->pdf->ln();
        $this->pdf->cell(138, 3.3, ' ', 'LR');
        $this->pdf->cell(1, 3.3,   ' ', '');
        $this->pdf->cell(43, 3.3,  _(' ( - ) Outras deduções'), 'LRT');
        $this->pdf->ln();
        $fMessagePos[1]->x = $this->pdf->getX();
        $fMessagePos[1]->y = $this->pdf->getY();
        $this->pdf->cell(138, 2.95, ' ', 'LR');
        $this->pdf->cell(1, 2.95,'', '');
        $this->pdf->cell(43, 2.95, '', 'LRB');
        $this->pdf->ln();
        $this->pdf->cell(138, 3.3, ' ' , 'LR');
        $this->pdf->cell(1, 3.3, ' ', '');
        $this->pdf->cell(43, 3.3, _(' ( + ) Mora e Multa'), 'LRT');
        $this->pdf->ln();
        $fMessagePos[2]->x = $this->pdf->getX();
        $fMessagePos[2]->y = $this->pdf->getY();
        $this->pdf->cell(138, 2.95, ' ', 'LR');
        $this->pdf->cell(1, 2.95,'', '');
        $this->pdf->cell(43, 2.95, '',  'LRB');
        $this->pdf->ln();
        $this->pdf->cell(138, 3.3, ' ', 'LR');
        $this->pdf->cell(1, 3.3, ' ', '');
        $this->pdf->cell(43, 3.3, _(' ( + ) Outros acréscimos'), 'LRT');
        $this->pdf->ln();
        $fMessagePos[3]->x = $this->pdf->getX();
        $fMessagePos[3]->y = $this->pdf->getY();
        $this->pdf->cell(138, 2.95, ' ', 'LR');
        $this->pdf->cell(1, 2.95,' ', '');
        $this->pdf->cell(43, 2.95,' ',  'LRB');
        $this->pdf->ln();
        $this->pdf->cell(138, 3.3, ' ', 'LR');
        $this->pdf->cell(1, 3.3, ' ', '');
        $this->pdf->cell(43, 3.3, _(' ( = ) Valor cobrado'), 'LRT');
        $this->pdf->ln();
        $fMessagePos[4]->x = $this->pdf->getX();
        $fMessagePos[4]->y = $this->pdf->getY();
        $this->pdf->cell(138, 2.95,' ', 'LRB');
        $this->pdf->cell(1, 2.95,'', '');
        $this->pdf->cell(43, 2.95,'', 'LRB');
        $this->pdf->ln();
        $this->pdf->cell(182, 1, ' ', '');
        $this->pdf->ln();
        $this->pdf->cell(182, 2.4, _('Sacado'), 'LR');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 8);
        $this->pdf->cell(182, 2.95, $this->sab->getClientName(), 'LR');
        $this->pdf->ln() ;
        $this->pdf->cell(182, 2.95, $this->sab->getClientAddress(), 'LR');
        $this->pdf->ln();
        $this->pdf->cell(182, 2.95, $this->sab->getClientCityState(), 'LR');
        $this->pdf->ln();
        $this->pdf->setFont('Arial', '', 5);
        $this->pdf->cell(152, 2.95,_('Sacador/Avalista'), 'LB');
        $this->pdf->cell(30, 2.95, _('Código de baixa'), 'BR');
        $this->pdf->ln();
        if (is_object($headerData))
        {
            if ($headerData->type == 'TEXT')
            {
                $this->pdf->cell(40,  2.4, _('Recebimento através do cheque no.'));
                $this->pdf->cell(80, 2.4, ' ');
                $this->pdf->cell(20, 1.15, ' ', 'B');
                $this->pdf->cell(22, 2.4, _('Autenticação mecânica'), 0, 0, 'C');
                $this->pdf->cell(20, 1.15, ' ', 'B');
                $this->pdf->ln(2.4);
                $this->pdf->cell(182, 2.4, _('Do Banco'));
                $this->pdf->ln(2.4);
                $this->pdf->cell(182, 2.4, _('Esta quitação só terá validade após o pagamento do cheque pelo banco sacado.'));
                $this->pdf->ln(2.4);
                $this->pdf->cell(182, 2.4, _('Até o vencimento pagável em qualquer agência bancária.'));
                $this->pdf->ln(2.4);
            }
            else
            {
                $this->pdf->setFont('Arial', '', 7);
                //Foi comentada a linha que imprime o código de barras convertido em números
                //$this->pdf->cell(140, 2.4, $this->fields->getBarCodeNumber(), 0);
                $this->pdf->cell(140, 2.4, ' ', 0);
                $this->pdf->setFont('Arial', '', 5);
                $this->pdf->cell(42,  2.4, _('Autenticação mecânica/FICHA DE COMPENSAÇÃO'), 0);
                $this->pdf->setFillColor('#000000');
                $this->pdf->i25(10, 278, $this->fields->getBarCodeNumber(), 0.8, '13');
                $this->pdf->ln();
            }
        }
        else
        {
            $this->pdf->ln();
        }
        if ($this->bodyPosition<$this->sab->getNumberOfBodies())
        {
            $this->pdf->cell(182, 2, '- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -');
            $this->pdf->ln();
        }
        $this->pdf->ln();
    }
}
?>
