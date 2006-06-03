<?php

/** AgataSqlite
 *  Agata Driver for Sqlite
 */
class AgataSqlite
{

    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        if ($database)
        {
            if (!file_exists($database))
            {
                return new AgataError(_a('File Not Found'));
            }
        }
        else
        {
            return new AgataError(_a('File Error'));
        }

        $connect_function = $persistent ? 'sqlite_popen' : 'sqlite_open';
        if (!($conn = sqlite_open($database)))
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
        $ret = sqlite_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = sqlite_query($query, $this->connection);
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
        $ar = sqlite_fetch_array($result, SQLITE_NUM);
        return $ar;
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult(&$result)
    {
        if (!is_resource($result)) {
            return false;
        }
        $result = null;
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = sqlite_num_fields($result);
        if (!$cols) {
            return $this->sqliteRaiseError();
        }
        return $cols;
    }

    /** Function NumRows
     *  Returns the number of rows of a query
     */
    function NumRows($result)
    {
        $rows = sqlite_num_rows($result);
        if (!is_integer($rows)) {
            return $this->raiseError();
        }
        return $rows;
    }

    /** Function RaiseError
     *  Returns an AgataError Object
     */
    function RaiseError()
    {
        return new AgataError(sqlite_last_error($this->connection));
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

        $count = sqlite_num_fields($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = sqlite_field_name ($id, $i);
        }
        return $res;
    }
}
?>