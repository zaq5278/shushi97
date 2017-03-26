<?php
namespace Plugin\Core;
use Phalcon\Di;
use Phalcon\Logger\Adapter\File as LogFile;
use Phalcon\Logger\Formatter\Line as LineFormatter;

/**
 * 日志类
 * Class QSTBaseLogger
 * @package Plugin\Core
 */
class QSTBaseLogger
{
    /**
     * @var \Phalcon\Logger\Adapter\File, 日志适配器
     */
    private $_logger;
    /**
     * @var string，日志存放路径
     */
    private $_logpath;
    /**
     * @var string，日志命名空间，用于日志文件命名
     */
    private $_namespace;

    /**
     * QSTBaseLogger constructor.
     * @param mixed $dir，日志路径
     * @param string $namespace，日志命名空间
     */
    public function __construct($dir = null, $namespace = 'app')
    {
        $this->_logger = null;
        $this->_logpath = empty($dir) ? "" : $dir . "/";
        $this->_namespace = $namespace;
    }

    /**
     * 日志记录
     * @param $array array
     * @return string
     */
    public function Array2Json_chn($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $jsonw = stripslashes(json_encode($array));
        $jsonw = urldecode($jsonw);
        return  $jsonw;
    }

    /**
     * @param $array
     * @param $function
     * @param bool $apply_to_keys_also
     */
    private function arrayRecursive(&$array, $function, $apply_to_keys_also = false) {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die_err("0997");//请注意 die方法将无法正常从该函数中返回，从而导致调用者无法得知错误原因
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

    /**
     * @deprecated 复合格式的序列化是复杂过程，使用该对象的人不应该通过变更扩充基类来实现对不同复杂类型的序列化支持，不
     * 建议传入复合类型的log参数，请自行序列化为string类型，该方法将在后续版本中被移除。
     * @param $log
     * @param int $level
     */
    public function logComplex($log, $level = \Phalcon\Logger::DEBUG)
    {
        $type = "string";
        if (is_object($log)) {
            $log = QSTBaseModel::object2array($log);//该方法仅支持深度为1的对象转为字符串
            $type = "object";
        }

        if (is_array($log)) {
            $log = $this->Array2Json_chn($log);
            $type = "array";
        }
        if (!is_string($log)) {
            $log = "格式不正确，无法打印！";
            $type = "undefined";
        }

        $log = "(type:" . $type . ")" . $log ;
        $this->_logger->log($log, $level);
    }

    /**
     * 检测并创建日志adapter
     * @since 2017.11.22 将该操作由构造函数一直日志记录时执行，优化由于依赖注入导致的，无日志行为的请求的操作。
     */
    private function createLoggerOnce()
    {
        if(null == $this->_logger){ //日志adapter未被创建，创建adapter
            if (!is_dir($this->_logpath)) {
                mkdir($this->_logpath, 0755, true);
            }
            $fileName = $this->_logpath . '/'. $this->_namespace.'.log';
            if(file_exists($fileName)){//检测日志是否超过10M或发生日期变更
                if(filesize($fileName) > 10 * 1024 * 1024 || date('Y-m-d') != date('Y-m-d', fileatime($fileName))){
                    rename($fileName, $fileName.'-'.date('Ymd_His', fileatime($fileName)));
                }
            }
            $this->_logger = new LogFile($fileName);
            $format = new LineFormatter("[%date%][%type%] %message%");
            $format->setDateFormat('Y-m-d G:i:s');
            $this->_logger->setFormatter($format);
        }else{//日志adapter已被创建，不执行任何操作吗，为方便问题定位，单次请求过程，无论是否超过文件限制或日期更换，不做日志文件切换

        }
    }
    /**
     * @param $log mixed, 日志，强烈建议传入类型为string的log参数，后续其他类型参数将不再支持，请尽量按照参数类型，自行序列化
     * @param $level integer
     * @todo 允许业务层传入未知格式的日志是有风险，不可控的接口定义，希望在后续过程中去掉复合类型入参支持。
     */
    public function log($log, $level = \Phalcon\Logger::DEBUG)
    {
        if (LOG_OFF == '0') {
            return;
        }
        $this->createLoggerOnce();
        if(is_string($log)){
            $this->_logger->log($log, $level);
        }else{
            $this->logComplex($log, $level);
        }
    }

    /**
     * @author caohl
     * @deprecated, 由于\Plugin\Core\QSTBaseLogger使用并非为单例模式，该接口使用存在较大歧义，强烈建议使用getDefault来
     * 替换该接口，接口未被移出仅为兼容旧代码。
     * @return mixed
     */
    public static function getInstance()
    {
        return self::getDefault();
    }

    /**
     * @author caohl
     * 获取模块注入的logger实例
     * @return mixed, 如果当前模块的logger组件注入，返回 \Plugin\Core\QSTBaseLogger实例
     */
    public static function getDefault()
    {
        return Di::getDefault()->get('logger');
    }
}

