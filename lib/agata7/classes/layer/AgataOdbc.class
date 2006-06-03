<?php

/** AgataOdbc
 *  Agata Driver for ODBC
 */
class AgataOdbc
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $host = $host ? $host : 'localhost';
        $conn = odbc_connect($host, $user, $pass);
        
        if (!$conn)
        {
            return $this->RaiseError();
        }
        $this->connection = $conn;
        
        return true;
    }

    /** Function Disconnect
     *  Disconnects a Database
     */
    function Disconnect()
    {
        $ret = @odbc_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = odbc_exec($this->connection, $query);
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
        $cols = odbc_fetch_into($result, $row);
        return $row;
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult()
    {
        if (is_resource($result)) {
            return odbc_free_result($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = odbc_num_fields($result);
        if ($cols == -1)
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
        $rows = odbc_num_rows($result);
        if ($rows === -1)
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
        if (!isset($this->connection) || !is_resource($this->connection))
        {
            $error = odbc_errormsg();
        }
        else
        {
            $error = odbc_errormsg($this->connection);
        }
        
        return new AgataError($error);
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

        $count = odbc_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = @odbc_field_name  ($id, $i +1);
        }
        return $res;
    }
}
?>