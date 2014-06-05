<?php
/*~ class.pop3.php - POP3 message retrieval & decomposition
.---------------------------------------------------------------------------.
|  Software: N3O CMS                                                        |
|   Version: 2.0.0                                                          |
|   Contact: contact author (also www.kristan-sp.si/blazk)                  |
| ------------------------------------------------------------------------- |
|    Author: Blaž Kristan (blaz@kristan-sp.si)                              |
| Copyright (c) 2000-2010, Blaž Kristan. All Rights Reserved.               |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/

/**
 * POP3 Access Class
 * Version 1.0.0
 *
 * Author: Blaz Kristan (blaz@kristan-sp.si)
 * Modifications: -
 *
 * This class is based on the IMAP PHP extensions, so they are required.
 *
 * This class is rfc 1939 compliant and implements all the commands
 * required for POP3 connection, authentication and disconnection.
 *
 * @package N3O CMS
 * @author Blaz Kristan
 */

class POP3 {
	/**
	* POP3 Carriage Return + Line Feed
	* @var string
	*/
	public $CRLF = "\r\n";

	/**
	* Displaying Debug warnings? (0 = no, 1+ = yes)
	* @var int
	*/
	public $do_debug = 0;

	/**
	* POP3 Mail Server
	* @var string
	*/
	public $host = 'localhost';

	/**
	* POP3 Port
	* @var int
	*/
	public $port = 110;

	/**
	* SSL indicator
	* @var bool
	*/
	public $ssl = false;

	/**
	* POP3 Username
	* @var string
	*/
	public $username;

	/**
	* POP3 Password
	* @var string
	*/
	public $password;

	/**
	* Sets the POP3 PHPMailer Version number
	* @var string
	*/
	public $Version         = '1.0.0';

/////////////////////////////////////////////////
// PROPERTIES, PRIVATE AND PROTECTED
/////////////////////////////////////////////////

	private $pop_conn;  // IMAP connection stream
	private $connected; // bool connection indicator
	private $error;     // Error log array

/////////////////////////////////////////////////
//  Public Methods
/////////////////////////////////////////////////

	/**
	* Constructor, sets the initial values
	* @access public
	* @return POP3
	*/
	public function __construct() {
		$this->pop_conn  = null;
		$this->connected = false;
		$this->error     = null;
	}

	/**
	* Connect to the POP3 server
	* @access public
	* @param string $host
	* @param integer $port
	* @param integer $tval
	* @return boolean
	*/
	public function Connect( $username = '', $password = '', $host = '', $port = false, $ssl = false ) {
		//  Are we already connected?
		if ( $this->connected ) {
			return true;
		}

		if ( empty($host) ) {
			$host = $this->host;
		}
		
		if ( !$port ) {
			$port = $this->port;
		}

		if ( empty($username) ) {
			$username = $this->username;
		}

		if ( empty($password) ) {
			$password = $this->password;
		}

		set_error_handler( array(&$this, 'catchWarning') );

		//  Connect to the POP3 server
		try {
			$ssl = ($ssl==false) ? "" : "/ssl/novalidate-cert";
		    $this->pop_conn = @imap_open( "{".$host.":".$port."/pop3".$ssl."}INBOX",
		    	$username,
		    	$password
			);
		} catch (Exception $e) {
			$this->pop_conn = false;
			$this->connected = false;
			$this->error = "Failed to connect to server $host on port $port";
		}

		//  Restore the error handler
		restore_error_handler();

		//  Does the Error Log now contain anything?
		if ( $this->error && $this->do_debug >= 1 ) {
			$this->displayErrors();
		}

		//  Did we connect?
		if ( $this->pop_conn == false ) {
			//  It would appear not...
			$this->error = "Failed to connect to server $host on port $port";

			if ( $this->do_debug >= 1 ) {
				$this->displayErrors();
			}

			$this->connected = false;
			return false;
		}

		//  The connection is established and the POP3 server is talking
		$this->connected = true;
		return true;

	}

	/**
	* Disconnect from the POP3 server
	* @access public
	*/
	public function Disconnect() {
		//  Are we already connected?
		if ( !$this->connected ) {
			return true;
		}
		
		imap_expunge( $this->pop_conn );
		$res = imap_close( $this->pop_conn );

		$this->connected = true;
		$this->pop_conn = null;
		
		return $res;
	}

