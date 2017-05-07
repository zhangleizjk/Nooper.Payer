<?php
// declare(strict_types = 1);
namespace Nooper;

use Throwable;
use Exception;
use DateTime;
use DateTimeZone;
use DateInterval;

class Payer {
	/**
	 */
	const operate_create = 1;
	const operate_query = 2;
	const operate_close = 3;
	const operate_refund = 4;
	const operate_refund_query = 5;
	const operate_download = 6;
	const operate_qrcode_create = 7;
	const operate_qrcode_change = 8;
	const operate_callback_input = 9;
	const operate_callback_output = 10;
	const operate_notify = 11;
	const operate_reply = 12;
	
	/**
	 */
	protected $appid;
	protected $mchid;
	protected $key;
	protected $hash = 'MD5';
	protected $datas = [];
	protected $urls = [
		self::operate_create=>'https://api.mch.weixin.qq.com/pay/unifiedorder', 
		self::operate_query=>'https://api.mch.weixin.qq.com/pay/orderquery', 
		self::operate_close=>'https://api.mch.weixin.qq.com/pay/closeorder ', 
		self::operate_refund=>'https://api.mch.weixin.qq.com/secapi/pay/refund', 
		self::operate_refund_query=>'https://api.mch.weixin.qq.com/pay/refundquery', 
		self::operate_download=>'https://api.mch.weixin.qq.com/pay/downloadbill', 
		self::operate_qrcode_create=>'weixin://wxpay/bizpayurl', 
		self::operate_qrcode_change=>'https://api.mch.weixin.qq.com/tools/shorturl', 
		self::operate_callback_input=>null, 
		self::operate_callback_output=>null, 
		self::operate_notify=>null, 
		self::operate_reply=>null
	];
	protected $params = [];
	protected $createParams = [
		'from'=>[
			'trade_type', 
			'device_info', 
			'out_trade_no', 
			'openid', 
			'product_id', 
			'body', 
			'detail', 
			'total_fee', 
			'fee_type', 
			'limit_pay', 
			'goods_tag', 
			'spbill_create_ip', 
			'time_start', 
			'time_expire', 
			'attach'
		], 
		'to'=>[
			'return_code', 
			'result_code', 
			'trade_type', 
			'prepay_id', 
			'code_url'
		]
	];
	protected $queryParams = [
		'from'=>[
			'transaction_id', 
			'out_trade_no'
		], 
		'to'=>[
			'return_code', 
			'result_code', 
			'trade_type', 
			'trade_state', 
			'transaction_id', 
			'out_trade_no', 
			'openid', 
			'total_fee', 
			'settlement_total_fee', 
			'cash_fee', 
			'coupon_fee', 
			'time_end', 
			'attach'
		]
	];
	protected $closeParams = [
		'from'=>[
			'out_trade_no'
		], 
		'to'=>[
			'return_code', 
			'result_code'
		]
	];
	protected $refundParams = [
		'from'=>[
			'transaction_id', 
			'out_trade_no', 
			'out_refund_no', 
			'total_fee', 
			'refund_fee', 
			'refund_fee_type', 
			'refund_account', 
			'op_user_id'
		], 
		'to'=>[
			'return_code', 
			'result_code', 
			'transaction_id', 
			'out_trade_no', 
			'refund_id', 
			'out_refund_no', 
			'refund_fee', 
			'settlement_refund_fee', 
			'total_fee', 
			'settlement_total_fee', 
			'cash_fee', 
			'cash_refund_fee', 
			'coupon_refund_fee'
		]
	];
	protected $refundQueryParams = [
		'from'=>[
			'device_info', 
			'transaction_id', 
			'out_trade_no', 
			'out_refund_no', 
			'total_fee', 
			'refund_fee', 
			'refund_fee_type', 
			'refund_account', 
			'op_user_id'
		], 
		'to'=>[
			'return_code', 
			'result_code', 
			'device_info', 
			'transaction_id', 
			'out_trade_no', 
			'total_fee', 
			'settlement_total_fee', 
			'cash_fee', 
			'refund_count'
		]
	];
	protected $downloadParams = [
		'from'=>[
			'device_info', 
			'bill_date', 
			'bill_type', 
			'tar_type'
		], 
		'to'=>[]
	];
	protected $qrcodeCreateParams = [
		'product_id', 
		'time_stamp'
	];
	protected $qrcodeChangeParams = [
		'long_url'
	];
	protected $callbackInputParams = [
		'from'=>[
			'openid', 
			'is_subscribe', 
			'product_id'
		], 
		'to'=>[]
	];
	protected $callbackOutputParams = [
		'from'=>[
			'return_code', 
			'return_msg', 
			'prepay_id', 
			'result_code', 
			'err_code_des'
		], 
		'to'=>[]
	];
	protected $notifyParams = [
		'from'=>[], 
		'to'=>[]
	];
	protected $replyParams = [
		'from'=>[
			'return_code', 
			'return_msg'
		], 
		'to'=>[]
	];
	
