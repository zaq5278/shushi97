<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/10/19
 * Time: 17:52
 */

namespace Plugin\Core;

use Phalcon\Mvc\User\Component;

class QSTBaseSearch extends Component
{
    // SPK = Search Params Key
    const SPK_LINK = "link";
    const SPK_CONDITIONS = "conditions";
    // SCT = Search Condition Type
    const SCT_INPUT = 0;
    const SCT_SELECT = 1;
    const SCT_DATE = 2;
    const SCT_HIDE = 3;
    const SCT_TREE_SELECTOR = 4;

    private $link;
    private $conditionArray = array();

    public function __construct($link = null) {
        $this->link = $link;
    }

    /**
     * 查询条件转换为SQL查询的WHERE条件
     * @param $modal
     * @return string
     */
    public function toWhere($model) {
        $itemWhere = [];
        foreach ($this->conditionArray as $condition) {
            switch ($condition["type"]) {
                // 输入型
                case QSTBaseSearch::SCT_INPUT:
                    if (!empty($condition["value_default"])) {
                        if (!empty($condition["key_default"])) {
                            $itemWhere[] = $model->condition2Key($condition["key_default"]) . " like '%" . $condition["value_default"] . "%'";
                        } else {
                            $keyWheres = [];
                            foreach ($condition["keys"] as $key=>$value) {
                                $keyWheres[] = $model->condition2Key($key) . " like '%" . $condition["value_default"] . "%'";
                            }
                            $itemWhere[] = "(" . implode(" OR ", $keyWheres) .")";
                        }
                    }
                    break;

                // 条件选择型
                case QSTBaseSearch::SCT_SELECT:
                case QSTBaseSearch::SCT_HIDE:
                    if (!empty($condition["key"]) && !empty($condition["value"])) {
                        $itemWhere[] = $model->condition2Key($condition["key"]) . " like " . $condition["value"];
                    }
                    break;
                    
                // 日期型
                case QSTBaseSearch::SCT_DATE:
                    // TODO 生成日期条件类型的where语句
                    break;
            }
        }

        if (count($itemWhere) == 0) {
            return null;
        }

        $where = "(" . implode(" AND ", $itemWhere) . ")";
        $this->logger->log($where);
        return $where;
    }

    /**
     * 查询条件转换为SQL查询的WHERE条件
     * @param callable $keyConvert 回调函数-用于字段名称转换，常用于连表查询有相同字段（"status" => ""）
     * @param callable $valueConvert 回调函数-用于字段值转换
     * @param string $where 回调函数-where语句子条件
     * @return null|string
     */
    public function toWhereEx($keyConvert = null, $valueConvert = null, $where = null) {
        $itemWhere = [];
        foreach ($this->conditionArray as $condition) {
            switch ($condition["type"]) {
                // 输入型
                case QSTBaseSearch::SCT_INPUT:
                    if (!is_null($condition["value_default"])) {
                        if (!empty($condition["key_default"])) {
                            if ($where && $whereValue = $where($condition["value_default"], $condition["key_default"])) {
                                $itemWhere[] = $whereValue;
                            } else {
                                $value = $valueConvert ? $valueConvert($condition["value_default"]) : $condition["value_default"];
                                $key = $keyConvert ? $keyConvert($condition["key_default"]) : $condition["key_default"];
                                $itemWhere[] = $key . " like '%" . $value . "%'";
                            }
                        } else {
                            $keyWheres = [];
                            foreach ($condition["keys"] as $key=>$value) {
                                if ($where && $whereValue = $where($key, $value)) {
                                    $itemWhere[] = $whereValue;
                                } else {
                                    $value = $valueConvert ? $valueConvert($condition["value_default"] ) : $condition["value_default"] ;
                                    $key = $keyConvert ? $keyConvert($key) : $key;
                                    $keyWheres[] = $key . " like '%" . $value . "%'";
                                    tracelog($keyWheres);
                                }
                            }
                            $itemWhere[] = "(" . implode(" OR ", $keyWheres) .")";
                        }
                    }
                    break;

                // 条件选择型
                case QSTBaseSearch::SCT_SELECT:
                case QSTBaseSearch::SCT_HIDE:
                    if (!is_null($condition["key"]) && !is_null($condition["value"]) && $condition["value"] !== "") {
                        if ($where && $whereValue = $where($condition["key"], $condition["value"])) {
                            $itemWhere[] = $whereValue;
                        } else {
                            $value = $valueConvert ? $valueConvert($condition["value"]) : $condition["value"];
                            $key = $keyConvert ? $keyConvert($condition["key"]) : $condition["key"];
                            $itemWhere[] = $key . " like '%" . $value . "%'";
                        }
                    }
                    break;

                // 日期型
                case QSTBaseSearch::SCT_DATE:
                    // TODO 生成日期条件类型的where语句
                    break;
            }
        }

        if (count($itemWhere) == 0) {
            return null;
        }

        $where = "(" . implode(" AND ", $itemWhere) . ")";
        tracelog($where);
        return $where;
    }
    
    /**
     * @param $condition
     * 增加一个查询条件
     */
    public function addCondition($condition) {
        array_push($this->conditionArray, $condition);
    }

    public function setLink($link) {
        $this->link = $link;
    }

    /**
     * @return array
     * 转换成数组形式，用于模板文件中使用
     */
    public function toArray() {
        $page_search[QSTBaseSearch::SPK_LINK] = $this->link;
        $page_search[QSTBaseSearch::SPK_CONDITIONS] = $this->conditionArray;
        return $page_search;
    }
}

