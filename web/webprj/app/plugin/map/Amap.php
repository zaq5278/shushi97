<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/9/7
 * Time: 14:05
 */

namespace Plugin\Map;


/**
 * Class Amap 高德地图云存储接口
 * @package Plugin\Map
 */
class Amap
{
    private $config;
    private $_key, $_tableId, $_privKey;
    function __construct($key, $privKey = null){
        if(!isset($key)){
            throw new \Phalcon\Exception("Amap app key is required");
        }
        $this->_key = $key;
        $this->_privKey = $privKey;
    }

    public function table($tableId){
        if(!isset($tableId)){
            throw new \Phalcon\Exception("Amap associate with table tableId is required");
        }
        $this->_tableId = $tableId;
        return $this;
    }

    public function getTableID(){
        return $this->_tableId;
    }

    /**
     * 创建高德云存储记录
     * @param $data, 用户数据必须包含"_name"字段，参照http://lbs.amap.com/yuntu/reference/cloudstorage/#t2
     * @param int $loctype 1 坐标， 2 地址
     * @return array|mixed
     */
    public function createData($data, $loctype = 1){
        $postParams = Array(
            'data'=>json_encode($data),
            'key'=>$this->_key,
            'loctype'=>$loctype,
            'tableid'=>$this->_tableId
        );
        $postData = $this->getDataString($postParams);
        return $this->sendRequest("http://yuntuapi.amap.com/datamanage/data/create", $postData);
    }

    /**
     * @param $data array 更新的数据，必须包含_id字段
     * @param int $loctype 1 坐标， 2 地址
     * @return array|mixed
     */
    public function updateData($data , $loctype = 1){
        $postParams = Array(
            'data'=>json_encode($data),
            'key'=>$this->_key,
            'loctype'=>$loctype,
            'tableid'=>$this->_tableId
        );
        $postData = $this->getDataString($postParams);
        return $this->sendRequest("http://yuntuapi.amap.com/datamanage/data/update", $postData);
    }

    /**
     * 获取带签名data字符串，注 测试尚有问题，后续解决
     * @param $dataArray
     * @return string
     */
    private function getSignData($dataArray){
        ksort($dataArray);
        $data = $this->getDataString($dataArray);
        return $data."&sig=".md5($data.$this->_privKey);
    }

    /**
     * 获取data字符串
     * @param $dataArray
     * @return string
     */
    private function getDataString($dataArray){
        $index = 0;
        $data = "";
        foreach ($dataArray as $key => $value){
            if(0 == $index){
                $data = $key."=".$value;
            }else{
                $data = $data."&".$key."=".$value;
            }
            $index++;
        }
        return $data;
    }

    private function sendRequest($url, $data){
        $ch = curl_init($url);
        if(false == $ch){
            return array(
                "status"=>-2,
                "infocode"=>curl_errno($ch),
                "info"=>curl_error($ch)
            );
        }
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/x-www-form-urlencoded"));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($ch);
        if(false == $resp){
            $resp = array(
                "status"=>-1,
                "infocode"=>curl_errno($ch),
                "info"=>curl_error($ch)
            );
            return $resp;
        }
        return json_decode($resp, true);
    }
}