<?php

namespace Maleeby\MRest\Libs;

class DB {

    private $_pdo = null;
    /**
     *
     * @var \PDOStatement
     */
    private $_stmt = null;
    private $_params = [];

    public function __construct($con) {
        $config = \MRest\MRest::getConfig()['databases'];
        if ($con instanceof \PDO) {
            $pdoObj = $con;
        } elseif (is_array($con)) {
            $pdoData = $con;
        } elseif (is_string($con) && isset($config[$con])) {
            $pdoData = $config[$con];
        } else {
            throw new \Exception('Invalid database connection [' . $con . ']', 500);
        }
        if (isset($pdoData) && !isset($pdoObj)) {
            $pdoObj = new \PDO($pdoData['driver'] . ':host=' . $pdoData['dbhost'] . ';dbname=' . $pdoData['dbname'], $pdoData['dbuser'], $pdoData['dbpass'], isset($pdoData['pdoOptions']) ? $pdoData['pdoOptions'] : []);
        }
        $this->_pdo = $pdoObj;
        $this->_pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        return $this;
    }
    
    public function prepare($query, $params = [], $pdoOptions = []) {
        $this->_stmt = $this->_pdo->prepare($query, $pdoOptions);
        $this->_params = $params;
        
        return $this;
    }
    
    public function execute($params = []) {
        if(count($params)) {
            $this->_params = $params;
        }
        $this->_stmt->execute($this->_params);
        
        return $this;
    }
    
    public function query($query, $params = [], $pdoOptions = []) {
        return $this->prepare($query, $params, $pdoOptions)->execute();
    }
    
    public function fetchAssoc() {
        return $this->_stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function fetchRowAssoc() {
        return $this->_stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    public function fetchColumn($column) { //@TODO
        return $this->_stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
    }
    
    public function lastId() {
        return $this->_pdo->lastInsertId();
    }
    
    public function numRows() {
        $this->_stmt->rowCount();
    }
    
    public function getStmt() {
        return $this->_stmt;
    }
    
    public function getPdo() {
        return $this->_pdo;
    }

}
