<?php 

namespace system\db;

use exception\Error;

class Eobject extends Model 
{

    public static function className() 
    {
        return get_called_class();
    }
    /**
     * 初始化参数
     * 
     */
    public function init() 
    {
        $this->pageLimit = $this->request->getParams['page_limit'] ?? null;
        $this->db = self::$instance->db;
    }
    
    /**
     * 
     * @return \system\Object
     */
    protected function find() 
    {
        $this->_action = 'select';
        $this->_find = true;
        return $this;
    }
    
    /**
     * 
     * @param string $class
     * @param array $on
     * @throws Error
     * @return \system\Object
     */
    public function hasMany(string $class,array $on) 
    {
        if (!class_exists($class)) {
            throw new Error("class [ $class ] doesn't exist");
        }
        $this->_hasOne = false;
        $obj = new $class;
        switch ($this->_action) {
            case 'delete':
                $sql = 'DELETE FROM `'.$obj->dbTable.'` WHERE `'.key($on).'` = ?';
                break;
            case 'select':
                $sql = 'SELECT * FROM '.$obj->dbTable.' WHERE `'.key($on).'` = ?';
                break;
            default:
                throw new Error("Invalid action: ".$this->_action);        
        }
        $this->_stmt = $this->db->prepare($sql);
        $this->_key = current($on);
        return $this;
    }
    
    /**
     * 
     * @param string $class
     * @param array $on
     * @throws Error
     * @return \system\Object
     */
    public function hasOne(string $class,array $on) 
    {
        if (!class_exists($class)) {
            throw new Error("class [ $class ] doesn't exist");
        }
        $obj = new $class;
        $this->_stmt = $this->db->prepare('SELECT * FROM '.$obj->dbTable.' WHERE `'.key($on).'` = ? LIMIT 1');
        $this->_key = current($on);
        return $this;
    }
    
    /**
     * 
     * @param string $objectName
     * @param string $on
     * @throws Error
     * @return \system\Object
     */
    public function with(string $object) 
    {
        $this->_withObj = $object;
        $method = 'get'.ucfirst($object);
        if (!method_exists($this, $method)) {
            throw new Error("method [ $method ] doesn't exist in model ".get_called_class());
        }
        return call_user_func_array([$this, $method], []);
    }
    
    /**
     * 
     * @throws Error
     * @return multitype:unknown
     */
    private function one()
    {
	    if('select' == $this->_action) {   
       	    $this->_query .= ' LIMIT 1';
	    }
        $this->_one = true;
        return $this->get();
    }
    
    /**
     * 
     * 
     * @throws Error
     * @return multitype:unknown
     */
    private function all() 
    {
        if (isset($this->_limit)) {
            $this->_query .= $this->_limit;
        }
        return $this->get();
    }
    
    /**
     * 
     * 
     * @return \system\Object
     */
    protected function update() 
    {
        $this->_action = 'update';
        return $this;
    }
    
    /**
     * 
     * 
     * @param unknown $set
     * @return \system\Object
     */
    public function set($set) 
    {
        if (is_array($set)) {
            $set_str = '';
            foreach ($set as $key => $value) {
                $set_str .= "`$key` = '".addslashes($value)."',";
            }
            $this->_set = rtrim($set_str,',');
        } else {
            
        }
        return $this;
    }
    
    /**
     * 
     * 
     * @param array $datas
     * @return Ambigous <boolean, unknown>
     */
    protected function insert(array $datas) 
    {
        if (empty($datas)) {
            return false;
        }
        if (method_exists($this, 'setTime')) {
            $arr = call_user_func_array([$this,'setTime'], []);
	    $datas = array_merge($datas,$arr);
        }
        $this->_action = 'insert';
        $name = $value = '(';
        foreach ($datas as $key => $data) {
            $name .= " `$key`,";
            $value .= "'".addslashes($data)."',";
        }
        $this->_insert = trim($name,',').' ) VALUE '.trim($value,',').' )';
        $this->_buildquery($this->dbTable, $this->_action);
        return $this->get();
    }
    
    protected function batchInsert(array $datas) 
    {
        if (empty($datas)) {
            return false;
        }
	    $arr = [];
        if (method_exists($this, 'setTime')) {
            $arr = call_user_func_array([$this,'setTime'], []);
        }
        $value_b = '';
        foreach ($datas as $data) {
	    empty($arr) or $data = array_merge($data,$arr);
            $this->_action = 'insert';
            $name = $value = '(';
            foreach ($data as $key => $d) {
                $name .= " `$key`,";
                $value .= "'".addslashes($d)."',";
            }
            $value_b .= trim($value,',').' ),';
        }
        $this->_insert = trim($name,',').') VALUES '.trim($value_b,',');
        $this->_buildquery($this->dbTable, $this->_action);
        return $this->get();
    }
    
