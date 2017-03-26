<?php
/**
 * Created by PhpStorm.
 * User: MiaoGang
 * Date: 2016/8/3
 * Time: 11:12
 */

namespace Plugin\Upload;
use Plugin\Upload\Validation\Base;

/**
 * Class Validator
 */
class Validator
{
    // 检测器列表
    private $validations = array();
    // 错误信息
    private $errors;

    /**
     * 执行检测
	 * @param $file
     * @return boolean
     */
    public function validate($file) {
        // Validate is uploaded file
        if ($file->isUploadedFile() === false) {
            $this->errors[] = 'The uploaded file was not sent with a POST request';
        }

        // User validations
        foreach ($this->validations as $validation) {
            if ($validation->validate($file) === false) {
                $this->errors[] = $validation->getMessage();
            }
        }

        return empty($this->errors);
    }

    /**
     * 增加检测器
     * @param $validations
     */
    public function addValidator($validations) {
        if (!is_array($validations)) {
            $validations = array($validations);
        }
        foreach ($validations as $validation) {
            if ($validation instanceof Base) {
                $this->validations[] = $validation;
            }
        }
    }

    /**
     * 获取文件检测错误信息
     * @return array[String]
     */
    public function getErrors() {
        return $this->errors;
    }
}