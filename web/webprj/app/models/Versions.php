<?php
/**
 * Created by PhpStorm.
 * User: dodo
 * Date: 2016/9/2
 * Time: 23:14
 */

namespace App\Models;

use Plugin\Core\QSTBaseModel;

class Versions extends QSTBaseModel
{
    static public function checkVersion($oldVersion) {
        $oldVersionValue = self::version2Value($oldVersion);

        $version = self::findFirst(["order" => "id DESC"]);
        if (!$version) {
            return false;
        }
        $newVersionValue = self::version2Value($version->number);

        if ($newVersionValue <= $oldVersionValue) {
            return false;
        }

        return $version;
    }

    /**
     * 版本文本串转换为版本值
     * 版本号格式要求:  x.x.x
     * @param $versionString
     */
    static private function version2Value($versionString) {
        $versionArray = explode(".", $versionString);
        $versionValue = 0;
        for ($i = 0; $i < 3; $i++) {
            $versionValue += $versionArray[$i] * pow(10, 3 - $i);
        }
        return $versionValue;
    }

    public function beforeValidationOnCreate() {
        $this->time = date("Y-m-d H:i:s", time());
    }
}
