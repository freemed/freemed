<?php

/** AgataMysql
 *  Agata Driver for Mysql
 */
class AgataMysql
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        if ($host && $user && $pass)
        {
            $conn = mysql_connect($host, $user, $pass);
        }
        elseif ($host && $user)
        {
            $conn = mysql_connect($host, $user);
        }
        elseif ($host)
        {
            $conn = mysql_connect($host);
        }
        else
        {
            $conn = false;
        }
        
        if (!$conn)
        {
            return new AgataError(mysql_error());
        }

        if ($database)
        {
            if (!mysql_select_db($database, $conn))
            {
                return $this->RaiseError();
            }
        }
        
        $this->connection = $conn;
        
        return true;
    }

    /** Function Disconnect
     *  Disconnects a Database
     */
    function Disconnect()
    {
        $ret = mysql_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = mysql_query($query, $this->connection);
        if (!$result)
        {
            return $this->RaiseError();
        }
        return $result;
    }

    /** Function FetchRow
     *  Fetch a Row and returns as an array.
     */
    function FetchRow($result)
    {
        $ar = mysql_fetch_row($result);
        return $ar;
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult()
    {
        if (is_resource($result)) {
            return mysql_free_result($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = mysql_num_fields($result);
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
        $rows = mysql_num_rows($result);
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
        return new AgataError(mysql_error($this->connection));
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

        $count = mysql_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = @mysql_field_name  ($id, $i);
            $res[$i]['type']  = @mysql_field_type  ($id, $i);
            $res[$i]['len']   = @mysql_field_len   ($id, $i);
        }
        return $res;
    }
}
?>