<?php 

namespace system\db;

use system\Dispatcher;
use exception\Error;
use system\Config;

class Model extends Dispatcher 
{
    protected static $instance;
    public $pageLimit = 50;
    protected $db;
    protected $_find = false;
    protected $object;
    protected $_query;
    protected $_action;
    protected $_where;
    protected $_limit;
    protected $_column;
    protected $_table;
    protected $_order;
    protected $_with;
    protected $_stmt;
    protected $_key;
    protected $_set;
    protected $_insert;
    protected $_withObj;
    protected $_withPage = false;
    protected $_one = false;
    protected $_hasOne = true;
    protected $_value;
    protected $_group;
    
    /**
     * 数据模型，初始化数据库链接
     * 
     * @return \system\Model
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            $conf = Config::get('db.mysql');
            self::$instance->db = @new \mysqli($conf['host'], $conf['username'], $conf['password'], $conf['db'], $conf['port']);
            if (self::$instance->db->connect_errno) {
                Error::fatal("Mysql connect failed: ".self::$instance->db->connect_errno);
            }
            self::$instance->db->set_charset($conf['charset']);
        }
        return new static();
    }
    
    
    /**
     * build sql
     * 
     * @param string $table
     * @param string $action
     * @throws Error
     */
    protected function _buildquery(string $table,string $action) {
        switch ($action) {
            case 'select':
                $column = empty($this->_column) ? '*' : $this->_column;
                $this->_query = "SELECT $column FROM `".$table.'`';
                empty($this->_with)  or $this->_query .= $this->_with;
                empty($this->_where) or $this->_query .= ' WHERE '.$this->_where;
                empty($this->_order) or $this->_query .= ' ORDER BY '.$this->_order;
                empty($this->_group) or $this->_query .= ' GROUP BY `'.$this->_group.'`';
                break;
                
            case 'update':
                $this->_query = "UPDATE `$table`".' SET '.$this->_set.' WHERE '.$this->_where;
                break;
            case 'insert':
                $this->_query = "INSERT INTO `$table` ".$this->_insert; 
                break;
            case 'delete':
                $this->_query = "DELETE FROM `$table` WHERE ".$this->_where;
                break;       
            default:
                Error::fatal('Invalid method : '.$action);
        }
    }
    
    
    /**
     * exec sql
     * 
     * @param string $sql
     * @throws Error
     * @return Ambigous <NULL, unknown, multitype:>|boolean
     */
    protected function get($sql = '') {
        $sql = empty($sql) ? $this->_query : $sql;
        if (true === Config::get('debug')) {
            $sqls = [];
            if (!isset($this->request->getSqls)) {
                $sqls = [$sql];                
            } else {
                $sqls = array_merge($this->request->getSqls,[$sql]);
            }
            $this->request->getSqls = $sqls;
        }
        $result = $this->db->query($sql);
        if ($result === false) {
            return $this->db->error;
        }
        switch ($this->_action) {
            case 'select':
                if (!$result) {
                    Error::fatal("sql语句［ $sql ］查询失败");
                }
                $data = [];
                $i = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[$i] = $row;
                    if (isset($this->_stmt) && isset($this->_key)) {
                        $this->_stmt->bind_param("s",$row[$this->_key]);
                        $this->_stmt->execute();
                        $data[$i][$this->_withObj] = $this->_dynamicBindResults($this->_stmt);
                    }
                    $i++;
                }
                return $this->_one ? (empty($data) ? null : $data[0]) : $data;
            case 'insert':
                return $this->db->insert_id;
            case 'update':
                return $this->db->affected_rows == 0 ? false : true;
            case 'delete':
                if (isset($this->_stmt) && isset($this->_value)) {
                    $this->_stmt->bind_param("s",$this->_value);
                    $this->_stmt->execute();
                }
                return $this->db->affected_rows == 0 ? false : true;
            default:
                Error::fatal();
        }
    }
    
    
    /**
     * This helper method takes care of prepared statements' "bind_result method
     * , when the number of variables to pass is unknown.
     *
     * @param mysqli_stmt $stmt Equal to the prepared statement object.
     *
     * @return array The results of the SQL fetch.
     */
    protected function _dynamicBindResults(\mysqli_stmt $stmt) {
        $meta = $stmt->result_metadata();
        if(!$meta && $stmt->sqlstate) {
            return [];
        }
        $parameters = $results = $row = [];
        
        while ($field = $meta->fetch_field()) {
            $row[$field->name] = null;
            $parameters[] = & $row[$field->name];
        }
        // avoid out of memory bug in php 5.2 and 5.3
        // https://github.com/joshcam/PHP-MySQLi-Database-Class/pull/119
        if (version_compare (phpversion(), '5.4', '<')) {
            $stmt->store_result();
        }
        call_user_func_array(array($stmt, 'bind_result'), $parameters);

        $this->totalCount = 0;
        $this->count = 0;
        while ($stmt->fetch()) {
            $x = array();
            foreach ($row as $key => $val) {
            $x[$key] = $val;
            }
            $this->count++;
            array_push($results, $x);
        }
        return empty($results) ? [] : ($this->_hasOne ? $results[0] : $results);
        // stored procedures sometimes can return more then 1 resultset
        if ($this->_mysqli->more_results())
            $this->_mysqli->next_result();

        if (in_array ('SQL_CALC_FOUND_ROWS', $this->_queryOptions)) {
        $stmt = $this->_mysqli->query ('SELECT FOUND_ROWS()');
        $totalCount = $stmt->fetch_row();
        $this->totalCount = $totalCount[0];

        }
        return $results;
    }
}
