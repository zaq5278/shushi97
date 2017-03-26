<?php
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);

require_once "../lib/WxPay.Api.php";
require_once "WxPay.NativePay.php";
require_once 'log.php';

//ģʽһ
/**
 * ���̣�
 * 1����װ����֧����Ϣ��url�����ɶ�ά��
 * 2���û�ɨ���ά�룬����֧��
 * 3��ȷ��֧��֮��΢�ŷ�������ص�Ԥ�����õĻص���ַ���ڡ�΢�ſ���ƽ̨-΢��֧��-֧�����á��н�������
 * 4���ڽӵ��ص�֪֮ͨ���û�����ͳһ�µ�֧����������֧����Ϣ�����֧��������native_notify.php��
 * 5��֧�����֮��΢�ŷ�������֪֧ͨ���ɹ�
 * 6����֧���ɹ�֪ͨ����Ҫ�鵥ȷ���Ƿ�����֧���ɹ�������notify.php��
 */
$notify = new NativePay();
$url1 = $notify->GetPrePayUrl("123456789");

//ģʽ��
/**
 * ���̣�
 * 1������ͳһ�µ���ȡ��code_url�����ɶ�ά��
 * 2���û�ɨ���ά�룬����֧��
 * 3��֧�����֮��΢�ŷ�������֪֧ͨ���ɹ�
 * 4����֧���ɹ�֪ͨ����Ҫ�鵥ȷ���Ƿ�����֧���ɹ�������notify.php��
 */
$input = new WxPayUnifiedOrder();
$input->SetBody("test");
$input->SetAttach("test");
$input->SetOut_trade_no(WxPayConfig::MCHID.date("YmdHis"));
$input->SetTotal_fee("1");
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
$input->SetTrade_type("NATIVE");
$input->SetProduct_id("123456789");
$result = $notify->GetPayUrl($input);
$url2 = $result["code_url"];
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>΢��֧������-�˿�</title>
</head>
<body>
	<div style="margin-left: 10px;color:#556B2F;font-size:30px;font-weight: bolder;">ɨ��֧��ģʽһ</div><br/>
	<img alt="ģʽһɨ��֧��" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=<?php echo urlencode($url1);?>" style="width:150px;height:150px;"/>
	<br/><br/><br/>
	<div style="margin-left: 10px;color:#556B2F;font-size:30px;font-weight: bolder;">ɨ��֧��ģʽ��</div><br/>
	<img alt="ģʽ��ɨ��֧��" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=<?php echo urlencode($url2);?>" style="width:150px;height:150px;"/>
	
</body>
</html>