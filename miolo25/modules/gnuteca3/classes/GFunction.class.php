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
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 26/11/2008
 *
 * */
$MIOLO->getClass('gnuteca3', 'gIso2709Export');

class GFunction
{
    private $MIOLO;
    private $module;
    private $variable;
    private $executeFunctions = false;
    private $busMaterial;
    public $line; //usado para informar para o GFunction qual a linha do exemplar a pegar no caso do getTagDescription

    function __construct()
    {
        $this->MIOLO = MIOLO::getInstance();
        $this->module = $this->MIOLO->getCurrentModule();
        $this->setVariable('$LN', "\n");
        $this->busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');
    }

    /**
     * Define várias variáveis de uma única vez.
     *
     * @param array $variables deve se passar um array indexado pelo nome da variável.
     * @example array( 'nomeDaVariável' => 'conteúdo' )
     *
     */
    public function setVariables(array $variables)
    {
        if ( is_array($variables) )
        {
            foreach ( $variables as $variable => $value )
            {
                $this->setVariable($variable, $value);
            }
        }
    }

    public function setVariable($variable, $value)
    {
        $this->variable[$variable] = $value;
    }

    public function getVariable($variable)
    {
        return $this->variable[$variable];
    }

    public function clearVariables()
    {
        unset($this->variable);
    }

    public function replaceVariables($content)
    {
        //FIXME Solução adicionar quebra de linha <br/> nos conteúdos que tenham enter
        if ( is_array($this->variable) )
        {
            foreach ( $this->variable as $line => $variable )
            {
                $this->variable[$line] = str_replace("\n", '$BR', $variable); //para cada conteúdo das variáveis que contiver \n, trocar para $BR
            }
        }

        $content = strtr($content, $this->variable);
        // REMOVE AS VARIAVEIS TAG QUE NAO FORAM TROCADAS
        $content = preg_replace('/\$[0-9]{3}.[a-zA-Z0-9]{1}/', '', $content);
        return $content;
    }

    /**
     * Determine if can do execute functions
     *
     * @param boolean $executeFunctions Determine if can do execute functions
     */
    public function SetExecuteFunctions($executeFunctions)
    {
        $this->executeFunctions = $executeFunctions;
    }

