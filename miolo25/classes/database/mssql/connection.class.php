<?php
#+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
# @title
#   MsSQL Connection
#
# @description
#   This file contains MsSQL connection functions
#
# @see      miolo/database.class,
#           miolo/mssql_query.class,
#
# @topics   db, business
#
# @created
#   2003/09/17
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
#   Clausius Duque G. Reis   [user]   [clausius@ufv.br]
#
# @maintainers
#   Vilson Cristiano Gartner [author] [vgartner@univates.br]
#   Thomas Spriestersbach    [author] [ts@interact2000.com.br]
#
# history: see miolo cvs.
#
# @id $id: mssql_connection.class,v 1.0 2003/09/12 11:18:00 clausius Exp $
#---------------------------------------------------------------------

/**
 *
 */
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MssqlConnection
{
/**
 * Attribute Description.
 */
    public $conf;       // name of database configuration

/**
 * Attribute Description.
 */
    public $id;         // the connection identifier

/**
 * Attribute Description.
 */
    public $traceback;  // a list of transaction errors

/**
 * Attribute Description.
 */
    public $level;      // a counter for the transaction level


/**
 * Brief Description.
 * Complete Description.
 *
 * @param $conf (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function mssqlConnection($conf)
    {
        $MIOLO = MIOLO::getInstance();

        $this->conf = $conf;

        $MIOLO->uses('database/mssql_query.class');
	}

    // opens a connection to the specified data source
/**
 * Brief Description.
 * Complete Description.
 *
 * @param $dbhost (tipo) desc
 * @param $loginDB (tipo) desc
 * @param $loginUID (tipo) desc
 * @param $loginPWD (tipo) desc
 * @param $persistent (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function open($dbhost,$loginDB,$loginUID,$loginPWD,$persistent=true)
    {
        global $php_errormsg;
        $MIOLO = MIOLO::getInstance();

        if ( $this->id )
        {
            Close();
        }

        $this->traceback = null;
        $this->level = 0;

        if ( false && $persistent )
        {
            $this->id = mssql_pConnect($dbhost,$loginUID,$loginPWD);
        }
        else
        {
            $this->id = mssql_Connect($dbhost,$loginUID,$loginPWD);
        }

        if ( ! $this->id )
        {
            $this->traceback[] = "Unable to estabilish DataBase Conection to host: $dbhost, DB: $loginDB";
        }

        return $this->id;
    }

    // closes a previously opened connection
/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function close()
    {
        if ( $this->id )
        {
            $MIOLO = MIOLO::getInstance();

			$MIOLO->assert($this->level==0,"Transactions not finished!");

            mssql_close($this->id);

            $this->id = 0;
        }
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function begin()
    {
        $this->execute("begin transaction");

        $this->level++;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function finish()
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->assert($this->level>0,"Transaction level underrun!");

        $success = $this->getErrorCount() == 0;

        if ( $success )
        {
            $this->execute("commit");
        }
        else
        {
            $this->execute("rollback");
        }

        $this->level--;

        return $success;
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

        if ( ! $this->id )
        {
			$err = "No valid Database connection estabilished.";
            if ( $this->traceback )
            {
				$err .= "<br>" . implode("<br>", $this->traceback);
            }

        }
        else
        {
		 	$err = mssql_get_last_message($this->id);
        }
        return $err;
    }


/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getErrors()
    {
		return $this->traceback;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function getErrorCount()
    {
		return empty($this->traceback) ? 0 : count($this->traceback);
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @returns (tipo) desc
 *
 */
    public function checkError()
    {
        $MIOLO = MIOLO::getInstance();

        if ( empty($this->traceback) )
        {
            return;
        }

        $n = count($this->traceback);

        if ( $n > 0 )
        {
            $msg = "";

            for ( $i=0; $i<$n; $i++ )
            {
                $msg .= $this->traceback[$i] . "<br>";
            }

            $MIOLO->assert(false ,"Transaction Error",$msg);
        }
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
    public function execute($sql)
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->logSQL($sql,false,$this->conf);

        if ( $this->level == 0 )
        {
            $this->traceback = null;
        }

        $rs = mssql_query($this->id,$sql);

        $success = false;

        if ( $rs )
        {
            $success = true;

            mssql_free_result($rs);
        }
        else
        {
            $this->traceback[] = $this->getError();
        }

        return $success;
    }

/**
 * Brief Description.
 * Complete Description.
 *
 * @param $sql" (tipo) desc
 *
 * @returns (tipo) desc
 *
 */
    public function createQuery($sql="")
    {
        $MIOLO = MIOLO::getInstance();

        $MIOLO->assert($this->id, $this->getErrors());

        $q = new MssqlQuery($this->conf);

        $q->conn   = $this;
        $q->sql    = $sql;
        $q->result = 0;
        $q->row    = -1;

        if ( $sql != "" )
        {
            $q->open();
        }

        return $q;
    }
};

?>
