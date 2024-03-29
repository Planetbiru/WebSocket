<?php

namespace WebSocket\PicoWebSocket;

class WSConnection
{
	private $socket;
	private $remoteAddress = '';
	private $remotePort = 0;
	private $headers = array();
	private $cookies = array();
	private $sessions = array();
	private $sessionSavePath = '/';
	private $sessionFilePrefix = 'sess_';
	private $sessionCookieName = 'PHPSESSID';
	private $sessionId = '';
	private $resourceId = 0;
	private $username = '';
	private $httpVersion = '';
	private $method = '';
	private $uri = '';
	private $host = '';
	private $path = '';
	private $query = array();
	private $clientData = array();

	private $obj = null;
	private $loginCallback = null;
	
	public function setLoginCallback($loginCallback)
	{
		$this->loginCallback = $loginCallback;
		return $this;
	}
	
	public function __construct($resourceId, $socket, $headers, $sessionCookieName = 'PHPSESSID', $sessionSavePath = null, $sessionFilePrefix = 'sess_', $obj = null)
	{
		$this->resourceId = $resourceId;
		$this->socket = $socket;
		if($sessionSavePath === null)
		{
			$this->sessionSavePath = session_save_path();
		}
		else
		{
			$this->sessionSavePath = $sessionSavePath;
		}
		if($sessionCookieName !== null)
		{
			$this->sessionCookieName = $sessionCookieName;
		}
		if($sessionFilePrefix !== null)
		{
			$this->sessionFilePrefix = $sessionFilePrefix;
		}
		$headerInfo = WSUtility::parseHeaders($headers);
		$this->headers = $headerInfo['headers'];
		$this->method = $headerInfo['method'];
		$this->uri = $headerInfo['uri'];
		$this->path = $headerInfo['path'];
		$this->query = $headerInfo['query'];
		$this->httpVersion = $headerInfo['version'];
		$this->obj = $obj;
		
		if(isset($this->headers['x-forwarded-host']))
		{
			$host = $this->headers['x-forwarded-host'];
		}
		else if(isset($this->headers['x-forwarded-server']))
		{
			$host = $this->headers['x-forwarded-server'];
		}
		else
		{
			$host = $this->headers['host'];
		}
		if(stripos($host, ":") !== false)
		{
			$arrHost = explode(":", $host);
			$host = $arrHost[0];
			$port = $arrHost[1];
		}
		else
		{
			$port = "443";
		}
		$this->host = $host;
		$this->performHandshaking($headers, $host, $port);
		
		$this->updateSessionData();
	}

	public function updateSessionData()
	{
		if(isset($this->headers['cookie']))
		{
			$this->cookies = WSUtility::parseCookie($this->headers['cookie']);
		}
		if(isset($this->cookies[$this->sessionCookieName]))
		{
			$this->sessionId = $this->cookies[$this->sessionCookieName];
			$this->sessions = WSUtility::getSessions($this->getSessionId(), $this->getSessionSavePath(), $this->getSessionFilePrefix());
			$this->clientData = call_user_func(array($this->obj, $this->loginCallback), $this); 
		}

		
	}
	
	/**
	 * Close client connection
	 *
	 * @return void
	 */
	public function close()
	{
		socket_close($this->socket);
	}

