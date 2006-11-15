<?php

/** AgataPgsql
 *  Agata Driver for PostgreSQL
 */
class AgataPgsql
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $protocol = 'tcp';
        //port
        if (strpos($host, ':'))
        {
            $pieces = explode(':', $host);
            $host = $pieces[0];
            $port = $pieces[1];
            $connstr = ' port=' . $port;
        }
        $connstr .= ' host=' . $host;
        if (isset($database))
        {
            $connstr .= ' dbname=' . $database;
        }
        if (!empty($user))
        {
            $connstr .= ' user=' . $user;
        }
        if (!empty($pass))
        {
            $connstr .= ' password=' . $pass;
        }

        ob_start();
        $conn = pg_connect($connstr);
        $error = ob_get_contents();
        ob_end_clean();
        if ($conn == false)
        {
            return new AgataError($error);
        }
        $this->connection = $conn;
        return true;
    }

    /** Function Disconnect
     *  Disconnects a Database
     */
    function Disconnect()
    {
        $ret = @pg_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = pg_query($this->connection, $query);
        if (!$result) {
            return $this->RaiseError();
        }
        return $result;
    }

    /** Function FetchRow
     *  Fetch a Row and returns as an array.
     */
    function FetchRow($result)
    {
        return @pg_fetch_row($result);
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult($result)
    {
        if (is_resource($result))
        {
            return @pg_freeresult($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = @pg_numfields($result);
        if (!$cols) {
            return $this->RaiseError();
        }
        return $cols;
    }

    /** Function NumRows
     *  Returns the number of rows of a query
     */
    function NumRows($result)
    {
        $rows = @pg_numrows($result);
        if ($rows === null) {
            return $this->RaiseError();
        }
        return $rows;
    }

    /** Function RaiseError
     *  Returns an AgataError Object
     */
    function RaiseError()
    {
        return new AgataError(pg_errormessage($this->connection));
    }

    /** Function Properties
     *  Returns the Query Information
     */
    function Properties($result)
    {
        $id = $result;
        if (empty($id))
        {
            return $this->RaiseError();
        }

        $count = @pg_numfields($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = @pg_fieldname ($id, $i);
            $res[$i]['type']  = @pg_fieldtype ($id, $i);
            $res[$i]['len']   = @pg_fieldsize ($id, $i);
        }
        return $res;
    }
}
?>