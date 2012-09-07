<?php

require_once(dirname(__FILE__) . "/host.class.php");

class RedisHost extends Host {
	private $prefix = "";

	public function __construct($host = null, $port = 6379, $prefix = "") {
		parent::__construct($host, $port);
		$this->prefix = $prefix;
	}

	public function getKeyName($id) {
		return $this->prefix . $id;
	}
}

?>
