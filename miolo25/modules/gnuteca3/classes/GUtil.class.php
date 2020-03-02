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
 * GUtil - Usefull miscelaneous functions
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 *
 * @since
 * Class created on 29/07/2008
 *
 * */
class GUtil
{

    /**
     * Get array of "Yes" or "No" to use on MIOLO form's (MSelection/combolist/etc)
     *
     * @param $type (int): Format (0 or 1)
     *
     * @return $data (array): Array of options
     */
    public static function listYesNo($type = 0)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        if ( $type == 0 )
        {
            $data = array(
                DB_TRUE => 'Sim',
                DB_FALSE => 'Não'
            );
        }
        elseif ( $type == 1 )
        {
            $data = array(
                array( 'Sim', DB_TRUE ),
                array( 'Não', DB_FALSE )
            );
        }

        return $data;
    }

    /**
     * Get array list of "Yes" and "No"
     *
     * @param $key (String): If defined, returns specified value
     *
     * @return $data (array): Array of options
     */
    public static function getYesNo($key = null)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            DB_TRUE => 'Sim',
            DB_FALSE => 'Não'
        );
        if ( $key )
        {
            return $data[$key];
        }
        return $data;
    }

    public static function getDbActionList()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $array = array
            (
            "I" => _M("Inserir", $module),
            "U" => _M("Atualizar", $module),
            "D" => _M("Apagar", $module),
        );

        return $array;
    }

    /**
     * Compara dois array verificando se são ou não iguais
     * 
     * Retorna true se são iguais
     * Retorna false se diferentes
     *
     * //TODO avaliar se não deveriam estar na catalogação
     * 
     * @param $array1
     * @param $array2
     * @return boolean
     */
    public static function compareArray($array1, $array2)
    {
        //filtra o array tirando, dados em branco 
        if ( is_array($array1) )
        {
            $array1 = array_filter($array1, "verifyValue");

            if ( count($array1) == 0 )
            {
                $array1 = array( );
                $array1 = null;
            }
        }
        else
        {
            $array1 = null;
        }

        if ( is_array($array2) )
        {
            $array2 = array_filter($array2, "verifyValue");

            if ( count($array2) == 0 )
            {
                $array2 = null;
            }
        }
        else
        {
            $array2 = null;
        }

        // se os dois arrays forem nulos retorna true, dizendo que são iguais
        if ( is_null($array1) && is_null($array2) )
        {
            return true;
        }

        //se a quantidade for diferente, é diferente
        if ( count($array1) != count($array2) )
        {
            return false;
        }

        //faz a comparação linha a linha, comparando recursivamente
        foreach ( $array1 as $index => $content )
        {
            if ( is_array($content) && is_array($array2[$index]) )
            {
                $ok = GUtil::compareArray($content, $array2[$index]);

                if ( !$ok )
                {
                    return false;
                }

                continue;
            }

            if ( $content !== $array2[$index] )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Extrai uma relação de variaveis marc de uma string.
     *
     * //TODO avaliar se isso não deveria estar no GFunction
     *
     * @param string $text
     * @return array
     */
    public static function extractMarcVariables($text)
    {
        preg_match_all('/\$[0-9]{3}\.[0-9a-zA-Z]/', $text, $matches);
        return $matches[0];
    }

    /**
     * Return formatted money value
     *
     * //TODO avaliar real necessidade e local desta função
     *
     * @param unknown_type $value
     * @return unknown
     */
    public static function moneyFormat($value)
    {
        return number_format($value, 2, ',', '.');
    }

    /**
     * Retorna o calculo somado de um array de valores ou string
     *
     * //TODO avaliar real necessidade e local desta função
     *
     * @param Array $values ou String com operacao
     * 
     * @return float
     */
    public static function moneySum($values)
    {
        if ( is_array($values) )
        {
            foreach ( $values as $i => $value )
            {
                $values[$i] = GUtil::moneyToFloat($value);
            }
            $str = implode('+', $values);
        }
        else
        {
            $str = $values;
        }

        $GBusiness = new GBusiness();
        $value = $GBusiness->executeSelect("SELECT ({$str})");
        $value = $value[0][0];

        return GUtil::floatToMoney($value);
    }

    //TODO avaliar real necessidade e local desta função
    public static function moneyToFloat($valor)
    {
        $nao_tem_virgula = strpos($valor, ",") === false;
        $tem_ponto = strpos($valor, ".") !== false;

        if ( $nao_tem_virgula )
        {
            if ( $tem_ponto )
            {
                $pos = strrpos($valor, ".");
                $valor = substr($valor, 0, $pos) . "," . substr($valor, $pos + 1, strlen($valor) - $pos + 1);
            }
        }

        $valor = str_replace(".", "", $valor);
        $valor = str_replace(",", ".", $valor);
        //$valor = (float)$valor;
        return $valor;
    }

    //TODO avaliar real necessidade e local desta função
    public static function floatToMoney($valor)
    {
        return number_format($valor, 2, ",", ".");
    }

    /**
     * Obtém a versão do gnuteca
     * 
     * @return: string com a versão
     */
    public static function getVersion()
    {
        $MIOLO = MIOLO::getInstance();
        //tem de ser hardcoded pois a acessando da statusBar o módulo não é reconhecido
        $module = 'gnuteca3';
        $version = file($MIOLO->getModulePath($module, "VERSION"));
        $v = explode('.', $version[0]);

        return trim($v[0] . '.' . $v[1]);
    }

    /**
     * Obtém a sub versão do gnuteca
     *
     * @return: string com a versão
     */
    public static function getSubVersion()
    {
        $MIOLO = MIOLO::getInstance();
        //tem de ser hardcoded pois a acessando da statusBar o módulo não é reconhecido
        $module = 'gnuteca3';
        $version = file($MIOLO->getModulePath($module, "VERSION"));
        $v = explode('.', $version[0]);

        return trim($v[2]);
    }

    public static function getChangeLog()
    {
        $MIOLO = MIOLO::getInstance();
        //tem de ser hardcoded pois a acessando da statusBar o módulo não é reconhecido
        $module = 'gnuteca3';
        return file_get_contents($MIOLO->getModulePath($module, "ChangeLog"));
        ;
    }

    /**
     * Retorna a string de chamada para algum evento AJAX do formulario
     *
     * @param String $function
     * @param String $args pode ser string array ou objeto (que nesse caso é convertido para array)
     * @param String|Array $formName
     * @param boolean $literalJsArgs
     * @param string a classe para fazer o doLink miolo ou gnuteca
     * @return String
     */
    public static function getAjax($function, $args = null, $formName = null, $literalJsArgs = false, $class = 'miolo')
    {
        $MIOLO = MIOLO::getInstance();

        if ( is_object($args) )
        {
            $args = (array) $args;
        }

        if ( !$formName )
        {
            $formName = $MIOLO->page->getFormId();
        }

        if ( is_array($args) )
        {
            $args = GUtil::encodeJsArgs($args);
        }

        if ( !$literalJsArgs )
        {
            $args = "'{$args}'";
        }

        return $class . ".doAjax('{$function}', {$args}, '{$formName}');";
    }

    /**
     * Retorna a string de chamada para algum evento postBack do formulario
     *
     * @param String $function
     * @param String $args pode ser string array ou objeto (que nesse caso é convertido para array)
     * @param String|Array $formName
     * @param boolean $literalJsArgs
     * @param string a classe para fazer o doLink miolo ou gnuteca
     * @return String
     */
    public static function getPostBack($function, $args = null, $formName = null, $literalJsArgs = false, $class = 'miolo')
    {
        $MIOLO = MIOLO::getInstance();

        if ( is_object($args) )
        {
            $args = (array) $args;
        }

        if ( !$formName )
        {
            $formName = $MIOLO->page->getFormId();
        }

        if ( is_array($args) )
        {
            $args = GUtil::encodeJsArgs($args);
        }

        if ( !$literalJsArgs )
        {
            $args = "'{$args}'";
        }

        return $class . ".doPostBack('{$function}', {$args}, '{$formName}');";
    }

    /**
     * Reconstrói a última função ajax usada
     * 
     * @return string retorna a última função ajax usada
     */
    public static function getLastAjax()
    {
        $MIOLO = MIOLO::getInstance();
        $formName = MIOLO::_REQUEST('__FORMSUBMIT') ? MIOLO::_REQUEST('__FORMSUBMIT') : $MIOLO->page->getFormId();
        $ajaxFuntion = GUtil::getAjaxFunction();
        $ajaxArgument = GUtil::getAjaxEventArgs();

        return "miolo.doAjax( '{$ajaxFuntion}', '{$ajaxArgument}', '{$formName}' );";
    }

    /**
     * Retorna um link (miolo.doLink) para uma ação
     *
     * @param string $action ex: main:configuration:libraryUnit
     * @return string
     */
    public static function getActionLink($action, $formName=null, $class = 'miolo')
    {

        $MIOLO = MIOLO::getInstance();
        $url = $MIOLO->getConf('home.url');

        if ( !$formName )
        {
            $formName = $MIOLO->page->getFormId();
        }

        return $class . ".doLink('{$url}/index.php?module=gnuteca3&action={$action}', '{$formName}');";
    }

    /*
     * Codifica um array para utilização dentro de função ajax.
     *
     */
    public static function encodeJsArgs(array $args)
    {
        foreach ( $args as $key => $val )
        {
            $str[] = "{$key}|~|{$val}";
        }
        return implode('|#|', $str);
    }

    /*
     * Decodifica os argumentos passados através do encodeJsArgs
     *
     */
    public static function decodeJsArgs($value)
    {
        if ( !strpos($value, '|~|') ) //Se não tem nenhum atributo
        {
            return $value; //Retorna a string pura.
        }

        $values = explode('|#|', $value);
        $result = array( );

        if ( $values )
        {
            foreach ( $values as $v )
            {
                list($key, $val) = explode('|~|', $v);
                $result[$key] = $val;
            }
        }

        return (object) $result;
    }

    /*
     * Retorna os argumentos passados na função ajax.
     * Note que ainda precisa ser utilizada o decodeJsArgs, caso necessário
     *
     */
    public static function getAjaxEventArgs()
    {
        $MIOLO = MIOLO::getInstance();
        return MIOLO::_REQUEST("{$MIOLO->page->getFormId()}__EVENTARGUMENT");
    }

    /**
     * Retorna qual é a função/chamada ajax executada neste momento
     * @return <string> função/chamada ajax executada neste momento
     */
    public static function getAjaxFunction()
    {
        $MIOLO = MIOLO::getInstance();
        return MIOLO::_REQUEST("{$MIOLO->page->getFormId()}__EVENTTARGETVALUE");
    }

    /**
     * Retorna string da açao javascript de fechar uma janela de diálogo
     *
     * @return <string> açao javascript de fechar uma janela de diálogo
     */
    public static function getCloseAction($javascript = false)
    {
        $result = 'gnuteca.closeAction();';

        if ( $javascript )
        {
            $result = 'javascript:' . $result;
        }

        return $result;
    }

    /**
     * Funcao utilizada para alinhar campos que nao estao sendo renderizados corretamente nos formularios
     *
     * @param array $controls
     * @return MContainer
     */
    public static function alinhaForm($controls)
    {
        $controls = GForm::accessibility($controls);
        $isControlsArrayInitial = is_array($controls);

        if ( !is_array($controls) )
        {
            $controls = array( $controls );
        }

        $container = new MContainer('hctAlinhaForm' . rand(), $controls, null, MControl::FORM_MODE_SHOW_SIDE);
        //$container->setControls( GContainer::parseControls( $container->getControls() ) );

        if ( $isControlsArrayInitial ) //Se recebeu array, retorna array, senao apenas o MContainer
        {
            return array( $container );
        }

        return $container;
    }

    /**
     * Retorna url completa da imagem do tema
     *
     * @param string $imageName exemplo report-16x16.png
     * @return string url completa
     */
    public static function getImageTheme($imageName)
    {
        $MIOLO = MIOLO::getInstance();
        return $MIOLO->getUI()->getImageTheme($MIOLO->getTheme()->getId(), $imageName);
    }

    /**
     * Execute um teste unitário dentro do gnuteca
     *
     * @param <string> $testFile TestLibraryUnit.class.php';
     * @return <string> shell return string
     */
    public static function executeUnitTest($testFile, $format = true)
    {
        $MIOLO = MIOLO::getInstance();
        $serverPath = $MIOLO->getConf('home.miolo');
        $serverPath .= '/modules/gnuteca3/unittest/';

        $filePath = $serverPath . $testFile;

        //verifica a existencia do arquivo
        if ( !file_exists($filePath) )
        {
            throw new Exception(_M('Teste unitário não existe : "@1" ', 'gnuteca3', $filePath));
        }

        ob_start();
        $command = "cd ../modules/gnuteca3/unittest/; phpunit $testFile";
        system($command);
        $message = ob_get_contents();
        ob_clean();

        $tempMessage = explode("\n", $message);

        //nulifica linha só com espaços
        if ( is_array($tempMessage) )
        {
            foreach ( $tempMessage as $line => $info )
            {
                $tempMessage[$line] = trim($info);

                if ( $tempMessage[$line][0] == '.' )
                {
                    $tempMessage[$line][0] = '';
                    $tempMessage[$line] = trim($tempMessage[$line]);
                }
            }
        }

        $tempMessage = array_filter($tempMessage); //remove linhas vazias
        $tempMessage = array_values($tempMessage); //reajeita os arrays
        $lastLine = $tempMessage[count($tempMessage) - 1];
        $result = stripos($lastLine, 'OK') === 0 ? true : false;

        if ( $format )
        {
            $message = implode("\n", $tempMessage);
            $message = str_replace("OK", "<b><font color ='green'>OK</font></b>", $message);
            $message = str_replace("FAILURES", "<b><font color ='red'>FAILURES</font></b>", $message);
            $message = str_replace("FAIL", "<b><font color ='red'>FAIL</font></b>", $message);
        }

        return array( $result, $message );
    }

    /**
     * Gera o conteúdo de um test unitário baseado em um template.
     * 
     * @param stdClass $data dados do formuláio
     * @param string $alias no do business sem 'Bus' na frente.
     * @return string o conteúdo do teste unitário
     */
    public function generateUnitTest($data, $alias)
    {
        $dataContent = _utLog($data);
        $templatePath = $this->MIOLO->getConf('home.modules') . '/gnuteca3/unittest/gBusinessUnitTest.template';

        $template = file_get_contents($templatePath);
        $template = str_replace('$nowDate', GDate::now()->getDate(GDate::MASK_DATE_USER), $template);
        $template = str_replace('$busName', $alias, $template);
        $template = str_replace('$formData;', $dataContent, $template);

        return $template;
    }

    /*
      public function startGCron()
      {
      $logFile    =  "/tmp/gcron.log";
      $fullPath   = str_replace('html','', getcwd() );
      $fileName   =  "modules/gnuteca3/misc/scripts/";

      //se o script não existir retorna falso
      if ( !file_exists( $fullPath .$fileName ) )
      {
      return false;
      }

      //executa em segundo plano
      $exec = "cd {$fullPath}{$fileName} ; php gcron.php > $logFile &";

      exec( $exec);
      chmod( $logFile, 0777 );

      return true;
      }
     */

    /**
     * Retorna o tempo atual, diferenciando do tempo anterior.
     * Função muito útil para debug.
     *
     * @staticvar integer $tempo
     * @return string
     */
    public static function getTime()
    {
        static $tempo;

        if ( $tempo == NULL )
        {
            $tempo = microtime(true);
        }
        else
        {
            return 'Tempo (segundos): ' . (microtime(true) - $tempo) . '';
        }
    }

    /**
     * StrPad compatível com UTF8.
     *
     * @param string $input
     * @param integer $pad_length
     * @param string $pad_string
     * @param integer $pad_type
     * @return integer
     */
    public static function strPad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
    {
        $diff = mb_strlen($input, 'ISO-8859-1') - mb_strlen($input, 'UTF-8');
        return str_pad($input, $pad_length + $diff, $pad_string, $pad_type);
    }

    /**
     * Função de acesibilidade, adiciona tabIndex, alt e title ao campo passado
     *
     * @param MControl $field
     * @param integer $tabIndex
     * @param string $label
     */
    public static function accessibility($field, $tabIndex, $label = null )
    {
        //tenta obter a label do campo campo não tenha sido passada
        if ( !$label )
        {
            $label = $field->label;
        }

        //IMPORTANTE: esta implementação somente foi pois existem alguns problema de acessibilidade em alguns browsers
        // é importante reavaliar essas funcionalidades em um futuro (quando novas versões sairem)
        //caso especifico do MImageButton/MImageLink , que tem diferenças em acessabilidade IE e firefox
        if ( $field instanceof MImageLink )
        {
            //verifica se ie Internet explorer, essa verificação somente é feita em função de um problema com NVDA e IE8
            $isIE = ereg('MSIE ([0-9].[0-9]{1,2})', $_SERVER['HTTP_USER_AGENT']);

            //caso for firefox ou não ie
            if ( !$isIE )
            {
                //coloca o link como inacessível por tabulação
                $field->addAttribute('tabindex', '-1');

                $onPressEnter = $field->href;
                //determina a imagem como elemento principal
                $field = $field->image;
                //para funcionar o enter no Chrome, pois por padrão o enter não abre links no chrome
                $field->addAttribute('onpressenter', $onPressEnter);
            }
        }

        //aplica regra de tabulação
        if ( $tabIndex )
        {
            $field->addAttribute('tabindex', $tabIndex);
        }

        //aplica alt e title para que o leitor informe o usuário
        if ( $label )
        {
            $field->addAttribute('alt', $label);
            $field->addAttribute('title', $label);
        }
    }

    /**
     * Função que procurar dentro de um array de campos (ou mdiv/mcontainer)
     * o campo que você precisa.
     *
     * Caso encontre retorna o objeto do miolo, caso não retorna nulo
     *
     * @param string $fieldId código do campo a ser localizado
     * @param multi $fields array de campos a procurar, ou container, ou div
     * @return MControl Caso encontre retorna o objeto do miolo, caso não retorna nulo
     */
    public static function getField($fieldId, $fields)
    {
        //caso não tenha passado o código do campo facilita tudo retornando nulo
        if ( !$fieldId )
        {
            return null;
        }

        //caso seja um array tenta localizar para cada campo
        if ( is_array($fields) )
        {
            foreach ( $fields as $key => $field )
            {
                $ok = GUtil::getField($fieldId, $field);

                if ( $ok )
                {
                    return $ok;
                }
            }
        }
        //caso seja container tenta localizar nos campos internos
        else if ( $fields instanceof MContainer )
        {
            return $fields->getControlById($fieldId);
        }
        //caso seja div tenta localizar nos campos internos
        else if ( $fields instanceof MDIV )
        {
            if ( $fields->id == $fieldId )
            {
                return $fields;
            }

            $ok = GUtil::getField($fieldId, $fields->getInner());

            if ( $ok )
            {
                return $ok;
            }
        }
        //caso seja um objeto único tenta objter pelo seu id
        else if ( is_object($fields) )
        {
            if ( $fields->id == $fieldId )
            {
                return $fields;
            }
        }

        return null;
    }

    /**
     * Obtem MImage com a foto da pessoa
     *
     * @example GUtil::getPersonPhoto($person->personId, array('height'=>'90px') 
     *
     * @param integer $personId
     * @param array $attributes
     * @return MImage com foto da pessoa
     *
     */
    public static function getPersonPhoto($personId, $attributes)
    {
        $image = NULL;
        
        if ( MUtil::getBooleanValue(SAGU_PHOTO_INTEGRATION) && SAGU_URL != '' )
        {
            $personIdCoded = urlencode(base64_encode(gzcompress($personId, 9)));
            $url = SAGU_URL . '/index.php?module=basic&action=getphoto&personId=' . $personIdCoded;
            
            $image = new MImage('personPhoto' . $personId, _M('Foto da pessoa @1', 'gnuteca3', $personId), $url, $attributes);
        }
        // Se tiver url própria do cliente 
        else if ( PHOTO_URL != '' )
        {
            $MIOLO = MIOLO::getInstance();            
            $busPerson = $MIOLO->getBusiness('gnuteca3', 'BusPerson');
            $person= $busPerson->getPerson($personId);
            $url = PHOTO_URL;
            
            //Para cada atributo da classe que estiver na PHOTO_URL 
            foreach ( $person as $attrib => $value)
            {
                if ( !is_object($value) )
                {
                    $url = str_replace('$'.$attrib,$value, $url);
                }
            }

        }
        else
        {
            $MIOLO = MIOLO::getInstance();
            $busFile = $MIOLO->getBusiness('gnuteca3', 'BusFile');
            $busFile->folder = 'person';
            $busFile->fileName = $personId . '.';

            $file = $busFile->searchFile(true);
            $file = $file[0];

            //imagem padrão caso não tenha foto
            if ( !$file )
            {
                $file->basename = 'default.png';
                $personId = 'default';
            }

            //adiciona atributos passados
            if ( is_array($attributes) )
            {
                foreach ( $attributes as $atribute => $value )
                {
                    $att .= '&' . $atribute . '=' . $value;
                }
            }

            $url = 'file.php?folder=person&file=' . $file->basename . $att;
        }

        if ( $image == NULL )
        {
            $image = new MImage('personPhoto' . $personId, _M('Foto da pessoa @1', 'gnuteca3', $personId), $url, $attributes);
        }

        return $image;
    }

    /**
     * Retorna uma string com informações de memória, utilizado para acompanhamento e log de uso de memória do gnuteca.
     * 
     * @return string com informações de memória
     */
    public static function getMemoryInformation()
    {
        $limitMemory = ini_get('memory_limit');
        $allocMemory = number_format(memory_get_usage() / 1048576, 2) . "M";
        $scriptMemory = number_format(memory_get_peak_usage(0) / 1048576, 2) . "M";
        $realMemory = number_format(memory_get_peak_usage(1) / 1048576, 2) . "M";

        $result = _M('[Total= @1][Alocada = @2][Script= @3]', 'gnuteca3', $realMemory, $allocMemory, $scriptMemory);
        $result .= _M('[Limite= @1]', 'gnuteca3', $limitMemory);

        return $result;
    }

    /**
     * Um strip_tags que só tira a tag selecionadas
     * 
     * @param string $str conteúdo
     * @param mixed $tags string ou array
     * @param boolean $stripContent
     * 
     * @return string
     */
    public static function strip_only($str, $tags, $stripContent = false)
    {
        $content = '';

        if ( !is_array($tags) )
        {
            $tags = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags)) : array( $tags ));

            if ( end($tags) == '' )
            {
                array_pop($tags);
            }
        }

        foreach ( $tags as $tag )
        {
            if ( $stripContent )
            {
                $content = '(.+</' . $tag . '[^>]*>|)';
            }

            $str = preg_replace('#</?' . $tag . '[^>]*>' . $content . '#is', '', $str);
        }

        return $str;
    }
    
    /**
     * Método público e estático para retirar acentos de strings.
     * 
     * @param String $string String com acentos.
     * @return String sem acentos. 
     */
    public static function unaccent($string)
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        
        $db = new MBusiness($module);
        $sql = new MSQL();
        $sql->setColumns("unaccent(?)");
        
        $result = $db->query($sql, array($string))->result;
        
        return $result[0][0];
    }
    
    /**
     * Método que verifica a sintaxe de um script php.
     * Útil para verificar se um código está ok antes de ser executado em um eval.
     * 
     * @param String $code Código php a ser checada a sintaxe.
     * @return boolean Retorna verdadeiro se não houver erro de sintaxe no código informado. 
     */
    public static function checkSyntax($code)
    {
        return @eval('return true;' . $code);
    }
    
    /**
     * Função parecida com o explode mais que suporta mais de um parâmetro.
     * Função criada especialmente para a ordenção de kardex chamada pela função GMaterialDetail::getKardexFields()
     * FIXME não realizei testes para verificar o funcionamento em outros casos.
     * @param type Array $delimiters
     * @param type $string
     * @return type Array()
     */
    public static function multiexplode ($delimiters,$string) 
    {
        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }
    
    /**
     * Função criada para ordenar uma matriz através da posição de um vetor
     * Usada na GMaterialDetail para ordenar o conteúdo pela posição na fila de reservas
     * 
     * @param type Array $matriz
     * @param type $int
     * @return type Array()
     */

    public static function orderMatrizByPositionVector($matriz, $column_number)
    {
        $newVector = array();
        foreach( $matriz as $i => $data )
        {
            $newVector[$data[$column_number]][] = $data;
        }

        ksort($newVector, SORT_NUMERIC);

        $matriz = array();
        foreach ( $newVector as $i => $data)
        {
            foreach ( $data as $j => $dataI )
            {
                $matriz[] = $dataI;
            }
        }
        return $matriz;
    }
    
    public static function convertBooleanToString($param)
    {
        return $param ? "Y" : "N";
    }

}

/**
 * FIXME função chamado no compareArray para verificar se valor é nulo
 * Feito fora da classe para compatibilidade com PHP 5.2.4
 *
 * @param String $value
 * @return boolean
 */
function verifyValue($value)
{
    if ( $value )
    {
        return true;
    }
    if ( $value == '0' )
    {
        return true;
    }
    else
    {
        return false;
    }
    
  
}

?>