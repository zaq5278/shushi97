<?php

/**
 * Phalcon Model
 * @author MiaoGang
 */
namespace Plugin\Core;
use Plugin\Misc\ErrorDescriptions as ErrDesc;

if(!defined('_MULTI_RESULTS_')) {define('_MULTI_RESULTS_', 131072);}

class QSTBaseModel extends \Phalcon\Mvc\Model
{
    /**
     * 自动添加表前缀，优先使用指定的表前缀
     * @param $tableName string
     * @param null $tablePrefix string
     */
    public function setSourceWithPrefix($tableName, $tablePrefix = null)
    {
        $prefix = $this->config->database->prefix;
        if (isset($tablePrefix)) {
            $prefix = $tablePrefix;
        }
        $this->setSource($prefix . $tableName);
    }

    /**
     * save to db with exception catch.
     * @return int
     */
    public function logSave()
    {
        try {
            if (false == $this->save()) {
                foreach ($this->getMessages() as $msg) {
                    QSTBaseLogger::getDefault()->log("db error trace ===> " . $msg);
                }
                QSTBaseLogger::getDefault()->log("[" . $this->getSource() . "]" . " save db failed", \Phalcon\Logger::ALERT);
                return 2004;
            }
        } catch (\PDOException $exception) {
            QSTBaseLogger::getDefault()->log("[" . $this->getSource() . "]" . " save db failed, err: " . $exception->getMessage(), \Phalcon\Logger::ALERT);
            return 2004;
        }
        return 0;
    }

    /**
     * @param $array array
     * @return \stdClass
     */
    static public function array2object($array)
    {
        if (!empty($array) && is_array($array)) {
            $obj = new \stdClass();
            foreach ($array as $key => $val) {
                $obj->$key = $val;
            }
        } else {
            $obj = $array;
        }
        return $obj;
    }

    /**
     * @deprecated 该方法仅使用于对象存储为字符串，数字等简单对象场景，是否有问题待测试，强烈建议使用
     * \Phalcon\Mvc\Model提供了built in的toArray()方法。
     * @param $object object
     * @return mixed
     */
    static public function object2array($object)
    {
        if (is_object($object)) {
            foreach ($object as $key => $value) {
                $array[$key] = $value;
            }
        } else {
            $array = $object;
        }
        return $array;
    }


    /**
     * @param $sql string
     * @param $param string
     */
    static private function addsubString(&$sql, $param)
    {
        $sql = $sql . $param;
    }

        /*
        将存储过程返回的结果集和出参的字符串(KEY=VALUE)转换成
        JSON格式数据，如{"status":"0","desc":"\u6210\u529f","out_data":{"id":"33333","appid":"tttt"},"data":[]}
        $record: 为返回结果集
        $result：为输出参字符串
        */
        static private function clt_json_encode($record, $result){
            $resp_arr = array(
                'status'=>$result["@vo_result"],
                'desc'=>ErrDesc::getErrorDesc($result["@vo_result"]),
                'out_data'=>self::str2array($result["@vo_data"],',','='),
                'data'=>$record
                );
            return $resp_arr;
        }

    /*
    将 "key=111,key2=222" 的字符串 转换成数组
    该函数用于执行存储过程输出参数数据转换成数组
    */
    static private function str2array($str, $delimiter1, $delimiter2)
    {
        if (!$str) return '';
        $arr = array();
        $tmp0 = explode($delimiter1, $str);
        foreach ($tmp0 as $key => $value) {
            $tmp1 = explode($delimiter2, $value);
            $arr[$tmp1[0]] = $tmp1[1];
        }
        return $arr;
    }

    /*
    将数组转换成 KEY~VALUE,KEY~VALUE 格式的字符串
    该字符串作为执行存储过程的输入参数
    */
    static private function array2str(&$body, $body_arr){
      
        if (is_array($body_arr)) {
            foreach ($body_arr as $key1 => $value1) {
                $value1 =str_replace("~","",$value1);
                $value1 =str_replace("`","",$value1);
                $body .= $key1 . "~" . $value1 . "`";
            }
            $body = substr($body, 0, -1);
        }
        $body = "'" . $body . "'";
    }

