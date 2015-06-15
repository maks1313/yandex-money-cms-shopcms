<?php
/**
 * @connect_module_class_name CYandexMoney
 *
 */
// YandexMoney method implementation
// see also
//                http://money.yandex.ru

class CYandexMoney extends PaymentModule {
	const YAVERSION = '1.1.0';
	
	public $test_mode;
	public $org_mode;
	public $status;

	public $orderId;
	public $orderTotal;
	public $userId;

	public $successUrl;
	public $failUrl;

	public $reciver;
	public $formcomment;
	public $short_dest;
	public $writable_targets = 'false';
	public $comment_needed = 'true';
	public $label;
	public $quickpay_form = 'shop';
	public $payment_type = '';
	public $targets;
	public $sum;
	public $comment;
	public $need_fio = 'true';
	public $need_email = 'true';
	public $need_phone = 'true';
	public $need_address = 'true';

	public $shopid;
	public $scid;
	public $account;
	public $password;

	public $method_ym;
	public $method_cards;
	public $method_cash;
	public $method_mobile;
	public $method_wm;
	public $method_ab;
	public $method_sb;

	public $pay_method;
	
    function _initVars(){
			 
             $this->title                 = "YandexMoney";
             $this->description         = "YandexMoney (money.yandex.ru). ������ �������� � ������ �������������� ������. ���� ������ ����� ������������ ��� �������������� ������� �������� �������.<br/>
			 <b>���������</b>: <br>����� ������ HTTP ����������� (paymentAvisoURL / checkURL): <br/> http(s)://�����_��������/index.php?yandexmoney=yes<br/><br/>������ ������ ".self::YAVERSION;
			
             $this->sort_order         = 0;

			 $array_params = array('testmode', 'mode', 'method_ym', 'method_cards', 'method_cash', 'method_phone', 'method_wm', 'method_ab', 'method_sb', 'method_ma', 'method_pb', 'password', 'shopid', 'scid', 'account', 'status');
             foreach ($array_params as $key => $value) {
				$value2 = 'CONF_PAYMENTMODULE_YM_' . strtoupper($value);
				$this->Settings[] = $value2;
				$this->$value = $this->_getSettingValue($value2);
			 }
			
			 $this->org_mode = ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_MODE') == 2);
			 $this->test_mode = ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_TESTMODE') == 1);

			 if (!empty($_POST['ym_method'])) {
				$_SESSION['ym_method'] = $_POST['ym_method'];
			 }			 
        }

