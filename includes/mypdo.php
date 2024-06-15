<?php

class MyPDO
{
    protected static $_instance = null;
    protected $dbName = '';
    protected $dsn;
    protected $dbh;

    /**
     * 构造
     *
     * @return MyPDO
     */
    public function __construct($dbHost, $dbUser, $dbPasswd, $dbName, $port)
    {
        try {
            $this->dbh = new mysqli($dbHost, $dbUser, $dbPasswd, $dbName, $port);
            mysqli_set_charset($this->dbh, 'utf8');
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 防止克隆
     *
     */
    private function __clone()
    {
    }

    /**
     * Singleton instance
     *
     * @return Object
     */
    public static function getInstance($dbHost, $dbUser, $dbPasswd, $dbName, $port)
    {
        if (self::$_instance === null) {
            self::$_instance = new self($dbHost, $dbUser, $dbPasswd, $dbName, $port);
        }
        return self::$_instance;
    }

    /**
     * Query 查询
     *
     * @param String $strSql SQL语句
     * @param String $queryMode 查询方式(All or Row)
     * @param Boolean $debug
     * @return Array
     */
    public function query($strSql, $queryMode = 'All', $debug = false)
    {
        //echo $strSql;
        $recordset = $this->dbh->query($strSql);

        if ($recordset) {
            if ($queryMode == 'All') {
                $result = $recordset->fetch_assoc();
            } else {
                $result = $recordset->fetch_assoc();
            }
            return $result;
        } else {
            $this->getPDOError();
        }
    }

    /**
     * update使用预处理更新数据
     */
    public function update($table, $arr, $where, $debug = false)
    {
        $arr1 = array();
        $str = '';
        foreach ($arr as $k => $v) {
            $arr1[':' . $k] = $v;
            $str .= $str == '' ? "`{$k}`='{$v}'" : ",`{$k}`='{$v}'";
        }
        $sql = "update `{$table}` set {$str} where {$where}";
        if ($debug) {
            echo $sql;
        }
        $stm = $this->dbh->query($sql);
        if (!$stm) {
            $this->dbh->rollback();
            return false;
        }
        return true;
    }

    /**
     * insert预处理插入数据
     * @param string $table
     * @param array $arr
     * @param boolean $debug
     * @return boolean $rs 返回true or false
     */
    public function insert($table, $arr, $debug = false)
    {
        $arr1 = array();
        $str = '';
        foreach ($arr as $k => $v) {
            $arr1[':' . $k] = $v;
            $str .= $str == '' ? "`{$k}`=:{$k}" : ",`{$k}`=:{$k}";
        }
        $sql = "insert into `{$table}`(`" . implode('`,`', array_keys($arr)) . "`) values('" . implode("','", array_values($arr)) . "')";
        if ($debug) {
            echo $sql;
        }
        $stm = $this->dbh->query($sql);
        if (!$stm) {
            $this->dbh->rollback();
            return false;
        }
        return true;
    }

    /**
     * delete 删除数据
     * @param string $table
     * @param string $where 如'`id`=:id'
     * @param array $arr 如array(':id'=>690)
     * @param boolean $debug
     * @return boolean $rs
     */
    public function delete($table, $where, $debug = false)
    {
        $sql = "delete from `{$table}` where {$where}";

        if ($debug) {
            echo $sql;
        }
        $stm = $this->dbh->query($sql);
        //echo $stm;
        if ($stm) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * fetchAll 获取所有符合条件的数据
     * @param string $table
     * @param string $where
     * @param array $arr
     * @param string $fields
     * @param boolean $debug
     * @return array $rows 返回数组
     */
    public function fetchAll($table, $where, $fields = null, $debug = false)
    {
        $fields = $fields ? $fields : '*';
        $sql = "select {$fields} from `{$table}` where {$where}";

        if ($debug) {
            echo $sql;
        }
        $stm = $this->dbh->query($sql);
        if ($stm && $stm->num_rows > 0) {
            $row = $stm->fetch_all(MYSQLI_ASSOC);
            return $row;
        } else {
            return false;
        }
    }

    /**
     * fetch 返回符合条件的一条数据
     * @param string $table 查询表
     * @param string $where 查询条件，预处理
     * @param array $arr 查询条件数据
     * @param string $fields 查询的某些字段
     * @param unknown_type $debug 是否显示sql语句
     * @return array $row 返回数组
     */
    public function fetch($table, $where, $fields = null, $debug = false)
    {
        $fields = $fields ? $fields : '*';
        $sql = "select {$fields} from `{$table}` where {$where}";
        if ($debug) {
            echo $sql;
        }
        $stm = $this->dbh->query($sql);
        if ($stm && $stm->num_rows > 0) {
            $row = $stm->fetch_assoc();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * destruct 关闭数据库连接
     */
    public function __destruct()
    {
        $this->dbh = null;
    }
}

?>