	/**
	 * public void function __construct(string appid, string $mchid, string $key, string $notify)
	 */
	public function __construct(string $appid, string $mchid, string $key, string $notify) {
		$keys = array_merge($this->createParams['from'], $this->queryParams['from'], $this->closeParams['from']);
		$keys = array_merge($keys, $this->refundParams['from'], $this->refundQueryParams['from'], $this->downloadParams['from']);
		$keys = array_merge($keys, $this->qrcodeCreateParams['from'], $this->qrcodeChangeParams['from']);
		$keys = array_merge($keys, $this->callbackInputParams['from'], $this->callbackOuputParams['from']);
		$keys = array_merge($keys, $this->notifyParams['from'], $this->refundParams['from']);
		$this->params = array_merge(array_unique($keys));
		
		$this->url(self::operate_notify, $notify);
		$this->key = $key;
		$this->mchid = $mchid;
		$this->appid = $appid;
	}
	
	/**
	 * public void function __destruct(void)
	 */
	function __destruct() {
		// echo '- end -';
	}
	
	/**
	 * public string function appid(string $appid)
	 */
	public function appid(string $appid): string {
		$this->appid = $appid;
		return $appid;
	}
	
	/**
	 * public string function mchid(string $mchid)
	 */
	public function mchid(string $mchid): string {
		$this->mchid = $mchid;
		return $mchid;
	}
	
	/**
	 * public string function key(string $data)
	 */
	public function key(string $data): string {
		$this->key = $data;
		return $data;
	}
	
	/**
	 * public boolean function url(int $operate, string $url)
	 */
	public function url(int $operate, string $url): bool {
		$keys = array_keys($this->urls);
		if(in_array($operate, $keys, true)){
			$this->urls[$operate] = $url;
			return true;
		}
		return false;
	}
	
	/**
	 * public boolean function data(string $name, string $value)
	 */
	public function data(string $name, string $value): bool {
		if(in_array($name, $this->params, true)){
			$this->datas[$name] = $value;
			return true;
		}
		return false;
	}
	
	/**
	 * public integer function datas(array $params)
	 */
	public function datas(array $params): int {
		$counter = 0;
		foreach($params as $key => $param){
			try{
				$this->data($key, $param);
				$counter++;
			}catch(Throwable $e){
			}
		}
		return $counter;
	}
	
	/**
	 * public void function clear(void)
	 */
	public function clear(): void {
		$this->datas = [];
	}
	
	/**
	 * public ?array function create(boolean $clip = true)
	 */
	public function create(bool $clip = true): array {
		$ends = $this->send(self::operate_create);
		if(!is_null($ends)) return $clip ? $this->clip(self::operate_create, $ends) : $ends;
		return null;
	}
	
	/**
	 * public ?array function query(boolean $clip = true)
	 */
	public function query(bool $clip = true): array {
		$ends = $this->send(self::operate_query);
		if(!is_null($ends)) return $clip ? $this->clip(self::operate_query, $ends) : $ends;
		return null;
	}
	
	/**
	 * pulblic ?array function close(boolean $clip = true)
	 */
	public function close(bool $clip = true): array {
		$ends = $this->send(self::operate_close);
		if(!is_null($ends)) return $clip ? $this->clip(self::operate_close, $ends) : $ends;
		return null;
	}
	
	/**
	 * public ?array function refund(boolean $clip = true)
	 */
	public function refund(bool $clip = true): array {
		$ends = $this->send(self::operate_refund);
		if(!is_null($ends)) return $clip ? $this->clip(self::operate_refund, $ends) : $ends;
		return null;
	}
	
