<?
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# @title
#   Barcode
#
# @description
#   Barcode generation classes 
#
# @topics   contributions
#
# @created
#   2001/08/15
#
# @organisation
#   UNILASALLE
#
# @legal
#   UNILASALLE
#   CopyLeft (L) 2001-2002 UNILASALLE, Canoas/RS - Brasil
#   Licensed under GPL (see COPYING.TXT or FSF at www.fsf.org for
#   further details)
#
# @contributors
#   Rudinei Pereira Dias     [author] [rudinei@lasalle.tche.br]
# 
# @maintainers
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#
# @history
#   $log: barcode.class,v $
#   Revision 1.1  2002/10/03 19:14:05  vgartner
#   Added barcode class.
#
#
# @id $id: barcode.class,v 1.1 2002/10/03 19:14:05 vgartner Exp $
#---------------------------------------------------------------------

#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# Rotina para a geraÃ§Ã£o de CÃ³digo de Barra
# no padrÃ£o Interleved 2 of 5 (Intercalado 2 de 5)
# utilizado para os documentos bancÃ¡rios conforme
# padrÃ£o FEBRABAN.
# UNILASALLE
#---------------------------------------------------------------------
class BarcodeI25
{
    //Public properties
    public $codigo;       //SET: CÃ³digo a converter em cÃ³digo de barras
    public $ebf;          //SET: Espessura da barra fina: usar 1 atÃ© 2.
    public $ebg;          //SET: Espessura da barra grossa: usar 2x a 3x da esp_barra_fn.
    public $altb;         //SET: altura do cÃ³digo de barras
    public $ipp;          //SET: EndereÃ§o completo da imagem do ponto PRETO p/compor o cÃ³digo de barras
    public $ipb;          //SET: EndereÃ§o completo da imagem do ponto BRANCO p/compor o cÃ³digo de barras
    public $tamanhoTotal; //Propriedade de RETORNO do tamanho total da imagem do cÃ³digo de barras

    //Private properties
    public $mixed_code;
    public $bc = array(
        );

    public $bc_string;

    public function barcodeI25($code = '')
    {
        //Construtor da classe
        $this->ebf = 1;
        $this->ebg = 3;
        $this->altb = 50;
        $this->ipp = "images/ponto_preto.gif";
        $this->ipb = "images/ponto_branco.gif";
        $this->mixed_code = "";
        $this->bc_string = "";
        $this->tamanhoTotal = 0;

        if ($code !== '')
        {
            $this->setCode($code);
        }
    }

    public function setCode($code)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->assert(strlen($code) > 0, "CÃ³digo de Barras nÃ£o informado. (Barcode Undefined)");

        $MIOLO->assert(!(strlen($code) % 2), "Tamanho invÃ¡lido de cÃ³digo. Deve ser mÃºltiplo de 2.");

        $this->codigo = $code;
    }

    public function getCode()
    {
        return $this->codigo;
    }

    public function generate()
    {
        $this->codigo = trim($this->codigo);

        $th = "";
        $new_string = "";
        $lbc = 0;
        $xi = 0;
        $k = 0;
        $this->bc_string = $this->codigo;

        //define barcode patterns
        //0 - Estreita    1 - Larga
        //Dim bc(60) As String   Obj.DrawWidth = 1

        $this->bc[0] = "00110"; //0 digit
        $this->bc[1] = "10001"; //1 digit
        $this->bc[2] = "01001"; //2 digit
        $this->bc[3] = "11000"; //3 digit
        $this->bc[4] = "00101"; //4 digit
        $this->bc[5] = "10100"; //5 digit
        $this->bc[6] = "01100"; //6 digit
        $this->bc[7] = "00011"; //7 digit
        $this->bc[8] = "10010"; //8 digit
        $this->bc[9] = "01010"; //9 digit
        $this->bc[10] = "0000"; //pre-amble
        $this->bc[11] = "100";  //post-amble

        $this->bc_string = strtoupper($this->bc_string);

        $lbc = strlen($this->bc_string) - 1;

        //Gera o cÃ³digo com os patterns
        for ($xi = 0; $xi <= $lbc; $xi++)
        {
            $k = (int)substr($this->bc_string, $xi, 1);
            $new_string = $new_string . $this->bc[$k];
        }

        $this->bc_string = $new_string;

        //Faz a mixagem do CÃ³digo
        $this->mixCode();

        $this->bc_string = $this->bc[10] . $this->bc_string . $this->bc[11]; //Adding Start and Stop Pattern

        $lbc = strlen($this->bc_string) - 1;

        $barra_html = "";

        for ($xi = 0; $xi <= $lbc; $xi++)
        {
            $imgBar = "";
            $imgWid = 0;

            //barra preta, barra branca

            $imgBar = ($xi % 2 == 0) ? $this->ipp : $this->ipb;
            $imgWid = ($this->bc_string[$xi] == "0") ? $this->ebf : $this->ebg;

            //criando as barras
            $barra_html = $barra_html . "<img src=\"" . $imgBar . "\" width=\"" . $imgWid . "\" height=\"" . $this->altb
                              . "\" border=\"0\">";

            $this->tamanhoTotal = $this->tamanhoTotal + $imgWid;
        }

        $this->tamanhoTotal = (int)($this->tamanhoTotal * 1.1);

        //        echo "<div align=\"center\">$barra_html</div>\n";
        //        return "<div class=\"barcode\">$barra_html</div>\n";
        return $barra_html;
    } //End of drawBrar

    public function mixCode()
    {
        //Faz a mixagem do valor a ser codificado pelo CÃ³digo de Barras I25
        //DeclaraÃ§Ã£o de Variaveis
        $i = 0;
        $l = 0;
        $k = 0;  //inteiro, inteiro, longo
        $s = ""; //String

        $l = strlen($this->bc_string);

        if (($l % 5) != 0 || ($l % 2) != 0)
        {
            $this->barra_html = "<b> CÃ³digo nÃ£o pode ser intercalado: Comprimento invÃ¡lido (mix).</b>";
        }
        else
        {
            $s = "";

            for ($i = 0; $i <= $l; $i += 10)
            {
                $s = $s . $this->bc_string[$i] . $this->bc_string[$i + 5];
                $s = $s . $this->bc_string[$i + 1] . $this->bc_string[$i + 6];
                $s = $s . $this->bc_string[$i + 2] . $this->bc_string[$i + 7];
                $s = $s . $this->bc_string[$i + 3] . $this->bc_string[$i + 8];
                $s = $s . $this->bc_string[$i + 4] . $this->bc_string[$i + 9];
            }

            $this->bc_string = $s;
        }
    } //End of mixCode
}     //End of Class
?>
