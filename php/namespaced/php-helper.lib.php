<?php

namespace Pnet;

class Api3 {

	public $apikey = false;
	public $format = 'json';
	protected $response = false;
	public $method = "setlist/latest"; //default method
	public $args = [];
	protected $output = false;

	public function set_apikey($key) {
		$this->args['apikey'] = $key;
		return $this;
	}

	public function set_arg($key,$val) {
		$this->args[$key] = $val;
		return $this;
	}

	public function set_format($format) {
		$this->format = strtolower($format);
		return $this;
	}

	public function set_method($method) {
		$this->method = $method;
		return $this;
	}

	public function set_args($array) {
		if(is_array($array)) {
			foreach($array as $k=>$v) {
				$this->$k = $v;
			}
		}
		return $this;
	}

	public function finish() {
		return $this->output;
	}

	public function get_setlist($date=false) {
		if(isset($this->args['showdate'])) { $date = $this->args['showdate']; }
		if(@date("Y",@strtotime($date)) < 1970) {
			$showdate = '1999-12-31'; //default date, because cheesecake
		} else {
			$showdate = date("Y-m-d",@strtotime($date));
		}
		$this->args['showdate'] = $showdate;
		self::set_method('setlists/get');
		self::fetch();
		$this->output = self::parse();
		return $this;
	}

	protected function parse() {
		if(!isset($this->format)) { $this->format = 'json'; }
		if(!isset($this->response)) {
			error_log("Error in Phish.net API Library; No response received.");
			self::error_response();
		}
		switch($this->format):
			case 'sphp':
			case 'php':
				return serialize(json_decode($this->response,1));
				break;
			case 'array':
				return @json_decode($this->response,1);
				break;
			case 'clean':
				return "<pre>".print_r(@json_decode($this->response,1),true)."</pre>";
				break;
			case 'object':
				return @json_decode($this->response);
				break;
			case 'yaml':
				return function_exists('yaml_emit') ? yaml_emit(json_decode($this->response)) : "Function \"yaml_emit()\" not found";
				break;
			case 'json':
			case 'raw':
			default:
				return $this->response;
				break;
		endswitch;
	}

	protected function error_response() {
		echo "<p>Error in Phish.net API Library.</p>";
	}

	protected function fetch() {
		$ch = curl_init('https://api.phish.net/v3/'.$this->method);
		curl_setopt($ch, CURLOPT_POST,true);
		if(is_array($this->args)) { $postdata = http_build_query($this->args); }
		curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
		curl_setopt($ch, CURLOPT_HEADER,0);  // DO NOT RETURN HTTP HEADERS
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);  // RETURN THE CONTENTS OF THE CALL
		$err = curl_error($ch);
		if ($err) {
			error_log("Error in Phish.net API Library; cURL Error #: " . $err);
		} else {
			$response = curl_exec($ch);
			$this->response = $response;
		}
	}

}
