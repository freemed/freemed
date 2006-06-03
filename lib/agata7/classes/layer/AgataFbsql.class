<?php

/** AgataFbsql
 *  Agata Driver for Frontbase
 */
class AgataFbsql
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $host = $host ? $host : 'localhost';
        
        if ($host && $user && $pass)
        {
            $conn = fbsql_connect($host, $user, $pass);
        }
        elseif ($host && $user)
        {
            $conn = fbsql_connect($host, $user);
        }
        elseif ($host)
        {
            $conn = fbsql_connect($host);
        }
        else
        {
            $conn = false;
        }

        if (!$conn)
        {
            return $this->RaiseError();
        }

        if ($database)
        {
            if (!fbsql_select_db($database, $conn))
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
        $ret = fbsql_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = fbsql_query("$query;", $this->connection);
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
        return fbsql_fetch_row($result);
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult($result)
    {
        if (is_resource($result))
        {
            return fbsql_free_result($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = @fbsql_num_fields($result);
        if (!$cols)
        {
            return $this->RaiseError();
        }
        return $cols;
    }

    /** Function NumRows
     *  Returns the number of rows of a query
     */
    function NumRows($result)
    {
        $rows = @fbsql_num_rows($result);
        if ($rows === null)
        {
            return $this->RaiseError();
        }
        return $rows;
    }

    /** Function RaiseError
     *  Returns an AgataError Object
     */
    function RaiseError()
    {
        return new AgataError(fbsql_errno($this->connection));
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

        $count = @fbsql_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = @fbsql_field_name  ($id, $i);
            $res[$i]['type']  = @fbsql_field_type  ($id, $i);
            $res[$i]['len']   = @fbsql_field_len   ($id, $i);
        }
        return $res;
    }
}
?>