	/**
	* Get STAT from the POP3 server
	* @access public
	* @return array
	*/
	public function Stat() {
	    if ( $this->connected == false ) {
			$this->error = 'Not connected to POP3 server';
			
			if ( $this->do_debug >= 1 ) {
				$this->displayErrors();
			}
			return false;
	    }
		
	    $check = imap_mailboxmsginfo( $this->pop_conn );
	    return ((array)$check);
	}

	/**
	* Retrieve a list of messages from the POP3 server
	* @access public
	* @param string $range ["X:Y"]
	* @return array
	*/
	public function GetMessageList( $range = '' ) {
		if ($this->connected == false) {
			$this->error = 'Not connected to POP3 server';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		if ( empty($range) ) {
			$MC = imap_check( $this->pop_conn );
			$range = "1:".$MC->Nmsgs;
		}
		
		$arr = array();
		$response = imap_fetch_overview( $this->pop_conn, $range );
		foreach ( $response as $msg )
			$arr[$msg->msgno] = (array)$msg;
		return $arr;
	}
	
	/**
	* Remove a message from the POP3 server
	* @access public
	* @param integer $messageid
	* @return boolean
	*/
	public function DeleteMessage( $messageid ) {
	    if ( $this->connected == false ) {
			$this->error = 'Not connected to POP3 server';
			
			if ( $this->do_debug >= 1 ) {
				$this->displayErrors();
			}
			return false;
	    }
		
		if ( !$messageid ) {
			$this->error = 'No messages selected for removal';
			
			if ( $this->do_debug >= 1 ) {
				$this->displayErrors();
			}
			return false;
		}

		return imap_delete( $this->pop_conn, $messageid );
	}

	/**
	* Retrieve message part from the POP3 server
	* @access public
	* @param integer $messageid
	* @return array
	*/
	public function GetMessage( $messageid ) {
		if ($this->connected == false) {
			$this->error = 'Not connected to POP3 server';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		if ( !$messageid ) {
			$this->error = 'No messages selected for retrieval';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		$arr = array();
		$hdr = $this->GetMessageHeaders( $messageid );
		foreach ( $hdr as $key => $item ) {
			$arr[$key] = $item;
		}
		$arr['Body'] = $this->GetMessageBody( $messageid );
		
		return $arr;
	}

	/**
	* Retrieve a message headers from the POP3 server
	* @access public
	* @param integer $messageid
	* @return string
	*/
	public function GetMessageHeaders( $messageid ) {
		if ($this->connected == false) {
			$this->error = 'Not connected to POP3 server';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		if ( !$messageid ) {
			$this->error = 'No messages selected for retrieval';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		// retireve a message headers
		return $this->parseHeaders( imap_fetchheader( $this->pop_conn, $messageid, FT_PREFETCHTEXT ) );
	}

	/**
	* Retrieve text message body from the POP3 server
	* @access public
	* @param integer $messageid
	* @return string
	*/
	public function GetMessageBody( $messageid ) {
		if ($this->connected == false) {
			$this->error = 'Not connected to POP3 server';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		if ( !$messageid ) {
			$this->error = 'No messages selected for retrieval';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		$mail = $this->mimeToArray( $messageid );
		$str = '';
		foreach ( $mail as $part ) {
			if ( isset($part['charset']) ) {
				$str .= $part['data'];
			}
		}
		
		return $str;
	}

	/**
	* Retrieve message attachments from the POP3 server
	* @access public
	* @param integer $messageid
	* @return array
	*/
	public function GetMessageAttachments( $messageid ) {
		if ($this->connected == false) {
			$this->error = 'Not connected to POP3 server';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		if ( !$messageid ) {
			$this->error = 'No messages selected for retrieval';
			
			if ($this->do_debug >= 1) {
				$this->displayErrors();
			}
			return null;
		}
		
		$mail = $this->mimeToArray( $messageid );
		$arr = array();
		foreach ( $mail as $part ) {
			if ( isset($part['is_attachment']) ) {
				$arr[] = array( 'filename' => $part['filename'], 'data' => $part['data'] );
			}
		}
		return $arr;
	}

/////////////////////////////////////////////////
//  Private Methods
/////////////////////////////////////////////////

	/**
	* Parses message headers into array
	* @access private
	* @param string $headers
	* @return array
	*/
	private function parseHeaders( $headers ) {
		$headers = preg_replace( '/\r\n\s+/m', '', $headers );
		preg_match_all('/([^: ]+): (.+?(?:\r\n\s(?:.+?))*)?\r\n/m', $headers, $matches);
		foreach ( $matches[1] as $key =>$value )
			$result[$value] = $matches[2][$key];
		return $result;
	}
	
	/**
	* Breaks message into array of parts (including headers)
	* @access private
	* @param integer $messageid
	* @return array
	*/
	private function mimeToArray( $messageid, $parse_headers = false ) {
		$mail = imap_fetchstructure( $this->pop_conn, $messageid );
		$mail = $this->getParts( $messageid, $mail, 0 );
		if ( $parse_headers )
			$mail[0]["parsed"] = $this->parseHeaders( $mail[0]["data"] );
		return $mail;
	}
	
	/**
	* Breaks message parts into array (recursive!)
	* @access private
	* @param integer $messageid
	* @param array $part
	* @param integer $prefix
	* @return array
	*/
	private function getParts( $messageid, $part, $prefix ) {    
		$attachments = array();
		$attachments[$prefix] = $this->decodePart( $messageid, $part, $prefix );
		
		if ( isset($part->parts) ) { // multipart
			$prefix = ($prefix == "0") ? "" : "$prefix.";
			foreach ( $part->parts as $number => $subpart) 
				$attachments = array_merge( $attachments, $this->getParts( $messageid, $subpart, $prefix.($number+1) ) );
		}
		return $attachments;
	}
	
	/**
	* Decodes a single message part
	* @access private
	* @param integer $messageid
	* @param array $part
	* @param integer $prefix
	* @return array
	*/
	private function decodePart( $messageid, $part, $prefix ) {
		$attachment = array();
	
		if ( $part->ifdparameters ) {
			foreach ( $part->dparameters as $object ) {
				$attachment[strtolower($object->attribute)] = $object->value;
				if ( strtolower($object->attribute) == 'filename' ) {
					$attachment['is_attachment'] = true;
					$attachment['filename'] = $object->value;
				}
			}
		}
	
		if ( $part->ifparameters ) {
			foreach ( $part->parameters as $object ) {
				$attachment[strtolower($object->attribute)] = $object->value;
				if ( strtolower($object->attribute) == 'name' ) {
					$attachment['is_attachment'] = true;
					$attachment['name'] = $object->value;
				}
			}
		}
	
		$attachment['data'] = imap_fetchbody( $this->pop_conn, $messageid, $prefix );
		if ( $part->encoding == 3 ) { // 3 = BASE64
			$attachment['data'] = base64_decode( $attachment['data'] );
		}
		elseif ( $part->encoding == 4 ) { // 4 = QUOTED-PRINTABLE
			$attachment['data'] = quoted_printable_decode( $attachment['data'] );
		}
		return $attachment;
	}

	/**
	* If debug is enabled, display the error message array
	* @access private
	*/
	private function displayErrors () {
		echo '<pre>';

		foreach ($this->error as $single_error) {
		  print_r($single_error);
		}

		echo '</pre>';
	}

	/**
	* Takes over from PHP for the socket warning handler
	* @access private
	* @param integer $errno
	* @param string $errstr
	* @param string $errfile
	* @param integer $errline
	*/
	private function catchWarning ($errno, $errstr, $errfile, $errline) {
		$this->error[] = array(
			'error' => "Connecting to the POP3 server raised a PHP warning: ",
			'errno' => $errno,
			'errstr' => $errstr
		);
	}

	//  End of class
}

/*
//  Connect
$conn = new POP3;
$conn->Connect('user', 'password', 'localhost', 110);

if ($conn) {
	echo "<pre>";
//	echo "<br>";
//	var_dump($conn->GetMessage(1));
//	var_dump($conn->GetMessageBody(1));
	var_dump($conn->GetMessageAttachments(1));
	echo "\n";
//	var_dump(imap_bodystruct($conn,1,1));
//	var_dump(imap_fetchbody($conn,1,1));
//	echo "\n";
//	var_dump(imap_fetchbody($conn,1,2));
	echo "</pre>";

	//  We need to disconnect
	$conn->Disconnect();

}
*/
?>