    protected function delete()
    {
        $this->_action = 'delete';
        return $this;
    }
    /**
     * 
     * @param string $method
     * @param array $args
     * @throws Error
     * @return mixed
     */
    public function __call($method, $args) 
    {
        if (method_exists($this, $method)) {
            $sql = $this->_buildquery($this->dbTable,$this->_action);
            return call_user_func_array([$this,$method], $args);
        }
    }
    
    /**
     * where
     * 
     * @param string | array $where
     * @return \system\db\Object
     */
    public function where($where) 
    {
        if (!empty($where)) {
            $this->_where = $this->setWhere($where);
        }
        if (isset($this->_key) && isset($where[$this->_key])) {
            $this->_value = $where[$this->_key];
        }
        return $this;
    }
    
    
    /**
     * and where
     * 
     * @param string | array $where
     * @return \system\db\Object
     */
    public function andWhere($where) 
    {
        if (!empty($where)) {
            $this->_where .= $this->setWhere($where, true);
        }
        return $this;
    }
    
    
    /**
     * set where sql
     * 
     * @param string | array $where
     * @return string
     */
    private function setWhere($where, $and = false) 
    {
        $str = $and ? ' AND ' : '';
        if (is_array($where)) {
            //特殊语法
            if (isset($where[0])) {
                switch (strtolower($where[0])) {
                    case 'in':
                        $v = '';
                        foreach ($where[2] as $w) {
                            $v .= "'".$w."',";
                        }
                        $str .= '`'.$where[1].'` IN ('.trim($v,',').')';
                        break;
                    case 'like':
                        $str .= empty($where[2]) ? '' : '`'.$where[1]."` like '%".$where[2]."%'";
                        break;
                    case 'between':
                        if ($where[2][0] == $where[2][1]) {
                            $str .= '`'.$where[1].'` = '.$where[2][0];
                        } else {
                            $str .= '`'.$where[1].'` BETWEEN '.$where[2][0].' AND '.$where[2][1];
                        }
                        break;
                    default:
                        $value = is_numeric($where[2]) ? ' '.$where[2] : " '".$where[2]."'";
                        $str .= "`$this->dbTable`.".$where[1].' '.$where[0].$value;
                }
            } else {
                foreach ($where as $name => $value) {
		    is_numeric($value) or $value = "'".addslashes($value)."'";
                    $str .= "`$this->dbTable`.$name = $value AND ";
                }
                $str = substr($str, 0, -5);
            }
        } else {
            $str .= $where;
        }
        return $str;
    }
    /**
     * 
     * @param string $column
     * @return \system\Object
     */
    public function select (string $column) 
    {
	    $column = trim($column);
        //
        if (false !== strpos($column, ',')) {
            $arr = explode(',', $column);
            $str = '';
            foreach ($arr as $c) {
                if (empty($c)) {
                    continue;
                }
                $str .= $c.',';
            }
            $this->_column = trim($str,',');
                        
        } else {
            $this->_column = $column;
        }
        return $this;
    }
    
    /**
     * 
     */
    public function limit($limit) 
    {
        $this->_limit = " LIMIT $limit";
	    $this->pageLimit = $limit;
        return $this; 
    }
    
    public function table($table) 
    {
        $this->dbTable = $table;
        return $this;
    }
    
    
    /**
     * 
     * @param string $order
     * @return \system\object
     */
    public function orderBy(string $order) 
    {
        $this->_order = $order;
        return $this;
    }
    
    /**
     * 
     * @param string $field
     * @return \system\object
     */
    public function groupBy(String $field) 
    {
        $this->_group = $field;
        return $this;
    }
    /**
     *
     * @param int $page
     * @return multitype:unknown
     */
    private function page($page = 1) 
    {
        $offset = $page == 1 ? 0 : ($page - 1) * $this->pageLimit;
        $this->_query .= ' LIMIT '.$offset.','.$this->pageLimit;
        $res = [
            'data' => $this->get(),
            'count' => $this->count(),
            'limit' => $this->pageLimit
        ];
        return (object)$res;
    }
    /**
     * 
     * @param int $page
     * @return \system\object
     */
    protected function pagination($page) 
    {
        $this->_action = 'select';
        $this->_buildquery($this->dbTable,$this->_action);
        $offset = $page == 1 ? 0 : ($page - 1) * $this->pageLimit + 1;
        $this->_query .= ' LIMIT '.$offset.','.$this->pageLimit;
        $res = [
            'data' => $this->get(),
            'count' => $this->count(),
            'limit' => $this->pageLimit
        ];
        return (object)$res;
    }

    /**
     * 
     * @return \system\object
     */
    protected function count() 
    {
        $this->_action = 'select';
        $this->_one = true;
        $where = empty($this->_where) ? '' : 'WHERE '.$this->_where;
        $data = $this->get("SELECT count(*) as count FROM $this->dbTable $where");
        return current($data);
    }
}
