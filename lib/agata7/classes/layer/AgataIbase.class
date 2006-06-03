<?php

/** AgataIbase
 *  Agata Driver for Interbase/Firebird
 */
class AgataIbase
{

    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        $dbhost = $host ?
                  ($host . ':/' . $database) :
                  $database;

        $conn = ibase_connect($dbhost, $user, $pass);
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
        $ret = ibase_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = ibase_query($this->connection, $query);
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
        $ar = ibase_fetch_row($result);
        return $ar;
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult()
    {
        if (is_resource($result)) {
            return ibase_free_result($result);
        }
        return true;
    }

    function FreeQuery($query)
    {
        ibase_free_query($query);
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = ibase_num_fields($result);
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
        return new AgataError(ibase_errmsg());
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

        $count = ibase_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            $info = ibase_field_info($id, $i);
            $res[$i]['name']  = $info['name'];
            $res[$i]['type']  = $info['type'];
            $res[$i]['len']   = $info['length'];
        }
        return $res;
    }
}
?>