    /*
    执行存储过程
    * body_arr:传入的body数组
    * procedure:存储过程名
    * type:类别，如果非1，代表不单独建立数据库连接 
    * $dbconn:如果type=2，则要传数据库句柄
    返回:数组array(status,desc,data)
    */
    static function execsql($body_arr, $procedure, $type = 1, $dbconn = 0)
    {

        //构建存储过程参数
        $sql = "";
        $body = "";
        self::addsubString($sql, "CALL " . $procedure . "(");
        self::array2str($body, $body_arr);
        self::addsubString($sql, $body);
        self::addsubString($sql, ",@vo_data, @vo_result)");

        tracelog($sql);
        // 执行存储过程
        $Records = array();
        $result = array();
        $ret = true;
        if ($type != 1 && $dbconn) {
            $ret = self::db_query_no_conn($dbconn, $sql, $Records, array("@vo_data", "@vo_result"), $result);
        } else {
            $ret = self::db_query($sql, $Records, array("@vo_data", "@vo_result"), $result);

        }
        $resp = "";
        if ($ret == false) {
            $result['@vo_result'] = '9998';
            $resp = self::clt_json_encode($Records, $result);
            tracelog($resp);
        } else {
            #数组转换{"status":"0","desc":"\u6210\u529f","out_data":{"id":"33333","appid":"tttt"},"data":[]} 格式的JSON 包
            $resp = self::clt_json_encode($Records, $result);
            tracelog($resp);
        }

        return $resp;
    }

    /*
   * 执行存储，不包括数据库的连接，释放
   */
    static private function db_query_no_conn($mysqli, $sql, &$select_result, $out_arg = NULL, &$out_value = NULL)
    {
        $mysqli->query("set names 'utf8'");//输出中文
        $mysqli->autocommit(FALSE);
        $arry = array();
        $result_arr = array();
        if ($mysqli->multi_query($sql)) {
            if ($result = $mysqli->store_result()) {
                while (!is_null($select_result) && $row = $result->fetch_assoc()) {
                    array_push($select_result, $row);
                }
                $result->close();
            }
            while ($mysqli->more_results() && $mysqli->next_result()) {
                $result = $mysqli->store_result();
            }
        } else {
            tracelog("db_query_no_conn:" . __LINE__ . mysqli_error($mysqli) . ifile_name(__FILE__));
            return false;
        }
        $mysqli->commit();
        $num = count($out_arg);
        $i = 0;
        while ($i < $num) {
            $result2 = $mysqli->query("select " . $out_arg[$i] . " ;");
            if ($result2) {
                while ($row = $result2->fetch_assoc()) {
                    $out_value = array_merge($out_value, $row);
                }
                $i++;
                $result2->close();
            } else {
                tracelog("db_query_no_conn:" . __LINE__ . mysqli_error($mysqli) . ifile_name(__FILE__));
                return false;
            }
        }
        return true;
    }


    /* $sql 查询的sql语句，$select_result语句数据的结果集
     * $out_arg 为存储过程的输出参数名
     */

    static private function db_query($sql, &$select_result, $out_arg = NULL, &$out_value = NULL)
    {
        //tracelog("db_query _HOST_:" ._HOST_.":"._PORT_." _USER_ "._USER_.'_PSW_ '._PSW_ ."_DB_ "._DB_);
        $mysqli = new \mysqli(_HOST_ . ":" . _PORT_, _USER_, _PSW_, _DB_);
        if (mysqli_connect_errno()) {
            tracelog("db_query:" . __LINE__ . mb_convert_encoding(\mysqli_connect_error(), 'utf-8', 'gb2312') . \ifile_name(__FILE__));
            return false;
        }
        $mysqli->query("set names 'utf8'");//输出中文
        $mysqli->autocommit(FALSE);
        $arry = array();
        $result_arr = array();
        if ($mysqli->multi_query($sql)) {
            if ($result = $mysqli->store_result()) {
                while (!is_null($select_result) && $row = $result->fetch_assoc()) {
                    array_push($select_result, $row);
                }
                $result->close();
            }
            while ($mysqli->more_results() && $mysqli->next_result()) {
                $result = $mysqli->store_result();
            }
        } else {
            tracelog("db_query:" . __LINE__ . \mysqli_error($mysqli));
            return false;
        }
        $mysqli->commit();
        $num = count($out_arg);
        $i = 0;
        while ($i < $num) {
            $result2 = $mysqli->query("select " . $out_arg[$i] . " ;");
            if ($result2) {
                while ($row = $result2->fetch_assoc()) {
                    $out_value = array_merge($out_value, $row);
                }
                $i++;
                $result2->close();
            } else {
                tracelog("db_query:" . __LINE__ . mysqli_error($mysqli) . ifile_name(__FILE__));
                return false;
            }
        }
        $mysqli->close();
        return true;
    }

    /**
     * PHQL字段名转换
     */
    protected static function toColumn($column, $table = null, $alias = null)
    {
        $columnString = $column;

        if (!empty($table)) {
            $columnString = $table . "." . $columnString;
        }

        if (!empty($alias)) {
            $columnString .= " as " . $alias;
        }

        return $columnString;
    }

    /**
     * PHQL字段数组转字符串语句
     */
    protected static function column2String($columns)
    {
        if (!is_array($columns)) {
            return null;
        }
        return implode(", ", $columns);
    }
}

