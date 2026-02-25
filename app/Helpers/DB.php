<?php

class Db
{
    private static $_connections = [];
    private static $_activeConnection = null;

    protected static $_fetchMode = \PDO::FETCH_ASSOC;
    protected static $_driverOptions = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ];

    /**
     * Set connection information
     *
     * @example Db::setConnectionInfo('mysql', 'dbname', 'username', 'password', 'hostname', 'connectionName');
     */
    public static function setConnectionInfo(
        $driver,
        $dbname,
        $username,
        $password,
        $hostname = 'localhost',
        $connectionName = 'default'
    ) {
        self::$_connections[$connectionName] = [
            'driver' => $driver,
            'connectionString' => "{$driver}:dbname={$dbname};host={$hostname}",
            'username' => $username,
            'password' => $password,
        ];

        if (self::$_activeConnection === null) {
            self::$_activeConnection = $connectionName;
        }
    }

    /**
     * Switch to a different connection
     */
    public static function useConnection($connectionName)
    {
        if (!isset(self::$_connections[$connectionName])) {
            throw new \Exception("Connection '{$connectionName}' does not exist.");
        }
        self::$_activeConnection = $connectionName;
    }

    /**
     * Get the current PDO object
     */
    public static function getPDOObject()
    {
        return self::_getConnection();
    }

    /**
     * Execute a statement and returns the first row
     */
    public static function getRow($sql, $params = [])
    {
        $statement = self::_query($sql, $params);
        return $statement->fetch(self::$_fetchMode);
    }

    /**
     * Execute a statement and returns all rows
     */
    public static function select($sql, $params = [])
    {
        $statement = self::_query($sql, $params);
        return $statement->fetchAll(self::$_fetchMode);
    }


    /**
     * Insert a new row into the database
     */
    public static function insert($table, $data)
    {
        $pdo = self::_getConnection();

        ksort($data);
        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));

        try {
            $sth = $pdo->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");

            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            $sth->execute();

            return json_encode([
                'Code' => 1,
                'Message' => 'Created',
                'ID' => $pdo->lastInsertId()
            ]);
        } catch (\PDOException $e) {
            return json_encode([
                'Code' => 0,
                'Message' => 'Error Creating Entry: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update existing rows in the database
     */
    public static function update($table, $data, $where)
    {
        $pdo = self::_getConnection();

        ksort($data);
        $fieldDetails = implode(', ', array_map(function($key) {
            return "`$key` = :$key";
        }, array_keys($data)));

        try {
            $sth = $pdo->prepare("UPDATE $table SET $fieldDetails WHERE $where");

            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            $sth->execute();
            $count = $sth->rowCount();

            return json_encode([
                'Code' => ($count > 0) ? 1 : 0,
                'Rows' => $count,
                'Message' => ($count > 0) ? 'Updated' : 'No Records Updated'
            ]);
        } catch (\PDOException $e) {
            return json_encode([
                'Code' => $e->errorInfo[1],
                'Message' => 'Error Updating Database: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute a statement and returns number of affected rows
     */
    public static function execute($sql, $params = [])
    {
        $statement = self::_query($sql, $params);
        return $statement->rowCount();
    }



    /**
     * Execute a statement and returns a single value
     */
    public static function getValue($sql, $params = [])
    {
        $statement = self::_query($sql, $params);
        return $statement->fetchColumn(0);
    }

    /**
     * Set PDO fetch mode
     */
    public static function setFetchMode($fetchMode)
    {
        self::$_fetchMode = $fetchMode;
    }

    /**
     * Begin a transaction
     */
    public static function beginTransaction()
    {
        self::_getConnection()->beginTransaction();
    }

    /**
     * Commit a transaction
     */
    public static function commitTransaction()
    {
        self::_getConnection()->commit();
    }

    /**
     * Rollback a transaction
     */
    public static function rollbackTransaction()
    {
        self::_getConnection()->rollBack();
    }

    /**
     * Set PDO driver options
     */
    public static function setDriverOptions(array $options)
    {
        self::$_driverOptions = $options;
    }

    /**
     * Get or create the PDO connection
     */
    private static function _getConnection()
    {
        $connectionInfo = self::$_connections[self::$_activeConnection];

        if (!isset($connectionInfo['pdo'])) {
            $connectionInfo['pdo'] = new \PDO(
                $connectionInfo['connectionString'],
                $connectionInfo['username'],
                $connectionInfo['password'],
                self::$_driverOptions
            );
            self::$_connections[self::$_activeConnection] = $connectionInfo;
        }

        return $connectionInfo['pdo'];
    }

    /**
     * Prepare and execute a PDO statement
     */
    private static function _query($sql, $params = [])
    {
        $pdo = self::_getConnection();
        $statement = $pdo->prepare($sql);

        if (!$statement) {
            $errorInfo = $pdo->errorInfo();
            throw new \PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }

        if (!$statement->execute($params) || $statement->errorCode() != '00000') {
            $errorInfo = $statement->errorInfo();
            throw new \PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }

        return $statement;
    }

    /**
     * Delete rows from the database
     */
    public static function delete($table, $where)
    {
        $pdo = self::_getConnection();

        // Build the WHERE clause dynamically
        $whereConditions = implode(' AND ', array_map(function($key) {
            return "`$key` = :$key";
        }, array_keys($where)));

        try {
            $sth = $pdo->prepare("DELETE FROM $table WHERE $whereConditions");

            // Bind the parameters from the where array
            foreach ($where as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            $sth->execute();
            $count = $sth->rowCount();

            return json_encode([
                'Code' => ($count > 0) ? 1 : 0,
                'Rows' => $count,
                'Message' => ($count > 0) ? 'Deleted' : 'No Records Deleted'
            ]);
        } catch (\PDOException $e) {
            return json_encode([
                'Code' => $e->errorInfo[1],
                'Message' => 'Error Deleting From Database: ' . $e->getMessage()
            ]);
        }
    }

    public static function batchInsert($table, array $data)
    {
        if (empty($data)) {
            return json_encode([
                'Code' => 0,
                'Message' => 'No data provided for insertion'
            ]);
        }

        $pdo = self::_getConnection();

        // Assume all rows have the same structure as the first row
        $firstRow = reset($data);
        $columns = array_keys($firstRow);
        $columnString = '`' . implode('`, `', $columns) . '`';

        // Create placeholders for each row
        $rowPlaceholder = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
        $valuePlaceholders = implode(', ', array_fill(0, count($data), $rowPlaceholder));

        $sql = "INSERT INTO $table ($columnString) VALUES $valuePlaceholders";

        try {
            $stmt = $pdo->prepare($sql);

            // Flatten the data array and bind values
            $values = [];
            foreach ($data as $row) {
                foreach ($columns as $column) {
                    $values[] = $row[$column];
                }
            }

            $stmt->execute($values);

            $insertedCount = $stmt->rowCount();

            return json_encode([
                'Code' => 1,
                'Message' => 'Batch insert successful',
                'InsertedRows' => $insertedCount
            ]);
        } catch (\PDOException $e) {
            return json_encode([
                'Code' => 0,
                'Message' => 'Error performing batch insert: ' . $e->getMessage()
            ]);
        }
    }
}