		function getMethodsHtml(){
				
			$html = "<br/><b>������ ������:</b><br/><select name=\"ym_method\">";

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_YM')) {
				$html .= '<option value="PC">������ �� �������� � ������.�������</option>';
			}

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_CARDS')) {
				$html .= '<option value="AC">������ � ������������ ���������� �����</option>';
			}	
			
			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_CASH') && $this->org_mode) {
				$html .= '<option value="GP">������ ��������� ����� ����� � ���������</option>';
			}	

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_PHONE') && $this->org_mode) {
				$html .= '<option value="MC">������ �� ����� ���������� ��������</option>';
			}

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_WM') && $this->org_mode) {
				$html .= '<option value="WM">������ �� �������� � ������� WebMoney</option>';
			}

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_AB') && $this->org_mode) {
				$html .= '<option value="AB">������ ����� �����-����</option>';
			}

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_SB') && $this->org_mode) {
				$html .= '<option value="SB">������ ����� ��������: ������ �� SMS ��� �������� ������</option>';
			}

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_MA') && $this->org_mode) {
				$html .= '<option value="MA">������ ����� MasterPass</option>';
			}

			if ($this->_getSettingValue('CONF_PAYMENTMODULE_YM_METHOD_PB') && $this->org_mode) {
				$html .= '<option value="PB">������ ����� ��������-���� ��������������</option>';
			}
			$html .= "</select><br/> <br/>";

			return $html;
		}

		function payment_form_html()
        {
		   $text = '';
		   $payment_methods = payGetAllPaymentMethods(true);
		   foreach ($payment_methods as $method) {
			   if ($_GET['paymentMethodID'] == $method['PID']) {
				   $currentPaymentModule = modGetModuleObj($method['module_id'], PAYMENT_MODULE);
				   if ( $currentPaymentModule != null ){
					   if (get_class($currentPaymentModule) == 'CYandexMoney') { 
						   $currentPaymentModule->_initVars();
						   $text .= $currentPaymentModule->getMethodsHtml();
					   }
					}
			   }
		   }
		    return $text;
        }


        function _initSettingFields(){
			$this->SettingsFields['CONF_PAYMENTMODULE_YM_TESTMODE'] = array(
				'settings_value'                 => '1',
				'settings_title'                         => '�������� �����',
                'settings_description'         => '����������� �������� ����� ��� �������� ������',
                'settings_html_function'         => 'setting_CHECK_BOX(',
                'sort_order'                         => 1,
              );
			 $this->SettingsFields['CONF_PAYMENTMODULE_YM_MODE'] = array(
                        'settings_value'                 => '1',
                        'settings_title'                         => '�������� ������ ������',
                        'settings_description'         => '',
                        'settings_html_function'         => 'setting_SELECT_BOX(CYandexMoney::getModes(),',
                        'sort_order'                         => 2,
                );
			  $this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_YM'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '������� ������.������',
                        'settings_description'         => '',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 3,
                );
			  $this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_CARDS'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '���������� �����',
                        'settings_description'         => '',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 3,
                );

			    $this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_CASH'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '��������� ����� ����� � ���������',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 4,
                );
				 $this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_PHONE'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '���� ���������� ��������',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 5,
                );
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_WM'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '������� WebMoney',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 6,
                );
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_AB'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '�����-����',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 7,
                );
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_SB'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '��������: ������ �� SMS ��� �������� ������',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 8,
                );
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_MA'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => 'MasterPass',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 8,
                );				
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_METHOD_PB'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '��������-���� ��������������',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_CHECK_BOX(',
                        'sort_order'                         => 8,
                );
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_ACCOUNT'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '����� �������� ������',
                        'settings_description'         => '������ ��� ���������� ���',
                        'settings_html_function'         => 'setting_TEXT_BOX(0,',
                        'sort_order'                         => 7,
                );
				
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_SHOPID'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '������������� ������ �������� � ������.������� (ShopID)',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_TEXT_BOX(0,',
                        'sort_order'                         => 7,
                );
				$this->SettingsFields['CONF_PAYMENTMODULE_YM_SCID'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '������������� ������� ������ �������� � ������.������� (scid)',
                        'settings_description'         => '������ ��� ����������� ���',
                        'settings_html_function'         => 'setting_TEXT_BOX(0,',
                        'sort_order'                         => 7,
                );

				$this->SettingsFields['CONF_PAYMENTMODULE_YM_PASSWORD'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '��������� ����� (shopPassword) ��� ������ �����������',
                        'settings_description'         => '',
                        'settings_html_function'         => 'setting_TEXT_BOX(0,',
                        'sort_order'                         => 7,
                );
				
                $this->SettingsFields['CONF_PAYMENTMODULE_YM_STATUS'] = array(
                        'settings_value'                 => '',
                        'settings_title'                         => '������ ������ ����� ������',
                        'settings_description'         => '�������, ����� ������ ����������� ������ ����� ���������� �������. ������������� ���������� ��� �� ������, ��� ���������� � ���������� �������� � �������� ������� ������������ ������. ��� �������� �������� ���������� �������� ��������� ������.',
                        'settings_html_function'         => 'setting_ORDER_STATUS_SELECT(',
                        'sort_order'                         => 1,
                );
        }

		 function getModes(){

                return array(
                        array(
                                'title' => '�� ���� ����������� ���� � ����������� ������ ������.�����',
                                'value' => '1',
                                ),
                        array(
                                'title' => '�� ��������� ���� ����������� � ����������� �������� � ������.��������',
                                'value' => '2',
                                ),
                        );
        }

        function after_processing_html( $orderID )
        {	
				$order = ordGetOrder( $orderID );
				
				$this->orderId = $orderID;
				$this->comment = $order['customers_comment'];
				$this->orderTotal = floatval($order["order_amount"] * $order["currency_value"]);
				
				$this->_initVars();
				
				$this->pay_method = $_SESSION['ym_method'];

				$this->userId = $order['customerID'];
				

				$this->createFormHtml();
				
				return $res;
        }

        function before_payment_php( $orderID, $OutSum, $merch)
		{
			$res  = '_before_payment_php_';
			return $res;
        }

		public function createFormHtml(){
			
			if ($this->org_mode){
				$html = '
					<form method="POST" action="'.$this->getFormUrl().'"  id="paymentform" name = "paymentform">
					   <input type="hidden" name="paymentType" value="'.$this->pay_method.'" />
					   <input type="hidden" name="shopid" value="'.$this->shopid.'">
					   <input type="hidden" name="scid" value="'.$this->scid.'">
					    <input type="hidden" name="shopSuccessURL" value="' . getTransactionResultURL('success') . '&InvId=' . $this->orderId  . '" >
					    <input type="hidden" name="shopFailURL" value="' . getTransactionResultURL('failure') . '&InvId=' . $this->orderId  . '" >
					   <input type="hidden" name="orderNumber" value="'.$this->orderId.'">
					   <input type="hidden" name="sum" value="'.$this->orderTotal.'" data-type="number" >
					   <input type="hidden" name="customerNumber" value="'.$this->userId.'" >	
					   <input type="hidden" name="cms_name" value="shopcms" >	
					</form>';
			}else{
				$html = '<form method="POST" action="'.$this->getFormUrl().'"  id="paymentform" name = "paymentform">
						   <input type="hidden" name="receiver" value="'.$this->account.'">
						   <input type="hidden" name="formcomment" value="Order '.$this->orderId.'">
						   <input type="hidden" name="short-dest" value="Order '.$this->orderId.'">
						   <input type="hidden" name="writable-targets" value="'.$this->writable_targets.'">
						   <input type="hidden" name="comment-needed" value="'.$this->comment_needed.'">
						   <input type="hidden" name="label" value="'.$this->orderId.'">
						   <input type="hidden" name="quickpay-form" value="'.$this->quickpay_form.'">
						   <input type="hidden" name="paymentType" value="'.$this->pay_method.'">
						   <input type="hidden" name="targets" value="����� '.$this->orderId.'">
						   <input type="hidden" name="sum" value="'.$this->orderTotal.'" data-type="number" >
						   <input type="hidden" name="comment" value="'.$this->comment.'" >
						   <input type="hidden" name="need-fio" value="'.$this->need_fio.'">
						   <input type="hidden" name="need-email" value="'.$this->need_email.'" >
						   <input type="hidden" name="need-phone" value="'.$this->need_phone.'">
						   <input type="hidden" name="need-address" value="'.$this->need_address.'">
						  
						</form>';
			}
			$html .= '<script type="text/javascript">
						document.getElementById("paymentform").submit();
						</script>';
			echo $html; exit;
			return $html;
		}

		public function checkSign($callbackParams){
			$string = $callbackParams['action'].';'.$callbackParams['orderSumAmount'].';'.$callbackParams['orderSumCurrencyPaycash'].';'.$callbackParams['orderSumBankPaycash'].';'.$callbackParams['shopId'].';'.$callbackParams['invoiceId'].';'.$callbackParams['customerNumber'].';'.$this->password;
			$md5 = strtoupper(md5($string));
			return ($callbackParams['md5']==$md5);
		}

		public function sendAviso($callbackParams, $code){
			header("Content-type: text/xml; charset=utf-8");
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<paymentAvisoResponse performedDatetime="'.date("c").'" code="'.$code.'" invoiceId="'.$callbackParams['invoiceId'].'" shopId="'.$this->shopid.'"/>';
			echo $xml;
		}

		public function sendCode($callbackParams, $code){
			header("Content-type: text/xml; charset=utf-8");
			$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<checkOrderResponse performedDatetime="'.date("c").'" code="'.$code.'" invoiceId="'.$callbackParams['invoiceId'].'" shopId="'.$this->shopid.'"/>';
			echo $xml;
		}

		public function checkOrder($callbackParams, $sendCode=FALSE, $aviso=FALSE){ 
		
			if ($this->checkSign($callbackParams)){
				$code = 0;
			}else{
				$code = 1;
			}
			
			if ($sendCode){
				if ($aviso){
					$this->sendAviso($callbackParams, $code);
				}else{
					$this->sendCode($callbackParams, $code);
				}
				exit;
			}else{
				return $code;
			}
		}

		public function individualCheck($callbackParams){
			$string = $callbackParams['notification_type'].'&'.$callbackParams['operation_id'].'&'.$callbackParams['amount'].'&'.$callbackParams['currency'].'&'.$callbackParams['datetime'].'&'.$callbackParams['sender'].'&'.$callbackParams['codepro'].'&'.$this->password.'&'.$callbackParams['label'];
			$check = (sha1($string) == $callbackParams['sha1_hash']);
			if (!$check){
				header('HTTP/1.0 401 Unauthorized');
				return false;
			}
			return true;
		
		}

		/* ���������� ����� */
		public function ProcessResult()
		{
			$callbackParams = $_POST;
			$order_id = false;
			if ($this->org_mode){
				if ($callbackParams['action'] == 'checkOrder'){
					$code = $this->checkOrder($callbackParams);
					$this->sendCode($callbackParams, $code);
					$order_id = (int)$callbackParams["orderNumber"];
				}
				if ($callbackParams['action'] == 'paymentAviso'){
					$this->checkOrder($callbackParams, TRUE, TRUE);
				}
			}else{
				$check = $this->individualCheck($callbackParams);
				
				if (!$check){
					
				}else{
					$order_id = (int)$callbackParams["label"];
				}
			}
			
			return $order_id;
		}
		
		public function getFormUrl(){
			if (!$this->org_mode){
				return $this->individualGetFormUrl();
			}else{
				return $this->orgGetFormUrl();
			}
		}

		public function individualGetFormUrl(){
			if ($this->test_mode){
				return 'https://demomoney.yandex.ru/quickpay/confirm.xml';
			}else{
				return 'https://money.yandex.ru/quickpay/confirm.xml';
			}
		}

		public function orgGetFormUrl(){
			if ($this->test_mode){
				return 'https://demomoney.yandex.ru/eshop.xml';
			} else {
				return 'https://money.yandex.ru/eshop.xml';
			}
		}



        function after_payment_php( $orderID, $params){
				$this->_initVars();
				 $order = ordGetOrder( $orderID );
				$order_id = $this->ProcessResult();
				if ($order_id) {
					ostSetOrderStatusToOrder($order_id, $this->_getSettingValue('CONF_PAYMENTMODULE_YM_STATUS'));
				}
				exit;

        }

	
}
?>