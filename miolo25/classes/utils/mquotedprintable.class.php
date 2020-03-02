<?php

/**
 * Class MQuotedPrintable.
 *
 * @author Ely Edison Matos [ely.matos@ufjf.edu.br]
 *
 * \b Maintainers: \n
 * Armando Taffarel Neto [taffarel@solis.coop.br]
 * Daniel Hartmann [daniel@solis.coop.br]
 *
 * @since
 * Creation date 2006/03/08
 *
 * \b Organization: \n
 * SOLIS - Cooperativa de Solções Livres \n
 *
 * \b Copyright: \n
 * Copyright (c) 2006-2011 SOLIS - Cooperativa de Soluções Livres \n
 *
 * \b License: \n
 * Licensed under GPLv2 (for further details read the COPYING file or http://www.gnu.org/licenses/gpl.html)
 *
 */

class MQuotedPrintable
{
    public function encode($str)
    {
        define('CRLF', "\r\n");
        $lines = preg_split("/\r?\n/", $str);
        $out = '';

        foreach ( $lines as $line )
        {
            $newpara = '';

            for ( $j = 0; $j <= strlen($line) - 1; $j++ )
            {
                $char = substr($line, $j, 1);
                $ascii = ord($char);

                if ( $ascii < 32 || $ascii == 61 || $ascii > 126 )
                {
                    $char = '=' . strtoupper(dechex($ascii));
                }

                if ( ( strlen($newpara) + strlen($char) ) >= 76 )
                {
                    $out .= $newpara . '=' . CRLF;
                    $newpara = '';
                }
                $newpara .= $char;
            }
            $out .= $newpara . $char;
        }
        return trim($out);
    }

    public function decode($str)
    {
        $out = preg_replace('/=\r?\n/', '', $str);
        $out = preg_replace('/=([A-F0-9]{2})/e', chr(hexdec('\\1')), $out);

        return trim($out);
    }
}

?>