    /**
     * Interpreta a string passada em content, considerando as variáveis informadas
     *
     * @param $content (String)
     *
     * @return String
     */
    public function interpret($content, $replaceTextDefaultFormated = true)
    {
        if ( $replaceTextDefaultFormated )
        {
            $content = str_replace(array( "\n", "\t", "\r" ), '', $content);
        }

        if ( count($this->variable) > 0 )
        {
            $content = $this->replaceVariables($content);
        }

        /* Monta a expressão regular baseada nas funções declaradas no help.
         * Toda e qualquer função, para funcionar, deve estar no help. Com isso,
         * a gFunction passa a suportar html
         */
        $hFunc = array_keys($this->helpFunctions());
        if ( !is_array($hFunc) )
        {
            throw new Exception("AQUI MENSAGEM DE ERRO");
        }

        foreach ( $hFunc as $hf )
        {
            $regExp[] .= '\/?' . strtolower($hf) . '[^>]*';
        }

        $regExp[] = '\/ *';
        $regExp = '<(' . implode('|', $regExp) . ')>';
        $explode = preg_split('/' . $regExp . '/Ui', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        $level = 0;
        foreach ( $explode as $key => $exp )
        {
            if ( ($key % 2) == 0 ) //Se for 0 é o texto do usuário
            {
                $text[$level] .= $exp;
            }
            
            else //Se for 1 é a tag
            {
                if ( substr($exp, 0, 1) == '/' )
                {
                    $result = $this->callFunction($tag[$level], $text[$level]);
                    if ( is_array($result) )
                    {
                        return $result;
                    }
                    $text[$level - 1] .= $result;
                    $level--;
                }
                else
                {
                    $level++;
                    $text[$level] = '';
                    $tag[$level] = $exp;
                }
            }
        }

        $text[0] = str_replace('$BR', '<br/>', $text[0]); //FIXME Por dificuldade em quebrar linhas é feita troca todos $BR por <br/> manualmente aqui.
        return $text[0];
    }

    /**
     * Call a definide funcion
     *
     * @param $function (String) the name of the function
     * @param $content (String) the content to apply the function
     *
     * @return String the content/string converted/interpreted
     */
    public function callFunction($function, $content)
    {
        $f = $this->explodeFunction($function);
        $f->content = $content; //adiciona no objeto para poder utilizar nas funções internas
        
        switch ( strtolower($f->name) )
        {
            
              
            //Funções genéricas
            case 'cut':
                if ( $f->parameter[1] )
                {
                    $content = GString::construct($content)->sub($f->parameter[0], $f->parameter[1]);
                }
                else
                {
                    $content = GString::construct($content)->sub($f->parameter[0]);
                }
                break;

            case 'lower':
                $content = strtolower($content);
                break;

            case 'pad':
                $p1 = $f->parameter[1];
                if ( !$p1 )
                {
                    $p1 = ' ';
                }
                switch ( strtoupper($f->parameter[2]) )
                {
                    case 'RIGHT':
                        $p3 = STR_PAD_RIGHT;
                        break;
                    case 'LEFT':
                        $p3 = STR_PAD_LEFT;
                        break;
                    case 'BOTH':
                        $p3 = STR_PAD_BOTH;
                        break;
                    default:
                        $p3 = STR_PAD_RIGHT;
                        break;
                }
                $content = GUtil::strPad($content, $f->parameter[0], $p1, $p3);
                
                // Impede que i texto exeda o tamanho.
                if ( BString::construct($content)->length() > $f->parameter[0] )
                {
                   $content = BString::construct($content)->sub(0, ($f->parameter[0] - 3));
                   $content = $content->__toString() . "...";
                }

                break;

            case 'replace':
                $content = str_replace($f->parameter[0], $f->parameter[1], $content);
                break;

            case 'upper':
                $content = strtoupper($content);
                break;

            case 'style':
                if ( $f->amountParameters > 0 )
                {
                    $style = "";
                    foreach ( $f->parameter as $line => $info )
                    {
                        switch ( $info )
                        {
                            case 'b':
                                $content = '<b>' . $content . '</b>';
                                break;
                            case 'i':
                                $content = '<i>' . $content . '</i>';
                                break;
                            case 'u':
                                $content = '<u>' . $content . '</u>';
                                break;
                            default:

                                $permition = array( 'color', 'font-size' );

                                list($command, $value) = explode('=', $info);

                                if ( in_array($command, $permition) )
                                {
                                    $style .= $command . ':' . $value . '; ';
                                }

                                break;
                        }
                    }
                    if ( strlen($style) > 0 )
                    {
                        $content = '<font style= "' . $style . '">' . $content . '</font>';
                    }
                }
                break;

            case 'date' :
                $f->parameter[0] = str_replace("y", "Y", $f->parameter[0]);
                $content = date($f->parameter[0]);
                break;

            case 'datemarc' :
                $content = date('ymd');
                break;

            case 'ifexists' :
                if ( $f->parameter[0] )
                {
                    $content = $content;
                }
                else
                {
                    $content = '';
                }
                break;

            case 'ifnotexists' :
                if ( !strlen($f->parameter[0]) )
                {
                    $content = $content;
                }
                else
                {
                    $content = '';
                }
                break;

            case 'compare' :
                if ( ereg("\([0-9]{1,20} [0-9]{3}\.[a-z0-9]{1}\)", $f->parameter[0]) )
                {
                    $f->parameter[0] = str_replace(array( '(', ')' ), '', $f->parameter[0]);
                    list($controlNumber, $tag) = explode(" ", $f->parameter[0]);

                    //caso for exemplar passa a linha
                    if (stripos($tag, '949') !== false )
                    {
                        $line = $this->line;
                    }
                    else //caso for dado do material, desconsidera a linha
                    {
                        $line = 0 ;
                    }                           
                    
                    $f->parameter[0] = $this->busMaterial->getContentTag($controlNumber, $tag, $line);
                }
                if ( ereg("\([0-9]{1,20} [0-9]{3}\.[a-z0-9]{1}\)", $f->parameter[2]) )
                {
                    $f->parameter[2] = str_replace(array( '(', ')' ), '', $f->parameter[2]);
                    list($controlNumber, $tag) = explode(" ", $f->parameter[2]);
                    //caso for exemplar passa a linha
                    if (stripos($tag, '949') !== false )
                    {
                        $line = $this->line;
                    }
                    else //caso for dado do material, desconsidera a linha
                    {
                        $line = 0 ;
                    }     
                    
                    $f->parameter[2] = $this->busMaterial->getContentTag($controlNumber, $tag, $line);
                }
                switch ( $f->parameter[1] )
                {
                    case 'maior' :
                        $content = ($f->parameter[0] > $f->parameter[2]) ? $content : '';
                        break;

                    case 'menor' :
                        $content = ($f->parameter[0] < $f->parameter[2]) ? $content : '';
                        break;

                    case 'menor=' :
                        $content = ($f->parameter[0] >= $f->parameter[2]) ? $content : '';
                        break;

                    case 'maior=' :
                        $content = ($f->parameter[0] <= $f->parameter[2]) ? $content : '';
                        break;

                    case '!=' :
                        $content = ($f->parameter[0] != $f->parameter[2]) ? $content : '';
                        break;

                    case 'like' :
                        $content = ereg($f->parameter[0], $f->parameter[2]) ? $content : '';
                        break;

                    case 'ilike' :
                        $content = eregi($f->parameter[0], $f->parameter[2]) ? $content : '';
                        break;

                    default :
                        $content = ($f->parameter[0] == $f->parameter[2]) ? $content : '';
                        break;
                }

                break;

            case 'href' :
                $content = $this->href($f);
                break;

            case 'pregmatch' :

                if ( !strlen($f->parameter[1]) )
                {
                    break;
                }

                preg_match("/{$f->parameter[0]}/", $f->parameter[1], $match);
                if ( isset($match[0]) )
                {
                    $content = $match[0];
                }
                break;


            case 'executephp' :
                if ( $this->executeFunctions )
                {
                    $php = '$content = ' . $content . ';';
                    $php = str_replace("\'", "'", $php);
                    $php = str_replace('\"', '"', $php);
                    eval($php);
                }
                break;


            case 'dbmaterialcount' :

                $controlNumber = $f->parameter[0];
                $tag = $f->parameter[1];
                $operator = $f->parameter[2];
                $value = $f->parameter[3];

                if ( !$controlNumber || !$tag )
                {
                    $content = '';
                    break;
                }

                list($fieldId, $subFieldId) = explode(".", $tag);
                $sql = "SELECT count(*) FROM gtcMaterial WHERE controlNumber = '$controlNumber' AND fieldId = '$fieldId' AND subFieldId = '$subFieldId'";

                $GBusiness = new GBusiness();
                $result = $GBusiness->executeSelect($sql);
                $result = $result[0][0];

                if ( !$operator || !$value )
                {
                    $content = $result;
                    break;
                }

                switch ( $operator )
                {
                    case 'maior' :
                        $content = ($result > $value) ? $content : '';
                        break;

                    case 'menor' :
                        $content = ($result < $value) ? $content : '';
                        break;

                    case 'maior=' :
                        $content = ($result >= $value) ? $content : '';
                        break;

                    case 'menor=' :
                        $content = ($result <= $value) ? $content : '';
                        break;

                    case '=' :
                        $content = ($result == $value) ? $content : '';
                        break;

                    case '!=' :
                        $content = ($result != $value) ? $content : '';
                        break;
                }

                break;

            case 'executesql' :
                if ( $this->executeFunctions )
                {
                    $GBusiness = new GBusiness();
                    $sql = $content;
                    $result = $GBusiness->executeSelect($sql);
                    if ( count($result) == 1 && count($result[0]) == 1 )
                    {
                        $content = $result[0][0];
                    }
                    else
                    {
                        $content = $result;
                    }
                }
                break;

            case 'executedb' :
                if ( $this->executeFunctions )
                {
                    $temp = explode('->', $content);
                    $bus = $temp[0];
                    $function = $temp[1];
                    $busTemp = $this->MIOLO->getBusiness($this->module, $bus);
                    $options = $busTemp->$function();
                    //return $options;
                    $content = $options;
                    //$content = date($f->parameter[0]);
                };
                break;

            //Funções específicas do gnuteca
            case 'gtcgetmaterialcontent':
            case 'getmaterialcontent':

                $content = $this->busMaterial->getContentTag($f->parameter[0], $f->parameter[1]);

                if ( $f->parameter[2] == '1' )
                {
                    $prefix = $busMaterial->getMaterialTagPrefixSuffix($f->parameter[0], $f->parameter[1], 1);
                    $content = "{$prefix}{$content}";
                }

                if ( $f->parameter[3] == '1' )
                {
                    $suffix = $busMaterial->getMaterialTagPrefixSuffix($f->parameter[0], $f->parameter[1], 2);
                    $content .= "{$suffix}";
                }

                break;

            case 'gettagdescription' :

                //parameter[0] = controlNumber
                //parameter[1] = tag
                //parameter[2] = Caso for 1 obtem controlnumber do pai
                
                //Caso for 1 obtem controlnumber do pai
                if ( $f->parameter[2] == 1 )
                {
                    $controlNumberGranFather = $this->busMaterial->getContentTag($f->parameter[0], MARC_ANALITIC_ENTRACE_TAG);

                    if ( $controlNumberGranFather )
                    {
                        $f->parameter[0] = $controlNumberGranFather;
                    }
                }
                
                //caso for exemplar passa a linha
                if (stripos($f->parameter[1], '949') !== false )
                {
                    $line = $this->line;
                }
                else //caso for dado do material, desconsidera a linha
                {
                    $line = 0 ;
                }
                

                //$this->line pega a linha definida no gnuteca function
                $contentX = $this->busMaterial->getContentTag($f->parameter[0], $f->parameter[1], $line);

                if ( !$contentX )
                {
                    $content = '';
                    break;
                }

                $contentX = $this->busMaterial->relationOfFieldsWithTable($f->parameter[1], $contentX, false);

                if ( !$contentX )
                {
                    $content = '';
                    break;
                }

                $content = str_replace("_DESCRIPTION_", $contentX, $content);
                break;

            case 'getauthors700aabntformat' :

                /** O desenvolvimento deste algoritimo para obter autores no formato bibliográfico
                 * foi baseado nas regras explicadas no link http://www.leffa.pro.br/textos/abnt.htm :
                 * As regras ABNT que este algoritimo contempla são :
                 * 
                 * 4.1.2 Dois ou três autores
                 * 4.1.3 Mais de três autores
                 * 4.1.4 Responsabilidade intelectual diferente de autor
                 * 
                 * */
                $author100a = $this->busMaterial->getContent($f->parameter[0], '100', 'a', null, true, true, 'line');
                $author700a = $this->busMaterial->getContent($f->parameter[0], '700', 'a', null, true, true, 'line');

                //Prepara nome do autor no formato : SOBRENOME, Nome
                $author = $author100a[0]->content;
                $primeiraVirgula = strpos($author, ',');
                $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                $nomeResto = trim(substr($author, $primeiraVirgula));
                $author100a = "{$sobreNomeUpper}{$nomeResto}";

                if ( strlen($author100a) )
                {
                    $authors[] = $author100a;
                }

                foreach ( $author700a as $key => $aut )
                {
                    //Prepara nome do autor no formato : SOBRENOME, Nome
                    $author = $aut->content;
                    $primeiraVirgula = strpos($author, ',');
                    $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                    $nomeResto = trim(substr($author, $primeiraVirgula));
                    $author = "{$sobreNomeUpper}{$nomeResto}";
                    $tag7004 = $this->busMaterial->getContent($f->parameter[0], '700', '4', $aut->line, true, true, 'line');

                    //Se tiver abreviatura da responsabilidade intelectual do autor 700.4 
                    if ( strlen($tag7004[0]->content) > 0 )
                    {
                        //Adiciona a abreviatura (700.4) no formato abnt
                        $author .= " ({$tag7004[0]->content})";
                    }

                    $authors[] = $author;

                    //Se tiver três autores
                    if ( count($authors) == 3 )
                    {
                        break;
                    }
                }

                //Por padrão trata como se não tivesse autores
                $content = "";

                //Se tiver mais de 3 autores
                if ( (count($author700a) > 2 && strlen($author100a)) || count($author700a) > 3 )
                {
                    //mostra o primeiro autor seguido de et al.
                    $content = $authors[0] . " et al.";
                }
                // Se tiver até 3 autores
                elseif ( count($author700a) > 0 || strlen($author100a) )
                {
                    //Mostra os três  ou menos autores 
                    $content = implode("; ", $authors);
                    //bota '.' no final, se não tiver 
                    $content .= (substr($content, -1) == '.' && strlen(($content))) ? '' : '.';
                }

                break;

            case 'getauthors700aabntformatunivates' :

                $author100a = $this->busMaterial->getContent($f->parameter[0], '100', 'a', null, true, true, 'line');
                $author700a = $this->busMaterial->getContent($f->parameter[0], '700', 'a', null, true, true, 'line');

                //se tiver 100a
                if ( $author100a )
                {
                    $author = $author100a[0]->content;
                    $primeiraVirgula = strpos($author, ',');
                    $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                    $nomeResto = trim(substr($author, $primeiraVirgula));
                    $content = "{$sobreNomeUpper}{$nomeResto}";

                    if ( sizeof($author700a) > 0 )
                    {
                        $cont1 = 0;
                        foreach ( $author700a as $k => $aut )
                        {
                            $e700 = $this->busMaterial->getContent($f->parameter[0], '700', 'e', $aut->line, true, true, 'line');
                            if ( strlen($e700[0]->content) == 0 ) //não pode ter termo relacionador
                            {
                                $author = $aut->content;
                                $primeiraVirgula = strpos($author, ',');
                                $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                                $nomeResto = trim(substr($author, $primeiraVirgula));
                                $content .= "; {$sobreNomeUpper}{$nomeResto}";

                                if ( $cont == 1 )
                                {
                                    break;
                                }
                                $cont++;
                            }
                        }
                    }
                    $content .= substr($content, -1) == '.' ? '' : '.'; //bota '.' no final, se não tiver
                }
                else
                {
                    //pega o 700.4 da primeira linha
                    $author7004 = $this->busMaterial->getContent($f->parameter[0], '700', '4', $author700a[0]->line, true, true, 'line');

                    if ( sizeof($author700a) > 0 )
                    {
                        if ( (!preg_match('/ - /', $author700a[0]->content, $var1)) && (preg_match('/et al/', $author7004[0]->content, $var2)) ) //et al sem org
                        {
                            $author = $author700a[0]->content;
                            $primeiraVirgula = strpos($author, ',');
                            $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                            $nomeResto = trim(substr($author, $primeiraVirgula));
                            $content = "{$sobreNomeUpper}{$nomeResto} et al.";

                            break;
                        }
                        elseif ( preg_match('/et al/', $author7004[0]->content, $var2) ) //et al com org, no primeiro
                        {
                            $author = $author700a[0]->content;
                            $primeiraVirgula = strpos($author, ',');
                            $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                            $nomeResto = trim(substr($author, $primeiraVirgula));

                            $broken = explode(' - ', $nomeResto);
                            $nomeResto = $broken[0];

                            $pChar = strtoupper(substr($broken[1], 0, 1));
                            $resto = str_replace('.', '', $pChar . substr($broken[1], 1, strlen($broken[1]) - 1)); //substituí '.' por nada
                            $resto = "({$resto}.)";

                            $content = "{$sobreNomeUpper}{$nomeResto} et al. {$resto}.";
                        }
                        else
                        {
                            //faz o agrupamento por 700.e
                            $group = '';
                            $cont = 0;
                            $content = '';
                            $sufix = '';

                            //700.4 -> sufixo que será utilizado
                            //700.e -> agrupador
                            foreach ( $author700a as $key => $lineVal )
                            {
                                $e700 = $this->busMaterial->getContent($f->parameter[0], '700', 'e', $lineVal->line, true, true, 'line');

                                if ( $e700[0]->content == $group || $key == 0 )
                                {
                                    $group = $e700[0]->content;

                                    if ( $key == 0 ) //se for na primeira volta, pega o sufixo da string
                                    {
                                        $codRel = $this->busMaterial->getContent($f->parameter[0], '700', '4', $lineVal->line, true, true, 'line');
                                        $sufix = $codRel[0]->content;

                                        //tira '.' no final da string, caso tiver
                                        if ( substr($sufix, -1, 1) == '.' )
                                        {
                                            $sufix = substr($sufix, 0, strlen($sufix) - 1);
                                        }
                                    }

                                    $author = $lineVal->content;
                                    $primeiraVirgula = strpos($author, ',');
                                    $sobreNomeUpper = strtoupper(substr($author, 0, $primeiraVirgula));
                                    $nomeResto = trim(substr($author, $primeiraVirgula));
                                    $content .= "{$sobreNomeUpper}{$nomeResto}; ";

                                    if ( $cont == 2 ) //para de agrupar no máximo 3 autores
                                    {
                                        break;
                                    }

                                    $cont++;
                                }
                            }

                            //trata o content
                            $content = substr($content, 0, strlen($content) - 2); //tira a virgula do final
                            //examplo de sufix -> (Trads, Orgs)
                            if ( strlen($sufix) > 0 )
                            {
                                $sufix = strtoupper(substr($sufix, 0, 1)) . substr($sufix, 1, strlen($sufix) - 1); //substitio '.' por nada, bota o primeriro caractér como maiúsculo
                                $sufix .= $cont > 1 ? 's' : ''; //se tiver mais que 1 autor, concateca 's' no final
                                $content .= " ({$sufix}).";
                            }

                            $content .= substr($content, -1) == '.' ? '' : '.'; //se não tiver '.' no final, bota.
                        }
                    }
                }
                break;

            case "gettitleabntfotmated" :

                $controlNumber = $f->parameter[0];
                list($field, $subField) = explode(".", $f->parameter[1]);

                $titles = $this->busMaterial->getContent($controlNumber, $field, $subField, null, true, true, 'line');
                $title = $titles[0];

                if ( !$title )
                {
                    break;
                }

                $cutStart = strlen($title->indicator2) ? $title->indicator2 : 0;
                $titleX = substr($title->content, $cutStart);
                $firstSpace = strpos($titleX, " ");
                $firstSpace = !$firstSpace ? strlen($titleX) : $firstSpace;
                $firstWord = substr($titleX, 0, $firstSpace);

                $content = str_replace($firstWord, strtoupper($firstWord), $title->content);

                break;

            case 'gtcgettagname':
                $busTag = $this->MIOLO->getBusiness($this->module, 'BusTag');
                $content = $busTag->getTagNameByTag($f->parameter[0]);
                break;

            case 'gtcseparator':
                $content = $this->gtcSeparator($f);
                break;

            case 'gtcgetseparator':
                $content = $this->gtcGetSeparator($f);
                break;

            case 'getevaluation':
                $content = $this->getEvaluation($f);
                break;

            case 'gtciso2709':
                $content = $this->gtcIso2709($f);
                break;

            case 'unique' :
                $separador = $f->parameter[0];

                if ( !$separador )
                {
                    $separador = '-#-';
                }

                $array = explode($separador, $content);
                $array = array_unique($array);
                $content = implode($separador, $array);
                break;
                
                
            case 'getmarc21content' :
                $controlNumber = $f->parameter[0];
                
                // Define os delimitadores.
                $fieldDelimiter = MARC_FIELD_DELIMITER; 
                $subFieldDelimiter = MARC_SUBFIELD_DELIMITER;
                $emptyIndicator = MARC_EMPTY_INDICATOR;
                
                $ignoreFields = $f->parameter;
                
                // Retira o número de controle como tag a ser ignorada.
                unset($ignoreFields[0]);
                
                $MIOLO = MIOLO::getInstance();
                $MIOLO->uses('classes/gMarc21Record.class.php', 'gnuteca3');
                $busMaterial = $MIOLO->getBusiness('gnuteca3', 'BusMaterial');
                
                // Obtém dados do material, ignorando as tags informadas pelo usuário.
                $material = $busMaterial->searchMaterialOfControlNumber($controlNumber, $ignoreFields);
                
                // Define o objeto marc21.
                $gMarc21Record = new gMarc21Record(NULL, $fieldDelimiter, $subFieldDelimiter, $emptyIndicator);
                
                // Define os dados, ao definir o marc é atualizado internamente do objeto.
                $gMarc21Record->setTags($material);
                $content = $gMarc21Record->getRecord();
                
                // Troca o delimitador de campo pelo <br> para visualizar em tela.
                $content = str_replace($gMarc21Record->getFieldDelimiter(), '</br>', $content);
                
                break;
            
        }

        return $content;
    }

    /**
     * Cria um link html, normalmente usado para o campo 856.u da catalogação
     *
     * Parametros:
     * 0 - o link, ou links caso tenha separador
     * 1 - o separador, valor padrão "\n"
     *
     * @param stdClass $data
     * @return string
     */
    protected function href($data)
    {
        $content = $data->content;
        $link = $data->parameter[0];
        $separator = $data->parameter[1] ? $data->parameter[1] : "-#-"; //\n separador default
        //explode pelo separador, transformando em array
        $link = explode($separator, $link);

        if ( is_array($link) )
        {
            foreach ( $link as $line => $l )
            {
                $label = strlen($content) ? $content : $l;
                
                if ( count($link) > 1 && $label != $l )
                {
                    $label .= ' ' . ($line + 1);
                }

                $result .= "<a href=\"{$l}\" target=\"_new\" >{$label}</a> ";
            }
        }
        return $result;
    }

    /**
     * Este metodo documenta detalhes sobre as funções;
     *
     * @param string $function
     * @return simple array details
     */
    public function helpFunctions($function = null)
    {
        if ( ($function != null) && (!is_array($function)) )
        {
            $function = array( $function );
        }

        $help['cut']->description = 'Retorna a parte da string especificada pelo parâmetro 1 e 2.';
        $help['cut']->parameter[0] = 'Se parâmetro 1 não for negativo, a string retornada iniciará na posição especificada, começando em zero. Por exemplo, na string \'abcdef\', o caractere na posição 0 é \'a\', o caractere na posição 2 é \'c\', e assim em diante.';
        $help['cut']->parameter[1] = 'Se parâmetro 2 for dado e for positivo, a string retornada irá conter x caracteres começando no valor especificado no parâmetro 1.';
        $help['cut']->example = '<cut 0| 11>O retorno será as 11 primeiras letras</cut>';
        $help['cut']->return = 'O retorno s';

        $help['upper']->description = 'Converte uma string para maiúsculas';
        $help['upper']->example = '<upper>maiúscula</upper>';
        $help['upper']->return = 'MAIÚSCULA';

        $help['style']->description = 'Adiciona um estilo a string';
        $help['style']->parameter[0] = 'Estillo a ser adicionado';
        $help['style']->example = '<style b>Título</style >';
        $help['style']->return = 'Retorna a string em negrito';

        $help['pad']->description = 'Retorna a string preenchida na esquerda, direita ou ambos os lados até o tamanho especificado.';
        $help['pad']->parameter[0] = 'Tamanho final da string.';
        $help['pad']->parameter[1] = 'Se não for indicado, a string é preenchida com espaços';
        $help['pad']->parameter[2] = 'Pode ser RIGHT (preencher a direita), LEFT (preencher a esquerda), ou BOTH (preencher de ambos os lados). Se não for especificado é assumido que seja RIGHT.';
        $help['pad']->example = '<pad 10| -| LEFT>Gntueca</pad>';
        $help['pad']->return = '---Gnuteca';

        $help['replace']->description = 'Substitui todas as ocorrências do parâmetro 1 pelo parâmetro 2.';
        $help['replace']->parameter[0] = 'String de procura.';
        $help['replace']->parameter[1] = 'String de substituição.';
        $help['replace']->example = '<replace bom| ótimo>O gnuteca é um bom software</replace>';
        $help['replace']->return = 'O gnuteca é um ótimo software';

        $help['lower']->description = 'Converte uma string para minúsculas';
        $help['lower']->example = '<lower>MINÚSCULA</lower>';
        $help['lower']->return = 'minúscula';

        $help['date']->description = 'Retorna datas.';
        $help['date']->parameter[0] = 'Formato requerido.<br>Exemplos:<br>d = Dia;<br>m = Mês;<br>Y = Ano;<br>H = Hora;<br>i = Minutos;<br>s = Segundos;<br> Maiores Informações: http://php.net/date';
        $help['date']->example = '<date d/m/Y></date>';
        $help['date']->return = 'dd/mm/YYYY = 01/01/2009';

        $help['datemarc']->description = 'Retorna data no formato marc.';
        $help['datemarc']->example = '<datemarc>';
        $help['datemarc']->return = '110711';

        $help['ifexists']->description = 'Verifica se o parâmetro existe, caso não existe, não mostra o contéúdo.';
        $help['ifexists']->parameter[0] = 'o texto para verificação';
        $help['ifexists']->example = '<ifexists texto>algum texto</>';
        $help['ifexists']->return = 'o conteúdo original caso exista o parâmetro';

        $help['ifnotexists']->description = 'Verifica se o parâmetro NÃO existe, caso não existe, mostra o contéúdo.';
        $help['ifnotexists']->parameter[0] = 'o texto para verificação';
        $help['ifnotexists']->example = '<ifnotexists texto>algum texto</ifnotexists>';
        $help['ifnotexists']->return = 'o conteúdo original caso NÃO exista o parâmetro';

        $help['compare']->description = 'Faz comparação entre do valores.';
        $help['compare']->parameter[0] = 'Primeiro valor, para comparar com um valor da base, utilize ($001.a tag marc). Ex: ($001.a 100.a)';
        $help['compare']->parameter[1] = 'condição [ = | != | maior | menor | maior= | menor= | like | ilike ]';
        $help['compare']->parameter[3] = 'Segundo valor';
        $help['compare']->example = '<compare 1 | menor | 2> RETORNO </compare><compare luiz | ilike | ($001.a 100.a)> RETORNO </compare>';
        $help['compare']->return = 'Retorna o conteúdo de dentro das tags.';

        $help['href']->description = 'Cria um link para um conteudo.';
        $help['href']->parameter[0] = 'o link';
        $help['href']->parameter[1] = 'um separador caso existam vários links.';
        $help['href']->example = '<href $link > $description </>';
        $help['href']->return = '';

        $help['pregmatch']->description = 'Retona o conteúdo conforme uma expressão regular.';
        $help['pregmatch']->parameter[0] = 'Expressão regular';
        $help['pregmatch']->example = 'Conteúdo onde será aplicado a expressão regular.';
        $help['pregmatch']->return = 'Conteúdo encontrado.';

        $help['gtcgetmaterialcontent']->description = 'Pega o conteúdo de um etiqueta marc de um determinado material.';
        $help['gtcgetmaterialcontent']->parameter[0] = 'Número de controle do material.';
        $help['gtcgetmaterialcontent']->parameter[1] = 'Etiqueta marc.';
        $help['gtcgetmaterialcontent']->parameter[2] = 'Retorna o Prefixo';
        $help['gtcgetmaterialcontent']->parameter[3] = 'Retorna o Suffixo';
        $help['gtcgetmaterialcontent']->example = '<gtcGetMaterialContent 25| 245.a | 1 | 0 ></gtcGetMaterialContent>';
        $help['gtcgetmaterialcontent']->return = 'Título do material';

        $help['getmaterialcontent']->description = 'Pega o conteúdo de um etiqueta marc de um determinado material.';
        $help['getmaterialcontent']->parameter[0] = 'Número de controle do material.';
        $help['getmaterialcontent']->parameter[1] = 'Etiqueta marc.';
        $help['getmaterialcontent']->parameter[2] = 'Retorna o Prefixo';
        $help['getmaterialcontent']->parameter[3] = 'Retorna o Suffixo';
        $help['getmaterialcontent']->example = '<gtcGetMaterialContent 25| 245.a | 1 | 0 ></gtcGetMaterialContent>';
        $help['getmaterialcontent']->return = 'Título do material';

        $help['getauthors700aabntformat']->description = 'Regras para autor 700.a da ABNT';
        $help['getauthors700aabntformat']->example = '<getauthors700aabntformat></getauthors700aabntformat>';
        $help['getauthors700aabntformat']->return = 'Autores formatados';

        $help['gettitleabntfotmated']->description = 'Regras para título da ABNT';
        $help['gettitleabntfotmated']->example = '<gettitleabntfotmated $001.a | 245.a></gettitleabntfotmated>';
        $help['gettitleabntfotmated']->return = 'Retórna o título formatado';

        $help['gettagdescription']->description = 'Retorna a descrição de uma determinado campo que armazena indices';
        $help['gettagdescription']->parameter[0] = 'Número de controle';
        $help['gettagdescription']->parameter[1] = 'Tag marc';
        $help['gettagdescription']->parameter[2] = 'Se for 1, tenta buscar o conteúdo do pai.';
        $help['gettagdescription']->example = '<gettagdescription $001.a | 901.c | 1 >_DESCRIPTION_</gettagdescription>';
        $help['gettagdescription']->return = 'Descrição do campo. A Contante _DESCRIPTION_ sera substituida pelo conteúdo encontrado.';

        $help['gtcgettagname']->description = 'Pega o nome de uma determinada Tag.';
        $help['gtcgettagname']->parameter[0] = 'Tag MARC.';
        $help['gtcgettagname']->example = '<gtcGetTagName 245.a></gtcGetTagName>';
        $help['gtcgettagname']->return = 'Nome da Tag';

        $help['gtcseparator']->description = 'Une varias tags separando-as por um caracter determinado. O caracter \'#\' significa que é um separador cadastrado na base.';
        $help['gtcseparator']->parameter[0] = '$001.a = Parametro Obrigatório, pois a função necessita do numero de controle da obra.';
        $help['gtcseparator']->example = '<gtcSeparator $001.a | 245.a | # | 245.b | x | 246.a></gtcSeparator> <br> Para carregar os prefixos e/ou sufixos da tags, utilize um ponto de interrogação no inicio ou no final da tag. Ex: <gtcSeparator $001.a | ?245.a? | # | ?245.b? | x | ?246.a?></gtcSeparator> ';
        $help['gtcseparator']->return = 'Conteudo dos campos';

        $help['gtcgetseparator']->description = 'Retorna o separador da tag especificada. Caso o separador não estiver cadastrado para o material específico, será adicionado como padrão o caracter definido no quarto parâmetro. ';
        $help['gtcgetseparator']->parameter[0] = '$001.a = Parametro Obrigatório, pois a função necessita do numero de controle da obra.';
        $help['gtcgetseparator']->parameter[1] = 'Parametro Obrigatório, tag1.';
        $help['gtcgetseparator']->parameter[2] = 'Parametro Obrigatório, tag2.';
        $help['gtcgetseparator']->parameter[3] = 'Utiliza este para definir um separador padrão caso o material não possua um cadastrado.';
        $help['gtcgetseparator']->example = '<gtcGetSeparator $001.a | 245.a | 245.b | : ></gtcGetSeparator> <br> A função buscará o separador das tags especificadas. Ex: <gtcGetSeparator $001.a | 245.a | 245.b | : ></gtcGetSeparator> ';
        $help['gtcgetseparator']->return = 'Separador';

        $help['executeDB']->description = 'Executa função de um business.';
        $help['executeDB']->example = '<executedb>BusLibraryUnit->listLibraryUnit</executedb>';
        $help['executeDB']->return = 'Retorna a lista de bibliotecas que é gerada na função listLibraryUnit da business LibraryUnit.';

        $help['executePHP']->description = 'Executa código PHP.';
        $help['executePHP']->example = '<executephp>array("1" => "mesa", "2" => "cadeira");</executephp>';
        $help['executePHP']->return = 'Retorna Mesa e Cadeira do array.';

        $help['dbmaterialcount']->description = 'Retorna o numero de ocorrencias de uma tag na gtcMaterial ou tambem pode compara com ou outro valor.';
        $help['dbmaterialcount']->parameter[0] = 'Número de controle do material.';
        $help['dbmaterialcount']->parameter[1] = 'Etiqueta marc.';
        $help['dbmaterialcount']->parameter[2] = '[Opcional] = Condição de comparação [ = | != | maior | menor | maior= | menor= ]';
        $help['dbmaterialcount']->parameter[3] = '[Opcional] = Valor a ser comparado';
        $help['dbmaterialcount']->example = '<dbMaterialCount 25 | 700.a | maior | 3></dbMaterialCount>';
        $help['dbmaterialcount']->return = 'Conteúdo dentro da tag.';

        $help['executeSQL']->description = 'Gera select de SQL.';
        $help['executeSQL']->example = '<executesql>SELECT exemplaryStatusId, description FROM gtcExemplaryStatus</executesql>';
        $help['executeSQL']->return = 'Lista todos os estados dos exemplares. Retorna uma string ou um array.';

        $help['getevaluation']->description = 'Lista a média da avaliação do material.';
        $help['getevaluation']->parameter[0] = 'Número de controle do material';
        $help['getevaluation']->example = '<getevaluation $001.a></getevaluation>';
        $help['getevaluation']->return = 'Lista a média da avaliação do material desenhada por estrelas.';

        $help['gtcIso2709']->description = 'Mostra o material em formato ISO 2709.';
        $help['gtcIso2709']->parameter[0] = 'Número de controle do material';
        $help['gtcIso2709']->example = '<gtcIso2709 $001.a></gtcIso2709>';
        $help['gtcIso2709']->return = 'Material em formato ISO 2709';

        $help['unique']->description = 'Mostra dados repetidos uma única vez';
        $help['unique']->parameter[0] = 'Separador de texto utilizado para quebrar o texto, por padrão é -#-';
        $help['unique']->example = 'String Livro-#-Livro-#-Revista está dentro de $949.d; então use <unique>$949.d </unique>';
        $help['unique']->return = 'Livro-#-Revista';
        
        $help['getmarc21content']->description = 'Obtém dados do material na formatação MARC';
        $help['getmarc21content']->parameter[0] = 'Tag $001.a número de controle do material.';
        $help['getmarc21content']->parameter[1] = 'Campo que será ignorado no formato. Pode ser o campo todo ex: 100.a, ou somente parte, 100 para os campos 100.a e 100.b. Também pode ser usado 1 para ignorar tags como 100, 110';
        $help['getmarc21content']->parameter[2] = 'O número de parâmetros é infinito, cada parâmetro é um campo a ser ignorado.';
        $help['getmarc21content']->example = '<getMarc21Content $001.a | 949 | 001 | 000></getMarc21Content> Neste exemplo são ignoradas as tags: 949.a, 949.b, 949.., 001 e 000 ';
        $help['getmarc21content']->return = 'Conteúdo do material em formato MARC.';
        
        if ( $function )
        {
            foreach ( $help as $func => $val )
            {
                if ( !in_array($func, $function) )
                {
                    unset($help[$func]);
                }
            }
        }

        return $help;
    }

    /**
     *
     *
     * @param $function (String)
     *
     * @return Obj
     */
    public function explodeFunction($function)
    {
        $explode = preg_split('/ ([^\|]*)/', $function, -1, PREG_SPLIT_DELIM_CAPTURE);

        $amountParameters = 0;
        foreach ( $explode as $key => $exp )
        {
            $exp = trim($exp);

            if ( $key == 0 )
            {
                $func->name = strtolower($exp);
            }
            elseif ( !is_null($exp) && $exp != "|" )
            {
                $func->parameter[] = $exp;
                $amountParameters++;
            }
        }

        /**
         * Adicionado esta verificação para remover o oltimo indice que estava
         * vindo sempre "a mais" ou seja... sobrando...
         * so que antes de remove-lo... é feita uma verificaçao para garantir que este indice esta vazio
         * -- Luiz
         */
        if ( !strlen(trim($func->parameter[$amountParameters - 1])) )
        {
            $func->amountParameters = ($amountParameters - 1);
            unset($func->parameter[$func->amountParameters]);
        }

        return $func;
    }

    /**
     * Este metodo é uma função  do gnuteca function, foi criado pois o switch que seleciona a função esta muito comprido ja.
     *
     * @param object $f
     * @return string content
     */
    private function gtcSeparator($f)
    {
        $content = '';
        $controlNumber = null;
        $tagAnterior = null;
        $separator = null;
        $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        foreach ( $f->parameter as $value )
        {
            $value = trim($value);

            if ( !strlen($value) )
            {
                continue;
            }

            if ( is_null($controlNumber) )
            {
                $controlNumber = $value;
                continue;
            }

            if ( ereg("[0-9]{3}.[0-9A-Za-z]{1}", $value) )
            {
                $getPrefix = ($value[0] == "?");
                $getSuffix = ($value[strlen($value) - 1] == "?");
                $value = str_replace("?", "", $value);

                //caso seja um valor não númerico retorna string vazia
                if ( !is_numeric($controlNumber) )
                {
                    return '';
                }

                $tagContent = $busMaterial->getContentTag($controlNumber, $value);

                if ( $getPrefix && strlen($tagContent) )
                {
                    $prefix = $busMaterial->getMaterialTagPrefixSuffix($controlNumber, $value);
                    $tagContent = "{$prefix}{$tagContent}";
                }
                if ( $getSuffix && strlen($tagContent) )
                {
                    $suffix = $busMaterial->getMaterialTagPrefixSuffix($controlNumber, $value, 2);
                    $tagContent.= "{$suffix}";
                }

                if ( !strlen($tagContent) )
                {
                    $tagAnterior = null;
                    return $content;
                }
                if ( strlen($tagAnterior) && $separator == '#' )
                {
                    $separator = $busMaterial->getTagSeparator($tagAnterior, $controlNumber);
                    if ( !$separator )
                    {
                        return $content;
                    }
                    $content .= $separator[0][2];
                }
                elseif ( strlen($tagAnterior) && $separator != '#' )
                {
                    $content.= " $separator ";
                }

                $content .= $tagContent;
                $tagAnterior = $value;
            }
            elseif ( strlen($value) < 5 && strlen($tagAnterior) )
            {
                $separator = $value;
            }
        }

        return $content;
    }

    /**
     * Retorna o separadore de duas tags.
     *
     * @param object $f
     * @return string content
     */
    private function gtcGetSeparator($f)
    {
        $controlNumber = $f->parameter[0];
        $tag1 = $f->parameter[1];
        $tag2 = $f->parameter[2];
        $separator = $f->parameter[3];
        $busMaterial = $this->MIOLO->getBusiness($this->module, 'BusMaterial');

        //caso seja um valor não númerico retorna string vazia
        if ( !is_numeric($controlNumber) || !$controlNumber )
        {
            return '';
        }

        $tagSeparator = $busMaterial->getTagSeparator($tag1, $controlNumber);

        return ( $tagSeparator[0][2] ? $tagSeparator[0][2] : $separator );
    }

    /**
     * Retorna a média das notas dadas para um material
     *
     * @param object $f
     * @return string content
     */
    private function getEvaluation($f)
    {
        $controlNumber = $f->parameter[0];

        //caso seja um valor não númerico retorna string vazia
        if ( !is_numeric($controlNumber) || !$controlNumber )
        {
            return '';
        }
        else
        {
            $busMaterialEvaluation = $this->MIOLO->getBusiness($this->module, 'BusMaterialEvaluation');
            $average = $busMaterialEvaluation->getAverage($controlNumber);
            $averagePoints = $average[0][1];
            $evaluationCount = $average[0][0];

            if ( !is_null($averagePoints) && $averagePoints > 0 ) //Se tem média e ela é maior que zero
            {
                $this->MIOLO->uses('classes/controls/GStar.class.php', 'gnuteca3');
                $controls[] = $averageStar = new GStar('star' . $controlNumber, $averagePoints, true, 16);
                $averageStar->addStyle('float', 'left');
                $controls[] = new MSpan('', "{" . _M('Votos: ', $this->module) . $evaluationCount . "}");
                $averageStar = new MDiv('divEvaluation' . $controlNumber, $controls); //Retorna a média.
            }

            return $averageStar;
        }
    }

    /**
     * Retorna o material no formato ISO 2709
     *
     * @param object $f
     * @return string content
     */
    private function gtcIso2709($f)
    {
        $controlNumber = $f->parameter[0];

        //caso seja um valor não númerico retorna string vazia
        if ( !is_numeric($controlNumber) || !$controlNumber )
        {
            return '';
        }
        else
        {
            $objectIsoExport = new gIso2709Export(array( $controlNumber ));
            $material = $objectIsoExport->execute();

            return str_replace(' ', '&nbsp', new GString($material));
        }
    }
}

?>
