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
 * Class SAGU
 *
 * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Equipe Solis [sagu2@solis.coop.br]
 *
 * @since
 * Class created on 14/03/2004
 */

/**
 * Alias to PHP function var_dump(), but with some extra features. vd() puts HTML <pre> tags
 * on the dumped data for a better view.
 *
 * @param $1, $2, ..., $n (untyped): vd() may take as many parameters as you want. As a
 *         special behavior, if its last parameter is a boolean TRUE, the execution is interrupted
 *         with a call to exit().
 */
function vd()
{
    $numArgs = func_num_args();
    if ( $numArgs > 1 && is_bool(func_get_arg($numArgs - 1)) )
    {
        $numArgs--;
        $exit = func_get_arg($numArgs);
    }
    else
    {
        $exit = false;
    }

    echo ('<div align="left"><pre>');
    for ( $i = 0; $i < $numArgs; $i++ )
    {
        var_dump(func_get_arg($i));
    }
    echo ('</pre></div>');

    if ( $exit )
    {
        exit();
    }
}

/**
 * Alias to PHP function var_dump(), but with some extra features. flog() dumps all data to a
 * special file (pipe) so that it can be read out of the browser context. It has a script called
 * flog.sh, under tools directory, that helps reading out the pipe file contents.
 *
 * @param $1, $2, ..., $n (untyped): flog() may take as many parameters as you want.
 */
function flog()
{
    if ( file_exists('/tmp/var_dump') )
    {
        $numArgs = func_num_args();
        $dump = '';

        for ( $i = 0; $i < $numArgs; $i++ )
        {
            $dump .= var_export(func_get_arg($i), true) . "\n";
        }

        $f = fopen('/tmp/var_dump', 'w');
        fwrite($f, $dump);
        fclose($f);
    }
}

/**
 * Class used to call mainly functions used in Sagu2
 */
class SAGU
{

    /**
     * FIXME: Fazer de forma melhor, talvez regexp...
     * Convert money
     *
     * @param (int) $valueInCents - Value in cents
     * @param (string) $prefix - R$, U$, null...
     * @param (string) $decimalSeparator - Ex.: ","
     * @param (string) $chiliadSeparator - Ex.: "."
     * @param (int) $decimal_places - Ex.: "2"
     * @return value formated Ex.: R$1,00
     */
    public static function convertMoney($valueInCents, $prefix = null, $decimal_separator = ',', $thousand_separator = '.', $decimal_places = 2)
    {
        $num = str_pad($valueInCents, $decimal_places + 1, '0', STR_PAD_LEFT);
        $len = strlen($num);
        unset($newNum);

        for ( $i = $len - 1; $i >= 0; $i-- )
        {
            $newNum .= $num[$i];
            if ( $i == $len - $decimal_places )
            {
                $newNum .= $decimal_separator;
            }
            elseif ( ($len - $i - $decimal_places) % 3 == 0 && $len - $i > $decimal_places && $i > 0 )
            {
                $newNum .= $thousand_separator;
            }
        }

        return $prefix . strrev($newNum);
    }

    /**
     * Process every SQL parameter escaping data when necessary to avoid SQL injection.
     *
     * @param $sql (string): parameterized SQL with one "?" for each parameter
     *         $params (array): array containing data to be positionally substituted by
     *         each "?" symbol found in $sql.
     *         $upper (boolean): optional parameter indicating whether to uppercase each
     *         $params item or to leave it as is.
     */
    public static function prepare($sql, $params, $upper = true)
    {
        global $MIOLO;

        // Feito para funcionar no SUnitTest
        if ( !$MIOLO )
        {
            $MIOLO = MIOLO::getInstance();
        }

        $originalSql = $sql;
        $originalParams = $params;

        if ( isset($params) )
        {
            if ( is_object($params) )
            {
                foreach ( $params as $k => $v )
                {
                    $params_[] = $v;
                }
                $params = $params_;
            }
            elseif ( !is_array($params) )
            {
                $params = array(
                    $params);
            }
        }

        // convert all field values to uppercase
        if ( $upper )
        {
            for ( $i = 0; $i < count($params); $i++ )
            {
                $bs = new BString($params[$i]);
                $params[$i] = $bs->toUpper();
            }
        }

        //
        if ( substr_count($sql, '?') != count($params) )
        {
            $MIOLO->error(_M('Número de parâmetros inválidos! (@1)', 'basic', $sql));
        }

        //
        if ( substr_count($sql, '?') != count($params) )
        {
//            /*
//             * Debug code
//             */
//            for ( $i = 0; $i < count($originalParams); $i++ )
//            {
//                $p .= "\n[" . $i . "] = " . $originalParams[$i];
//            }
//            echo (
//<<<HERE
//<!--
//module: {$MIOLO->getCurrentModule()}
//action: {$MIOLO->getCurrentAction()}
//sql: $originalSql
//params: $p
//trace:
//HERE
//            );
//            debug_print_backtrace();
//            echo(
//<<<HERE
//-->
//
//HERE
//            );
            /*
             * End of debug code
             */

            // Captura debug
            ob_start();
            debug_print_backtrace();
            ob_end_flush();
            $debugBacktrace = ob_get_contents();
            ob_clean();

//            $divErrors = new MExpandDiv('divErrors', $debugBacktrace);
            $htmlDebug = '<a href="javascript: return;" onclick="document.getElementById(\'sqlDebug\').style.display=\'block\'">Exibir erros</a>';
            $htmlDebug .= '<div id="sqlDebug" style="display: none"><pre>' . $debugBacktrace . '</pre></div>';
            $MIOLO->error(_M('Número de parâmetros inválido! @1', 'basic', $htmlDebug));
        }

        $i = 0;

        while ( true )
        {
            $pos = strpos($sql, '?');

            if ( $pos === false )
            {
                $prepared .= $sql;
                break;
            }
            else
            {
                if ( $pos > 0 )
                {
                    $prepared .= substr($sql, 0, $pos);
                }

                if ( strlen($par = $params[$i++]) > 0 )
                {
                    $prepared .= "'" . addslashes($par) . "'";
                    // $prepared .= "'" . str_replace("'","''",$par) . "'";
                }
                else
                {
                    $prepared .= ' NULL';
                }
                // cut sql to process next parameter
                $sql = substr($sql, $pos + 1);
            }
        }

        // The following code is used to automatically update the basLog table
        $user = $MIOLO->getLogin();
        $user = $user->id;

        if ( substr(trim($prepared), 0, 6) == 'UPDATE' )
        {
            // find the last WHERE clause and use it as the basLog update's WHERE clause
            $where = strstr($prepared, 'WHERE');
            while ( strstr(substr($where, 5), 'WHERE') !== false )
            {
                $where = strstr(substr($where, 5), 'WHERE');
            }
            $where = substr($where, 5);
            $aux = substr($prepared, strpos($prepared, 'UPDATE') + 6, strlen($prepared));
            $aux = explode(' ', $aux);

            foreach ( $aux as $row )
            {
                if ( $row != '' )
                {
                    $table = $row;
                    break;
                }
            }

            $log = "UPDATE $table
                       SET userName = '$user',
                           dateTime = now(),
                           ipAddress = '" . $_SERVER['REMOTE_ADDR'] . "'
                     WHERE $where";
            return array(
                $prepared,
                $log );
        }
        elseif ( substr(trim($prepared), 0, 6) == 'INSERT' )
        {
            $pos = strpos($prepared, ')');
            $pos2 = strrpos($prepared, ')');
            $prepared = substr($prepared, 0, $pos) . ", userName, ipAddress" . substr($prepared, $pos, $pos2 - $pos) . ",'$user','" . $_SERVER['REMOTE_ADDR'] .
                    "');";
        }

        return $prepared;
    }

    /**
     * Gets the current css theme name
     *
     * @return returns the name of the css theme specified in miolo.conf
     */
    public static function getCurrentTheme()
    {
        $MIOLO = MIOLO::getInstance();

        return $MIOLO->getConf('theme.main');
    }

    /**
     * Authenticate an user.
     *
     * @param $uid User login
     * @param $passwd User password
     * @return True if user authentication was sucessful. Otherwise, false.
     */
    public static function authenticate($uid, $passwd)
    {
        $MIOLO = MIOLO::getInstance();
        $retVal = false;

        if ( SAGU::getParameter('BASIC', 'AUTH_METHOD') == 'SAGU' )
        {
            $business = $MIOLO->getBusiness('basic', 'BusPerson');
        }
        elseif ( SAGU::getParameter('BASIC', 'AUTH_METHOD') == 'LDAP' )
        {
            $MIOLO->import('classes::security::mauthldap');
            $business = new mAuthLdap();
        }

        if ( isset($business) )
        {
            $retVal = $business->authenticate($uid, $passwd, false);
        }

        return $retVal;
    }

