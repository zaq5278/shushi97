<?php
/**
 * Created by PhpStorm.
 * User: caohailiang
 * Date: 2016/8/31
 * Time: 16:37
 */

namespace Plugin\Misc;

use Phalcon\Exception;

class FileManager
{
    static public function createGuid($namespace = '') {
        static $guid = '';
        $uid = uniqid("", true);
        $data = $namespace;
        $data .= $_SERVER['REQUEST_TIME'];
        $data .= $_SERVER['HTTP_USER_AGENT'];
        /*$data .= $_SERVER['LOCAL_ADDR'];
        $data .= $_SERVER['LOCAL_PORT'];*/
        $data .= $_SERVER['REMOTE_ADDR'];
        $data .= $_SERVER['REMOTE_PORT'];
        $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
        $guid = /*'{' .*/
            substr($hash,  0,  8) .
            substr($hash,  8,  4) .
            substr($hash, 12,  4) .
            substr($hash, 16,  4) .
            substr($hash, 20, 12) /*.
            '}'*/;
        return $guid;
    }

    /**
     * @param $file \Phalcon\Http\Request\File
     * @param $savePath string
     * @return int|array
     */
    public function saveFile($file, $savePath){
        if(!isset($savePath) || !isset($file)){
            return -1;
        }
        if(!file_exists($savePath)){
            mkdir($savePath, 0755, true);
        }
        $mimeType = $file->getRealType();
        $type = 'unknown';
        if (preg_match("#^(\w+)/(\w+)$#", $mimeType, $matches)) {
            $type = $matches[1];
            $format = $matches[2];
        }
        $fileName = FileManager::createGuid($file->getName());
        if(isset($format)){
            $fileName .= ".".$format;
        }
        try{
            if(false == $file->moveTo($savePath.$fileName)){
                return -2;
            }
        }catch (Exception $e){
            return -3;
        }
        //return $fileName;
        return array(
            'type' => $type,
            'name' => $fileName,
            'key' => $file->getKey()
        );
    }

    /**
     * @param $request \Phalcon\Http\Request
     * @param $savePath string
     * @param $groupByType bool
     * @return int|array
     */
    public function saveFilesFromRequest($request, $savePath, $groupByType = false)
    {
        if(!isset($savePath) || !isset($request)){
            return -1;
        }
        $fileList = array();
        foreach ($request->getUploadedFiles() as $file/**\Phalcon\Http\Request\File*/) {
            $result = $this->saveFile($file, $savePath);
            if(is_array($result)){
                if(false == $groupByType){
                    array_push($fileList, $result['name']);
                }else{
                    $type = $result['type'];
                    if(!isset($fileList[$type])){
                        $fileList[$type] = array();
                    }
                    $fileList[$type][$result['key']] = $result['name'];
                }
            }
        }
        return $fileList;
    }

    public function deleteFiles($list, $savePath, $groupByType = false)
    {
        if(false == $groupByType){
            foreach ($list as $name){
                if(file_exists($savePath.$name)){
                    unlink($savePath.$name);
                }
            }
        }else{
            foreach ($list as $type => $subList){
                foreach ($subList as $key => $name){
                    if(file_exists($savePath.$name)){
                        unlink($savePath.$name);
                    }
                }
            }
        }
    }
}