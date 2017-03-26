<?php
require_once "../lib/WxPay.Api.php";
/**
 * 
 * ˢ��֧��ʵ����
 * ����ʵ����һ��ˢ��֧�������̣��������£�
 * 1���ύˢ��֧��
 * 2�����ݷ��ؽ�������Ƿ���Ҫ��ѯ�����������ѯ֮�󶩵���δ������Ҫ���ز�ѯ��һ�㷴����10�Σ�
 * 3�����������ѯ10������Ȼ���䣬����������
 * 4������������Ҫѭ��������һֱ�����ɹ�Ϊֹ��ע��ѭ������������10�Σ�
 * 
 * ������΢��֧���ṩ�����������̻��ɸ����Լ��������޸ģ�����ʹ��lib�е�api���п�����Ϊ�˷�ֹ
 * ��ѯʱholdס��̨php���̣��̻���ѯ�ͳ����߼�����ǰ�˵���
 * 
 * @author widy
 *
 */
class MicroPay
{
	/**
	 * 
	 * �ύˢ��֧��������ȷ�Ͻ�����ӿڱȽ���
	 * @param WxPayMicroPay $microPayInput
	 * @throws WxpayException
	 * @return ���ز�ѯ�ӿڵĽ��
	 */
	public function pay($microPayInput)
	{
		//�١��ύ��ɨ֧��
		$result = WxPayApi::micropay($microPayInput, 5);
		//������سɹ�
		if(!array_key_exists("return_code", $result)
			|| !array_key_exists("out_trade_no", $result)
			|| !array_key_exists("result_code", $result))
		{
			echo "�ӿڵ���ʧ��,��ȷ���Ƿ������Ƿ�����";
			throw new WxPayException("�ӿڵ���ʧ�ܣ�");
		}
		
		//ǩ����֤
		$out_trade_no = $microPayInput->GetOut_trade_no();
		
		//�ڡ��ӿڵ��óɹ�����ȷ���ص���ʧ��
		if($result["return_code"] == "SUCCESS" &&
		   $result["result_code"] == "FAIL" && 
		   $result["err_code"] != "USERPAYING" && 
		   $result["err_code"] != "SYSTEMERROR")
		{
			return false;
		}

		//�ۡ�ȷ��֧���Ƿ�ɹ�
		$queryTimes = 10;
		while($queryTimes > 0)
		{
			$succResult = 0;
			$queryResult = $this->query($out_trade_no, $succResult);
			//�����Ҫ�ȴ�1s�����
			if($succResult == 2){
				sleep(2);
				continue;
			} else if($succResult == 1){//��ѯ�ɹ�
				return $queryResult;
			} else {//��������ʧ��
				return false;
			}
		}
		
		//�ܡ���ȷ��ʧ�ܣ���������
		if(!$this->cancel($out_trade_no))
		{
			throw new WxpayException("������ʧ�ܣ�");
		}

		return false;
	}
	
	/**
	 * 
	 * ��ѯ�������
	 * @param string $out_trade_no  �̻�������
	 * @param int $succCode         ��ѯ�������
	 * @return 0 �������ɹ���1��ʾ�����ɹ���2��ʾ�����ȴ�
	 */
	public function query($out_trade_no, &$succCode)
	{
		$queryOrderInput = new WxPayOrderQuery();
		$queryOrderInput->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::orderQuery($queryOrderInput);
		
		if($result["return_code"] == "SUCCESS" 
			&& $result["result_code"] == "SUCCESS")
		{
			//֧���ɹ�
			if($result["trade_state"] == "SUCCESS"){
				$succCode = 1;
			   	return $result;
			}
			//�û�֧����
			else if($result["trade_state"] == "USERPAYING"){
				$succCode = 2;
				return false;
			}
		}
		
		//������ش�����Ϊ���˽��׶����Ų����ڡ���ֱ���϶�ʧ��
		if($result["err_code"] == "ORDERNOTEXIST")
		{
			$succCode = 0;
		} else{
			//�����ϵͳ�������������
			$succCode = 2;
		}
		return false;
	}
	
	/**
	 * 
	 * �������������ʧ�ܻ��ظ�����10��
	 * @param string $out_trade_no
	 * @param ������� $depth
	 */
	public function cancel($out_trade_no, $depth = 0)
	{
		if($depth > 10){
			return false;
		}
		
		$clostOrder = new WxPayReverse();
		$clostOrder->SetOut_trade_no($out_trade_no);
		$result = WxPayApi::reverse($clostOrder);
		
		//�ӿڵ���ʧ��
		if($result["return_code"] != "SUCCESS"){
			return false;
		}
		
		//������Ϊsuccess�Ҳ���Ҫ���µ��ó��������ʾ�����ɹ�
		if($result["result_code"] != "SUCCESS" 
			&& $result["recall"] == "N"){
			return true;
		} else if($result["recall"] == "Y") {
			return $this->cancel($out_trade_no, ++$depth);
		}
		return false;
	}
}