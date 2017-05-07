<?php
// declare(strict_types = 1);
namespace Nooper;

use DOMDocument;
use DOMElement;

class Translator {
	
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
	 * public string function createJSON(array $datas)
	 */
	public function createJSON(array $datas): string {
		$end = json_encode($datas, JSON_UNESCAPED_UNICODE);
		return is_bool($end) ? '{"error":"' . (string)json_last_error_msg() . '"}' : $end;
	}
	
	/**
	 * public array function parseJSON(string $json)
	 */
	public function parseJSON(string $json): array {
		$end = json_decode($json, true);
		return is_null($end) ? ['error'=>(string)json_last_error_msg()] : $end;
	}
	
	/**
	 * public string function createXML(array $datas, DOMDocument $doc = null, DOMElement $node = null, boolean $cdata = true, boolean $doctype = false)
	 */
	public function createXML(array $datas, DOMDocument $doc = null, DOMElement $node = null, bool $cdata = true, bool $doctype = false): string {
		if(is_null($doc)) $doc = new DOMDocument('1.0', 'utf-8');
		if(is_null($node)){
			$node = $doc->createElement('xml');
			$doc->appendChild($node);
		}
		foreach($datas as $key => $data){
			$child = $doc->createElement(is_string($key) ? $key : 'node');
			$node->appendChild($child);
			if(is_array($data)) $this->createXML($data, $doc, $child, $cdata, $doctype);
			else{
				if(is_string($data)) $data = trim($data);
				elseif(is_numeric($data)) $data = (string)$data;
				elseif(is_bool($data)) $data = $data ? 'true' : 'false';
				elseif(is_null($data)) $data = '';
				elseif(is_object($data)) $data = get_class($data);
				elseif(is_resource($data)) $data = get_resource_type($data);
				else $data = '';
				$end = $cdata ? $doc->createCDATASection($data) : $doc->createTextNode($data);
				$child->appendChild($end);
			}
		}
		return $doctype ? $doc->saveXML() : $doc->saveXML($node);
	}
	
	/**
	 * public mixed function parseXML(string $xml, boolean $root = false)
	 */
	public function parseXML(string $xml, bool $root = false) {
		if($root) $xml = '<xml>' . $xml . '</xml>';
		$doctype = '<?xml version="1.0" encoding="utf-8"?>';
		$xml = $doctype . $xml;
		$doc = new DOMDocument('1.0', 'utf-8');
		if(!$doc->loadXML($xml, LIBXML_NOBLANKS | LIBXML_NOERROR)) return null;
		$node = $doc->documentElement;
		$children = $node->childNodes;
		$yesNodeTypes = [XML_TEXT_NODE, XML_CDATA_SECTION_NODE, XML_ELEMENT_NODE];
		$yesEndNodeTypes = [XML_TEXT_NODE, XML_CDATA_SECTION_NODE];
		foreach($children as $child){
			if(!in_array($child->nodeType, $yesNodeTypes, true)) $node->removeChild($child);
		}
		$length = $children->length;
		if(0 == $length) $datas = '';
		elseif(1 == $length && in_array($children->item(0)->nodeType, $yesEndNodeTypes, true)) $datas = $child->wholeText;
		else{
			$datas = [];
			foreach($children as $child){
				if(in_array($child->nodeType, $yesEndNodeTypes, true)){
					$datas[] = $child->wholeText;
				}else{
					if('node' == $child->nodeName) $datas[] = $this->parseXML($doc->saveXML($child));
					else $datas[$child->nodeName] = $this->parseXML($doc->saveXML($child));
				}
			}
		}
		return $datas;
	}
	//
}

