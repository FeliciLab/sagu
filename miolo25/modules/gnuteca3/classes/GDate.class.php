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
 * Class GDate
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 06/10/2010
 *
 **/

class GDate
{
    private $day    = null,
            $month  = null,
            $year   = null,
            $hour   = null,
            $minute = null,
            $second = null;

    const MASK_DATE_USER      = 'd/m/Y';
	const MASK_TIME           = 'H:i:s';
	const MASK_TIMESTAMP_USER = 'd/m/Y H:i:s';
	const MASK_DATE_DB        = 'Y-m-d';
	const MASK_TIMESTAMP_DB   = 'Y-m-d H:i:s'; 
        const MASK_DATE_STRING    = 'YmdHis';
	
    const ROUND_AUTOMATIC = 'a';
	const ROUND_DOWN = 'd';
	const ROUND_UP = 'u';
    
    public function __construct($date = null)
    {
        $this->setDate($date);
    }


    /**
     * Contrutor estático usado para que possa se utilizar
     * o construtor e chamar a função necessária na mesma linha.
     *
     * @param string $date
     * @return GDate
     *
     * @example GDate::construct( $date )->generate() = retorna a data em formato de usuário
     */
    public static function construct( $date )
    {
        return new GDate($date);
    }

    
    /**
     * Seta o dia
     * 
     * @param $day
     */
    public function setDay($day)
    {
    	$this->day = $day;
    }
    
    
    /**
     * Obtém o dia
     * 
     * @return dia
     */
    public function getDay()
    {
    	return $this->day;
    }
    
    
    /**
     * Soma dias na data
     * 
     * @param $day
     * @return GDate para funcionar em uma linha
     */
    public function addDay($day)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second, $this->month, $this->day+$day, $this->year));
        $this->setDate($date);

        return $this;
    }
    
    
    /**
     * Seta o mês
     * 
     * @param $month
     */
    public function setMonth($month)
    {
        $this->month = $month;
    }
    
    
    /**
     * Obtém o mês
     * 
     * @return mês
     */
    public function getMonth()
    {
    	return $this->month;
    }
    
    
    /**
     * Soma meses na data
     * 
     * @param $month
     */
    public function addMonth($month)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second, $this->month+$month, $this->day, $this->year));
        $this->setDate($date);
    }
    
    
    /**
     * Seta o ano
     * 
     * @param $year
     */
    public function setYear($year)
    {
    	$this->year = $year;
    }
    
    
    /**
     * Obté o ano
     * 
     * @return ano
     */
    public function getYear()
    {
    	return $this->year;
    }
    
    
    /**
     * Soma anos na data
     * 
     * @param $year
     */
    public function addYear($year)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year+$year));
        $this->setDate($date);
    }
    
    
    /**
     * Seta a hora
     * 
     * @param $hour
     */
    public function setHour($hour)
    {
    	$this->hour = $hour;
    }
    
    
    /**
     * Obtém a hora
     * 
     * @return hora
     */
    public function getHour()
    {
    	return $this->hour;
    }
    
    
    /**
     * Soma horas na data
     * 
     * @param $hour
     */
    public function addHour($hour)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour+$hour, $this->minute, $this->second, $this->month, $this->day, $this->year));
        $this->setDate($date);
    }
    
    
    /**
     * Seta o minuto
     * 
     * @param $minute
     */
    public function setMinute($minute)
    {
    	$this->minute = $minute;
    }
    
    
    /**
     * Obtém o minuto
     * 
     * @return minuto
     */
    public function getMinute()
    {
    	return $this->minute;
    }
    
    
    /**
     * Soma minutos na data
     * 
     * @param $minute
     */
    public function addMinute($minute)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute+$minute, $this->second, $this->month, $this->day, $this->year));
        $this->setDate($date);
    }
    
    
    /**
     * Seta o segundo
     * 
     * @param $second
     */
    public function setSecond($second)
    {
        $this->second = $second;    	
    }
    
    
    /**
     * Obtém o segundo
     * 
     * @return segundo
     */
    public function getSecond()
    {
    	return $this->second;
    }
    
    
    /**
     * Soma segundos na data
     * 
     * @param $second
     */
    public function addSecond($second)
    {
        $date = date(self::MASK_TIMESTAMP_USER, mktime($this->hour, $this->minute, $this->second+$second, $this->month, $this->day, $this->year));
        $this->setDate($date);
    }
  
    
    /**
     * Seta a data que será trabalhada na classe, identificando qual é a máscara passada
     *
     * @param timestamp $date
     */
    public function setDate($date = null)
    {
        $this->clean();

        if(!is_null($date))
        {
           $this->explodeDate($date);
        }
    }


    /**
     * Verifica se é uma data valida.
     *
     * @return boolean
     */
    public function isValid()
    {
        if ( !$this->month || !$this->day || !$this->year )
        {
            return false;
        }

        return checkdate($this->month, $this->day, $this->year);
    }

  
    /**
     * Função chamada automaticamente pelo PHP quando precisa converter dado para String
     * 
     * @return a data no formato do usuário
     */
    public function __toString()
    {
        return $this->getDate( self::MASK_DATE_USER );
    }
    
    
    /**
     * Função que o miolo chama automaticamente, convertendo o objeto para string
     * 
     * @return a data no formato do usuário
     */
    public function generate()
    {
        return $this->getDate( self::MASK_DATE_USER );
    }


    /**
     * Retorna a diferença entre a data do objeto e a data do objeto do parametro.
     *
     * @param Object GDate
     * @return timestamp unix da diferença
     */
    public function subtractDate($date)
    {
    	$timesTamp2 = 0;
    	if ($date instanceof GDate)
    	{
    		$timesTamp2 = $date->getTimestampUnix();
    	}
        
    	return $this->getTimestampUnix() - $timesTamp2;
    }


    /**
     * Calcula a diferença entre datas
     * 
     * @param: da a ser comparada
     * @return (object DiffDate)
     */
    public function diffDates($date, $round=null)
    {
    	$timesTamp2 = 0;
        if ( $date instanceof GDate )
        {
            $timesTamp2 = $date->getTimestampUnix();	
        }
        $timesTamp1 = $this->getTimestampUnix();
        $diff       = $timesTamp1 - $timesTamp2;
        
        $data = new DiffDate();
        $data->seconds = $diff;
        $data->minutes = $this->roundNumber($diff / 60, $round);
        $data->hours   = $this->roundNumber($diff / 3600, $round);
        $data->days    = $this->roundNumber($diff / 86400, $round);
        $data->months  = $this->roundNumber($diff / 2592000, $round);
        $data->years   = $this->roundNumber($diff / 31536000, $round); 
        
        return $data;        
    }

    
    /**
     * Este metodo verifica se a data passada por parametro esta em um formato válido.
     * Estando em um formato válido, esta seta os atributos internos e retorna true.
     *
     * @param: data em qualquer máscara 
     * @return (boolean)
     */
    private function explodeDate( $date )
    {
        // formato = dd/mm/yyyy hh:ii:ss
        if(ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month  = $reg[2];
            $this->day    = $reg[1];
            $this->year   = $reg[3];

            return true;
        }
        // formato = dd/mm/yyyy hh:ii:ss.nnnnnn
        if(ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.(.{1,})\$", $date, $reg))
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month  = $reg[2];
            $this->day    = $reg[1];
            $this->year   = $reg[3];

            return true;
        }
        // formato = dd/mm/yyyy hh:ii
        if(ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4}) ([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = '00';
            $this->month  = $reg[2];
            $this->day    = $reg[1];
            $this->year   = $reg[3];

            return true;
        }
        // formato = dd/mm/yyyy
        if(ereg("^([0-9]{2})\/([0-9]{2})\/([0-9]{4})\$", $date, $reg))
        {
            $this->hour   = '00';
            $this->minute = '00';
            $this->second = '00';
            $this->month  = $reg[2];
            $this->day    = $reg[1];
            $this->year   = $reg[3];

            return true;
        }

        // formato = yyyy-mm-dd hh:ii:ss
        if(ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month  = $reg[2];
            $this->day    = $reg[3];
            $this->year   = $reg[1];

            return true;
        }
       
        // formato = yyyy-mm-dd hh:ii:ss.nnnnnn
        if(ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\:([0-9]{2})\.(.{1,})\$", $date, $reg))
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month  = $reg[2];
            $this->day    = $reg[3];
            $this->year   = $reg[1];

            return true;
        }
        
        // formato = yyyy-mm-dd hh:ii
        if(ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2}) ([0-9]{2})\:([0-9]{2})\$", $date, $reg))
        {
            $this->hour    = $reg[4];
            $this->minute  = $reg[5];
            $this->second  = '00';
            $this->month   = $reg[2];
            $this->day     = $reg[3];
            $this->year    = $reg[1];

            return true;
        }
        // formato = yyyy-mm-dd
        if(ereg("^([0-9]{4})-([0-9]{2})-([0-9]{2})\$", $date, $reg))
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = '00';
            $this->month  = $reg[2];
            $this->day    = $reg[3];
            $this->year   = $reg[1];

            return true;
        }
        
        // formato = yyyymmddhhiiss
        if(ereg("^([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})$", $date, $reg) )
        {
            $this->hour   = $reg[4];
            $this->minute = $reg[5];
            $this->second = $reg[6];
            $this->month  = $reg[2];
            $this->day    = $reg[3];
            $this->year   = $reg[1];

            return true;
        }

        //se for timestamp
        if (is_numeric($date))
        {
            $date = date(self::MASK_TIMESTAMP_USER, $date);
            $this->explodeDate($date);

            return true;
        }
        
        //Se não compreender nenhum dos formatos de data não é considerada uma data válida.
        return false;
    }


    /**
     * Limpa os atributos do objeto
     */
    private function clean()
    {
        $this->hour   =
        $this->minute =
        $this->second =
        $this->month  =
        $this->day    =
        $this->year;
    }


    /**
     * Método estático que retorna o tempo e data atual
     * 
     * @param máscara a ser aplicada
     * @return (objetct) GDate
     */
    public static function now()
    {
    	return new GDate(date(self::MASK_TIMESTAMP_USER));
    }
    
    
    /**
     * Método para obter a data conforme a máscara passada por parâmetro
     * 
     * @param $mask
     * @return string com a data
     */
    public function getDate($mask = self::MASK_TIMESTAMP_USER)
    {
    	if ( $this->getTimestampUnix() ) 
    	{
    	   $date = date($mask, $this->getTimestampUnix());
    	   return $date;
    	}
    	else 
    	{
    		return null;
    	}
    }
    
    
     /**
     * Retorna o timestamp unix da data
     *
     * @return long int
     */
    public function getTimestampUnix()
    {
    	if ( $this->month && $this->day && $this->year )
    	{
            return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
    	}
    	else 
    	{
    		return null;
    	}
    }
    
    
    /**
     * Compara dois objetos GDate
     *
     * @param (object) GDate
     * @param string $operation
     * @return boolean
     */
    public function compare($date, $operation = '=')
    {
        switch ($operation)
        {
            case '>'    :
                return ($this->getTimestampUnix() > $date->getTimestampUnix());

            case '<'    :
                return ($this->getTimestampUnix() < $date->getTimestampUnix());

            case '>='   :
                return (($this->getTimestampUnix() == $date->getTimestampUnix()) || ($this->getTimestampUnix() > $date->getTimestampUnix()));

            case '<='   :
                return (($this->getTimestampUnix() == $date->getTimestampUnix()) || ($this->getTimestampUnix() < $date->getTimestampUnix()));

            default     :
                return ($this->getTimestampUnix() == $date->getTimestampUnix());
        }

    }
    
    
    /**
     * Método privado para arredondar valores
     * 
     * @param número
     * @param arredondamento
     * @return valor arredondado
     */
    private function roundNumber($number, $round)
    {
    	if($round == self::ROUND_DOWN)
    	{
    		$number = floor($number);
    	}
    	elseif ( $round == self::ROUND_UP)
    	{
    		$number = ceil($number);
    	}
    	elseif( $round == self::ROUND_AUTOMATIC)
        {
            $number = round($number);
        }
        
        return $number;
    }

    /**
     * Retorna o dia da semana de 1 a 7
     *
     * @return integer
     */
    public function getDayOfWeek()
    {
        return date( 'N' , $this->getTimestampUnix() );
    }
    
    //Método para transformar sua data no padrão abaixo
    public function getYYYYMMDDHHMMSS($data)
    {   
        //Verifica o formato da data
        if($data[4] == '-')
        {
            $v1 = str_replace("-", "", $data);
            $v2 = str_replace(" ", "", $v1);
            $v3 = str_replace(":", "", $v2);
            $v4 = str_replace("/", "", $v3);
            return $v4;
        }
        
        $v1 = str_replace("-", "", $data);
        $v2 = str_replace(" ", "", $v1);
        $v3 = str_replace(":", "", $v2);
        $v4 = str_replace("/", "", $v3);
        
        $yyyy = substr($v4, 4, 4);
        $mm = substr($v4, 2, 2);
        $dd = substr($v4, 0, 2);
        
        $hh = substr($v4, 8, 2);
        $MM = substr($v4, 10, 2);
        $ss = substr($v4, 12, 2); 
        
        $v4 = $yyyy . $mm . $dd . $hh . $MM . $ss;
        return $v4;
    }
}

/**
 * Class DiffDate
 *
 * @author Jader Osvino Fiegenbaum [jader@solis.coop.br]
 *
 * @version $Id$
 *
 * \b Maintainers: \n
 * Eduardo Bonfandini [eduardo@solis.coop.br]
 * Jamiel Spezia [jamiel@solis.coop.br]
 * Moises Heberle [moises@solis.coop.br]
 *
 * @since
 * Class created on 08/10/2010
 *
 **/

class DiffDate
{
	public $days, 
	       $months, 
	       $years, 
	       $hours, 
	       $minutes, 
	       $seconds;
}

?>
