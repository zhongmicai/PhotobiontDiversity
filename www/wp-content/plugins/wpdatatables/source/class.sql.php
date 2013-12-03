<?php

/**
 * MySQL abstract layer for the phpDataTables module
 * 
 * @author cjbug@ya.ru
 * @since 10.10.2012
 *
 * */
class PDTSql {

    private $dbhost;
    private $dbname;
    private $dbuser;
    private $dbpass;
    private $link;
    private $sqllog;
    private $query;
    private $result;
    private $error;
    private $key;

    /**
     * Constructor
     * @param string $sqlhost
     * @param string $sqldbname
     * @param string $sqluser
     * @param string $sqlpassword 
     */
    function __construct($sqlhost, $sqldbname, $sqluser, $sqlpassword) {
        $this->dbhost = (((string) $sqlhost)) ? $sqlhost : '';
        $this->dbname = (((string) $sqldbname)) ? $sqldbname : '';
        $this->dbuser = (((string) $sqluser)) ? $sqluser : '';
        $this->dbpass = (((string) $sqlpassword)) ? $sqlpassword : '';
        $this->sqlConnect();
    }

    /**
     * Initializes the connection to the database
     * @return boolean 
     */
    function sqlConnect() {
        $this->link = @mysqli_connect($this->dbhost, $this->dbuser, $this->dbpass, $this->dbname);
        if (!$this->link) {
            die(mysqli_connect_error());
        } else {
            $result = mysqli_select_db($this->link, $this->dbname);
            mysqli_query($this->link, 'SET character_set_client="utf8",character_set_connection="utf8",character_set_results="utf8"; ');
            if (!$result) {
                die(mysqli_error($this->link));
            }
        }
        return true;
    }

    /**
     * Close the DB connection
     * @return boolean 
     */
    function sqlClose() {
        mysqli_close();
        return true;
    }

    /**
     * Set the group key
     * @param string $key 
     */
    function setGroupKey($key) {
        $this->key = $key;
    }

    /**
     * Clear the group key 
     */
    function dropGroupKey() {
        $this->key = '';
    }

    /**
     * Do a query without expected result (insert, update, delete)
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function doQuery() {
        if ($result = $this->prepare(func_get_args())) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Get a single field value from query result
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean Get
     */
    function getField() {
        if ($result = $this->prepare(func_get_args())) {
            $row = mysqli_fetch_row($result);
            return $row[0];
        } else {
            return false;
        }
    }

    /**
     * Get a single row from query result
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function getRow() {
        if ($result = $this->prepare(func_get_args())) {
            $row = mysqli_fetch_assoc($result);
            @mysqli_free_result($result);
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Get all results of a query as an indexed array
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function getArray() {
        if ($result = $this->prepare(func_get_args())) {
            while ($row = mysqli_fetch_array($result))
                $tmp[] = $row;
            @mysqli_free_result($result);
            return $tmp;
        } else {
            return false;
        }
    }

    /** 
     * Get all results of a query as an assoc array
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function getAssoc() {
        if ($result = $this->prepare(func_get_args())) {
            while ($row = mysqli_fetch_assoc($result))
                $tmp[] = $row;
            @mysqli_free_result($result);
            return $tmp;
        } else {
            return false;
        }
    }

    /**
     * Get the results of a query as an assoc array
     * grouped by a provided key
     * @param $key a key by which we group the result
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function getAssocGroups() {
        $params = func_get_args();
        $key = $params[0];
        array_shift($params);
        if ($result = $this->prepare($params)) {
            while ($row = mysqli_fetch_assoc($result))
                $tmp[($row[$key])][] = $row;
            @mysqli_free_result($result);
            return $tmp;
        } else {
            return false;
        }
    }

    /**
     * Get the results of a query sorted by a provided key
     * @param $key a key by which we group the result
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function getAssocByKey() {
        $params = func_get_args();
        $key = $params[0];
        array_shift($params);
        if ($result = $this->prepare($params)) {
            while ($row = mysqli_fetch_assoc($result)) {
                $tmp[($row[$key])] = $row;
            }
            @mysqli_free_result($result);
            return $tmp;
        } else {
            return false;
        }
    }

    /**
     * Get the results of a query as pairs (id/val)
     * @param $query
     * @param parameters - a single array, or all values
     * separated by comma
     * @return boolean 
     */
    function getPairs() {
        if ($result = $this->prepare(func_get_args())) {
            while (@$row = mysqli_fetch_row($result))
                $tmp[strval($row[0])] = $row[1];
            @mysqli_free_result($result);
            return $tmp;
        } else {
            return false;
        }
    }


    /**
     * Prepares the query and the parameters passed 
     */
    function prepare($params) {
        $q = $params[0];
        unset($params[0]);
        $q = preg_replace('/\?/', 'x?x', $q);
        if (count($params) > 1) {
            foreach ($params as $p) {
                $p = '\'' . mysqli_real_escape_string($this->link, $p) . '\'';
                $q = preg_replace('/x\?x/', $p, $q, 1);
            }
        }elseif( (count($params) == 1) && (is_array($params[1])) ){
            foreach ($params[1] as $p) {
                $p = '\'' . mysqli_real_escape_string($this->link, $p) . '\'';
                $q = preg_replace('/x\?x/', $p, $q, 1);
            }
        }
        $this->query = $q;
        $this->error = '';

        $result = mysqli_query($this->link, $this->query);

        if (mysqli_error($this->link))
            die(mysqli_error($this->link));

        while (mysqli_next_result($this->link))
            mysqli_store_result($this->link);

        if (@mysqli_num_rows($result)) {
            $row = mysqli_fetch_assoc($result);
            if (isset($row['error'])) {
                $this->error = $row['error'];
                return false;
            } else {
                mysqli_data_seek($result, 0);
                return $result;
            }
        } else {
            return false;
        }
    }

}

?>