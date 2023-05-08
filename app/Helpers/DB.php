<?php

// namespace StaticPdo;

class Db
{

    private static $_pdoObject = null;

    protected static $_fetchMode = \PDO::FETCH_ASSOC;
    protected static $_connectionStr = null;
    protected static $_driverOptions = array();

    private static $_username = null;
    private static $_password = null;

    /**
     * Set connection information
     *
     * @example    Db::setConnectionInfo('basecamp','dbuser', 'password', 'mysql', 'http://mysql.abcd.com');
     */
    public static function setConnectionInfo($driver = 'mysql', $dbname = null, $username = null, $password = null, $hostname = 'localhost')
    {
        if($driver == 'mysql') {
            self::$_connectionStr = "mysql:dbname=$dbname;host=$hostname";
            self::$_username      = $username;
            self::$_password      = $password;
        } else if($driver == 'sqlite'){
            // For sqlite, $schema is the file path
            self::$_connectionStr = "sqlite:$dbname";
        }

        // Making the connection blank
        // Will connect with provided info on next query execution
        self::$_pdoObject = null;
    }




    /**
     * Execute a statement and returns the first row
     *
     * @param   string  $sql    SQL statement
     * @param   array   $params A single value or an array of values
     * @return  array   A result row
     */
    public static function getRow($sql, $params = array())
    {
        $statement = self::_query($sql, $params);
        return $statement->fetch(self::$_fetchMode);
    }


    /**
     * Execute a statement and returns the first row
     *
     * @param   string  $sql    SQL statement
     * @param   array   $params A single value or an array of values
     * @return  array   A result row
     */
    public static function selectselect($sql, $params = array())
    {
        $statement = self::_query($sql, $params);
        return $statement->fetchAll(self::$_fetchMode);
    }


    public static function insert($table, $data)
    {
        if(self::$_pdoObject == null) {
            self::_connect();
        }

        ksort($data);

        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));


        try {
            $sth = self::$_pdoObject->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");


            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            $sth->execute();
            $msg = $sth->errorInfo();


            if(!$msg[1]) { $msgCode = 1; } else { $msgCode = $msg[1]; }
            if(!$msg[2]) { $msgMessage = 'Created'; } else { $msgMessage = $msg[2]; }

            $Response['Code']=$msgCode;
            $Response['Message']=$msgMessage;
            $Response['ID']= self::$_pdoObject->lastInsertId();

        } catch (PDOException $e) {
            $Response['Code']=0;
            $Response['Message']= 'Error Creating Entry: ' . $e->getMessage();
        }

        return json_encode($Response);
    }


    /**
     * update
     * @param string $table A name of table to insert into
     * @param string $data An associative array
     * @param string $where the WHERE query part
     */
    public static function update($table, $data, $where)
    {
        if(self::$_pdoObject == null) {
            self::_connect();
        }

        ksort($data);

        $fieldDetails = NULL;
        foreach($data as $key=> $value) {
            $fieldDetails .= "`$key`=:$key,";
        }
        $fieldDetails = rtrim($fieldDetails, ',');

        try {

            $sth = self::$_pdoObject->prepare("UPDATE $table SET $fieldDetails WHERE $where");

            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }


            $sth->execute();
            $count = $sth->rowCount();
            if($count){
                $Response['Code']=1;
                $Response['Rows']= $count;
                $Response['Message']='Updated';
            } else {
                $Response['Code']=0;
                $Response['Rows']= $count;
                $Response['Message']='No Records Updated';
            }

        } catch (PDOException $e) {
            $ErrorCode = $e->errorInfo[1];
            $Response['Code']=$ErrorCode;
            $Response['Message']= 'Error Updating Database: ' . $e->getMessage();
        }

        return json_encode($Response);
    }


    /**
     * Execute a statement and returns number of effected rows
     *
     * Should be used for query which doesn't return resultset
     *
     * @param   string  $sql    SQL statement
     * @param   array   $params A single value or an array of values
     * @return  integer number of effected rows
     */
    public static function execute($sql, $params = array())
    {
        $statement = self::_query($sql, $params);
        return $statement->rowCount();
    }

    /**
     * Execute a statement and returns a single value
     *
     * @param   string  $sql    SQL statement
     * @param   array   $params A single value or an array of values
     * @return  mixed
     */
    public static function getValue($sql, $params = array()) {
        $statement = self::_query($sql, $params);
        return $statement->fetchColumn(0);
    }



    /**
     * select
     * @param string $sql An SQL string
     * @param array $array Paramters to bind
     * @param constant $fetchMode A PDO Fetch mode
     * @return mixed
     */
    public static function getResultsPagination($sql, $array = array()) {
        if(self::$_pdoObject == null) {
            self::_connect();
        }
        try {
            $sth = self::$_pdoObject->prepare($sql);
            foreach ($array as $key => $value) {
                $sth->bindValue("$key", $value);
            }

            $sth->execute();
            $RowCount = $sth->rowCount();

//            $statement = self::execute($sql);
//            \Debug::print_array($statement);

            $Result['results'] = $sth->fetchAll(PDO::FETCH_ASSOC);
            $Result['rowsfound'] = $RowCount;

        } catch (PDOException $e) {
            $Result['Code']=0;
            $Result['Message']= 'Error Select Entry: ' . $e->getMessage();

            return json_encode($Result);
        }

        return $Result;


    }

    /**
     * Execute a statement and returns row(s) as 2D array
     *
     * @param   string  $sql    SQL statement
     * @param   array   $params A single value or an array of values
     * @return  array   Result rows
     */
    public static function getResult($sql, $params = array()) {
        $statement = self::_query($sql, $params);
        return $statement->fetchAll(self::$_fetchMode);
    }

    public static function getLastInsertId($sequenceName = "") {
        return self::$_pdoObject->lastInsertId($sequenceName);
    }

    public static function setFetchMode($fetchMode) {
        self::_connect();
        self::$_fetchMode = $fetchMode;
    }

    public static function getPDOObject() {
        self::_connect();
        return self::$_pdoObject;
    }

    public static function beginTransaction() {
        self::_connect();
        self::$_pdoObject->beginTransaction();
    }

    public static function commitTransaction() {
        self::$_pdoObject->commit();
    }

    public static function rollbackTransaction() {
        self::$_pdoObject->rollBack();
    }

    public static function setDriverOptions(array $options) {
        self::$_driverOptions = $options;
    }

    private static function _connect() {
        if(self::$_pdoObject != null){
            return;
        }

        if(self::$_connectionStr == null) {
            throw new \PDOException('Connection information is empty. Use Db::setConnectionInfo to set them.');
        }

        self::$_pdoObject = new \PDO(self::$_connectionStr, self::$_username, self::$_password, self::$_driverOptions);
    }

    /**
     * Prepare and returns a PDOStatement
     *
     * @param   string  $sql SQL statement
     * @param   array   $params Parameters. A single value or an array of values
     * @return  PDOStatement
     */
    private static function _query($sql, $params = array())
    {
        if(self::$_pdoObject == null) {
            self::_connect();
        }

        $statement = self::$_pdoObject->prepare($sql, self::$_driverOptions);

        if (! $statement) {
            $errorInfo = self::$_pdoObject->errorInfo();
            throw new \PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }

        $paramsConverted = (is_array($params) ? ($params) : (array ($params )));

        if ((! $statement->execute($paramsConverted)) || ($statement->errorCode() != '00000')) {
            $errorInfo = $statement->errorInfo();
            throw new \PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }

        return $statement;
    }
}