    /**
     * Function that create a pessword for new user
     *
     * @return Return the password created.
     */
    public static function createPassword()
    {
        // Caracteres de cada tipo
        $combinations = array( );

        // Combinacoes do tipo letras + numeros
        if ( SAGU::getParameter('BASIC', 'AUTOMATIC_PASSWORD_GENERATION_SOURCE') == 'ALPHANUMERIC' )
        {
            $combinations[] = 'abcdefghijklmnopqrstuvwxyz';
            $combinations[] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        // numeros (AUTOMATIC_PASSWORD_GENERATION_SOURCE = NUMERIC)
        $combinations[] = '1234567890';

        // Agrupa todos os tipos de de caracteres
        $caracteres = implode('', $combinations);

        // Obtem o tamanho dos caracteres agrupados
        $len = strlen($caracteres);

        $return = null;

        for ( $count = 1;
                    $count <= SAGU::getParameter('BASIC', 'PASSWORD_INITIAL_SIZE');
                    $count++ )
        {
            // Cria um número aleatório de 1 até $len para pegar um dos caracteres
            $rand = mt_rand(1, $len);

            // Concatenado o caracteres gerado aleatório na variável $retorno
            $return .= $caracteres[$rand - 1];
        }

        return $return;
    }

    /**
     * Obtem validador para campos do tipo Senha no SAGU, de acordo com preferencia.
     *
     * @param string $id
     * @param string $label
     * 
     * @return MValidator
     */
    public static function getPasswordValidator($id, $label, $type = 'optional')
    {
        $validator = SAGU::getParameter('BASIC', 'AUTOMATIC_PASSWORD_GENERATION_SOURCE') == 'NUMERIC' ?
                new MIntegerValidator($id, $label, $type) :
                new MPasswordValidator($id, $label, $type);

        $validator->min = SAGU::getParameter('BASIC', 'PASSWORD_MIN_SIZE');
        $validator->max = SAGU::getParameter('BASIC', 'PASSWORD_MAX_SIZE');

        return $validator;
    }

    /**
     * Get a boolean value and return in text format as "Yes" or "Not"
     *
     * @param $array (array): Array containing the list
     * @param $pos (int): Specific position to modify
     * @return (array): Return the array with specific fields parsed
     */
    public static function booleanToText($array, $pos)
    {
        global $module;
        if ( is_array($array) )
        {
            if ( (strlen($pos) >= 0) && ($pos >= 0) )
            {
                for ( $x = 0; $x <= count($array); $x++ )
                {
                    if ( strlen($array[$x][$pos]) > 0 )
                    {
                        $value = $array[$x][$pos];
                        if ( $value == 't' )
                        {
                            $value = _M('Sim', $module);
                        }
                        elseif ( $value == 'f' )
                        {
                            $value = _M('Não', $module);
                        }
                        $array[$x][$pos] = $value;
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Format to CPF format - 999.999.999-99
     *
     * @param $text: CPF number without delimiters
     * @return $text: Return CPF in CPF format
     */
    public static function convertInCPFFormat($cpf)
    {
        $cpfFormat = substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);

        return $cpfFormat;
    }

    /**
     * Format CPF without delimiters
     *
     * @param $text: CPF number if not delimiters
     * @return $text: Return CPF in CPF format
     */
    public static function convertInCPFWithoutDelimiters($cpf)
    {
        $cpfFormat = str_replace(array( ".", ",", "/", "-" ), "", $cpf);

        return $cpfFormat;
    }

    /**
     * Get a arrays, number of buttons and return a buttons generated
     * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
     *
     * @param $msg (string): string containing the  msg | $msg
     * @param $goto (array): Array containing the array informations goto | $goto[$i][0]
     * @param $event (array): Array containing the array informations event | $event[$i][0]
     * @param $label (array): Array containing the array labels of buttons | $label[$i][0]
     * @param $buttons (int): Number of buttons on this question
     * @return (array): Return the array with specific fields parsed
     */
    public static function manyButtonsQuestion($msg, $goto, $event, $label, $buttons)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();
        $prompt = new Prompt(_M('Confirmação'), $msg, $MIOLO->url_home . '/images/question.gif');
        $prompt->SetType('question');

        for ( $i = 0; $i < $buttons; $i++ )
        {
            $prompt->AddButton($label[$i][0], $goto[$i][0], $event[$i][0]);
        }

        $MIOLO->Prompt($prompt);
    }

    /**
     * Check if parameter is exist in basConfig or don't have value.
     * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
     *
     * @param $parameter (string): string containing a name of parameter of basConfig parameter
     * @return (array): Return true if is existent value of parameter
     */
    public static function checkParameter($parameter)
    {
        $sql = 'SELECT value
                    FROM basConfig
                    WHERE parameter = ? ';
        $args[] = $parameter;
        $result = SDatabase::query($sql, $args);

        return (strlen($result[0][0]) > 0);
    }

    public static function listSex()
    {
        // FIXME: Should the keys be lowercase here or uppercase in getSex()?
        $data = array(
            'M' => _M('Masculino', 'basic'),
            'F' => _M('Feminino', 'basic') );

        return $data;
    }

    public static function getSex($key)
    {
        // FIXME: Should the keys be lowercase here or uppercase in setSex()?
        $data = array(
            'm' => _M('Masculino', 'basic'),
            'f' => _M('Feminino', 'basic') );

        return $data[$key];
    }

    public static function listTrueFalse($type = 0, $capital = false)
    {
        // for MSelection
        if ( $type == 0 )
        {
            if( !$capital )
            {
                $data = array(
                    't' => _M('Sim', 'basic'),
                    'f' => _M('Não', 'basic') );
            }
            else
            {
                $data = array(
                    't' => _M('SIM', 'basic'),
                    'f' => _M('NÃO', 'basic') );
            }
        }
        // for MRadioButtonGroup
        elseif ( $type == 1 )
        {
            if( !$capital )
            {
                $data = array(
                    array(
                        _M('Sim', 'basic'),
                        't' ),
                    array(
                        _M('Não', 'basic'),
                        'f' ) );
            }
            else
            {
                $data = array(
                    array(
                        _M('SIM', 'basic'),
                        't' ),
                    array(
                        _M('NÃO', 'basic'),
                        'f' ) );
            }
        }
        return $data;
    }

    public static function getTrueFalse($key)
    {
        $data = array(
            't' => _M('Verdadeiro', 'basic'),
            'f' => _M('Falso', 'basic') );

        return $data[$key];
    }

    public static function listTrueFalseIndifferent($type = 0)
    {
        // for MSelection
        if ( $type == 0 )
        {
            $data = array(
                't' => _M('Sim', 'basic'),
                'f' => _M('Não', 'basic'),
                '' => _M('Indiferente', 'basic') );
        }
        // for MRadioButtonGroup
        elseif ( $type == 1 )
        {
            $data = array(
                array(
                    _M('Sim', 'basic'),
                    't' ),
                array(
                    _M('Não', 'basic'),
                    'f' ),
                array(
                    _M('Indiferente', 'basic'),
                    '' ) );
        }

        return $data;
    }

    public static function getTrueFalseIndifferent($key)
    {
        $data = array(
            't' => _M('Verdadeiro', 'basic'),
            'f' => _M('Falso', 'basic'),
            '' => _M('Indiferente', 'basic') );

        return $data[$key];
    }

    public static function listAccountTypes()
    {
        // FIXME: This seems to be a hard code.
        $data = array(
            '01' => _M('Conta corrente', 'basic'),
            '05' => _M('Poupança', 'basic') );

        return $data;
    }

    public static function listYesNo($type = 0, $capital = false)
    {
        if ( $type == 0 )
        {
            if( !$capital )
            {
                $data = array(
                    DB_TRUE => _M('Sim', 'basic'),
                    DB_FALSE => _M('Não', 'basic') );
            }
            else
            {
               $data = array(
                    DB_TRUE => _M('SIM', 'basic'),
                    DB_FALSE => _M('NÃO', 'basic') ); 
            }
        }
        elseif ( $type == 1 )
        {
            if( !$capital )
            {
                $data = array(
                    array(
                        _M('Sim', 'basic'),
                        DB_TRUE ),
                    array(
                        _M('Não', 'basic'),
                        DB_FALSE ) );
            }
            else
            {
                $data = array(
                    array(
                        _M('SIM', 'basic'),
                        DB_TRUE ),
                    array(
                        _M('NÃO', 'basic'),
                        DB_FALSE ) );                
            }
        }

        return $data;
    }

    public static function getYesNo($key)
    {
        $data = array(
            't' => _M('Sim', 'basic'),
            'f' => _M('Não', 'basic') );

        return $data[$key];
    }

    public static function listInOutTransition()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            'i' => _M('Entrada', $module),
            'o' => _M('Saída', $module),
            't' => _M('Transição', $module) );

        return $data;
    }

    public static function getInOutTransition($key)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            'i' => _M('Entrada', $module),
            'o' => _M('Saída', $module),
            't' => _M('Transição', $module) );

        return $data[$key];
    }

