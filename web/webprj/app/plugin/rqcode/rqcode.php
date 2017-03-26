<?php
namespace Plugin\rqcode;
class rqcode{
    private $png;
    public function getPng($value) {
        require_once(APP_DIR."/plugin/rqcode/phpqrcode.php");
        $errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H
        $matrixPointSize = "4"; // 点的大小：1到10
        if (empty($this->png)) {
            $this->png = \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize);
        }
        return $this->png;
    }
}
?>