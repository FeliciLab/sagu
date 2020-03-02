<?php
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# @title
#   MsSQL Query
#
# @description
/**
 * Brief Class Description.
 * Complete Class Description.
 */
#   This file contains de query class definitions
#
# @see      miolo/database.class,
#           miolo/mssql_connection.class,
#
# @topics   db, business
#
# @created
#   2001/08/14
#
# @organisation
#   MIOLO - Miolo Development Team - UNIVATES Centro Universitario
#   http://miolo.codigolivre.org.br
#
# @legal
#   CopyLeft (L) 2001-2002 UNIVATES, Lajeado/RS - Brasil
#   Licensed under GPL (see COPYING.TXT or FSF at www.fsf.org for
#   further details)
#
# @contributors
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#   Clausius Duque G. Reis            [clausius@ufv.br]
#
# @maintainers
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#   Clausius Duque G. Reis            [clausius@ufv.br]
#
# history: see miolo cvs.
#
# @id $id: mssql_query.class,v 1.2 2003/09/12 11:18:00 clausius Exp $
#---------------------------------------------------------------------

/**
 *
 */
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MssqlQuery
{
/**
 * Attribute Description.
 */
    public $conf;     // name of database configuration

/**
 * Attribute Description.
 */
    public $conn;     // the connection id

/**
 * Attribute Description.
 */
    public $sql;      // the SQL command string

/**
 * Attribute Description.
 */
    public $result;   // the SQL command result set

/**
 * Attribute Description.
 */
    public $row;      // the current row index

/**
 * Attribute Description.
 */
    public $error;    // the query's error message from the query execution


/**
 * Brief Description.
 * Complete Description.
 *
 * @param $conf (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function mssqlQuery($conf)
    {
        $this->conf = $conf;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getError()
    {
		return $this->error;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function open()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->logSQL($this->sql,false,$this->conf);

        $this->result = mssql_query($this->conn->id,$this->sql);
        $this->row    = -1;

		$this->error = mssql_get_last_message();;

        return $this->result != null;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function close()
    {
        if ( $this->result != null )
        {
            mssql_free_result($this->result);

            $this->result = null;
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function movePrev()
    {
        if ( $this->row >= 0 )
        {
            $this->row--;

            return true;
        }

        return false;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function moveNext()
    {
        if ( $this->row + 1 < $this->getRowCount() )
        {
            $this->row++;

            return true;
        }

        return false;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getRowCount()
    {
        if ($this->result != 0) {
		  return mssql_num_rows($this->result);
		}
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getColumnCount()
    {
        return mssql_num_fields($this->result);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $col (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function getColumnName($col)
    {
        return mssql_field_name($this->result,$col-1);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $col (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function getValue($col)
    {
        return mssql_result($this->result,$this->row,$col-1);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getRowValues()
    {
        return mssql_fetch_row($this->result,$this->row);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $c (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setConnection($c)
    {
        $this->conn = $c;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $sql (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function setSQL($sql)
    {
        $this->sql  = $sql;
    }
}

?>
