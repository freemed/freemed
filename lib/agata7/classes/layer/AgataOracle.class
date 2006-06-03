<?php

/** AgataOci8
 *  Agata Driver for Oracle
 */
class AgataOracle
{
    /** Function Connect
     *  Connects to a Database
     */
    function Connect($host, $database, $user, $pass)
    {
        if ($user && $pass && $host)
        {
            $conn = OCILogon($user, $pass, $host);
        }
        elseif ($user && $pass)
        {
            $conn = OCILogon($user, $pass);
        }
        else
        {
            $conn = false;
        }

        if ($conn == false)
        {
            $error = OCIError();
            return new AgataError($error['message']);
        }
        $this->connection = $conn;
        return true;
    }

    /** Function Disconnect
     *  Disconnects a Database
     */
    function Disconnect()
    {
        $ret = OCILogOff($this->connection);
        $this->connection = null;
        return $ret;
    }

    /** Function Query
     *  Run a Query
     */
    function Query($query)
    {
        $result = OCIParse($this->connection, $query);
        $this->result=$result;
        if ($result)
        {
            $success = OCIExecute($result,OCI_COMMIT_ON_SUCCESS);
            
            if (!$success)
            {
                return $this->RaiseError();
            }
        }
        else
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
        $data = OCIFetchInto($result, $row, OCI_RETURN_NULLS + OCI_RETURN_LOBS);
        if (!$data)
        {
            return NULL;
        }
        return $row;
    }

    /** Function FreeResult
     *  Free the Database result
     */
    function FreeResult($result)
    {
        if (is_resource($result))
        {
            return OCIFreeStatement($result);
        }
        return true;
    }

    /** Function NumCols
     *  Returns the number of columns of a query
     */
    function NumCols($result)
    {
        $cols = OCINumCols($result);
        if (!$cols)
        {
            return $this->RaiseError();
        }
        return $cols;
    }

    /** Function RaiseError
     *  Returns an AgataError Object
     */
    function RaiseError()
    {
        if (is_resource($this->result))
        {
            $error = OCIError($this->result);
        }
        else
        {
            $error = OCIError($this->connection);
        }
        
        if (is_array($error))
        {
            $error = $error['message'];
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

        $count = OCINumCols($id);

        for ($i=0; $i<$count; $i++)
        {
            $res[$i]['name']  = OCIColumnName ($id, $i+1);
            $res[$i]['type']  = OCIColumnType ($id, $i+1);
            $res[$i]['len']   = OCIColumnSize ($id, $i+1);
        }
        return $res;
    }
}
?>