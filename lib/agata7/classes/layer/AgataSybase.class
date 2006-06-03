<?php

/** AgataSybase
 *  Agata Driver for Sybase
 */
class AgataSybase
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $host = $host ? $host : 'localhost';
        
        if ($host && $user && $pass)
        {
            $conn = sybase_connect($host, $user, $pass);
        }
        elseif ($host && $user)
        {
            $conn = sybase_connect($host, $user);
        }
        elseif ($host)
        {
            $conn = sybase_connect($host);
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
            if (!sybase_select_db($database, $conn))
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
        $ret = @sybase_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = sybase_query($query, $this->connection);
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
        return @sybase_fetch_row($result);
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult($result)
    {
        if (is_resource($result))
        {
            return @sybase_free_result($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = @sybase_num_fields($result);
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
        $rows = @sybase_num_rows($result);
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
        return new AgataError(sybase_get_last_message());
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

        $count = @sybase_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            @sybase_field_seek($id, $i);
            $field=@sybase_fetch_field($id);
            $res[$i]['name']  = $field->name;
        }
        return $res;
    }
}
?>