<?php
/**
 * 格式化输入变量内容并停止脚本运行
 * 
 */
function dd() {
    $args = func_get_args();
    echo '<pre>';
    foreach ($args as $arg) {
        output_var($arg);
    }
    exit();
}
function d() {
    $args = func_get_args();
    echo '<pre>';
    foreach ($args as $arg) {
        output_var($arg);
    }
}
function output_var($arg,$nbsp='') {
    $type = strtolower(gettype($arg));
    //$nbsp .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
    $nbsp .= '    ';
    switch ($type) {
        case 'string':echo "string(".strlen($arg).")<font color='#66DD00'>\"".htmlentities($arg)."\"</font>";break;
        case 'integer':echo "int(<font color=\"#E63F00\">$arg</font>)";break;
        case 'boolean':
            if ($arg) {
                $b = 'true';
                $c = '#33CCFF';
            }else {
                $b = 'false';
                $c = '#E63F00';
            }
            echo "bool(<font color=\"$c\">$b</font>)";
            break;
        case 'double':
            echo "float(<font color='#E63F00'>$arg</font>)";
            break;    
        case 'array':
            echo 'Array('.count($arg).') {<br>';
            foreach ($arg as $k => $v) {
                if (is_numeric($k)) {
                    echo "{$nbsp}[$k] => ";
                }else {
                    echo "{$nbsp}[\"$k\"] => ";
                }
                output_var($v,$nbsp);
            }
            echo "$nbsp}";
            break;
        case 'null':
            echo 'NULL';    
        default:
            print_r($arg);
            break;
    }
    echo '<br>';
}