	/**
	 * public ?array function queryr(boolean $clip = true)
	 */
	public function queryr(bool $clip = true): array {
		$ends = $this->send(self::operate_query_refund);
		if(!is_null($ends)) return $clip ? $this->clip(self::operate_query_refund, $ends) : $ends;
		return null;
	}
	
	/**
	 * public ?array function download(boolean $clip = true)
	 */
	public function download(bool $clip = true): array {
		// $this->data('tar_type', 'GZIP');
		// $ends = $this->send(self::operate_download);
		// if(!is_null($ends)) return $clip ? $this->clip(self::operate_down, $ends) : $ends;
		// return null;
	}
	
	/**
	 * public array function qrcode(string $prodouctId, ?string $timestamp = null)
	 */
	public function qrcode(string $productId): array {
		$this->data('product_id', $productId);
		$this->data('time_stamp', $this->now()['stamp']);
		$datas = $this->prepare(self::operate_qrcode_create);
		foreach($datas as $key => &$data){
			$data = ($key . '=' . $data);
		}
		$long = $this->urls[self::operate_qrcode_create] . '?' . implode('&', $datas);
		$short = $this->qrcodec($long);
		$image = null; /* ? */
		return [
			'long_url'=>$long, 
			'short_url'=>$short, 
			'image'=>$image
		];
	}
	
	/**
	 * public ?array function qrcodec(string $url, boolean $clip = true)
	 */
	public function qrcodec(string $url, bool $clip = true): array {
		$this->data('long_url', $url);
		$ends = $this->send(self::operate_qrcode_change);
		if(!is_null($ends)){
			$keys = [
				'short_url'
			];
			return $clip ? $this->clip($ends, $keys) : $ends;
		}
		return null;
	}
	
	/**
	 * public ?array function notify(boolean $clip = true)
	 */
	public function notify(bool $clip = true): array {
		// $xml = $GLOBALS['HTTP_RAW_POST_DATA'] ?? '';
		$xml = file_get_contents('php://input');
		$datas = $this->filter($xml);
		if(!is_null($datas)){
			$keys = [
				'transaction_id', 
				'out_trade_no', 
				'openid', 
				'trade_type', 
				'total_fee', 
				'settlement_total_fee', 
				'cash_fee', 
				'coupon_fee', 
				'time_end', 
				'attach'
			];
			return $clip ? $this->clip($datas, $keys) : $datas;
		}
	}
	
	/**
	 * public void function reply(string $code, ?string $message = null)
	 */
	public function reply(string $code, string $message = null): void {
		$this->data('return_code', $code);
		if(!is_null($message)) $this->data('return_msg', $message);
		$datas = $this->prepare(self::operate_reply, false);
		$helper = new Translator();
		$xml = $helper->createXML($datas);
		header('Content-type: text/xml');
		echo $xml;
	}
	
	/**
	 * public ?array function callbackin(boolean $clip = true)
	 */
	public function callbackInput(bool $clip = true): array {
		$xml = $GLOBALS['HTTP_RAW_POST_DATA'] ?? null;
		$helper = new Translator();
		$datas = $helper->parseXML($xml);
		$sign = $this->sign($datas);
		if(!isset($datas['sign'])){
			$code = '30001';
			$message = 'no sign data';
			throw new Exception($message, $code);
			return null;
		}elseif($datas['sign'] != $sign){
			$code = '30000';
			$message = 'sign error';
			throw new Exception($message, $code);
			return null;
		}
		$keys = [
			'openid', 
			'product_id'
		];
		return $clip ? $this->clip($datas, $keys) : $datas;
	}
	
	/**
	 * public void function callbackOutput(void)
	 */
	public function callbackOutput(): void {
		$datas = $this->prepare(self::operate_callback_output);
		$helper = new Translator();
		$xml = $helper->createXML($datas);
		header('Content-type: text/xml');
		echo $xml;
	}
	
	/**
	 * public array function prepare(integer $operate, boolean $primary=true)
	 */
	public function prepare(int $operate, bool $primary = true): array {
		$params = $this->map($operate);
		if(is_null($params)) return [];
		foreach($params['from'] as $param){
			if(isset($this->datas[$param])) $datas[$param] = $this->datas[$param];
		}
		if($primary){
			$datas['appid'] = $this->appid;
			$datas['mch_id'] = $this->mchid;
			$datas['nonce_str'] = $this->rand();
			$datas['sign'] = $this->sign($datas);
		}
		return $datas;
	}
	
