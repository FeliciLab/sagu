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
 * Class GMessages extend GnutecaBussines that extends the default MBussines,
 * including default database configuration and some usefull functions.
 *
 * @author Eduardo Bonfandini [eduardo@solis.coop.br]
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
 * Class created on 28/07/2008
 *
 **/
class GMessages extends GBusiness
{
    const TYPE_ERROR        = 1;
    const TYPE_INFORMATION  = 2;
    const TYPE_QUESTION     = 3;
    const TYPE_ALERT        = 4;

    private $msgs;
    private $miolo;


    /**
    * Default constructor
    */
    function GMessages()
    {
        parent::__construct();
        $this->MIOLO = MIOLO::getInstance();
    }


    /**
    * Add a Message with a type to message list. It will debug the file and the line that the error occurs
    *
    * @param $type 1 = error 2 = info 3 = question, 4 alert
    * @param $msg  the string with the messge you want to log
    */
    function addMessage($type , $msg, $debug_backtrace=NULL, $extraColumns = null, $msgCode = null )
    {
        $data = null;
        //se não vier debug_backtrace pega aqui;
        if (!$debug_backtrace)
        {
            $debug_backtrace    = debug_backtrace();
        }

        // aceita uma instancia de objeto de mensagem
        if ( $msg instanceof GMessage )
        {
        	$this->msgs[]       = $msg;
        }
        else
        {
	        $msg                = new GMessage($type, $msg, $debug_backtrace, $extraColumns );
	        $msg                ->setMsgCode($msgCode);
            $this->msgs[]       = $msg;
        }


    }


    /**
    * Add a ERROR Message to message list. It will debug the file and the line that the error occurs
    *
    * @param $msg  the string with the messge you want to log
    */
    function addError($message, $extraColumns = null, $msgCode = null )
    {
        $debug_backtrace    = debug_backtrace();
        $this->addMessage('1', $message, $debug_backtrace, $extraColumns, $msgCode );
    }


    /**
    * Add a INFO (information) Message to message list. It will debug the file and the line that the error occurs
    *
    * @param $msg  the string with the messge you want to log
    */
    function addInformation($message, $extraColumns = null, $msgCode = null )
    {
        $debug_backtrace    = debug_backtrace();
        $this->addMessage('2', $message, $debug_backtrace, $extraColumns, $msgCode);
    }


    /**
    * Add a QUESTION Message to message list. It will debug the file and the line that the error occurs
    *
    * @param $msg  the string with the messge you want to log
    */
    function addQuestion($message, $extraColumns = NULL, $msgCode = null )
    {
        $debug_backtrace    = debug_backtrace();
        $this->addMessage('3', $message, $debug_backtrace, $extraColumns, $msgCode);
    }

    /**
    * Add a ALERT Message to message list. It will debug the file and the line that the error occurs
    *
    * @param $msg  the string with the messge you want to log
    */
    function addAlert($message, $extraColumns = NULL, $msgCode = null )
    {
        $debug_backtrace    = debug_backtrace();
        $this->addMessage('4', $message, $debug_backtrace, $extraColumns, $msgCode);
    }


    /**
    * Return a array of objects with all information
    * @return a array of objects with all information
    */
    function getMessages( $type=NULL )
    {
        $msgs = $this->msgs;
        if ($type)
        {
            if ($msgs)
            {
                foreach ($msgs as $line => $info)
                {
                    if ($info->type == $type)
                    {
                        $result[] = $info;
                    }
                }
            }
            return $result;
        }
        else
        {
            return $this->msgs;
        }
    }

    public function setMessages($messages)
    {
        $this->msgs = $messages;
    }

    /**
     * Return all message to a string imploded by sepator;
     *
     * @param integer $type, is the type of message, used as a filter
     * @param string $separator to implode the data
     * @return unknown
     */
    function messagesToString($type=NULL, $separator=', ')
    {
    	$msgs = $this->getMessages($type);
    	if (count($msgs))
    	{
    		$retval = array();
            foreach ($msgs as $val)
            {
            	$retval[] = $val->message;
            }
            return implode($separator, $retval);
    	}
    	return '';
    }


    /**
    * Return a array of objects with all ERRORS
    * @return a array of objects with all ERRORS
    */
    function getErrors()
    {
        return $this->getMessages('1');
    }


    /**
    * Return a array of objects with all INFO (information)
    * @return a array of objects with all INFO (information)
    */
    function getInformations()
    {
        return $this->getMessages('2');
    }

    /**
    * Return a array of objects with all QUESTION
    * @return a array of objects with all QUESTION
    */
    function getQuestions()
    {
        return $this->getMessages('3');
    }

    /**
    * Return a array of objects with all ALERT
    * @return a array of objects with all ALERT
    */
    function getAlerts()
    {
        return $this->getMessages('4');
    }