	/**
	 * Send message to client
	 *
	 * @param string $message
	 * @return void
	 */
	public function send($message)
	{
		$maskedMessage = WSUtility::mask($message);
		@socket_write($this->socket, $maskedMessage, strlen($maskedMessage));
	}
	/**
	 * Handshake new client
	 * @param string $recevedHeader Request header sent by the client
	 * @param string $host Host name of the websocket server
	 * @param integer $port Port number of the websocket server
	 */
	public function performHandshaking($recevedHeader, $host, $port)
	{
		$headers = array();
		$lines = preg_split("/\r\n/", $recevedHeader);
		foreach ($lines as $line) 
		{
			$line = chop($line);
			if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) 
			{
				$headers[$matches[1]] = $matches[2];
			}
		}
		if(isset($headers['Sec-WebSocket-Key']))
		{
			$secKey = $headers['Sec-WebSocket-Key'];
			$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
			//hand shaking header
			$upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" 
				. "Upgrade: websocket\r\n" 
				. "Connection: Upgrade\r\n" 
				. "WebSocket-Origin: $host\r\n" 
				. "WebSocket-Location: ws://$host:$port\r\n" 
				. "Sec-WebSocket-Accept: $secAccept\r\n"
				. "Access-Control-Allow-Origin: *\r\n"
				. "X-Engine: PicoWebSocket\r\n\r\n";
			socket_write($this->socket, $upgrade, strlen($upgrade));
		}
	}

	/**
	 * Login
	 *
	 * @return bool
	 */
	public function login()
	{
		return true;
	}

	/**
	 * Parse cookie
	 *
	 * @param string $cookieString
	 * @return array
	 */
	public function parseCookie($cookieString)
	{
		$cookie_data = array();
		$arr = explode("; ", $cookieString);
		foreach($arr as $val)
		{
			$arr2 = explode("=", $val, 2);
			$cookie_data[$arr2[0]] = $arr2[1];
		}
		return $cookie_data;
	}  

	/**
	 * Get host and path to identify how does client access this server
	 *
	 * @return string
	 */
	public function getHostAndPath()
	{
		return $this->host . $this->path;
	}

	

	/**
	 * Get the value of remoteAddress
	 */ 
	public function getRemoteAddress()
	{
		return $this->remoteAddress;
	}

	/**
	 * Set the value of remoteAddress
	 *
	 * @return  self
	 */ 
	public function setRemoteAddress($remoteAddress)
	{
		$this->remoteAddress = $remoteAddress;

		return $this;
	}

	/**
	 * Get the value of cookies
	 */ 
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * Set the value of cookies
	 *
	 * @return  self
	 */ 
	public function setCookies($cookies)
	{
		$this->cookies = $cookies;

		return $this;
	}

	/**
	 * Get the value of sessions
	 */ 
	public function getSessions()
	{
		return $this->sessions;
	}

	/**
	 * Set the value of sessions
	 *
	 * @return  self
	 */ 
	public function setSessions($sessions)
	{
		$this->sessions = $sessions;

		return $this;
	}

	/**
	 * Get the value of sessionSavePath
	 */ 
	public function getSessionSavePath()
	{
		return $this->sessionSavePath;
	}

	/**
	 * Set the value of sessionSavePath
	 *
	 * @return  self
	 */ 
	public function setSessionSavePath($sessionSavePath)
	{
		$this->sessionSavePath = $sessionSavePath;

		return $this;
	}

	/**
	 * Get the value of sessionFilePrefix
	 */ 
	public function getSessionFilePrefix()
	{
		return $this->sessionFilePrefix;
	}

	/**
	 * Set the value of sessionFilePrefix
	 *
	 * @return  self
	 */ 
	public function setSessionFilePrefix($sessionFilePrefix)
	{
		$this->sessionFilePrefix = $sessionFilePrefix;

		return $this;
	}

	/**
	 * Get the value of sessionId
	 */ 
	public function getSessionId()
	{
		return $this->sessionId;
	}

	/**
	 * Set the value of sessionId
	 *
	 * @return  self
	 */ 
	public function setSessionId($sessionId)
	{
		$this->sessionId = $sessionId;

		return $this;
	}

	/**
	 * Get the value of resourceId
	 */ 
	public function getResourceId()
	{
		return $this->resourceId;
	}

	/**
	 * Set the value of resourceId
	 *
	 * @return  self
	 */ 
	public function setResourceId($resourceId)
	{
		$this->resourceId = $resourceId;

		return $this;
	}

	/**
	 * Get the value of username
	 */ 
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * Set the value of username
	 *
	 * @return  self
	 */ 
	public function setUsername($username)
	{
		$this->username = $username;

		return $this;
	}

	/**
	 * Get the value of httpVersion
	 */ 
	public function getHttpVersion()
	{
		return $this->httpVersion;
	}

	/**
	 * Set the value of httpVersion
	 *
	 * @return  self
	 */ 
	public function setHttpVersion($httpVersion)
	{
		$this->httpVersion = $httpVersion;

		return $this;
	}

	/**
	 * Get the value of method
	 */ 
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Set the value of method
	 *
	 * @return  self
	 */ 
	public function setMethod($method)
	{
		$this->method = $method;

		return $this;
	}

	/**
	 * Get the value of uri
	 */ 
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * Set the value of uri
	 *
	 * @return  self
	 */ 
	public function setUri($uri)
	{
		$this->uri = $uri;

		return $this;
	}

	/**
	 * Get the value of query
	 */ 
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Set the value of query
	 *
	 * @return  self
	 */ 
	public function setQuery($query)
	{
		$this->query = $query;

		return $this;
	}

	/**
	 * Get the value of clientData
	 */ 
	public function getClientData()
	{
		return $this->clientData;
	}

	/**
	 * Set the value of clientData
	 *
	 * @return  self
	 */ 
	public function setClientData($clientData)
	{
		$this->clientData = $clientData;

		return $this;
	}

	/**
	 * Get the value of remotePort
	 */ 
	public function getRemotePort()
	{
		return $this->remotePort;
	}

	/**
	 * Set the value of remotePort
	 *
	 * @return  self
	 */ 
	public function setRemotePort($remotePort)
	{
		$this->remotePort = $remotePort;

		return $this;
	}

	/**
	 * Get the value of sessionCookieName
	 */ 
	public function getSessionCookieName()
	{
		return $this->sessionCookieName;
	}

	/**
	 * Set the value of sessionCookieName
	 *
	 * @return  self
	 */ 
	public function setSessionCookieName($sessionCookieName)
	{
		$this->sessionCookieName = $sessionCookieName;

		return $this;
	}
}
