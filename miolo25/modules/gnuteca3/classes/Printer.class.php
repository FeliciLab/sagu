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
 * Class Printer
 *
 * @author Luiz Gilberto Gregory Filho [luiz@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Luiz Gregory Filho [luiz@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 14/07/2009
 *
 **/

require_once(str_replace("classes", "etc", str_replace("\\", "/", dirname(__FILE__))) . "/GPrinterServer.conf.php");

class Printer
{

    /**
     * attributes
     */
    private $printer            = null;
    private $errorCode          = null;
    private $printersClassPath  = null;


    /**
     * Constructor method
     */
    function __construct($printer = null, $model =  null)
    {
        $this->printersClassPath = str_replace("\\", "/", dirname(__FILE__)) . "/printers";

        $this->setPrinterModel($printer, $model);
    }



    /**
     * Seta a impressora que esta instalada e cria o objeto da impressora
     *
     * @param string $printer
     * @param string $model
     */
    public function setPrinterModel($printer = null, $model =  null)
    {
        if(is_null($printer))
        {
            $printer    = PRINTER_MARK;
        }

        if(is_null($model))
        {
            $model      = PRINTER_MODEL;
        }

        // SELECIONA A IMPRESSORA QUE SERA UTILIZADA PARA IMPRESSÃO
        switch (strtolower($printer))
        {
            case "daruma":
                require_once("{$this->printersClassPath}/DarumaPrinter.class.php");
                $this->printer = new DarumaPrinter("", $model);
                break;

            default:
                require_once("{$this->printersClassPath}/DefaultPrinter.class.php");
                $this->printer = new DefaultPrinter();
                break;
        }

        if(is_null($this->printer))
        {
            $this->errorCode = 1;
            return false;
        }

        return true;
    }



    /**
     * seta o conteudo a ser impresso
     *
     * @param string $content
     */
    public function setPrintContent($content)
    {
        if(is_null($this->printer))
        {
            $this->errorCode = 1;
            return false;
        }

        $this->printer->setPrintContent($content);
    }



    /**
     * Imprime o conteudo
     *
     * Este metodo abre o destino e vai escrevendo diretamente nele.
     *
     */
    public function __print()
    {
        if(is_null($this->printer))
        {
            $this->errorCode = 1;
            return false;
        }

        $handler = fopen(PRINTER_DESTINATION, "w");
        if(!$handler)
        {
            $this->errorCode = 2;
            return false;
        }

        $content = $this->printer->getPrintContent();
        $content = $this->makeContent($content);

        if(!fwrite($handler, $content))
        {
            $this->errorCode = 3;
            return false;
        }

        fclose($handler);
        return true;
    }


    /**
     * Este metodo aplica ajustes no conteudo independentemente do modelo da impressora
     *
     * @param string $content
     * @return string
     */
    private function makeContent($content)
    {
        if(REPLACE_ACENTUACAO)
        {
            // SUBSTITUI ACENTUACAO LETRA A
            $content = ereg_replace("[ãáâà]",   'a', $content);
            $content = ereg_replace("[ÃÁÂÀ]",   'A', $content);

            // SUBSTITUI ACENTUACAO LETRA E
            $content = ereg_replace("[éèê]",    'e', $content);
            $content = ereg_replace("[ÉÈÊ]",    'E', $content);

            // SUBSTITUI ACENTUACAO LETRA I
            $content = ereg_replace("[íìî]",    'i', $content);
            $content = ereg_replace("[ÍÌÎ]",    'I', $content);

            // SUBSTITUI ACENTUACAO LETRA O
            $content = ereg_replace("[óòõô]",   'o', $content);
            $content = ereg_replace("[ÓÒÕÔ]",   'O', $content);

            // SUBSTITUI ACENTUACAO LETRA U
            $content = ereg_replace("[úùû]",    'u', $content);
            $content = ereg_replace("[ÚÙÛ]",    'U', $content);

            // SUBSTITUI ACENTUACAO LETRA Ç
            $content = ereg_replace("[ç]",      'c', $content);
            $content = ereg_replace("[Ç]",      'C', $content);
        }

        return $content;
    }

}

?>