    /**
    * This function will clear the message list
    *
    */
    function clearMessages($type = null)
    {
    	if ($type)
    	{
            if ($this->msgs)
            {
            	foreach ($this->msgs as $i => $msg)
            	{
                    if ($msg->type == $type)
                    {
                        unset($this->msgs[$i]);
                    }
            	}
            }
    	}
    	else
    	{
            unset($this->msgs);
    	}
    }

    public function clean($type = null)
    {
        $this->clearMessages($type);
    }


    /**
     * Return a table raw with messages in class
     *
     * @param int $filterType the type of class, please see class docs
     * @param boolean $debugColumns true if you want extra debug information to go to tableRaw
     */
    function getMessagesTableRaw( $filterType = NULL, $debugColumns= false, $extraColumns = null )
    {
    	$MIOLO  = MIOLO::getInstance();
    	$module = MIOLO::getCurrentModule();
        
        $table = new MTableRaw('',null);
    	$table->setAttributes('cellspacing=1 width=null cellpadding=3 align=center width=100%');

    	$titles[] = _M('Tipo', $module);

    	if ( is_array($extraColumns) )
        {
            foreach ( $extraColumns as $line => $info)
            {
                $titles[] = $info;
            }
        }

    	$titles[] = _M('Mensagem', $module) ;

    	if ($debugColumns)
    	{
    		$titles[] = _M('Data', $module);
    		$titles[] = _M('Operador', $module);
    		$titles[] = _M('arquivo', $module);
    		$titles[] = _M('linha', $module);
    	}

    	$messages = $this->getMessages( $filterType );

    	$imageError = new MImage('error'    , 'error'   , GUtil::getImageTheme('error-16x16.png' ) ); //vermelho
        $imageInfo  = new MImage('info'     , 'info'    , GUtil::getImageTheme('info-16x16.png'  ) );
        $imageQuest = new MImage('question' , 'question', GUtil::getImageTheme('search-16x16.png') );
        $imageAlert = new MImage('alert'    , 'alert'   , GUtil::getImageTheme('alert-16x16.png' ) ); //laranja

    	if ( is_array( $messages ) )
    	{
    		foreach ($messages as $line => $info)
    		{
    			$tempData = null;

    			if ($info->type == 1)
    			{
    				$info->type = $imageError->generate();
                    $table->setRowAttribute($line, 'class', 'mTableRawRowError');
    			}
    		    if ($info->type == 2)
    			{
    				$info->type = $imageInfo->generate();
    			}
    		    if ($info->type == 3)
    			{
    				$info->type = $imageQuest->generate();
    			}
                if ($info->type == 4)
                {
                    $info->type = $imageAlert->generate();
                    $table->setRowAttribute($line, 'class', 'mTableRawRowAlert');
                }

    		    $tempData[] = $info->type;

		        if ( is_array($extraColumns) )
		        {
		            foreach ( $extraColumns as $l => $i)
		            {
		                $tempData[] = $info->$l;
		            }
		        }

		        $tempData[] = $info->message;

		        if ($debugColumns)
		        {
		            $tempData[] = $info->date;
		            $tempData[] = $info->operator;
		            $tempData[] = $info->file;
		            $tempData[] = $info->line;
		        }

		        $data[] = $tempData;

    		}
    	}
    	
        $table->setData($data);
        $table->colTitle = $titles;
        
    	return $table;
    }
}


/**
* Class to administrate ONE message.
*/
class GMessage
{
    public $type;
    public $message;
    public $date;
    public $operator;
    public $file;
    public $line;
    public $msgCode;

    /**
     * Consttuct a message object
     *
     * @param integer $type
     * @param string$message
     * @param array $debug_backtrace
     * @param array $extraData
     * @return GMessage
     */
    public function GMessage($type, $message, $debug_backtrace=NULL, $extraData = NULL)
    {
        $MIOLO              = MIOLO::getInstance();
        $this->operator     = $MIOLO->getLogin()->id;
        $this->date         = date('Y-m-d h:i:s');
        $this->message      = $message;
        $this->type         = $type;

        //se não vier debug_backtrace pega aqui;
        if (!$debug_backtrace)
        {
            $debug_backtrace    = debug_backtrace();
        }
        $debug_backtrace    = $debug_backtrace[0];
        $this->file         = $debug_backtrace['file'];
        $this->line         = $debug_backtrace['line'];
        /*$this->class        = $debug_backtrace['class'];
        $this->function     = $debug_backtrace['function'];*/

        //adiciona os dados extras ao objeto
        if ( $extraData && is_array( $extraData ) )
        {
        	foreach ( $extraData as $line => $info)
        	{
        		$this->$line = $info;
        	}
        }
    }

    public function setMsgCode($msgCode)
    {
        $this->msgCode = $msgCode;
    }

    public function getMsgCode()
    {
        return $this->msgCode;
    }

    public function getMessage()
    {
    	return $this->message;
    }

    public function setMessage( $message )
    {
        $this->message = $message;
    }

    public function generate()
    {
    	return $this->__toString();
    }

    public function __toString()
    {
        return $this->getMessage();
    }

}
?>
