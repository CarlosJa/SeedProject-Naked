<?php

class Database extends PDO
{


    public function __construct($DB_TYPE, $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS)
    {
        parent::__construct($DB_TYPE.':host='.$DB_HOST.';dbname='.$DB_NAME.';charset=utf8' , $DB_USER, $DB_PASS);
        parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_LAZY);  // Fetch_lazy - handles (num, assoc & object) without memory overhead
        parent::setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
        parent::setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, "SET NAME'utf8'");
        parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        parent::setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


    }

    /**
     * select
     * @param string $sql An SQL string
     * @param array $array Paramters to bind
     * @param constant $fetchMode A PDO Fetch mode
     * @return mixed
     */
    public function select($sql, $array = array(), $fetchMode = PDO::FETCH_ASSOC)
    {
        try {
            $sth = $this->prepare($sql);
            foreach ($array as $key => $value) {
                $sth->bindValue("$key", $value);
            }

            $sth->execute();
            return $sth->fetchAll($fetchMode);

        } catch (PDOException $e) {
            $Response['Code']='0';
            $Response['Message']= 'Error Select Entry: ' . $e->getMessage();


            echo json_encode($Response);
            return false;
        }


    }

    /**
     * insert
     * @param string $table A name of table to insert into
     * @param string $data An associative array
     */
    public function insert($table, $data)
    {
        ksort($data);

        $fieldNames = implode('`, `', array_keys($data));
        $fieldValues = ':' . implode(', :', array_keys($data));


        try {
            $sth = $this->prepare("INSERT INTO $table (`$fieldNames`) VALUES ($fieldValues)");


            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }

            $sth->execute();
            $msg = $sth->errorInfo();


            if(!$msg[1]) { $msgCode = 1; } else { $msgCode = $msg[1]; }
            if(!$msg[2]) { $msgMessage = 'Created'; } else { $msgMessage = $msg[2]; }

            $Response['Code']=$msgCode;
            $Response['Message']=$msgMessage;
            $Response['ID']= $this->lastInsertId();

        } catch (PDOException $e) {
            $Response['Code']='0';
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
    public function update($table, $data, $where)
    {
        ksort($data);

        $fieldDetails = NULL;
        foreach($data as $key=> $value) {
            $fieldDetails .= "`$key`=:$key,";
        }
        $fieldDetails = rtrim($fieldDetails, ',');

        try {

            $sth = $this->prepare("UPDATE $table SET $fieldDetails WHERE $where");

            foreach ($data as $key => $value) {
                $sth->bindValue(":$key", $value);
            }


            $sth->execute();
            $count = $sth->rowCount();
            if($count){
                $Response['Code']='1';
                $Response['Rows']= $count;
                $Response['Message']='Updated';
            } else {
                $Response['Code']='0';
                $Response['Rows']= $count;
                $Response['Message']='No Records Updated';
            }


        } catch (PDOException $e) {
            $Response['Code']='0';
            $Response['Message']= 'Error Updating Database: ' . $e->getMessage();


        }

        return json_encode($Response);
    }


    /**
     * delete
     *
     * @param string $table
     * @param string $where
     * @param integer $limit
     * @return integer Affected Rows
     */
    public function delete($table, $where, $limit = 1)
    {
        $sth = $this->prepare("DELETE FROM $table WHERE $where LIMIT $limit");
        return $sth->execute();
    }


    /**
     * Query
     *
     * @param string $sql
     * @return returns array results
     */
    public function query($sql, $limit='') {
//	echo $sql;
//	print_array($limit);
        if($limit) {
            $LimitStart = $limit['start'];
            $LimitEnd = $limit['end'];
        }

        $sth = $this->prepare($sql);
        if($LimitStart) $sth->bindParam(1, $LimitStart,PDO::PARAM_INT);
        if($LimitEnd) $sth->bindParam(2, $LimitEnd,PDO::PARAM_INT);

        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Query
     *
     * @param string $table
     * @param array $arrQuery
     * @return returns array results
     * $
     */
    public function wherein($table, $column, $arrQuery, $CustomSQL='') {
        $SQLWhereIn = implode(',', $arrQuery);
        if($CustomSQL) {
            $CustomSQL = " AND " . $CustomSQL;
        }
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM {$table} WHERE {$column} IN ({$SQLWhereIn}) $CustomSQL";
        // print_array($sql);
        $sth = $this->prepare($sql);
        $sth->execute();

        $count = $this->prepare('SELECT FOUND_ROWS() as Rows');
        $count->execute();
        $Counted = $count->fetchAll(PDO::FETCH_ASSOC);
        $Counted = $Counted[0]['Rows'];

        $Result['results'] = $sth->fetchAll(PDO::FETCH_ASSOC);
        $Result['rowsfound'] = $Counted;
        return $Result;

    }





    /**
     * select
     * @param string $sql An SQL string
     * @param array $array Paramters to bind
     * @param constant $fetchMode A PDO Fetch mode
     * @return mixed

    public function retrieve($table, $sql, $fetchMode = PDO::FETCH_ASSOC)
    {

    $sql = "SELECT * FROM business_profile WHERE 1 LIMIT 0, 100";
    print_array($sql);
    $sth = $this->prepare($sql);
    $sth->execute();

    $statement  = $this->query('SELECT FOUND_ROWS() AS CountedRows');
    print_array($statement);
    $Counted = $Counted[0]['CountedRows'];

    $Result['results'] = $sth->fetchAll(PDO::FETCH_ASSOC);
    $Result['rowsfound'] = $Counted;
    return $Result;
    //		        return $sth->fetchAll($fetchMode);

    }
     */





    /**
     * select
     * @param string $sql An SQL string
     * @param array $array Paramters to bind
     * @param constant $fetchMode A PDO Fetch mode
     * @return mixed
     */
    public function retrieve($sql, $array = array(), $pagination = '')
    {

        try {
            $sth = $this->prepare($sql);
            foreach ($array as $key => $value) {
                $sth->bindValue("$key", $value);
            }

            $sth->execute();

            $statement  = $this->query('SELECT FOUND_ROWS() AS CountedRows');
            $Counted = $statement[0]['CountedRows'];

            $Result['results'] = $sth->fetchAll(PDO::FETCH_ASSOC);
            $Result['rowsfound'] = $Counted;

            return $Result;
//		        return $sth->fetchAll($fetchMode);

        } catch (PDOException $e) {
            $Result['Code']='0';
            $Result['Message']= 'Error Select Entry: ' . $e->getMessage();

            return json_encode($Result);
        }
    }


    public function pagination($sql, $page='0', $limit='10')
    {
        try {
            $find = array("select * from", "LIMIT ?,?");
            $NewSQL = str_ireplace($find, '', $sql);
            $totalSQL = "SELECT count(*) as Counted FROM {$NewSQL}";
            $statement  = $this->query($totalSQL);
            $Counted = $statement[0]['Counted'];

            if(!$page) { $page = '0'; }
            if(!$limit) { $limit = 10; }
            $TotalPages = floor($Counted / $limit);
            if($page) { $page_first_result = ($page) * $limit; } else { $page_first_result = '0'; }


            $sth = $this->prepare($sql);
            $sth->execute([$page_first_result, $limit]);
            // echo "{$page_first_result}, $limit";



            $Result['results'] = $sth->fetchAll(PDO::FETCH_ASSOC);
            $Result['total'] = $Counted;
            $Result['limit'] = $limit;
            $Result['page'] = $page;
            $Result['pages'] = $TotalPages;

            return $Result;

        } catch (PDOException $e) {
            $Result['Code']='0';
            $Result['Message']= 'Error Select Entry: ' . $e->getMessage();

            return json_encode($Result);
        }
    }



}