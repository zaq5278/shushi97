<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/3
 * Time: 11:12
 */

namespace Plugin\Upload;

use Phalcon\Di;
use Phalcon\Db\Adapter\Pdo\Sqlite;
use Phalcon\Db\Column;
use App\Plugin\Upload\Validator;
/**
 * Class UploadFile
 * @package App\Plugin\Upload
 * 1. 支持根路径配置，路径结构-年/月/日，路径不存在自动创建
 * 2. 文件重命名
 * 3. 待清理文件管理，增删查(遍历)
 * 4. 文件检测
 */
class Upload
{
    private $validator;
    // baseUri
    private $uri_path;
    // root_path
    private $root_path = "./";
    // 上传图片根目录-相对于根目录的相对目录
    private $base_path = "./";
    // 待清理文件列表数据库(sqlite)
    private $db_fl;
    // 库名称
    private $sdb_name = "fl.db";
    // 表名称
    private $table_image = "qst_image_unused";
    // 表字段名称
    private $column_path = "path_name";
    private $column_uripath = "uri_path";
    private $column_create_time = "create_time";

    public function __construct($rootPath = null, $basePath = null) {
        $this->root_path = isset($rootPath) ? $rootPath : "./";
        $this->base_path = isset($basePath) ? $basePath : "./";

        $config = array(
            "dbname" => $this->root_path . $this->base_path . $this->sdb_name
        );
        // open db
        $this->db_fl = new Sqlite($config);

        // create table
        if (!$this->db_fl->tableExists($this->table_image)) {
            $this->db_fl->createTable(
                $this->table_image,
                null,
                array(
                    "columns" => array(
                        new Column(
                            "id",
                            array(
                                "type" => Column::TYPE_INTEGER,
                                "size" => 10,
                                "notNull" => true,
                                "autoIncrement" => true,
                                "primary" => true
                            )
                        ),
                        new Column(
                            $this->column_path,
                            array(
                                "type" => Column::TYPE_TEXT,
                                "size" => 256,
                                "unique" => true,
                                "notNull" => true
                            )
                        ),
                        new Column(
                            $this->column_uripath,
                            array(
                                "type" => Column::TYPE_TEXT,
                                "size" => 256,
                                "unique" => true,
                                "notNull" => true
                            )
                        ),
                        new Column(
                            $this->column_create_time,
                            array(
                                "type" => Column::TYPE_DATETIME,
                                "notNull" => true
                            )
                        )
                    )
                )
            );
        }
    }

    public function __destruct() {
        // TODO: Implement __destruct() method.
        $this->db_fl->close();
    }

    public function setUriPath($baseUri) {
        $this->uri_path = $baseUri;
    }

    /**
     * @param $file
     * @param $unused
     * @return string
     * 保存文件，返回
     */
    public function upload($file, $unused = true) {
        // 获取文件路径
        $path = $this->createPath();
        // 生成新的文件名（带路径）
        $ext = $file->getExtension();
        $url_path = "";
      
       
        if(empty($ext) && $file->getRealType() == 'image/png'){
            $ext ='png';
        }
        if(empty($ext) && $file->getRealType() == 'image/jpg'){
              $ext ='jpg';
        }
        
        if (!empty($ext)) {
            $url_path = $path . self::create_unique() . '.' .$ext;
        } else {
            $url_path = $path . self::create_unique();
        }
        
        $path = $this->root_path . $url_path;
        $this->log('上传文件成功, 文件名:' . $file->getName()
            . " 文件类型:" . $file->getRealType()
            . " 文件大小:" . $file->getSize()
            . " 新文件:" . $path
            . " URL:" . $url_path
            ." ext" .$ext);


        // 文件转存到指定路径
        $file->moveTo($path);
        // 将文件加入在待清理文件列表中
        if ($unused) {
            $this->cleanListAdd($url_path, $path);
        }
        //tracelog("-------------" . Di::getDefault()->get('url')->get($path));
        return $this->uri_path . $url_path;
    }

     public function bs64upload($src){
        $path = $this->createPath();
        // 生成新的文件名（带路径）
       if (preg_match("#^data:image/(\w+);base64,(.*)$#", $src, $matches)) {
            $base64 = $matches[2];
            $type = $matches[1];
            if ($type === 'jpeg') {
                $type = 'jpg';
            }
            tracelog("bs64upload------------$type" );
            $url = $path . self::create_unique() . '.' .$type;
            $filePath = $this->root_path.$url;

            tracelog("bs64upload------------filePath:" .$filePath);            
            $data = base64_decode($base64);
            file_put_contents($filePath, $data);
            tracelog("bs64upload------------return :" .$this->uri_path . $url);
            return $this->uri_path . $url;
        }
        return "";
     }
    
    /**
     * 将文件从待清理列表中移除
     * @param $filename
     */
    public function cleanListRemove($uriPath) {
        $this->db_fl->delete(
            $this->table_image,
            ":column = :value",
            array(
                "column" => $this->column_uripath,
                "value" => $uriPath
            )
        );
    }

    /**
     * 将文件加入到待清理列表中
     * @param $filename 文件名
     */
    public function cleanListAdd($uriPath, $filename) {
        $this->db_fl->insert(
            $this->table_image,
            array(
                $filename,
                $uriPath,
                time()
            ),
            array(
                $this->column_path,
                $this->column_uripath,
                $this->column_create_time
            )
        );
    }

    public function deleteFile($fileUri) {
        $result = @unlink($fileUri);
    }

    /**
     * 清理记录的无用文件
     */
    public function doCleanup() {
        $images = $this->db_fl->fetchAll('SELECT * FROM ' . $this->table_image);
        foreach ($images as $image) {
            if (isset($image[$this->column_path])) {
                $result = @unlink($image[$this->column_path]);
                $this->log("删除文件" . $image[$this->column_path] . ($result ? "成功" : "失败"));
            }
        }
        $this->db_fl->delete($this->table_image);
    }

    /**
     * 获取文件目录，指定根目录+已日期年月日为子目录
     * @return string
     */
    private function createPath()
    {
        $datePath = $this->base_path . date('Y/m/d', time());
        $path = $this->root_path . $datePath;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        return $datePath . "/";
    }

    /**
     * 输入日志
     * @param $content 日志内容，string/array
     * @param $level 日志级别
     */
    private function log($content, $level=\Phalcon\Logger::DEBUG) {
        Di::getDefault()->getShared("logger")->log($content, $level);
    }

    public function getValidator() {
        return $this->validator;
    }

    /**
     * 生成唯一标识
     * @return mixed
     */
    static public function create_unique() {
        $data = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] .time() . rand();
        return sha1($data);
    }
}