	/**
	 * public ?array function send(integer $operate, ?array $datas = null)
	 */
	public function send(int $operate, array $datas = null): array {
		$datas = $datas ?? $this->prepare($operate);
		if(!$datas) return null;
		$url = $this->urls[$operate] ?? null;
		if(is_null($url)) return null;
		$translator = new Translator();
		$xml = $translator->createXML($datas);
		$mimicry = new Mimicry();
		$end = $mimicry->post($url, $xml);
		return $this->filter($end);
	}
	
	/**
	 * protected ?array function filter(string $xml)
	 */
	protected function filter(string $xml): array {
		$helper = new Translator();
		$datas = $helper->parseXML($xml);
		if(!is_array($datas)) return $this->error('10001', '');
		elseif(!isset($datas['sign'])) return $this->error('40001', '');
		$sign = $this->sign($datas);
		if(strtolower($datas['return_code']) == 'fail'){
			$code = '20001';
			$message = $datas['return_msg'];
			return $this->error($code, $message);
		}elseif(strtolower($datas['result_code']) == 'fail'){
			$code = '30001';
			$message = $datas['error_code'] . ':' . $datas['error_msg'];
			return $this->error($code, $message);
		}elseif($datas['sign'] != $sign) return $this->error('40002', 'sign failure');
		return $datas;
	}
	
	/**
	 * public ?string sign(array $datas)
	 */
	public function sign(array $datas): string {
		foreach($datas as $key => $data){
			if(!is_string($key) or !is_string($key)) return null;
			elseif('' == $data) unset($datas[$key]);
			elseif('sign' == $key) unset($datas[$key]);
		}
		ksort($datas);
		foreach($datas as $key => $data){
			$params[] = $key . '=' . $data;
		}
		$params[] = ('key=' . $this->key);
		$str = implode('&', $params);
		return strtoupper(md5($str));
	}
	
	/**
	 * protected ?array function map(int $operate)
	 */
	protected function map(int $operate): array {
		switch($operate){
			case self::operate_create:
				return $this->createParamss;
				break;
			case self::operate_query:
				return $this->queryParams;
				break;
			case self::operate_close:
				return $this->closeParams;
				break;
			case self::operate_refund:
				return $this->refundParams;
				break;
			case self::operate_refund_query:
				return $this->refundQueyParams;
				break;
			case self::operate_download:
				return $this->downloadParams;
				break;
			case self::operate_qrcode_create:
				return $this->qrcodeCreateParams;
				break;
			case self::operate_qrcode_change:
				return $this->qrcodeChangeParams;
				break;
			case self::operate_callback_input:
				return $this->callbackInputParams;
				break;
			case self::operate_callback_output:
				return $this->callbackParams;
				break;
			case self::operate_notify:
				return $this->notifyParams;
				break;
			case self::operate_reply:
				return $this->replyParams;
				break;
			default:
				return null;
				break;
		}
	}
	
	/**
	 * protected array function clip(array $datas, array $keys)
	 */
	protected function clip(array $datas, array $keys): array {
		foreach($keys as $key){
			if(is_string($key) && isset($datas[$key])){
				$ends[$key] = $datas[$key];
			}
			return $ends ?? [];
		}
	}
	
	/**
	 * protected string function rand(integer $len = 30)
	 */
	protected function rand(int $len = 30): string {
		$str = '';
		$chars = array_merge(range('0', '9'), range('a', 'z'));
		$end = count($chars) - 1;
		for($i = 0;$i < $len;$i++){
			$str .= $chars[mt_rand(0, $end)];
		}
		return strtoupper($str);
	}
	
	/**
	 * public array function now(integer $seconds = 0)
	 */
	public function now(int $seconds = 0): array {
		$dt = new DateTime();
		$dt->setTimezone(new DateTimeZone('Asia/Shanghai'));
		$dt->add(new DateInterval('PT' . $seconds . 'S'));
		$datas['stamp'] = $dt->getTimestamp();
		$datas['format'] = $dt->format('YmdHis');
		return $datas;
	}
	
	/**
	 * protected void function error(string $code, string $mssage)
	 */
	protected function error(string $code, string $message): void {
		throw new Exception($message, $code);
	}
	//
}






























































