<?php
require_once "../lib/WxPay.Api.php";
//require_once "../lib/WxPay.MicroPay.php";


if(isset($_REQUEST["bill_date"]) && $_REQUEST["bill_date"] != ""){
	$bill_date = $_REQUEST["bill_date"];
    $bill_type = $_REQUEST["bill_type"];
	$input = new WxPayDownloadBill();
	$input->SetBill_date($bill_date);
	$input->SetBill_type($bill_type);
	$file = WxPayApi::downloadBill($input);
	echo $file;
	//TODO ���˵��ļ�����
    exit(0);
}
?>
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>΢��֧������-���˿</title>
</head>
<body>  
	<form action="#" method="post">
        <div style="margin-left:2%;">�������ڣ�</div><br/>
        <input type="text" style="width:96%;height:35px;margin-left:2%;" name="bill_date" /><br /><br />
        <div style="margin-left:2%;">�˵����ͣ�</div><br/>
        <select style="width:96%;height:35px;margin-left:2%;" name="bill_type">
		  <option value ="ALL">���ж�����Ϣ</option>
		  <option value ="SUCCESS">�ɹ�֧���Ķ���</option>
		  <option value="REFUND">�˿��</option>
		  <option value="REVOKED">�����Ķ���</option>
		</select><br /><br />
       	<div align="center">
			<input type="submit" value="���ض���" style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" />
		</div>
	</form>
</body>
</html>