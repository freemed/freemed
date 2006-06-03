<?php

/** AgataIfx
 *  Agata Driver for Informix
 */
class AgataIfx
{

    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $host     = $host ? '@' . $host : '';
        $database = $database ? $database . $host : '';
        $user     = $user ? $user : '';
        $pass     = $pass ? $pass : '';
        
        $conn = ifx_connect($database, $user, $pass);
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
        $ret = @ifx_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        //$result = ifx_query($query, $this->connection, IFX_SCROLL);
        echo $query."\n";
        $result = ifx_query($query, $this->connection);
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
        $ar = ifx_fetch_row($result);
        return $ar;
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult()
    {
        if (is_resource($result))
        {
            return ifx_free_result($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = ifx_num_fields($result);
        if (!$cols) {
            return $this->RaiseError();
        }
        return $cols;
    }

    /** Function RaiseError
     *  Returns an AgataError Object
     */
    function RaiseError()
    {
        return new AgataError(ifx_errormsg());
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

        $count = ifx_num_fields($id);
        $types = ifx_fieldtypes($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = key($types);
            $res[$i]['type']  = $types[$fname];
        }
        return $res;
    }
}
?>