    public static function listInOut()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            't' => _M('Entrada', $module),
            'f' => _M('Saída', $module) );

        return $data;
    }

    public static function getInOut($key)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            't' => _M('Entrada', $module),
            'f' => _M('Saída', $module) );

        return $data[$key];
    }

    public static function listPersonTypes()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            'P' => _M('Física', $module),
            'L' => _M('Jurídica', $module) );

        return $data;
    }

    public static function listModules($type = 0)
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        // For MSelection
        if ( $type == 0 )
        {
            $data = array(
                'ACADEMIC' => _M('Acadêmico', $module),
                'ACCOUNTANCY' => _M('Contábil', $module),
                'FINANCE' => _M('Financeiro', $module),
                'SELECTIVEPROCESS' => _M('Processo seletivo', $module),
                'TRAINING' => _M('Estágio', $module),
            );
        }
        // For MRadioButtonGroup
        elseif ( $type == 1 )
        {
            $data = array(
                array(
                    _M('Acadêmico', $module),
                    'ACADEMIC' ),
                array(
                    _M('Contábil', $module),
                    'ACCOUNTANCY' ),
                array(
                    _M('Básico', $module),
                    'BASIC' ),
                array(
                    _M('Financeiro', $module),
                    'FINANCE' ),
                array(
                    _M('Institucional', $module),
                    'INSTITUTIONAL' ),
                array(
                    _M('Processo seletivo', $module),
                    'SELECTIVEPROCESS' ) );
        }

        return $data;
    }

    public static function listFieldTypes()
    {
        $MIOLO = MIOLO::getInstance();
        $module = $MIOLO->getCurrentModule();

        $data = array(
            'TEXTFIELD' => _M('Campo de texto', $module),
            'MULTILINE' => _M('Campo de texto com várias linhas', $module),
            'COMBOBOX' => _M('Caixa de seleção', $module),
            'SELECTION' => _M('Seleção', $module),
            'RADIOGROUP' => _M('Grupo de escolha', $module),
            'CALENDAR' => _M('Campo de calendário', $module),
            'HIDDEN' => _M('Campo oculto', $module) );

        return $data;
    }

    /**
     * List all months of the year
     *
     * @return (varchar): An array containing all months of the year
     */
    public static function listMonths()
    {
        $module = 'basic';

        $data = array(
            '1' => _M('Janeiro', $module),
            '2' => _M('Fevereiro', $module),
            '3' => _M('Março', $module),
            '4' => _M('Abril', $module),
            '5' => _M('Maio', $module),
            '6' => _M('Junho', $module),
            '7' => _M('Julho', $module),
            '8' => _M('Agosto', $module),
            '9' => _M('Setembro', $module),
            '10' => _M('Outubro', $module),
            '11' => _M('Novembro', $module),
            '12' => _M('Dezembro', $module) );

        return $data;
    }
    
    /**
     * Lista percentuais de 0 a 100%
     *
     * @return array
     */
    public static function listPercents()
    {
        $data = array();
        
        for ($i = 1; $i <= 100; $i ++ )
        {
            $data[$i] = "{$i}%";
        }
        
        return $data;
    }

    /**
     * List all years from interval
     *
     * @return (varchar): An array containing all the year
     */
    public static function listYears($begin, $end)
    {
        for ( $x = $begin; $x <= $end; $x++ )
        {
            $data[$x] = $x;
        }

        return $data;
    }

    /**
     * Get the specified month description
     *
     * @param $key (integer): Integer representing the month who's description will be retrieved.
     * @return (varchar): An array containing the requested month data
     */
    public static function getMonth($key)
    {
        $data = self::listMonths();

        return $data[$key];
    }

    /**
     * Parse the "saguStack session" to get the "Search Form" URL
     *
     * @return If true, return the correct "Search Form" URL,
     *         otherwise return nul
     */
    public static function getStackBackUrl()
    {
        $MIOLO = MIOLO::getInstance();
        $session = $MIOLO->session;
        $saguStack = $session->IsRegistered('saguStack') ? unserialize($session->GetValue('saguStack')) : null;

        $x_ = (count($saguStack) - 1);

        for ( $x_; $x_ >= 0; $x_-- )
        {
            if ( strstr($saguStack[$x_], '&function=search') || strstr($saguStack[$x_], ':search&') )
            {
                $goto = $saguStack[$x_];
                $y_ = count($saguStack) - 1;
                $saguStack = unserialize($session->GetValue('saguStack'));

                for ( $y_; $y_ >= $x_; $y_-- )
                {
                    unset($saguStack[$y_]);
                }

                break;
            }
        }

        $session->SetValue('saguStack', serialize($saguStack));
        $session->SetValue('saguPromptEvent', 'true');

        return $goto;
    }

    /**
     * Reset the "saguStack session"
     *
     * @return true
     */
    public static function resetStack()
    {
        $MIOLO = MIOLO::getInstance();
        $session = $MIOLO->session;

        $session->SetValue('saguStack', NULL);

        return true;
    }

    /**
     * Return number in CNPJ format
     * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
     *
     * @return Return number in CNPJ format
     */
    public static function formatCNPJ($number)
    {
        if ( strlen($number) !== 14 )
        {
            return false;
        }

        $newNumber = substr($number, 0, 2) . '.';
        $newNumber .= substr($number, 2, 3) . '.';
        $newNumber .= substr($number, 5, 3) . '/';
        $newNumber .= substr($number, 8, 4) . '-';
        $newNumber .= substr($number, 12, 2);

        return $newNumber;
    }

    /**
     * Add interval in a date
     *
     * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
     * @maintainer William Prigol Lopes [william@solis.coop.br]
     *
     * @param date : date for add interval
     *         $type : 'c' = century, 'y' = year, 'm' = month, 'd' = day
     *         $value : number to add interval
     *         $operator : '-' or '+', default '+'
     *         $mask : SAGU::getParameter('BASIC', 'MASK_DATE') or SAGU::getParameter('BASIC', 'MASK_TIMESTAMP')
     *
     * @return date added interval
     */
    public static function addIntervalInDate($date, $type, $value, $operator = ' + ', $mask = null)
    {
        $type = strtolower($type);

        $value = $value ? $value : '0';

        if ( !$mask )
        {
            $mask = SAGU::getParameter('BASIC', 'MASK_DATE');
        }
        
        switch ( $type )
        {
            case 'c' :
                $typeName = ' centuries ';
                break;
            case 'y' :
                $typeName = ' years ';
                break;
            case 'm' :
                $typeName = ' months ';
                break;
            case 'd' :
                $typeName = ' days ';
                break;
            case 'mi' :
                $typeName = ' minutes ';
                break;
            default :
                $typeName = ' days ';
                break;
        }

        if ( strlen($operator) == 0 )
        {
            $operator = ' + ';
        }

        $sql = 'SELECT TO_CHAR(date(TO_DATE( ? , \'' . $mask . '\')) ' . $operator . ' \' ' . $value . $typeName . '\'' . '::interval, \'' . $mask . '\')';

        $return = SDatabase::query(SAGU::prepare($sql, $date));

        if ( is_array($return[0]) )
        {
            return $return[0][0];
        }

        return false;
    }
    
    /**
     * Faz o mesmo que SAGU::addIntervalInDate() porem timestamp
     *
     * @return string 
     */
    public static function addIntervalInTimestamp($date, $type, $value, $operator = null, $mask = null)
    {
        if ( !$mask )
        {
            $mask = SAGU::getParameter('BASIC', 'MASK_TIMESTAMP_DEFAULT');
        }
        
        return self::addIntervalInDate($date, $type, $value, $operator, $mask);
    }

    /**
     * Return actual date formatted
     * @author Eduardo Beal Miglioransa [eduardo@solis.coop.br]
     * @maintainer Arthur Lehdermann [arthur@solis.coop.br]
     *
     * @param (string) $mask Opcionalmente pode-se passar a máscara na qual deseja a data atual. Em branco utiliza MASK_DATE_PHP.
     * @return (string) representing current date in system standard date format
     */
    public static function getDateNow($mask = null)
    {
        $mask = is_null($mask) ? SAGU::getParameter('BASIC', 'MASK_DATE_PHP') : $mask;

        return date($mask);
    }

    /**
     * Return date in extense mode
     * @author William Prigol Lopes [william@solis.coop.br]
     *
     * @param string $date date to convert to extense mode (default: NOW)
     * @return string Date in extense mode
     */
    public static function getDateByExtense($date = null)
    {
        if ( !$date )
        {
            $date = SAGU::getDateNow();
        }

        // FIXME: This function needs to be modified if locale modifies the entries.
        // It should receive three parameters (day, month and year) instead of only one.

        $info = explode('/', $date);
        if ( count($info) != 3 )
        {
            echo "SAGU CLASS: Invalid date";
            return false;
        }
        $dd = $info[0];
        $mm = $info[1];
        $yyyy = $info[2];

        // Check if date is a valid date
        if ( checkdate($mm, $dd, $yyyy) )
        {
            $mm = strftime("%B", mktime(0, 0, 0, $mm, $dd, $yyyy));
            //FIXME Tive que retirar o _M() pois ocorria erro ao gerar um pdf com o adobe que utilizava o fpdf e esta função
            return $dd . ' ' . 'de' . ' ' . $mm . ' ' . 'de' . ' ' . $yyyy;
        }
        else
        {
            echo "SAGU CLASS: Invalid date";
            return false;
        }
    }

    /**
     * Return the string in capitulate format
     * @author Daniel Afonso Heisler [daniel@solis.coop.br]
     *
     * @param $string (string): String in upper or lower case
     * @return Formated string
     *
     * FIXME: The name of this function is incorrect (should be getCapitulatedString) and it does pt_BR-only
     *        conversions.
     */
    public static function getCapitulatetString($string)
    {

        $str = strtolower($string);
        $str = ucwords($str);
        $str = str_replace(' Ao ', ' ao ', $str);
        $str = str_replace(' De ', ' de ', $str);
        $str = str_replace(' Da ', ' da ', $str);

        return $str;
    }

    /**
     * Return a calc by postgres connection
     *
     * @param $calc: Data to calculate
     * @return (varchar): Return the calculated data if successfully or false...
     * @author William Prigol Lopes [william@solis.coop.br]
     *
     * @contributor Armando Taffarel Neto [taffarel@solis.coop.br]
     *  Contributed with regular expression to filter mathematical functions and numbers
     */
    public static function calcNumber($calc, $round = false, $roundValue = null, $db = null)
    {
        if ( $round == false )
        {
            $sql = ' SELECT ' . $calc;
        }
        else
        {
            $sql = ' SELECT ROUND (' . $calc . ', ' . ($roundValue == null ? SAGU::getParameter('BASIC', 'REAL_ROUND_VALUE') : $roundValue) . ')';
        }

        if( $db )
        {
            $return = $db->query($sql);
        }
        else
        {
            $return = SDatabase::query($sql);
        }
        

        if ( is_array($return[0]) )
        {            
            return $return[0][0];
        }

        return false;
    }

    /**
     * Return the number in default postgresql real format defined by sagu parameters
     *
     * @param $number (float): the number to format
     * @param $decimals (int): optional parameter indicating the number of decimal places
     * @return (varchar): Returns the value formatted by postgres
     */
    public static function formatNumber($number, $decimals = null)
    {
        if ( is_null($decimals) )
        {
            $decimals = SAGU::getParameter('BASIC', 'REAL_ROUND_VALUE');
        }
        $sql = 'SELECT ROUND(?, ' . $decimals . ' ) ';
        $args = array(
            $number );

        $return = SDatabase::query($sql, $args);

        if ( is_array($return[0]) )
        {
            return $return[0][0];
        }

        return false;
    }

    /**
     * Return the number in roman
     *
     * @param $number (int): the number to format
     * @return (varchar): Returns the value formatted in roman
     */
    public static function numberToRoman($number)
    {
        $n = intval($number);
        $result = '';

        $r = array( 'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1 );

        foreach ( $r as $roman => $value )
        {
            $matches = intval($n / $value);
            $result .= str_repeat($roman, $matches);
            $n = $n % $value;
        }

        return $result;
    }

    /**
     * This function sorts a multi-dimensional array
     *
     * @param $array (array) to sort multi-dimensional array
     * @param $key (int) sort array key
     * @return (array) sorted multi-dimensional array
     */
    public static function arraySort($array, $key)
    {
        $sortValues = array( );
        for ( $i = 0; $i < count($array); $i++ )
        {
            $sortValues[$i] = $array[$i][$key];
        }
        asort($sortValues, SORT_LOCALE_STRING);
        reset($sortValues);

        $sortedArr = array( );
        while ( list( $arrKey, $arrVal ) = each($sortValues) )
        {
            $sortedArr[] = $array[$arrKey];
        }

        return $sortedArr;
    }

    /**
     * This function replaces the not ascii chars
     *
     * @param $string (string): The string to replacea
     * @return (string): String with replaced ascii chars
     */
    public static function stringToASCII($string)
    {
        $strings = "áàãâäéèêëêíìïîóòôõöúùüûçÁÀÃÂÄÉÈÊËÍÌÏÎÓÒÔÖÕÚÙÛÜÇªºñÑ";
        $asciis = "aaaaaeeeeeiiiiooooouuuucAAAAAEEEEIIIIOOOOOUUUUCaonN";

        $string = strtr($string, $strings, $asciis);

        return $string;
    }

    /**
     * This function get a date in SQL default format mask and convert to other format (converted by sql parameters)
     *
     * @param $date (string): Default formatted date by MASK_DATE sagu2 constant
     * @param $format (string): New format (you can use all things in conformance with sql99 defaults)
     * @return (string): Formatted date if works otherwise false
     */
    public static function formatDate($date, $format)
    {
        $sql = 'SELECT TO_CHAR(TO_DATE(?, \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\'), ?)';
        $args = array(
            $date,
            $format );

        $return = SDatabase::query(SAGU::prepare($sql, $args, false));

        if ( is_array($return[0]) )
        {
            return $return[0][0];
        }

        return false;
    }

    /**
     * This function get a date in a SQL parameter format and mage to default date
     *
     * @param $date (string): Default formatted date
     * @return (string): Formatted date by default mask date
     */
    public static function toDefaultDate($date, $format)
    {
        $sql = ' SELECT TO_CHAR(TO_DATE(?, ?), \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\') ';
        $args = array(
            $date,
            $format );

        $return = SDatabase::query($sql, $args);

        return $return[0][0];
    }

    /**
     * Get the difference in days between two dates
     *
     * @param $startDate (string): A date formatted according to MASK_DATE constant
     * @param $endDate (string): A date formatted according to MASK_DATE constant
     * @return (string): The difference between the two dates in days.
     */
    public static function dateDiff($startDate, $endDate)
    {
        $sql = ' SELECT TO_DATE(?, \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\') - TO_DATE(?, \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\')';
        $args = array(
            $startDate,
            $endDate );

        $return = SDatabase::query($sql, $args);

        return $return[0][0];
    }

    /**
     * Get the difference in hours
     *
     * @param beginTime (string): A hours in formatted hh:mm
     * @param $endTime (string): A hours in formatted hh:mm
     * @return (string): The difference between the two hours.
     */
    public static function timeDiff($beginTime, $endTime)
    {
        $sql = ' SELECT SUM(?::INTERVAL - ?::INTERVAL) ';
        $args = array(
            $beginTime,
            $endTime
        );

        $return = SDatabase::query($sql, $args);

        return $return[0][0];
    }

    /**
     * Get the difference in months between two dates
     *
     * @param $startDate (string): A date formatted according to MASK_DATE constant
     * @param $endDate (string): A date formatted according to MASK_DATE constant
     * @return (string): The difference between the two dates in months.
     */
    public static function dateDiffInMonth($beginDate, $endDate)
    {
        $sql = ' SELECT extract(year from age)*12 + extract(month from age)
                    FROM ( SELECT age(TO_DATE(?, \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\'), TO_DATE(?, \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\')) ) AS t1;';

        $args = array( $endDate,
            $beginDate );

        $return = SDatabase::query($sql, $args);

        return $return[0][0];
    }

    /**
     * This function return only numbers of strings
     *
     * @param $data (string): The unformatted string
     * @return (string): Formatted number
     */
    public static function returnOnlyNumbers($data)
    {
        return ereg_replace('[^0-9]', '', $data);
    }

    /**
     * Output a buffer as a file if headers not modified
     *
     * @param $fileName (string): File name to return
     * @param $buffer (string): The buffer data to return as a file
     * @param $contentType (string) default 'text/plain': Type of content to return
     * @return Return nothing, send to browser the file and quit the process
     *          (in reality returns a string with size 0) but don't tell this for others. ;)
     */
    public static function returnAsFile($fileName, $buffer, $contentType = 'text/plain')
    {
        if ( ob_get_contents() )
        {
            ob_end_clean();
        }
        if ( php_sapi_name() != 'cli' )
        {
            header('Content-Type: ' . $contentType);

            if ( headers_sent() )
            {
                self::error('Some data has already been output to browser, can\'t send file');
            }

            header('Content-Length: ' . strlen($buffer));
            header('Content-disposition: inline; filename="' . $fileName . '"');
        }

        echo $buffer;

        return '';
    }

    /**
     * Return the number in extensive format
     *
     * FIXME: This function doesn't conform to internationalization standards.
     *
     * @param $number: Number value
     * @return the number in extension format
     */
    public static function extensive($cVALOR)
    {
        $aUNID = array(
            "",
            " UM ",
            " DOIS ",
            " TRES ",
            " QUATRO ",
            " CINCO ",
            " SEIS ",
            " SETE ",
            " OITO ",
            " NOVE " );
        $aDEZE = array(
            "",
            "   ",
            " VINTE E",
            " TRINTA E",
            " QUARENTA E",
            " CINQUENTA E",
            " SESSENTA E",
            " SETENTA E",
            " OITENTA E",
            " NOVENTA E " );
        $aCENT = array(
            "",
            "CENTO E",
            "DUZENTOS E",
            "TREZENTOS E",
            "QUATROCENTOS E",
            "QUINHENTOS E",
            "SEISCENTOS E",
            "SETECENTOS E",
            "OITOCENTOS E",
            "NOVECENTOS E" );
        $aEXC = array(
            " DEZ ",
            " ONZE ",
            " DOZE ",
            " TREZE ",
            " QUATORZE ",
            " QUINZE ",
            " DEZESSEIS ",
            " DEZESSETE ",
            " DEZOITO ",
            " DEZENOVE " );

        $nPOS1 = substr($cVALOR, 0, 1);
        $nPOS2 = substr($cVALOR, 1, 1);
        $nPOS3 = substr($cVALOR, 2, 1);

        if ( strlen($cVALOR) == 1 )
        {
            $cUNID = $aUNID[($nPOS1)];
        }
        else
        {
            $cCENTE = $aCENT[($nPOS1)];
            $cDEZE = $aDEZE[($nPOS2)];
            $cUNID = $aUNID[($nPOS3)];
        }

        if ( substr($cVALOR, 0, 3) == "100" )
        {
            $cCENTE = "CEM ";
        }

        if ( substr($cVALOR, 1, 1) == "1" )
        {
            $cDEZE = $aEXC[$nPOS3];
            $cUNID = "";
        }

        $cRESULT = $cCENTE . $cDEZE . $cUNID;
        $cRESULT = substr($cRESULT, 0, strlen($cRESULT) - 1);
        return $cRESULT;
    }

    /**
     * Return the number in extensive format
     *
     * @param $number: Number value
     * @return the number in extension format
     */
    public static function getExtensiveNumber($number, $currency = NULL, $pluralCurrency = NULL)
    {
        $zeros = "000.000.000,00";
        $cVALOR = number_format($number, 2, ',', '.');
        $cVALOR = substr($zeros, 0, strlen($zeros) - strlen($cVALOR)) . $cVALOR;

        if ( $currency && $pluralCurrency )
        {
            $cMOEDA_SINGULAR = ' ' . $currency;
            $cMOEDA_PLURAL = ' ' . $pluralCurrency;
        }
        else
        {
            $cMOEDA_SINGULAR = '';
            $cMOEDA_PLURAL = '';
        }

        $cMILHAO = SAGU::extensive(substr($cVALOR, 0, 3)) . ((substr($cVALOR, 0, 3) > 1) ? ' MILHOES' : '');
        $cMILHAR = SAGU::extensive(substr($cVALOR, 4, 3)) . ((substr($cVALOR, 4, 3) > 0) ? ' MIL' : '');
        $cUNIDAD = SAGU::extensive(substr($cVALOR, 8, 3)) . (($nVALOR == 1) ? $cMOEDA_SINGULAR : $cMOEDA_PLURAL);
        $cCENTAV = SAGU::extensive("0" . substr($cVALOR, 12, 2)) . ((substr($cVALOR, 12, 2) > 0) ? " CENTAVOS" : "");

        $cRETURN = $cMILHAO . ((strlen(trim($cMILHAO)) != 0 && strlen(trim($cMILHAR)) != 0) ? ", " : "") . $cMILHAR . ((strlen(trim($cMILHAR)) != 0 && strlen(
                        trim($cUNIDAD)) != 0) ? ", " : "") . $cUNIDAD . ((strlen(trim($cUNIDAD)) != 0 && strlen(trim($cCENTAV)) != 0) ? ", " : "") . $cCENTAV;

        return $cRETURN;
    }

    /**
     * Get a shorten name of a person
     *
     * @param $personName: String with the name
     *         $length: Length of the shorted name
     * @return the name shorted
     */
    public static function getShortenName($personName, $length)
    {
        $count = 1;
        $control = substr_count($personName, ' ');

        while ( strlen($personName) > $length && $count < $control )
        {
            $spaceNumber = 0;
            for ( $x = 0; $x < strlen($personName); $x++ )
            {
                if ( $spaceNumber == $count )
                {
                    $char = substr($personName, $x, 1);
                    $output .= $char . '. ';

                    while ( $char != ' ' && $x < strlen($personName) )
                    {
                        $x++;
                        $char = substr($personName, $x, 1);
                    }
                }
                else
                {
                    $output .= substr($personName, $x, 1);
                }
                if ( substr($personName, $x, 1) == ' ' )
                {
                    $spaceNumber++;
                }
            }

            $personName = $output;
            unset($output);

            $count++;
        }

        return substr($personName, 0, $length);
    }

    /**
     * Loads the JS file for php serialization support
     * in JavaScript
     *
     * @param $form: Current form
     * @return the URL for $this->page->scripts->add() method
     */
    public static function importJsSerialize($form)
    {
        $MIOLO = MIOLO::getInstance();

        $form->page->scripts->add($MIOLO->getActionURL('basic', 'html:scripts:phpSerialize.js'));
    }

    /**
     * Clean the toolbar and call MIOLO informarion method
     */
    public static function information($msg, $goto = '', $event = '', $halt = true)
    {
        global $MIOLO;
        $MIOLO->getTheme()->setElement('toolbar', null);

        $MIOLO->information($msg, $goto, $event, $halt);
    }

    /**
     * Clean the toolbar and call MIOLO question method
     */
    public static function question($msg, $gotoYes = '', $gotoNo = '', $eventYes = '', $eventNo = '', $halt = true)
    {
        global $MIOLO;
        $MIOLO->getTheme()->setElement('toolbar', null);

        $MIOLO->question($msg, $gotoYes, $gotoNo, $eventYes, $eventNo);
    }

    /**
     * Clean the toolbar and call MIOLO error method
     */
    public static function error($msg = '', $goto = '', $caption = '', $event = '', $halt = true)
    {
        global $MIOLO;
        $MIOLO->getTheme()->setElement('toolbar', null);

        $MIOLO->error($msg, $goto, $caption, $event, $halt);
    }

    /**
     * Replace variables inside receipt.
     * @author Samuel Koch [samuel@solis.coop.br]
     *
     * @param $conteudo (string): Is the information that will replace the variables
     * @param $tags     (string): string containing a name of parameter of parameter
     * @return (text): Return receipt
     */
    public static function interpretsReceipt($conteudo, $tags, $details=null)
    {
        // Substitui todas as variáveis pelo conteudo
        $c = strtr($conteudo, $tags);

        // Procura por funções e as aplicas
        $array = explode("\n", $c);
        foreach ( $array as $line )
        {
            if ( strpos($line, '$DETAILOP') === FALSE )
            {
                $arrayLine = explode(';', $line);
                if ( count($arrayLine) > 1 )
                {
                    unset($receiptLine);
                    $receiptLine = '|';

                    for ( $count = 1; $count < count($arrayLine);
                                $count = $count + 2 )
                    {
                        $receiptLine .= str_pad($arrayLine[$count], $arrayLine[$count + 1]);
                    }

                    $newReceipt[] = $receiptLine;
                }
                else
                {
                    $newReceipt[] = $line;
                }
            }
            elseif ( count($details) > 0 )
            {
                $pads = array( );
                $detailsIndex = 0;

                $arrayLine = explode(';', $line);
                if ( count($arrayLine) > 1 )
                {
                    unset($receiptLine);

                    for ( $count = 0; $count < count($arrayLine); $count++ )
                    {
                        if ( strpos($arrayLine[$count], '$DETAILOP') === FALSE )
                        {
                            continue;
                        }
                        else
                        {
                            $pads[$detailsIndex] = $arrayLine[$count + 1];
                            $detailsIndex++;
                            $count = $count + 1;
                        }
                    }

                    foreach ( $details as $detailData )
                    {
                        $receiptLine = '';

                        foreach ( $pads as $index => $padValue )
                        {
                            $receiptLine .= str_pad('|' . $detailData[$index], $padValue);
                        }

                        $newReceipt[] = $receiptLine . '|';
                    }
                }
                else
                {
                    $newReceipt[] = $line;
                }
            }
        }

        $newReceipt = implode("\n", $newReceipt);

        return $newReceipt;
    }

    /**
     * Obtain the value of the specified parameter.
     * @author Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
     *
     * @maintainers:
     * Alexandre Heitor Schmidt [alexsmith@solis.coop.br]
     * Arthur Lehdermann [arthur@solis.coop.br]
     * Fabiano Tomasini [fabiano@solis.coop.br]
     *
     * @param $module    (string): Module in which the parameter is declared.
     * @param $parameter (string): Parameter identificator.
     * @return (parameter dependent): Value associated with the specified parameter.
     */
    public static function getParameter($module, $parameter)
    {
        $MIOLO = MIOLO::getInstance();

        try
        {
            // If parameter is not defined yet, get its value from the database
            if ( !defined($parameter) )
            {
                // If $parameter contains no value, get value from basConfig
                $sql = 'SELECT getParameter(?, ?)';

                $params = array( );
                $params[] = $module;
                $params[] = $parameter;

                $db = $MIOLO->getDatabase('basic');
                $result = SDatabase::query(SAGU::prepare($sql, $params));

                if ( count($result) == 0 )
                {
                    throw new Exception(_M('O parâmetro @1 não existe no módulo @2.', 'basic', $parameter, $module));
                }

                // Define this parameter globally so that it can be used later without going
                // to the database again.
                define($parameter, $result[0][0]);
            }

            return constant($parameter);
        }
        catch ( Exception $e )
        {
            $MIOLO->error($e->getMessage());
        }
    }

    /**
     * Do all stuff to execute a handler request, including checking access permissions.
     * For forms that manage more than one table or that are complex in any other way, it is
     * suggested that you specify it in the $searchForm parameter, leaving $managementForm
     * null.
     *
     * @param (string) $module The module to which handler belongs to.
     * @param (string) $action The handler itself, such as main:register.
     * @param (string) $title Human readable name of the handler.
     * @param (string) $searchForm Name of the search form of this handler.
     * @param (string) $managementForm  Name of the management form of this handler OR array containing steps of SStepByStep
     * @param (array) $options (OU transactionName) Opcoes diversas que podem ser passadas para funcao. Caso $options seja uma STRING, é considerado como transactionName para manter compatibilidade
     * <br>
     * <br>Opcoes:
     * <br><b>transactionName</b> (string) Nome da transacao a ser utilizada (padrao: Nome do form)
     * <br><b>checkAccess</b> (boolean) Se deve verificar permissao de acesso (padrao: TRUE)
     * <br><b>toolbar</b> (boolean) Ativa/desativa exibicao de toolbar no form (padrao: TRUE)
     */
    public static function handle($module, $action, $title, $searchForm = null, $managementForm = null, $options = null)
    {
        global $theme, $navbar;

        $MIOLO = MIOLO::getInstance();
        $function = MIOLO::_REQUEST('function');
        $ui = $MIOLO->getUI();
        $isStepByStep = is_array($managementForm); // Quando for array, significa que é passo a passo
        // Manter a compatibilidade
        if ( $options && !is_array($options) )
        {
            $_options = $options;
            $options = array( );
            $options['transactionName'] = $_options;
        }
        // Define opcoes passadas, ou caso nao tenham sido passadas, as opcoes padrao
        // Este $options é tambem passado para o construtor do form (MForm/SForm..)
        $options = array_merge(array(
            'checkAccess' => true,
            'transactionName' => null,
            'toolbar' => true,
            'title' => $title, //passado como argumento para o form
                ), (array) $options);

        $MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);

        $navbar->addOption($title, $module, $action);

        /*
         * Caso não exista o ícone, exibe o default-16x16.png
         */
        $ico = array_pop(explode(':', $action)) . '-16x16.png';
        if ( !file_exists($MIOLO->GetModulePath($module, null) . 'html/images/' . $ico) )
        {
            $ico = 'default-16x16.png';
        }

        self::addAccess($title, $action, $ico, false);
        
        /*
         * Verificacao de permissoes 
         */
        $formName = $isStepByStep ? SStepByStepForm::getCurrentStepInfo($managementForm)->formName : SAGU::NVL($managementForm, $searchForm);
        $transactionName = strlen($options['transactionName']) > 0 ? $options['transactionName'] : $formName;
        if ( substr($transactionName, -6) == 'Search' )
        {
            $transactionName = substr($transactionName, 0, -6);
        }
        
        if ( $options['checkAccess'] == true )
        {
            SAGU::checkDefaultAccess($transactionName);
        }

        //única forma encontrada de passar a transação para o SForm durante a construção
        $MIOLO->SetConf('temp.setTransaction', $transactionName);

        // Carrega o formulário adequado
        if ( ((strlen($function) == 0) || ($function == 'search')) && ( strlen($searchForm) > 0 ) )
        {
            // Verifica se existe busca dinâmica para esse formulário
            // try/catch para o caso da tabela de cadastro dinâmico ainda não existir
            try
            {
                // Remove frm e Search do nome do formulário para obter o identificador
                $identificador = substr($searchForm, strlen('frm'), -strlen('Search'));
                $identificadorExiste = BasBuscaDinamica::verificarIdentificador($module, $identificador);
            }
            catch ( Exception $e )
            {
                $identificadorExiste = FALSE;
            }

            if ( $identificadorExiste )
            {
                $content = new SCustomSearchForm($title, $module, $identificador);

                // FIXME: Alterar quando sistema de permissão for alterado
                if ( strlen($options['transactionName']) == 0 )
                {
                    $options['transactionName'] = $searchForm;
                }
            }
            else
            {
                $content = $ui->getForm($module, $searchForm, $options);
            }

            $content->setClose($MIOLO->getActionURL($module, substr($action, 0, strrpos($action, ':'))));
        }
        else
        {
            // Verifica se existe busca dinâmica para esse formulário
            // try/catch para o caso da tabela de cadastro dinâmico ainda não existir
            try
            {
                // Remove frm do nome do formulário para obter o identificador
                if ( $identificador )
                {
                    $identificador = substr($managementForm, strlen('frm'));
                    $identificadorExiste = BasCadastroDinamico::verificarIdentificador($module, $identificador);
                }
            }
            catch ( Exception $e )
            {
                $identificadorExiste = FALSE;
            }

            if ( $identificadorExiste )
            {
                $content = new SCustomForm($title, $module, $identificador);

                // FIXME: Alterar quando sistema de permissão for alterado
                if ( strlen($options['transactionName']) == 0 )
                {
                    $options['transactionName'] = $managementForm;
                }
            }
            else
            {
                $content = $isStepByStep ? SStepByStepForm::getCurrentForm($managementForm) : $ui->getForm($module, $managementForm, $options);
            }

            // Quando não existe tela busca, desabilita este botão da toolbar
            if ( !$searchForm )
            {
                $content->getToolbar()->disableButton(MToolBar::BUTTON_SEARCH);
            }
        }

        //define a transação no formulário da forma correta
        if ( method_exists( $content, 'setTransaction' ) )
        {
            $content->setTransaction( $transactionName );
        }

        if ( !$options['toolbar'] && method_exists($content, 'disableToolbar') )
        {
            $content->disableToolbar();
        }

        if ( $theme->page->generateMethod != 'generateAjax' )
        {
            $theme->clearContent();
            $theme->insertContent($content);
        }
    }

    /**
     * Verifica permissoes CRUD padrao (Inserir, Ler, Atualizar, Deletar).
     *
     * @param String $transaction
     */
    public static function checkDefaultAccess($transaction)
    {
        $MIOLO = MIOLO::getInstance();
        $function = strtolower(MIOLO::_REQUEST('function'));

        if ( $MIOLO->getConf('login.check') == 'true' )
        {
            if ( (strlen($function) == 0) || ($function == 'search') )
            {
                $MIOLO->checkAccess($transaction, A_ACCESS, true, true);
            }
            else
            {
                switch ( $function )
                {
                    case 'insert':
                        $MIOLO->checkAccess($transaction, A_INSERT, true, true);
                        break;
                    case 'update':
                        if ( strlen(MIOLO::_REQUEST('event')) > 0 )
                        {
                            $MIOLO->checkAccess($transaction, A_UPDATE, true, true);
                        }
                        else
                        {
                            $MIOLO->checkAccess($transaction, A_ACCESS, true, true);
                        }
                        break;
                    case 'delete':
                        $MIOLO->checkAccess($transaction, A_DELETE, true, true);
                        break;
                }
            }
        }
    }

    /**
     * Insere um acesso ao handler na tabela de basAccess
     *
     * @param $label (string): Nome padrão
     *         $handler (string): Caminho Padrão
     *         $image (string): Nome da imagem
     */
    public static function addAccess($label, $handler, $image, $isBookmark=false)
    {
        $MIOLO = MIOLO::getInstance();

        if ( $MIOLO->getConf('login.check') == 'true' )
        {
            $module = MIOLO::getCurrentModule();
            $MIOLO->uses('types.class', 'basic');

            $login = $MIOLO->getLogin();
            $data = new basAccess();
            $data->login = $login->id;
            $data->moduleAccess = $module;
            $data->label = $label;
            $data->image = $image;
            $data->handler = $handler;
            $data->isBookmark = $isBookmark;

            $business = $MIOLO->getBusiness('basic', 'BusAccess');
            $business->insertAccess($data);
        }
    }

    /**
     * Return a part of a date (the day, the month, the year, etc, as defined by PostgreSQL EXTRACT() function.
     *
     * @param $date The date to be parsed by the DB. It must be given in the MASK_DATE format.
     * @param $part The part to be extracted. May be DAY, MONTH, YEAR or any other part supported by the EXTRACT() function.
     * @return The requested date part.
     */
    public static function getDatePart($date, $part)
    {
        $sql = 'SELECT EXTRACT(' . $part . ' FROM TO_DATE(?, \'' . SAGU::getParameter('BASIC', 'MASK_DATE') . '\'))';
        $args = array( $date );

        $return = SDatabase::query($sql, $args);

        return $return[0][0];
    }

    /**
     * Funcao utilizada para quebrar um timestamp em hora e data, retornando o que for pedido.
     *
     * @param $timestamp String Timestamp com data e hora
     * @param $return String Tipo de retorno (DATE ou TIME)
     * @return $split String Data ou hora
     */
    public static function splitTimestamp($timestamp, $return = 'DATE')
    {
        $split = explode(' ', $timestamp);
        return $return == 'DATE' ? $split[0] : $split[1];
    }

    /**
     * Print data using a socket to a fiscal printer
     * @author Leovan Tavares da Silva
     *
     * @param $data (object): the data to be printed
     */
    public static function printFiscalData($data)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->uses('classes/fiscalPrinterClient.class', 'basic');

        $serverPort = self::getParameter('BASIC', 'PRINT_SERVER_PORT');
        $serverAddres = self::getParameter('BASIC', 'PRINT_SERVER_ADDRESS');

        if ( $serverAddres == 'minhaMaquina' )
        {
            $serverAddres = $_SERVER['REMOTE_ADDR'];
        }

        $socket = new fiscalPrinterClient($serverAddres, $serverPort);

        try
        {
            if ( !$socket->starting() )
            {
                $socket->displayMsg("--LINE--");
                $socket->displayMsg("\nClient Connect Error:" . $socket->getError() . "");
                $socket->displayMsg("--LINE--");
                die();
            }

            $nMaxTentativas = 10;
            $nTentativas = 0;

            do
            {
                $socket->displayMsg("--LN--");
                $socket->displayMsg("--LINE--");
                $socket->displayMsg("Enviando Dados");
                $socket->displayMsg("--LINE--");

                $socket->send($data);
                $socket->send(PRINT_SERVER_SIGNAL_FINISH);

                $ok = $socket->waitingResponse($socket->getTextConfirmCode());

                if ( !$ok )
                {
                    $socket->displayMsg("--LN--");
                    $socket->displayMsg("--LINE--");
                    $socket->displayMsg("Uma falha no envio do conteudo.");
                    $socket->displayMsg("--LINE--");
                    $nTentativas++;

                    $socket->closeConnection();

                    if ( !$socket->starting() )
                    {
                        $socket->displayMsg("--LINE--");
                        $socket->displayMsg("\nClient Connect Error:" . $socket->getError() . "");
                        $socket->displayMsg("--LINE--");
                        die();
                    }
                }
                else
                {
                    $socket->displayMsg("--LN--");
                    $socket->displayMsg("--LINE--");
                    $socket->displayMsg("Conteudo enviado com sucesso.");
                    $socket->displayMsg("--LINE--");

                    do
                    {
                        $socket->send(PRINT_SERVER_SIGNAL_PRINT);
                        $ok = $socket->waitingResponse(PRINT_SERVER_SIGNAL_PRINTER_SUCCESSFUL);

                        if ( $ok )
                        {
                            $socket->displayMsg("--LN--");
                            $socket->displayMsg("--LINE--");
                            $socket->displayMsg("Print OK.");
                            $socket->displayMsg("--LINE--");
                        }
                        else
                        {
                            $socket->displayMsg("--LN--");
                            $socket->displayMsg("--LINE--");
                            $socket->displayMsg("Print FAIL.");
                            $socket->displayMsg("--LINE--");
                            $nTentativas++;

                            $socket->closeConnection();
                            if ( !$socket->starting() )
                            {
                                $socket->displayMsg("--LINE--");
                                $socket->displayMsg("\nClient Connect Error:" . $socket->getError() . "");
                                $socket->displayMsg("--LINE--");
                                die();
                            }
                        }
                    }
                    while ( !$ok && $nTentativas < $nMaxTentativas );

                    do
                    {
                        $socket->send(PRINT_SERVER_SIGNAL_EXIT);
                        $ok = $socket->waitingResponse(PRINT_SERVER_SIGNAL_EXIT_OK);

                        if ( $ok )
                        {
                            $socket->displayMsg("--LN--");
                            $socket->displayMsg("--LINE--");
                            $socket->displayMsg("EXIT OK.");
                            $socket->displayMsg("--LINE--");

                            break 2;
                        }
                        else
                        {
                            $socket->displayMsg("--LN--");
                            $socket->displayMsg("--LINE--");
                            $socket->displayMsg("EXIT FAIL.");
                            $socket->displayMsg("--LINE--");
                            $nTentativas++;

                            $socket->closeConnection();

                            if ( !$socket->starting() )
                            {
                                $socket->displayMsg("--LINE--");
                                $socket->displayMsg("\nClient Connect Error:" . $socket->getError() . "");
                                $socket->displayMsg("--LINE--");
                                die();
                            }
                        }
                    }
                    while ( !$ok && $nTentativas < $nMaxTentativas );
                }
            }
            while ( !$ok && $nTentativas < $nMaxTentativas );


            $socket->closeConnection();
        }
        catch ( Exception $e )
        {
            throw $e;
        }
    }

    public static function getPersonSteps()
    {
        $action = strtolower(MIOLO::getCurrentAction());
        $module = SAGU::getFileModule(__FILE__);
        $function = MIOLO::_REQUEST('function');

        $s = 1;

        if ( $function == SForm::FUNCTION_INSERT && (strpos($action, 'contract') || strpos($action, 'student') || strpos($action, 'professor')) )
        {
            $steps[$s++] = new SStepInfo('FrmPersonChoose', _M('Selecionar pessoa', 'basic'), $module);
        }

        $steps[$s++] = new SStepInfo('FrmPerson', _M('Pessoa', 'basic'), $module);

        if ( strpos($action, 'legal') ) // Legal person
        {
            $steps[$s++] = new SStepInfo('FrmLegalPerson', _M('Pessoa jurídica', $module));
        }
        else // Others
        {
            $steps[$s++] = new SStepInfo('FrmPhysicalPerson', _M('Pessoa física', 'basic'), $module);
            $steps[$s++] = new SStepInfo('FrmPhysicalPersonKinship', _M('Parentesco', 'basic'), $module);
            $steps[$s++] = new SStepInfo('FrmPersonDocument', _M('Documento', 'basic'), $module);

            if ( (strpos($action, 'student')) || (strpos($action, 'contract')) )
            {
                $steps[$s++] = new SStepInfo('FrmPhysicalPersonStudent', _M('Aluno', 'basic'), $module);
            }

            if ( strpos($action, 'professor') )
            {
                $steps[$s++] = new SStepInfo('FrmPhysicalPersonProfessor', _M('Carga horária', 'basic'), $module);
            }

            if ( strpos($action, 'contract') )
            {
                $steps[$s++] = new SStepInfo('FrmContract', _M('Contrato', 'academic'), 'academic');
            }
        }

        return $steps;
    }

    /**
     * Obtem passos da solicitacao de estagio
     *
     * @return array SStepInfo
     */
    public static function getTrainingRequestSteps()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $stepModule = 'training';
        $action = MIOLO::getCurrentAction();
        $function = MIOLO::_REQUEST('function');
        $i = 1;

        //Quando vem via webservices, nao exibir passo pessoa
        if ( $module != 'services' )
        {
            $steps[$i++] = new SStepInfo('FrmRequestChooser', _M('Seleção de pessoa', $module), $stepModule);
        }

        //Se tiver no módulo de serviço entra no passo de autenticação de usuário
        //
        //( strlen(MIOLO::_REQUEST('ignoreAuthentication')) <= 0 )
        if ( ( SAGU::userIsFromServices() && ( strlen($MIOLO->getLogin()->id) <= 0 ) ) ||
                ( strlen(MIOLO::_REQUEST('isFromAuthentication')) > 0 ) )
        {
            $steps[$i++] = new SStepInfo('FrmRequestAuthentication', _M('Identificação', $module), $stepModule);
        }

        $steps[$i++] = new SStepInfo('FrmRequestPersonalInformation', _M('Informações pessoais', $module), $stepModule);
        $steps[$i++] = new SStepInfo('FrmRequestDataStage', _M('Dados do estágio', $module), $stepModule);
        $steps[$i++] = new SStepInfo('FrmRequestDocuments', _M('Documentos', $module), $stepModule);
        $steps[$i++] = new SStepInfo('FrmRequestConfirmation', _M('Confirmação', $module), $stepModule);

        return $steps;
    }

    /**
     * Return the name of the module to which the specified file belongs to. This method is different
     * from MIOLO::getCurrentModule(), because MIOLO::getCurrentModule() returns the module based on the
     * "module" URL variable, while SAGU::getFileModule($file) analyzes $file path to determine to which
     * module it belongs to. Generally this method will be called like this:
     *
     * SAGU::getFileModule(__FILE__);
     *
     * In this case, the path to be analyzed will be the file under execution itself.
     *
     * @param (string) $file String containing the path to be analyzed.
     * @return (string) The name of the module where $file is located or empty string when module cannot be determined.
     */
    public static function getFileModule($file)
    {
        $MIOLO = MIOLO::getInstance();

        $dirs = explode(DIRECTORY_SEPARATOR, $file);
        $modulesDir = array_pop(explode(DIRECTORY_SEPARATOR, $MIOLO->getConf('home.modules')));
        $found = false;
        $moduleName = '';
        for ( $i = count($dirs); $i > 0 && !$found; $i-- )
        {
            if ( $dirs[$i] == $modulesDir )
            {
                $moduleName = $dirs[$i + 1];
                $found = true;
            }
        }

        return $moduleName;
    }

    /**
     * Convert MIOLO database result array (MDatabase->query()) to Object
     *
     * @param array $data Array or Multidimensional array
     * @param array $vars
     * @param Object $type SAGU Type
     */
    public static function resultToObject($data, $vars, $type = null)
    {
        return self::convertResult($data, $vars, $type, 'object');
    }
    
    /**
     * Convert numeric indexed array to indexed
     *
     * @param array $data Array or Multidimensional array
     * @param array $vars
     * @param Object $type SAGU Type
     */
    public static function resultToArray($data, $vars, $type = null)
    {
        return self::convertResult($data, $vars, $type, 'array');
    }
    
    private static function convertResult($data, $vars, $type, $returnType)
    {
        if ( is_array($data[0]) )
        {
            $result = array( );
            
            foreach ( $data as $d )
            {
                $result[] = self::convertResult($d, $vars, $type, $returnType);
            }
            
            return $result;
        }
        else if ( count($data) > 0 )
        {
            $result = null;

            if ( $type )
            {
                $typeVars = get_object_vars($type);
                
                foreach ( $vars as $i => $varName )
                {
                    if ( in_array($varName, array_keys($typeVars)) )
                    {
                        $result->$varName = $data[$i];
                    }
                }
            }
            else
            {
                $result = array_combine($vars, $data);
            }

            return $returnType == 'object' ? (object) $result : (array) $result;
        }
    }

    /**
     * Converte dados vindos de um SType para um array de stdClass com os valores
     *
     * @param Array $cols Colunas da subdetail
     * @param Array $typeArray Array de stdClass (geralmente vindo de um SType::search())
     */
    public static function convertSTypeToSubDetail($cols, $typeData)
    {
        $data = array( );
        foreach ( (array) $typeData as $val )
        {
            $ob = new stdClass();
            foreach ( $cols as $col )
            {
                $colName = $col->options; // ->options = id da coluna subdetail
                $ob->$colName = $val->$colName;
            }
            $data[] = $ob;
        }

        return $data;
    }

    /**
     * Converte dados vindos de uma SubDetail para um array de objetos SType,
     *  ja setando os valores para o objeto.
     *
     * @param string $subDetailName Id da subdetail (ex.: teams)
     * @param SType $typeObject Objeto do tipo SType instanciado (ex.: new TraTeam())
     */
    public static function convertSubDetailToSType($subDetailName, $typeObject)
    {
        $rows = array( );
        foreach ( (array) MSubDetail::getData($subDetailName) as $row )
        {
            unset($obj);

            $rows[] = $obj = clone($typeObject);
            foreach ( $row as $key => $val )
            {
                $obj->$key = $val;
            }
        }

        return $rows;
    }

    public static function postgresToPhpArray($inArray)
    {
        $newArray = explode(',', trim($inArray, '{}'));
        for ( $i = 0; $i < count($newArray); $i++ )
        {
            $newArray[$i] = trim($newArray[$i], '"');
        }

        return $newArray;
    }

    /**
     * Checks if the CPF is valid
     * @author Arthur Lehdermann[arthur@solis.coop.br]
     *
     * @maintainers:
     * Arthur Lehdermann[arthur@solis.coop.br]
     *
     * @param $cpf (integer): CPF number.
     * @return (boolean): True if is valid or false if is invalid.
     */
    public static function checkCPF($cpf)
    {
        $MIOLO = MIOLO::getInstance();

        // Remove os delimitadores
        $cpfNumber = SAGU::convertInCPFWithoutDelimiters($cpf);

        // Se tiver os 11 caracteres valida-o
        if ( strlen($cpfNumber) == 11 )
        {
            // Usa função na base de dados
            $sql = 'SELECT VALIDATE_CPF(?)';

            $pk = array( $cpfNumber );
            $db = $MIOLO->getDatabase('basic');

            $result = SDatabase::query(SAGU::prepare($sql, $pk, false));

            // Se vier true retorna DB_TRUE (válido)
            if ( $result )
            {
                $isValid = DB_TRUE;
            }
            else
            {
                $isValid = DB_FALSE;
            }
        }
        else
        {
            // Se o cpf não tem a quantidade certa de caracteres retorna DB_FALSE (inválido)
            $isValid = DB_FALSE;
        }

        return $isValid;
    }

    /**
     * Obtem os valores extras obtidos na url, removendo valores internos/padroes do MIOLO e PHP (PHPSESSID, m_*_position, etc..)
     *
     * @sample
     * Exemplo de uso:
     * $args = SAGU::getRequestArgs();
     * $args['meuNovoParametro'] = 'valor';
     * $MIOLO->getActionURL(null, null, null, $args);
     *
     * @param boolean $includePath TRUE para incluir o "module" e "action" atuais na URL
     * @return Array $args
     */
    public static function getRequestArgs($includePath = true)
    {
        $out = MIOLO::getInstance()->getContext()->getVars();

        if ( !$includePath )
        {
            unset($out['module']);
            unset($out['action']);
        }

        return $out;
    }

    public static function getEventName()
    {
        return MIOLO::_REQUEST('__EVENTTARGETVALUE');
    }

    public static function getEventArgs()
    {
        return MIOLO::_REQUEST('__EVENTARGUMENT');
    }

    public static function getRequiredLegend()
    {
        $symbol = new MLabel('*', 'red');
        $label = new MTextLabel('lblRequiredInfo', _M('Os campos demarcados com (@1) são obrigatórios.', $module, $symbol->generate()));
        return new MDiv('divRequiredInfo', array( new MSeparator(), $label ));
    }

    // FIXME Esta funcao nao terá mais necessidade quando for implementado um formulario-pai para o passo a passo da inscricao. (pode ser aplicado no pai para afetar todos formularios do passo a passo)
    /**
     * Obtem o botao de cancelar especifico para inscricao
     *
     * @return MButton
     */
    public static function getCancelButtonSubscription()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $args = array(
            'function' => MIOLO::_REQUEST('function'),
            'webservices' => MIOLO::_REQUEST('webservices'),
        );
        $goto = $MIOLO->getActionURL($module, $action, null, $args);

        return new MButton('cancelButton', '<span>' . _M('Cancelar', 'basic') . '</span>', $goto, 'images/button_cancel.png');
    }

    /**
     * Retorna o array de menus de relatorios genericos para ser adicionado ao array
     *
     * @param string $reportModule
     * @return array
     */
    public static function getGenericReportsMenu($reportModule)
    {
        $MIOLO = MIOLO::getInstance();
        $action = MIOLO::getCurrentAction();
        $grAction = $action . ':genericReports'; // Acao do genericReports (handler deve ter sempre este nome)

        $busGenericReports = $MIOLO->getBusiness('basic', 'BusGenericReports');

        $filters->module = strtoupper($reportModule);
        $genericReports = $busGenericReports->searchReportObject($filters);

        $menuItem = array( );
        foreach ( (array) $genericReports as $report )
        {
            if ( strtoupper($report->enabled) == strtoupper(DB_TRUE) || $MIOLO->checkAccess($reportModule, ACD_ADMIN, false, true) )
            {
                $reportLink = array( $report->name, 'genericReports-16x16.png', $grAction, null, array( 'reportId' => $report->reportId ) );
                $menuItem[] = $reportLink;
            }
        }

        return $menuItem;
    }

    /**
     * Processa o handler genericReports (geralmente genericReports.inc)
     * Solucao encontrada temporariamente (tempo-permanentemente) para nao repetir logica em modulos diferentes
     */
    public static function processGenericReportHandler()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $ui = $MIOLO->getUI();
        $theme = $MIOLO->getTheme();
        $navbar = $theme->getElement('navigation');

        $MIOLO->trace('file:' . $_SERVER['SCRIPT_NAME']);
        $MIOLO->checkAccess('FrmGenericReportGeneration', A_EXECUTE, true, true);

        $business = $MIOLO->getBusiness('basic', 'BusGenericReports');
        $reportData = $business->getReport(MIOLO::_REQUEST('reportId'));

        $navbar->addOption($reportData->name, $module, $action, null, array( 'reportId' => $reportData->reportId ));

        $frmGenericReportGen = $ui->getForm('basic', 'FrmGenericReportGeneration');

        $theme->clearContent();
        $theme->insertContent($frmGenericReportGen);
    }

    /**
     * Retorna nome de arquivo temporario do sistema
     * Ex.: SAGU::getTmpFile('teste') = /tmp/teste
     *
     * @param <type> $name
     */
    public static function getTmpFile($name)
    {
        return sys_get_temp_dir() . '/' . $name;
    }

    /**
     * Tenta alinhar campos em um formulario quando eles nao sao renderizados corretamente
     *
     * @param array $fields Campos MForm
     * @return array $fields Realinhados
     */
    public static function alignFields($fields = array( ))
    {
        return array( new MFormContainer(rand(), $fields) );
    }

    /**
     * Verifica se deve formatar data para formato da base ( yyyy-mm-dd ).
     * Funcao feita para casos onde nao ha certeza de que a data esta no formato da base
     */
    public static function convertDateToDb($date)
    {
        // Verifica se esta no formato portugues
        if ( strpos($date, '/') )
        {
            $sql = "SELECT TO_DATE(?, '" . SAGU::getParameter('BASIC', 'MASK_DATE') . "')";
            $result = SDatabase::query(SAGU::prepare($sql, $date));
            $date = $result[0][0];
        }

        return $date;
    }
    
    /**
     * verifica se as datas estão no mesmo intervalo de tempo.
     *
     * @param (date) $startDate1
     * @param (date) $endDate1
     * @param (date) $startDate2
     * @param (date) $endDate2
     * @return (boolean).
     */
    public static function dateOverlaps($startDate1, $endDate1, $startDate2, $endDate2)
    {
        return true;
        $sql = "SELECT (DATE '$startDate1', DATE '$endDate1') OVERLAPS (DATE '$startDate2', DATE '$endDate2');";
        $return = SDatabase::query($sql);
        return ($return[0][0] == DB_TRUE);
    }

    /**
     * Compara dois timestamps e retorna true ou false.
     *
     * @param (string) $leftDate Timestamp da esquerda.
     * @param (string) $operator Operador lógico qualquer (< <= = != >= >)
     * @param (string) $rightDate Timestamp da direita.
     * @param (string) $mask Opcionalmente pode-se passar a máscara na qual $leftDate e $rightDate estão formatados. Em branco utiliza MASK_TIMESTAMP.
     * @return (boolean) True (false) se a comparação resultar true (false).
     */
    public static function compareTimestamp($leftDate, $operator, $rightDate, $mask = null)
    {
        $mask = is_null($mask) ? SAGU::getParameter('BASIC', 'MASK_TIMESTAMP') : $mask;

        $sql = ' SELECT TO_TIMESTAMP(?, \'' . $mask . '\') ' . $operator . ' TO_TIMESTAMP(?, \'' . $mask . '\')';
        $args = array(
            $leftDate,
            $rightDate );

        $return = SDatabase::query($sql, $args);

        return ($return[0][0] == DB_TRUE);
    }

    /**
     * Envia uma mensagem para output (geralmente utilizado em console)
     *
     * @param string $line
     */
    public static function output($line, $breakLine = true)
    {
        echo $line . ($breakLine ? "\n" : null);
    }

    /**
     * Retorna uma string convertendo o objeto recebido por parametro na forma
     * de declaração de objeto do PHP, limpando os atributos do MIOLO.
     *
     * @param $data object Objeto obtido com o $this->getData() do form
     * @param $children object Cada item do array de objetos do subdetail
     * @param $index integer Índice do array dos objetos do subdetail
     * @return $ret string String contendo o objeto PHP na forma de string
     */
    public static function convertFormDataToPhpStringObject($data, $children=NULL, $index=NULL)
    {
        // FIXME: revisar função, talvez exista um forma melhor de fazer isso
        // Limpa tudo que for relativo ao Miolo.
        // TODO: Verificar essa lista com mais casos
        $excludeList = array( 'module', 'action', 'function', 'webForm', 'mquickaccess_input', 'cpaint_response_type', 'PHPSESSID', '__.*', '_lookupDescription', 'arrayItem', 'msubdetail', 'SaveStateCookie' );

        //Adiciona campos de types, que geralmente sao protected
        $typeData = array( );
        if ( $data instanceof SType )
        {
            foreach ( $data->getObjectVars() as $key => $value )
            {
                if ( !isset($data->$key) )
                {
                    $typeData[$key] = $value;
                }
            }
            $data = $typeData;
        }

        foreach ( $data as $key => $value )
        {
            $matchExcludeList = false;
            foreach ( $excludeList as $el )
            {
                if ( preg_match("/$el/", $key) )
                {
                    $matchExcludeList = true;
                }
            }
            if ( !$matchExcludeList )
            {
                if ( is_array($value) )
                {
                    // array de objetos do subdetail
                    $ret .= self::convertFormDataToPhpStringObject($value, $key);
                }
                else if ( is_object($value) )
                {
                    // cada objeto (stdClass) do subdetail
                    $ret .= self::convertFormDataToPhpStringObject($value, $children, $key);
                }
                else
                {
                    if ( $children )
                    {
                        // subdetail - array de objetos
                        $ret .= "\$obj->$children\[$index\]->$key = '$value';\n";
                    }
                    else
                    {
                        // campos normais
                        $ret .= "\$obj->$key = '$value';\n";
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Cria um arquivo de teste unitário para o SAGU, utilizando a função
     * convertFormDataToPhpStringObject.
     *
     * @param object $data Objeto obtido com o $this->getData() do form.
     * @param string $class Nome da classe.
     * @param array $pkeys Vetor com os nomes das chaves primárias.
     * @param string $module Nome do módulo, caso não seja o atual.
     */
    public static function createUT($data, $class, $pkeys, $module=NULL)
    {
        $MIOLO = MIOLO::getInstance();
        $home = $MIOLO->getConf('home.modules');

        if ( !$module )
        {
            $module = MIOLO::getCurrentModule();
        }

        $utTemplateFile = "$home/basic/tests/testTemplate.tpl";
        $utTemplate = fread(fopen($utTemplateFile, 'r'), filesize($utTemplateFile));

        $utLog = self::convertFormDataToPhpStringObject($data);
        $utLog = str_replace('\\', '', var_export($utLog, true));
        $utLog = substr($utLog, 1, mb_strlen($utLog) - 2);

        // SType
        try
        {
            $MIOLO->uses("types/$class.class", $module);

            $utLog = "\$obj = new $class();\n$utLog";
            $utLog = "\$MIOLO->uses('types/$class.class', '$module');\n$utLog;";
            $utLog .= "\n\$this->addType(\$obj);";
        }
        // Business
        catch ( Exception $e )
        {
            $utLog = "\$bus = \$MIOLO->getBusiness('$module', '$class');\n$utLog";
            $utLog .= "\n\$this->addBusiness(array(\$bus, \$obj));";
        }

        $utContent = str_replace('%DATA%', $utLog, $utTemplate);
        $utContent = str_replace('%CLASS%', $class, $utContent);
        $utContent = str_replace('%MODULE%', $module, $utContent);
        $utContent = str_replace('%PKEYS%', var_export((array) $pkeys, true), $utContent);

        $file = 'Tst' . ucfirst($class) . '.class';
        $testsDir = "$home/$module/tests";

        if ( !file_exists($testsDir) )
        {
            mkdir($testsDir);
        }

        file_put_contents("$testsDir/$file", $utContent);

        return true;
    }

    /**
     * Função que retorna um boolean informando se o usuário está acessando do módulo
     * de serviços.
     *
     * @return boolean
     */
    public static function userIsFromServices()
    {
        return defined('USER_IS_FROM_SERVICES');
    }

    /**
     * Define que usuario esta vindo do modulo SERVICES.
     * Função chamada geralmente em handlers do mesmo.
     */
    public static function defineUserIsFromServices()
    {
        if ( !defined('USER_IS_FROM_SERVICES') )
        {
            define('USER_IS_FROM_SERVICES', true);
        }
    }

    /**
     * Método para criptografar uma string ou descriptografar a mesma
     * @param type $str Mensagem
     * @return String Mensagem criptografada
     */
    public static function encriptDecrypt($str)
    {

        $Len_Str_Message = strlen($str);
        $Str_Encrypted_Message = "";
        for ( $Position = 0; $Position < $Len_Str_Message; $Position++ )
        {
            $Key_To_Use = (($Len_Str_Message + $Position) + 1);
            $Key_To_Use = (255 + $Key_To_Use) % 255;
            $Byte_To_Be_Encrypted = substr($str, $Position, 1);
            $Ascii_Num_Byte_To_Encrypt = ord($Byte_To_Be_Encrypted);
            $Xored_Byte = $Ascii_Num_Byte_To_Encrypt ^ $Key_To_Use;
            $Encrypted_Byte = chr($Xored_Byte);
            $Str_Encrypted_Message .= $Encrypted_Byte;
        }
        return $Str_Encrypted_Message;
    }

    /**
     * Converte arrays sequenciais (geralmente retornados pelo metodo list*() dos business ou types),
     *  para um array associativo utilizando a primeira posicao como chave e a segunda como valor.
     *
     * Ex.:
     * array( array ( 0 => '9842', 1 => 'MARIA DA SILVA'), array( 0 => '8882', 1 => 'JOSE MACHADO')  )
     *
     * Retorna:
     * array ( '9842' => 'MARIA DA SILVA', '8882' => 'JOSE MACHADO' )
     *
     *
     */
    public static function convertListToAssociative($array)
    {
        $out = array( );

        foreach ( (array) $array as $arr )
        {
            $out[$arr[0]] = $arr[1];
        }

        return $out;
    }

    /**
     * Efetua o login no sistema novamente, restaurando as informações originais do usuário
     */
    public static function reLogin()
    {
        $MIOLO = MIOLO::getInstance();
        $login = $MIOLO->getLogin();

        $user = $MIOLO->GetBusinessMAD('user');
        $user->GetByLogin($login->loginId);
        $login = new MLogin($user);

        if ( $this->manager->GetConf("options.dbsession") )
        {
            $session = $this->manager->GetBusinessMAD('session');
            $session->LastAccess($login);
            $session->RegisterIn($login);
        }
        $MIOLO->auth->SetLogin($login);
    }

    /**
     * Metodo identico ao $MIOLO->getActionURL(), com alguns adicionais:
     * - Envia para a URL de destino o unique id com os filtros utilizados na interface atual, podendo, assim, ser voltado para a tela onde estava.
     * - Nao tem o terceiro parametro $event, mas sim, $args (para passar o $event utilize o atributo 'event' => 'EVENTO')
     * 
     * @param string $module
     * @param string $action
     * @param array $args
     */
    public static function getActionURL($module, $action, $args)
    {
        $MIOLO = MIOLO::getInstance();

        $uniqueId = MUtil::NVL($MIOLO->getConf('uniqueSearchId'), MIOLO::_REQUEST('uniqueSearchId'));
        if ( strlen($uniqueId) > 0 )
        {
            $args['uniqueSearchId'] = $uniqueId;
        }

        return $MIOLO->getActionURL($module, $action, null, $args);
    }

    /**
     * Busca por arquivos em diretorios
     *
     * @param string $directory Diretorio inicial
     * @param string $pattern Expressao regular com filtros de arquivos (padrao preg_match())
     * @param boolean $recursive Buscar em sub-diretorios
     * 
     * @return array Array com arquivos
     */
    public static function findFiles($directory, $pattern = null, $recursive = true)
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);

        // Filtro padrao de nome arquivo
        if ( is_null($pattern) )
        {
            $pattern = '/(.*)/i';
        }

        $files = array( );
        $scan = glob($directory . '/*');

        foreach ( (array) $scan as $path )
        {
            // Se for diretorio
            if ( is_dir($path) )
            {
                if ( $recursive )
                {
                    $files = array_merge($files, self::findFiles($path, $pattern));
                }
            }
            else // Se for arquivo
            {
                if ( preg_match($pattern, basename($path)) )
                {
                    $files[] = $path;
                }
            }
        }

        return $files;
    }

    /**
     * Obtem configuracao de e-mail para testes, caso exista
     * 
     * @return string E-mail
     */
    public static function getTestMail()
    {
        $MIOLO = MIOLO::getInstance();

        return $MIOLO->getConf('mail.test');
    }

    /**
     * Obtem usuario logado atualmente
     * 
     * @return stdClass
     */
    public static function getUsuarioLogado()
    {
        $MIOLO = MIOLO::getInstance();
        $module = SAGU::getFileModule(__FILE__);

        $busPerson = $MIOLO->getBusiness($module, 'BusPerson');
        return $busPerson->getCurrentLoginPerson();
    }

    /**
     * Funcao identica ao MUtil::NVL, porem suporta infinitos valores nos parametros.
     * Ex: SAGU::NVL(null, null, 'teste', null, 'abc') retorna: 'teste'
     * 
     * @return string Primeira string nao-nula passada
     */
    public static function NVL()
    {
        $ret = null;

        foreach ( func_get_args() as $val )
        {
            if ( strlen($val) > 0 )
            {
                $ret = $val;
                break;
            }
        }

        return $ret;
    }
    
    
    /**
     * Retorna se usuario logado atualmente tem permissao.
     *
     * @param string $transactionName Exemplo: FrmContractPerson
     * @param string $permType Exemplos: A_ACESS, A_ADMIN, A_UPDATE, A_DELETE...
     * 
     * @return boolean
     */
    public static function userHasAccess($transactionName, $permType)
    {
        $MIOLO = MIOLO::getInstance();
        return $MIOLO->checkAccess($transactionName, $permType, false, true) || $MIOLO->checkAccess($transactionName, A_ADMIN, false, true);
    }
    
    /**
     * Retorna se usuario logado atualmente possui permissao para
     *  ao menos um dos tipos de permissoes passados.
     *
     * @param type $transactionName
     * @param array $permTypes 
     */
    public static function userHasAccessAny($transactionName, array $permTypes)
    {
        $ok = false;
        
        foreach ( $permTypes as $permType )
        {
            if ( !$ok )
            {
                $ok = self::userHasAccess($transactionName, $permType);
            }
        }
        
        return $ok;
    }
    
    
    /**
     * Retorna se usuario logado atualmente possui permissao para
     *  TODOS os tipos de permissoes passados.
     *
     * @param type $transactionName
     * @param array $permTypes 
     */
    public static function userHasAccessAll($transactionName, array $permTypes)
    {
        $ok = true;
        
        foreach ( $permTypes as $permType )
        {
            $ok = $ok && self::userHasAccess($transactionName, $permType);
        }
        
        return $ok;
    }
    
    /**
     * Chama o MIOLO->invokeHandler() de forma automatica para nao ter mais de criar
     *  os arquivos handlers para cada subdiretorio.
     *
     * @return boolean Se invocou ou nao algum handler
     */
    public static function invokeHandlerAuto()
    {
        $MIOLO = MIOLO::getInstance();
        $module = MIOLO::getCurrentModule();
        $action = MIOLO::getCurrentAction();
        $invoked = false;
        
        $shiftAction = str_replace('main:', '', $action);
        $shiftAction = str_replace(':', '/', $shiftAction);
        $shiftAction = trim($shiftAction);

        // Percorre subpath dos handlers para ver se existe um handler que deve ser chamado antes
//        $sysModule = SModules::getModule($module);
//        $modulePath = $sysModule->getSystemPath().'/handlers';
//        foreach ( (array) explode('/', $shiftAction) as $subPath )
//        {
//            $path .= "/{$subPath}";
//            $handlerFile = "{$modulePath}/{$path}.inc";
//            if ( file_exists($handlerFile) )
//            {
//                $invoked = true;
//                $MIOLO->invokeHandler($module, $path);
//            }
//        }
        
        if ( !$invoked && !in_array(strtolower($shiftAction), array('', 'main', 'diverseconsultation')) )
        {
            $confName = "temp.invoked.{$shiftAction}";
            $invoked = true;
            
            // Se ainda nao foi invocado esta acao
            if ( !$MIOLO->getConf($confName) )
            {
                $MIOLO->setConf($confName, true);
                $MIOLO->invokeHandler($module, $shiftAction);
            }
        }

        return $invoked;
    }
    
    
    /**
     * Percorre todos valores do array passado e adiciona uma quote entre cada um ($delimiter)
     * Util para converter valores para SQL, etc..
     *
     * @param array $values
     * @param string $delimiter Tipo de quote
     * @param string $callbackFunction Funcao que deve chamar para cada valor (ex.: strtolower)
     * 
     * @return array
     */
    public static function quoteArrayStrings(array $values, $delimiter = null, $callbackFunction = null)
    {
        if ( !$delimiter )
        {
            $delimiter = "'";
        }
        
        foreach ( $values as $key => $val )
        {
            if ( $callbackFunction )
            {
                $val = call_user_func($callbackFunction, $val);
            }
            
            $values[$key] = $delimiter . $val . $delimiter;
        }
        
        return $values;
    }
    
    /**
     * Verifica se todos valores passados possuem LENGTH maior que zero.
     * Ex.:
     * - allIsFilled('abc', 'uuu', null, 'eee') => FALSE
     * - allIsFilled('abc', 'uuu', 'eee') => TRUE
     *
     * @return boolean
     */
    public static function allIsFilled()
    {
        $ok = true;
        
        foreach ( func_get_args() as $val )
        {
            $ok = $ok && strlen($val) > 0;
        }
        
        return $ok;
    }

    /**
     * Obtem uma data por extenso (ex.: 21 de Janeiro de 2012)
     *
     * @param string $data
     * @return string
     */
    public static function obterDataPorExtenso($data)
    {
        $query = SDatabase::query('SELECT dataporextenso(?)', array($data));
        return $query[0][0];
    }
    
    /**
     * Retorna se esta em modo debug no SAGU.
     * 
     * Util para desenvolvedores conseguirem testar o sistema de forma facil,
     *  sem ter que entrar com logins com permissoes validas (ex. portal), etc..
     * 
     * 
     * @return boolean 
     */
    public static function isDebugMode()
    {
        $MIOLO = MIOLO::getInstance();
        return $MIOLO->getConf('options.sagudebugmode');
    }
    
    /**
     * Valida hash do webServicesBasic, gerado através da função wsLogin.
     * 
     * @return boolean - Verdadeiro caso o hash seja valido.
     */
    public static function validarHashDeAutenticacao()
    {
        return BasWebServiceLogin::validarHash();
    }
    
    /**
     * A partir do hash enviado pelo webServicesLogin são obtidos o usuário,
     * a senha e a unidade.
     * 
     * @return stdClass
     */
    public static function obterDadosDeLoginAPartirDoHash()
    {
        return BasWebServiceLogin::obterDadosDeLoginAPartirDoHash();
    }
}
?>