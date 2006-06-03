<?php

/** AgataDbase
 *  Agata Driver for Dbase files
 */
class AgataDbase
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

        ob_start();
        $conn  = dbase_open($database, 0);
        $error = ob_get_contents();
        ob_end_clean();
        if (!$conn)
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
        $ret = dbase_close($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $this->row_num = 1;
        $query = strtoupper($query);
        $pieces = explode('FROM ', $query, 2);
        if (count($pieces>1))
        {
            $select = substr($pieces[0],6); // removes SELECT
            $pieces = MyExplode($select, false, true);
            if ($pieces[1] != '*')
            {
                foreach ($pieces as $piece)
                {
                    $tmp = explode('.', $piece);
                    $fields[] = $tmp[1];
                }
                $this->fields = $fields;
                //$this->fields_= $pieces;
            }
        }
        return true;
    }

    /** Function FetchRow
     *  Fetch a Row and returns as an array.
     */
    function FetchRow($result)
    {
        if (!$this->row_num)
        {
            $this->row_num = 1;
        }
        
        $row_num = $this->row_num;
        $ar = @dbase_get_record_with_names($this->connection, $row_num);
        if ($ar)
        {
            foreach ($ar as $key=>$content)
            {
                if (in_array($key, $this->fields))
                {
                    $ret[] = $content;
                }
            }
        }
        
        $this->row_num ++;
        return $ret;
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
        //$cols = dbase_numfields($this->connection);
        //return $cols;
        return count($this->Properties($result));
    }

    /** Function NumRows
     *  Returns the number of rows of a query
     */
    function NumRows($result)
    {
        return dbase_numrecords($this->connection);
    }

    /** Function RaiseError
     *  Returns an AgataError Object
     */
    function RaiseError()
    {
        return new AgataError('');
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

        $count = dbase_numfields($this->connection);
        $ret = array_keys(dbase_get_record_with_names($this->connection, 1));
        $i = 0;
        foreach ($ret as $field)
        {
            if ($this->fields)
            {
                if (in_array($field, $this->fields))
                {
                    $res[$i]['name'] = $field;
                    $i ++;
                }
            }
            else
            {
                $res[$i]['name'] = $field;
                $i ++;
            }
        }
        return $res;
    }
}
?>