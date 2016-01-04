<?php
namespace Truvu\Lib;

class Socket
{
	private static $host, $port, $socket=null, $clients=array(), $client=null;
	private static $data=array(), $customHeader=array(), $headers=array();

	public static function listen($port=3000, $host='localhost'){
		self::$host = $host;
		self::$port = $port;
		self::$socket = self::getSocket();
		self::$clients = array(self::$socket);
	}
	private static function getSocket(){
		//Create TCP/IP sream socket
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		//reuseable port
		socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
		//bind socket to specified host
		socket_bind($socket, self::$host, self::$port);
		//listen to port
		socket_listen($socket);
		return $socket;
	}
	/*
	/	set property
	/
	*/
	public static function setUser($id=null){
		if(preg_match('/^[0-9]{1,11}$/', $id)){
			if(is_string($id)) $id = (float)$id;
			if(!isset(self::$clients[$id])) self::$clients[$id] = self::getSocket();
			self::$socket = self::$clients[$id];
		}
		return new self;
	}
	public static function setHeader($name='', $value=''){
		self::$customHeader[$name] = $value;
		return new self;
	}
	public static function connect($cb){
		$null = NULL;
		while(true){
			$changed = self::$clients;
			@socket_select($changed, $null, $null, 1);
			if(in_array(self::$socket, $changed)){
				$socket = socket_accept(self::$socket);
				self::$clients[] = $socket;
				// read data form client
				self::parseHeader(socket_read($socket, 1024));
				// setResponse socket
				self::setResponse($socket);

				socket_getpeername($socket, $ip);

				$found_socket = array_search(self::$socket, $changed);
				unset($changed[$found_socket]);
			}
			// loop all socket connection
			foreach($changed as $client){
				while(socket_recv($client, $buf, 2048, 0) > 0){
					call_user_func($cb, json_decode(self::unmask($buf), true));
					break 2;
				}
				$buf = @socket_read($client, 1024, PHP_NORMAL_READ);
				if($buf==false){
					$found_socket = array_search($client, self::$clients);
					socket_getpeername($client, $ip);
					unset(self::$clients[$found_socket]);
				}
			}
		}
	}
	private static function setResponse($client){
		if(count(self::$customHeader)){
			foreach(self::$customHeader as $name => $value) self::$headers[$name] = $value;
			self::$customHeader = array();
		}
		$secKey = self::getHeader('sec-websocket-key');
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));

		$upgrade = array('HTTP/1.1 101 Web Socket Protocol Handshake');
		array_push($upgrade, 'Upgrade: websocket');
		array_push($upgrade, 'Connection: Upgrade');
		array_push($upgrade, 'WebSocket-Origin: '.self::getHeader('origin'));
		array_push($upgrade, 'WebSocket-Location: wss://'.self::$host.':'.self::$port);
		array_push($upgrade, "Sec-WebSocket-Accept: $secAccept\r\n\r\n");
		$upgrade = implode("\r\n", $upgrade);

		socket_write($client, $upgrade, strlen($upgrade));
	}
	/*
	/	get property
	/
	*/
	public static function getUser($id=null){
		if(!preg_match('/^[0-9]{1,11}$/', $id)) return null;
		if(is_string($id)) $id = (float)$id;
		if(isset(self::$clients[$id])) return self::$clients[$id];
	}
	public static function getHeader($name=''){
		return isset(self::$headers[$name])?self::$headers[$name]:null;
	}

	/*
	/	parse client request
	/
	*/
	private static function parseHeader($headers=null){
		self::$headers = array();
		$lines = preg_split("/\r\n/", $headers);
		foreach($lines as $line){
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches)){
				$name = strtolower($matches[1]);
				self::$headers[$name] = $matches[2];
			}
		}
	}

	/*
	/ 	decode data form client
	/
	*/
	private static function unmask($text) {
		$length = ord($text[1]) & 127;
		if($length == 126){
			$masks = substr($text, 4, 4);
			$data = substr($text, 8);
		}
		elseif($length == 127){
			$masks = substr($text, 10, 4);
			$data = substr($text, 14);
		}
		else{
			$masks = substr($text, 2, 4);
			$data = substr($text, 6);
		}
		$text = ''; $n = strlen($data);
		for($i = 0; $i < $n; ++$i){
			$text .= $data[$i] ^ $masks[$i%4];
		}
		return $text;
	}

	/*
	/ 	Encode data for send to client.
	/
	*/
	private static function mask($text)
	{
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($text);
		
		if($length <= 125) $header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536) $header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536) $header = pack('CCNN', $b1, 127, $length);
		return $header.$text;
	}

	public static function emit($name, $data) {
		$array = array($name => $data);
		$text = self::mask(json_encode($array));
		$count = strlen($text);
		foreach(self::$clients as $uid => $client){
			@socket_write($client, $text, $count);
		}
	}
	public static function setData($data){
		self::$data = $data;
	}
	public static function on($name, $cb){
		if(isset(self::$data[$name])){
			call_user_func($cb, self::$data[$name]);
			unset(self::$data[$name]);
		}
	}
	public static function disconnect(){
		socket_close(self::$socket);
	}
}
