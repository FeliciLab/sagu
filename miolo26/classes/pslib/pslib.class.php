<?
// +-----------------------------------------------------------------+
// |        PSLib - PHP class for generating PostScript files.       |
// +-----------------------------------------------------------------+
// | CopyLeft (L) 1999-2002 Vilson Cristiano Gartner                 |
// |                        UNIVATES - Centro Universitario          |
// +-----------------------------------------------------------------+
// | Licensed under LGPL: see COPYING or FSF at www.fsf.org for      |
// |                     further details                             |
// |                                                                 |
// | Site: http://pslib.codigolivre.org.br                           |
// | E-mail: vgartner@univates.br                                    |
// |                                                                 |
// +-----------------------------------------------------------------+
// | Abstract: PSLib main class file.                                |
// |                                                                 |
// | Created: 2002/07/24 - Vilson Cristiano Gartner                  |
// |                                                                 |
// | PSLib Version: 0.4                                              |
// +-----------------------------------------------------------------+

class postscript
{
    public $fp;
    public $filename;
    public $string = "";
    public $page = 1;
    public $ISOLatin1 = true; //ISOLatin Encoding
    public $resources;
    public $fonts;
    public $red = -1;
    public $green = -1;
    public $blue = -1;

    // startup the whole thing = Aqui tudo inicia
    public function postscript($fname = "", $author = "", $title = "Generated with PSLib 0.4", $orientation = "Portrait",
                        $fonts = "")
    {
        // A text string was requested: file name to create
        if ($fname)
        {
            if (!$this->fp = fopen($fname, "w"))
                return (0);
        }

        if ($fonts)
        {
            foreach ($fonts as $f)
            {
                $this->fonts[] = $f;
            }
        }

        $this->string .= "%!PS-Adobe-3.0 \n";
        $this->string .= '%%Creator: ' . $author . " using PSLib (http://pslib.codigolivre.org.br)\n";
        $this->string .= '%%CreationDate: ' . date("d/m/Y, H:i") . "\n";
        $this->string .= '%%LanguageLevel: 2';
        $this->string .= '%%Title: ' . $title . "\n";
        $this->string .= "%%PageOrder: Ascend \n";
        $this->string .= '%%Orientation: ' . $orientation . "\n";
        $this->string .= "%%DocumentMedia: A4 595 842 0 () ()\n";
        $this->string .= "%%EndComments \n";
        $this->string .= "%%BeginProlog \n";
        $this->string .= "%%BeginResource: definicoes \n";

        // Include resources
        if (!empty($this->resources))
        {
            foreach ($this->resources as $r)
            {
                $this->string .= $r . " \n";
            }
        }

        $this->string .= "%%EndResource \n";
        $this->string .= "%%EndProlog \n\n";

        $this->string .= "/d {bind def} bind def \n";
        $this->string .= "/D {def} d \n";
        $this->string .= "/t true D \n";
        $this->string
            .= "/np {newpath} D /cp {closepath} D /gs {gsave} D /gr {grestore} D /sk {stroke} D /fl {fill} D /rt {rotate} D /s {show} D /ff {findfont} D /cf {scalefont setfont} d /mv {moveto} D /lw {setlinewidth} D /lt {lineto} D /sc {setrgbcolor} D /sp {showpage} D /rc {-1 -1 -1 sc} D \n";

        if ($this->ISOLatin1 == true)
        {
            // Fonts for encoding
            $this->string .= '/FL [';
            $this->string .= '/Arial /Arial-Bold /Arial-Italic /Arial-BoldItalic ';
            $this->string .= '/Times /Times-Roman /Times-Bold /Times-BoldItalic ';
            $this->string .= '/Courier /Courier-Oblique /Courier-Bold /Courier-BoldOblique';
            $this->string .= '/Helvetica /Helvetica-Oblique /Helvetica-Bold /Helvetica-BoldOblique';

            // Any aditional font
            if (!empty($this->fonts))
            {
                foreach ($this->fonts as $f)
                {
                    $this->string .= "/$f ";
                }
            }

            $this->string .= "] D \n";

            $this->string
                .= "/ReencodeISO { 
              dup dup findfont dup length dict 
              begin{1 index /FID ne{D}{pop pop}
            ifelse }forall \n";

            $this->string
                .= "/Encoding ISOLatin1Encoding D currentdict end definefont} D 
            /ISOLatin1Encoding [ 
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /space/exclam/quotedbl/numbersign/dollar/percent/ampersand/quoteright
            /parenleft/parenright/asterisk/plus/comma/hyphen/period/slash
            /zero/one/two/three/four/five/six/seven/eight/nine/colon/semicolon
            /less/equal/greater/question/at/A/B/C/D/E/F/G/H/I/J/K/L/M/N
            /O/P/Q/R/S/T/U/V/W/X/Y/Z/bracketleft/backslash/bracketright
            /asciicircum/underscore/quoteleft/a/b/c/d/e/f/g/h/i/j/k/l/m
            /n/o/p/q/r/s/t/u/v/w/x/y/z/braceleft/bar/braceright/asciitilde
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef/.notdef
            /.notdef/space/exclamdown/cent/sterling/currency/yen/brokenbar
            /section/dieresis/copyright/ordfeminine/guillemotleft/logicalnot
            /hyphen/registered/macron/degree/plusminus/twosuperior/threesuperior
            /acute/mu/paragraph/periodcentered/cedilla/onesuperior/ordmasculine
            /guillemotright/onequarter/onehalf/threequarters/questiondown
            /Agrave/Aacute/Acircumflex/Atilde/Adieresis/Aring/AE/Ccedilla
            /Egrave/Eacute/Ecircumflex/Edieresis/Igrave/Iacute/Icircumflex
            /Idieresis/Eth/Ntilde/Ograve/Oacute/Ocircumflex/Otilde/Odieresis
            /multiply/Oslash/Ugrave/Uacute/Ucircumflex/Udieresis/Yacute
            /Thorn/germandbls/agrave/aacute/acircumflex/atilde/adieresis
            /aring/ae/ccedilla/egrave/eacute/ecircumflex/edieresis/igrave
            /iacute/icircumflex/idieresis/eth/ntilde/ograve/oacute/ocircumflex
            /otilde/odieresis/divide/oslash/ugrave/uacute/ucircumflex/udieresis
            /yacute/thorn/ydieresis
            ] D \n";

            $this->string
                .= "[128/backslash 129/parenleft 130/parenright 141/circumflex 142/tilde
            143/perthousand 144/dagger 145/daggerdbl 146/Ydieresis 147/scaron 148/Scaron
            149/oe 150/OE 151/guilsinglleft 152/guilsinglright 153/quotesinglbase
            154/quotedblbase 155/quotedblleft 156/quotedblright 157/endash 158/emdash
            159/trademark] \n";

            $this->string
                .= "aload length 2 idiv 1 1 3 -1 roll{pop ISOLatin1Encoding 3 1 roll put}for
            t{FL{ReencodeISO D}forall}{4 1 FL length 1 sub{FL E get ReencodeISO D}for}ifelse \n";
        }

