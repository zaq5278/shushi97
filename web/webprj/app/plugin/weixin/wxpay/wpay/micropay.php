<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>΢��֧������-���˿</title>
</head>
<?php
require_once "../lib/WxPay.Api.php";
require_once "WxPay.MicroPay.php";
require_once 'log.php';

//��ʼ����־
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//��ӡ���������Ϣ
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

if(isset($_REQUEST["auth_code"]) && $_REQUEST["auth_code"] != ""){
	$auth_code = $_REQUEST["auth_code"];
	$input = new WxPayMicroPay();
	$input->SetAuth_code($auth_code);
	$input->SetBody("ˢ����������-֧��");
	$input->SetTotal_fee("1");
	$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
	
	$microPay = new MicroPay();
	printf_info($microPay->pay($input));
}

/**
 * ע�⣺
 * 1���ύ��ɨ֮�󣬷���ϵͳ��æ���û���������ȴ�����Ϣʱ��Ҫѭ���鵥��ȷ���Ƿ�֧���ɹ�
 * 2����Σ�һ��10�Σ�ȷ�϶�δ��ȷ�ɹ�ʱ��Ҫ���ó����ӿڳ�������ֹ�û��ظ�֧��
 */

?>
<body>  
	<form action="#" method="post">
        <div style="margin-left:2%;">��Ʒ������</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" readonly value="ˢ����������-֧��" name="auth_code" /><br /><br />
        <div style="margin-left:2%;">֧����</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" readonly value="1��" name="auth_code" /><br /><br />
        <div style="margin-left:2%;">��Ȩ�룺</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="auth_code" /><br /><br />
       	<div align="center">
			<input type="submit" value="�ύˢ��" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
		</div>
	</form>
</body>
</html>