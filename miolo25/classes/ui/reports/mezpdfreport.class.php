<?php

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MCpdf extends Cpdf
{
}

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MCezpdf extends Cezpdf
{

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $papera4' (tipo) desc
     * @param $orientation='portrait' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */

    public $mfontFamilies;

    public function __construct($paper = 'a4', $orientation = 'portrait')
    {
        parent::cezPDF($paper, $orientation);
        $this->pageWidth = $this->ez['pageWidth'];
        $this->pageHeight = $this->ez['pageHeight'];
        $this->initFontFamily();
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $trigger (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function initFontFamily()
    {
        $this->fontFamilies['arial.afm'] = array (
                 'b' => 'arial-Bold.afm',
                 'i' => 'arial-Italic.afm',
                 'bi' => 'arial-BoldItalic.afm',
                 'ib' => 'arial-BoldItalic.afm'
        );
        $this->fontFamilies['vera.afm'] = array (
                 'b' => 'vera-Bold.afm',
                 'i' => 'vera-Italic.afm',
                 'bi' => 'vera-BoldItalic.afm',
                 'ib' => 'vera-BoldItalic.afm'
        );
        $this->fontFamilies['veramono.afm'] = array (
                 'b' => 'veramono-Bold.afm',
                 'i' => 'veramono-Italic.afm',
                 'bi' => 'veramono-BoldItalic.afm',
                 'ib' => 'veramono-BoldItalic.afm'
        );
        $this->fontFamilies['verase.afm'] = array (
                 'b' => 'verase-Bold.afm',
                 'i' => 'verase.afm',
                 'bi' => 'verase-Bold.afm',
                 'ib' => 'verase-Bold.afm'
        );
        $this->fontFamilies['tahoma.afm'] = array (
                 'b' => 'tahoma-Bold.afm',
                 'i' => 'tahoma.afm',
                 'bi' => 'tahoma-Bold.afm',
                 'ib' => 'tahoma-Bold.afm'
        );
        $this->fontFamilies['Times.afm'] = array (
                 'b' => 'Times-Bold.afm',
                 'i' => 'Times-Italic.afm',
                 'bi' => 'Times-BoldItalic.afm',
                 'ib' => 'Times-BoldItalic.afm'
        );
        $this->fontFamilies['verdana.afm'] = array (
                 'b' => 'verdana-Bold.afm',
                 'i' => 'verdana-Italic.afm',
                 'bi' => 'verdana-BoldItalic.afm',
                 'ib' => 'verdana-BoldItalic.afm'
        );
    }

    public function callTrigger($trigger)
    {
        $method = array
            (
            $trigger[0],
            $trigger[1]
            );

        call_user_func($method, $trigger[2]);
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $trigger (tipo) desc
     * @param $class (tipo) desc
     * @param $module (tipo) desc
     * @param $param (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setTrigger($trigger, $class, $module, $param)
    {
        $this->trigger[$trigger] = array
            (
            $class,
            $module,
            $param
            );
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $dy (tipo) desc
     * @param $mod' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function ezSetDy($dy, $mod = '')
    {
        parent::ezSetDy($dy, $mod);
        return $this->y;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function ezNewPage()
    {
        if ($this->trigger['BeforeNewPage'])
        {
            $this->callTrigger($this->trigger['BeforeNewPage']);
        }

        parent::ezNewPage();

        if ($this->trigger['AfterNewPage'])
        {
            $this->callTrigger($this->trigger['AfterNewPage']);
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $percent (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function getWidthFromPercent($percent)
    {
        $total = $this->ez['pageWidth'] - $this->ez['leftMargin'] - $this->ez['rightMargin'];
        return $percent * $total / 100;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $top (tipo) desc
     * @param $bottom (tipo) desc
     * @param $left (tipo) desc
     * @param $right (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function ezSetMargins($top, $bottom, $left, $right)
    {
        parent::ezSetMargins($top, $bottom, $left, $right);
        $this->top = $this->pageHeight - $this->ez['topMargin'];
        $this->bottom = $this->ez['bottomMargin'];
        $this->left = $this->ez['leftMargin'];
        $this->right = $this->pageWidth - $this->ez['rightMargin'];
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function horizontalLine()
    {
        $this->line($this->left, $this->y, $this->right, $this->y);
    }
}

/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MEzPDFReport extends MReport
{
    /**
     * Attribute Description.
     */
    public $type;

    /**
     * Attribute Description.
     */
    public $pdf;

    /**
     * Attribute Description.
     */
    public $font;

    /**
     * Attribute Description.
     */
    public $diff;

    /**
     * Attribute Description.
     */
    public $fileout;

    /**
     * Attribute Description.
     */
    public $fileexp;

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $type2' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function __construct($type = '2', $orientation = 'portrait', $paper='a4')
    {
        $MIOLO = MIOLO::getInstance();

        $this->type = $type;

        if ($this->type == '1')
            $this->pdf = new MCpdf();
        else
            $this->pdf = new MCezpdf($paper,$orientation);

        $this->diff = array
            (
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            "space",
            "exclam",
            "quotedbl",
            "numbersign",
            "dollar",
            "percent",
            "ampersand",
            "quoteright",
            "parenleft",
            "parenright",
            "asterisk",
            "plus",
            "comma",
            "minus",
            "period",
            "slash",
            "zero",
            "one",
            "two",
            "three",
            "four",
            "five",
            "six",
            "seven",
            "eight",
            "nine",
            "colon",
            "semicolon",
            "less",
            "equal",
            "greater",
            "question",
            "at",
            "A",
            "B",
            "C",
            "D",
            "E",
            "F",
            "G",
            "H",
            "I",
            "J",
            "K",
            "L",
            "M",
            "N",
            "O",
            "P",
            "Q",
            "R",
            "S",
            "T",
            "U",
            "V",
            "W",
            "X",
            "Y",
            "Z",
            "bracketleft",
            "backslash",
            "bracketright",
            "asciicircum",
            "underscore",
            "quoteleft",
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g",
            "h",
            "i",
            "j",
            "k",
            "l",
            "m",
            "n",
            "o",
            "p",
            "q",
            "r",
            "s",
            "t",
            "u",
            "v",
            "w",
            "x",
            "y",
            "z",
            "braceleft",
            "bar",
            "braceright",
            "asciitilde",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            ".notdef",
            "dotlessi",
            "grave",
            "acute",
            "circumflex",
            "tilde",
            "macron",
            "breve",
            "dotaccent",
            "dieresis",
            ".notdef",
            "ring",
            "cedilla",
            ".notdef",
            "hungarumlaut",
            "ogonek",
            "caron",
            "space",
            "exclamdown",
            "cent",
            "sterling",
            "currency",
            "yen",
            "brokenbar",
            "section",
            "dieresis",
            "copyright",
            "ordfeminine",
            "guillemotleft",
            "logicalnot",
            "hyphen",
            "registered",
            "macron",
            "degree",
            "plusminus",
            "twosuperior",
            "threesuperior",
            "acute",
            "mu",
            "paragraph",
            "periodcentered",
            "cedilla",
            "onesuperior",
            "ordmasculine",
            "guillemotright",
            "onequarter",
            "onehalf",
            "threequarters",
            "questiondown",
            "Agrave",
            "Aacute",
            "Acircumflex",
            "Atilde",
            "Adieresis",
            "Aring",
            "AE",
            "Ccedilla",
            "Egrave",
            "Eacute",
            "Ecircumflex",
            "Edieresis",
            "Igrave",
            "Iacute",
            "Icircumflex",
            "Idieresis",
            "Eth",
            "Ntilde",
            "Ograve",
            "Oacute",
            "Ocircumflex",
            "Otilde",
            "Odieresis",
            "multiply",
            "Oslash",
            "Ugrave",
            "Uacute",
            "Ucircumflex",
            "Udieresis",
            "Yacute",
            "Thorn",
            "germandbls",
            "agrave",
            "aacute",
            "acircumflex",
            "atilde",
            "adieresis",
            "aring",
            "ae",
            "ccedilla",
            "egrave",
            "eacute",
            "ecircumflex",
            "edieresis",
            "igrave",
            "iacute",
            "icircumflex",
            "idieresis",
            "eth",
            "ntilde",
            "ograve",
            "oacute",
            "ocircumflex",
            "otilde",
            "odieresis",
            "divide",
            "oslash",
            "ugrave",
            "uacute",
            "ucircumflex",
            "udieresis",
            "yacute",
            "thorn",
            "ydieresis"
            );

        $fonts_path = $MIOLO->getConf('home.classes') . '/ezpdf/fonts';
        $this->pdf->setFontsPath($fonts_path);
        $this->setFont('Helvetica.afm');
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $font (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setFont($font)
    {
        $this->font = $font;
        $this->pdf->selectFont($this->font, array('encoding' => 'ISOLatin1Encoding', 'differences' => $this->diff));
    }

    public function &GetPdf()
    {
        return $this->pdf;
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @param $value' (tipo) desc
     *
     * @returns (tipo) desc
     *
     */
    public function setOutput($value = '')
    {
        if ($value != '')
        {
            $this->output = $value;
        }
        else
        {
            $this->output = ($this->type == '1') ? $this->pdf->output() : $this->pdf->ezOutput();
        }
    }

    /**
     * Brief Description.
     * Complete Description.
     *
     * @returns (tipo) desc
     *
     */
    public function getOutput()
    {
        if ($this->output == NULL)
        {
            $this->setOutput();
        }

        return $this->output;
    }

    public function newPage()
    {
        $this->pdf->ezNewPage();
    }

    public function execute()
    {
        global $MIOLOCONF;
        $MIOLO = MIOLO::getInstance();
        $page = $MIOLO->getPage();

        $pdfcode = $this->getOutput();
        $fname = substr(uniqid(md5(uniqid(""))), 0, 10) . '.pdf';
        $this->fileexp = $MIOLO->getConf('home.reports') . '/' . $fname;
        $fp = fopen($this->fileexp, 'x');
        fwrite($fp, $pdfcode);
        fclose ($fp);
        $this->fileout = $MIOLO->getActionURL('miolo', 'reports:' . $fname);

        //     $this->fileout = $MIOLO->getConf('url') . "/report.php?fname=$fname";
        //echo "$this->fileexp<br>";
        //echo "$this->fileout<br>";
        //echo $this->pdf->messages;
        $page->redirect(str_replace('&amp;','&',$this->fileout));
    }
}
?>