        return (1);
    }

    // Internal use. Adds fonts for ISOLatin1 Encoding
    public function _add_font($font)
    {
        if (!$font)
        {
            return (0);
        }

        if ($this->ISOLatin1 == true)
        {
            $ffound = false;

            if (!empty($this->fonts))
            {
                foreach ($this->fonts as $f)
                {
                    if ($font == $f)
                    {
                        $ffound = true;
                        break;
                    }
                }
            }

            if (!$ffound)
            {
                $this->fonts[] = $font;
            }
        }

        return (1);
    }

    // Insert a line in the file
    public function insert_line($line)
    {
        $this->string .= $line . " \n";

        return (1);
    }

    // Include resource in the file's Resources Section
    public function include_resource($res)
    {
        $this->resources[] = $res;

        return (1);
    }

    // Use ISOLatin1 Encoding = AcentuaÃ§Ã£o, caracteres grÃ¡ficos
    public function encode_ISOLatin1($var = true)
    {
        $this->ISOLatin1 = $var;

        return (1);
    }

    // Begin new page = Inicia uma nova pagina
    public function begin_page($page)
    {
        $this->string .= "%%Page: " . $page . ' ' . $page . "\n";

        return (1);
    }

    // End page = Finaliza pagina
    public function end_page()
    {
        $this->string .= "sp \n";

        return (1);
    }

    // Close the postscript file = Fecha o arquivo postscript 
    public function close()
    {
        $this->string .= "sp \n";

        if ($this->fp)
        {
            fwrite($this->fp, $this->string);
            fclose ($this->fp);
        }

        return ($this->string);
    }

    // Draw a line = Desenha uma linha
    public function line($xcoord_from = 0, $ycoord_from = 0, $xcoord_to = 0, $ycoord_to = 0, $linewidth = 0)
    {
        if ((!$xcoord_from) || (!$ycoord_to) || (!$xcoord_to) || (!$ycoord_to) || (!$linewidth))
            return (0);

        $this->string .= $linewidth . " lw  ";
        $this->string .= $xcoord_from . ' ' . $ycoord_from . " mv \n";
        $this->string .= $xcoord_to . ' ' . $ycoord_to . " lt \n";
        $this->string .= "sk \n";

        return (1);
    }

    // Move to coordinates = Move para as coordenadas
    public function moveto($xcoord, $ycoord)
    {
        if ((empty($xcoord)) || (empty($ycoord)))
            return (0);

        $this->string .= $xcoord . ' ' . $ycoord . " mv \n";

        return (1);
    }

    // Move to coordinates and change the font = Move para as coordenadas e muda a fonte
    public function moveto_font($xcoord, $ycoord, $font_name, $font_size)
    {
        if ((!$xcoord) || (!$ycoord) || (!$font_name) || (!$font_size))
            return (0);

        $this->string .= $xcoord . ' ' . $ycoord . " mv \n";
        $this->string .= '/' . $font_name . ' ff ' . $font_size . " cf \n";

        $this->_add_font($font_name);

        return (1);
    }

    // Insert a PS file/image (remember to delete the information in the top of the file (source))
    // Insere um arquivo/imagem PS (lembre-se de remover a informaÃ§ao no inicio daquele arquivo)
    public function open_ps($ps_file = "")
    {
        if (!$ps_file)
            return (0);

        if ($f = join('', file($ps_file)))
            $this->string .= $f;
        else
            return (0);

        return (1);
    }

    // Draw a circle = Desenha um circulo
    public function circle($xcoord, $ycoord, $ray, $linewidth)
    {
        if ((!$xcoord) || (!$ycoord) || (!$ray) || (!$linewidth))
            return (0);

        $this->arc($xcoord, $ycoord, $ray, $linewidth, 1, 360);

        return (1);
    }

    // Draw and shade circle = Desenha um circulo
    public function circle_fill($xcoord, $ycoord, $ray, $linewidth, $red, $green, $blue, $border = false)
    {
        if ((!$xcoord) || (!$ycoord) || (!$ray) || (!$linewidth) || (!$red) || (!$green) || (!$blue))
            return (0);

        $this->arc_fill($xcoord, $ycoord, $ray, $linewidth, 1, 360, $red, $green, $blue, $border);

        return (1);
    }

    // Draw a arc = Desenha um arco
    public function arc($xcoord, $ycoord, $ray, $linewidth, $angle_start, $angle_end)
    {
        if ((!$xcoord) || (!$ycoord) || (!$ray) || (!$angle_start) || (!$angle_end) || (!$linewidth))
            return (0);

        $this->string .= "np \n";
        $this->string .= $linewidth . " lw  \n";
        $this->string .= $xcoord . ' ' . $ycoord . ' ' . $ray . ' ' . $angle_start . ' ' . $angle_end . " arc \n";
        $this->string .= "cp \n";
        $this->string .= "sk \n";

        return (1);
    }

    // Draw and shade a arc = Desenha e preenche um arco
    public function arc_fill($xcoord, $ycoord, $ray, $linewidth, $angle_start, $angle_end, $red, $green, $blue,
                      $border = false)
    {
        if ((!$xcoord) || (!$ycoord) || (!$ray) || (!$angle_start) || (!$angle_end) || (!$linewidth))
            return (0);

        $this->string .= "np \n";
        $this->string .= $linewidth . " lw  \n";
        $this->string .= $xcoord . ' ' . $ycoord . ' ' . $ray . ' ' . $angle_start . ' ' . $angle_end . " arc \n";
        $this->string .= "cp \n";

        if ($border)
            $this->string .= "gs \n";

        $this->string .= "$red $green $blue sc fl\n";

        if ($border)
            $this->string .= "gr \n";

        $this->string .= "sk \n";

        return (1);
    }

    // Draw a rectangle = Desenha um retangulo
    public function rect($xcoord_from, $ycoord_from, $xcoord_to, $ycoord_to, $linewidth)
    {
        if ((!$xcoord_from) || (!$ycoord_from) || (!$xcoord_to) || (!$ycoord_to) || (!$linewidth))
            return (0);

        $this->string .= $linewidth . " lw  \n";
        $this->string .= "np \n";
        $this->string .= $xcoord_from . ' ' . $ycoord_from . " mv \n";
        $this->string .= $xcoord_to . ' ' . $ycoord_from . " lt \n";
        $this->string .= $xcoord_to . ' ' . $ycoord_to . " lt \n";
        $this->string .= $xcoord_from . " " . $ycoord_to . " lt \n";
        $this->string .= "cp \n";
        $this->string .= "sk \n";

        return (1);
    }

    // Draw and shade a rectangle = Desenha um retangulo e preenche
    public function rect_fill($xcoord_from, $ycoord_from, $xcoord_to, $ycoord_to, $linewidth, $red, $green, $blue,
                       $border = false)
    {
        if ((!$xcoord_from) || (!$ycoord_from) || (!$xcoord_to) || (!$ycoord_to) || (!$linewidth) || (!$red)
            || (!$green) || (!$blue))
            return (0);

        $this->string .= "np \n";
        $this->string .= $linewidth . " lw  \n";
        $this->string .= $xcoord_from . ' ' . $ycoord_from . " mv \n";
        $this->string .= $xcoord_to . ' ' . $ycoord_from . " lt \n";
        $this->string .= $xcoord_to . ' ' . $ycoord_to . " lt \n";
        $this->string .= $xcoord_from . ' ' . $ycoord_to . " lt \n";
        $this->string .= "cp \n";

        if ($border)
            $this->string .= "gs \n";

        $this->string .= "$red $green $blue sc fl \n";

        if ($border)
            $this->string .= "gr \n";

        $this->string .= "sk \n";

        return (1);
    }

    // Set rotation, use 0 or 360 to end rotation 
    // Muda a rotacao do texto, passe 0 ou 360 para finalizar a rotacao 
    public function rotate($degrees)
    {
        if (!$degrees)
            return (0);

        if (($degrees == '0') or ($degrees == '360'))
        {
            $this->string .= "gr \n";
        }
        else
        {
            $this->string .= "gs \n";
            $this->string .= $degrees . " rt \n";
        }

        return (1);
    }

    // Set the font to show = Muda a fonte
    public function set_font($font_name, $font_size)
    {
        if ((!$font_name) || (!$font_size))
            return (0);

        $this->string .= '/' . $font_name . ' ff ' . $font_size . " cf \n";

        $this->_add_font($font_name);

        return (1);
    }

    // Show some text at the current coordinates (use 'moveto' to set coordinates)
    // if red, green, blue left blank, defaults to black or last set_color
    // color is determined by standard red, green, blue coding from 0-1, decimals are usable
    // examples; light red = 1 0 0, light green = 0 1 0, light blue 0 0 1, 
    //           darker blue = 0 0 .5, light purple = 1 0 1, see palette.ps for all values
    // justification: l (default) left justified
    //                c = centered (centers around current coordinates, therefore to center
    //                    on a line, moveto the center of the page)
    //                r = right justified (right justifies at the current location, use moveto
    //                    to set coordinates)
    public function show($text, $red = '', $green = '', $blue = '', $justify = 'l')
    {
        if (!$text)
            return (0);

        if (($red) || ($green) || ($blue) && ($red != $red1) || ($green != $green1) || ($blue != $blue1))
        {
            $red1 = $this->red;
            $green1 = $this->green;
            $blue1 = $this->blue;

            $restart_color = true;
            $this->set_color($red, $green, $blue);
        }

        switch ($justify)
            {
            case 'c':
                $this->string .= "( $text ) dup stringwidth pop 2 div neg 0 rmoveto s \n";

                break;

            case 'r':
                $this->string .= "( $text ) dup stringwidth pop neg 0 rmoveto s \n";

                break;

            default:
                $this->string .= "( $text ) s \n";

                break;
            }

        $this->_add_font($font_name);

        if ($restart_color)
        {
            $this->set_color($red1, $green1, $blue1);
        }

        return (1);
    }

    // Evaluate the text and show it at the current coordinates
    // Processa o texto e o escreve na posicao atual
    public function show_eval($text)
    {
        if (!$text)
            return (0);

        eval ("\$text = \"$text\";");
        $this->string .= '(' . $text . ") s \n";

        return (1);
    }

    // Show some text at specific coordinates and resets color back to black
    // Escreve o texto na coordenada informada e reseta a cor para preto
    public function show_xy($text, $xcoord, $ycoord, $red = '', $green = '', $blue = '', $justify = 'l')
    {
        if ((!$text) || (!$xcoord) || (!$ycoord))
            return (0);

        $this->moveto($xcoord, $ycoord);
        $this->show($text, $red, $green, $blue, $justify);

        return (1);
    }

    // Show some text at specific coordinates with font settings
    // Mostra o texto na coordenada informa com a fonte especifica
    public function show_xy_font($text, $xcoord, $ycoord, $font_name, $font_size, $red = '', $green = '', $blue = '',
                          $justify = 'l')
    {
        if ((!$text) || (!$xcoord) || (!$ycoord) || (!$font_name) || (!$font_size))
            return (0);

        $this->set_font($font_name, $font_size);
        $this->show_xy($text, $xcoord, $ycoord, $red, $green, $blue, $justify);

        return (1);
    }

    // Set the color to show text as. = setar a cor padrao
    public function set_color($red = -1, $green = -1, $blue = -1)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;

        if (($red == -1) && ($green == -1) && ($blue == -1))
        {
            $this->string .= "rc \n";
        }
        else
        {
            $this->string .= "$red $green $blue sc \n";
        }

        return (1);
    }
}
?>
