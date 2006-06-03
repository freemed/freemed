<?php

/** AgataMssql
 *  Agata Driver for Sql Server
 */
class AgataMssql
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $host = $host ? $host : 'localhost';
        
        if ($host && $user && $pass)
        {
            $conn = mssql_connect($host, $user, $pass);
        }
        elseif ($host && $user)
        {
            $conn = mssql_connect($host, $user);
        }
        elseif ($host)
        {
            $conn = mssql_connect($host);
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
            if (!mssql_select_db($database, $conn))
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
        $ret = @mssql_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = mssql_query($query, $this->connection);
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
        return @mssql_fetch_row($result);
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult($result)
    {
        if (is_resource($result))
        {
            return @mssql_free_result($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = @mssql_num_fields($result);
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
        $rows = @mssql_num_rows($result);
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
        return new AgataError(mssql_get_last_message());
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

        $count = @mssql_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            //if sybase/freetds there are no mssql_field_* functions
            if (OS == 'WIN')
            {
                $res[$i]['name']  = @mssql_field_name($id, $i);
                $res[$i]['type']  = @mssql_field_type($id, $i);
                $res[$i]['len']   = @mssql_field_length($id, $i);
            }
            else
            {
                @mssql_field_seek($id, $i);
                $field=@mssql_fetch_field($id);
                $res[$i]['name']  = $field->name;
                $res[$i]['len']   = $field->max_length;
                //data type from INFORMATION_SCHEMA.COLUMN or sysobjectcolumns
                $res[$i]['type']  = $field->numeric;
            }
        }
        return $res;
    }
}
?>