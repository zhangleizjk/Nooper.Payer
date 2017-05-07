<?php
// declare(strict_types = 1);
namespace Nooper;

class Mimicry {
	
	/**
	 * public void function __construct(void)
	 */
	public function __construct() {
		//
	}
	
	/**
	 * public void function __destruct(void)
	 */
	function __destruct() {
		//
	}
	
	/**
	 * public string function get(string $url, ?array $qryStrings = null)
	 */
	public function get(string $url, array $qryStrings = null): string {
		if(is_array($qryStrings)){
			foreach($qryStrings as $key => &$qry){
				$qry = $key . '=' . rawurlencode($qry);
			}
		}
		$qryString = '?' . implode('&', $qryStrings);
		$url .= $qryString;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_HTTPGET, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($curl);
		curl_close($curl);
		return $data;
	}
	
	/**
	 * public string function post(string url, string $data)
	 */
	public function post(string $url, string $data): string {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$end = curl_exec($ch);
		if(is_bool($end)) $end = '{"errcode":-12,"errmsg":"'.curl_error($ch).'"}';
		curl_close($ch);
		return $end;
	}
	//
}

