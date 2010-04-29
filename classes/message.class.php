<?php

require_once(dirname(__FILE__)."/bodypart.class.php");

if (!function_exists("quoted_printable_encode")) {
	// aus http://de.php.net/quoted_printable_decode
	function quoted_printable_encode($string) {
		$string = str_replace(array('%20', '%0D%0A', '%'), array(' ', "\r\n", '='), rawurlencode($string));
		$string = preg_replace('/[^\r\n]{73}[^=\r\n]{2}/', "$0=\r\n", $string);
		return $string;
	}
}

class Message {
	private $articlenum;
	private $messageid;
	private $threadid;
	private $parentid = null;
	private $parentartnum = null;
	private $charset = "UTF-8";
	private $subject;
	private $date;
	private $author;
	private $mime = null;
	private $parts = array();
	private $childs = array();
	
	private $group;
	
	public function __construct($group, $articlenum, $messageid, $date, $author, $subject, $charset, $threadid, $parentid, $parentartnum) {
		$this->group = $group;
		$this->articlenum = $articlenum;
		$this->messageid = $messageid;
		$this->date = $date;
		$this->author = $author;
		$this->subject = $subject;
		$this->charset = $charset;
		$this->threadid = $threadid;
		$this->parentid = $parentid;
		$this->parentartnum = $parentartnum;
		$this->mime = $mime;
	}
	
	public function getArticleNum() {
		return $this->articlenum;
	}
	
	public function getMessageID() {
		return $this->messageid;
	}
	
	public function getThreadID() {
		return $this->threadid !== null ? $this->threadid : $this->getArticleNum();
	}

	public function hasParent() {
		return $this->parentid !== null;
	}

	public function getParentID() {
		if (! $this->hasParent()) {
			return null;
		}
		return $this->parentid;
	}

	public function getParentArtNum() {
		if (! $this->hasParent()) {
			return null;
		}
		return $this->parentartnum;
	}

	public function getSubject($charset = null) {
		if ($charset !== null) {
			return iconv($this->getCharset(), $charset, $this->getSubject());
		}
		return $this->subject;
	}

	public function getDate() {
		return $this->date;
	}

	public function getAuthor($charset = null) {
		if ($charset !== null) {
			return iconv($this->getCharset(), $charset, $this->getAuthor());
		}
		return $this->author;
	}
	
	public function getCharset() {
		return $this->charset;
	}
	
	public function addBodyPart(BodyPart $bodypart) {
		if ($bodypart->getMessageID() != $this->getMessageID()) {
			throw new Exception("MessageID not matching while trying to add a bodypart");
		}
		$this->parts[$bodypart->getPartID()] = $bodypart;
	}
	
	public function getBodyParts() {
		return $this->parts;
	}
	
	public function setBodyParts($parts) {
		$this->parts = $parts;
	}
	
	public function getBodyPart($i) {
		return $this->parts[$i];
	}
	
	public function saveAttachments($datadir) {
		/* Speichere alle Attachments ab */
		foreach ($this->parts AS $partid => &$part) {
			if ($part->isAttachment()) {
				$filename = $datadir->getAttachmentPath($this->group, $part);
				if (!file_exists($filename)) {
					$part->saveAsFile($filename);
				}
			}
		}
	}
	
	public function isMime() {
		return ($this->mime !== null);
	}

	public function getMimeType() {
		return $this->mime;
	}

	public function getChilds() {
		return array_keys($this->childs);
	}
	
	public function addChild($msg) {
		$this->childs[$msg->getMessageID()] = true;
	}
	
	public function removeChild($msg) {
		unset($this->childs[$msg->getMessageID()]);
	}
	
	public function getGroup() {
		return $this->group;
	}

	// TODO methode umlagern (zur NNTPConnection ?)
	public function getPlain($charset = null) {
		if ($charset === null) {
			$charset = $this->getCharset();
		}
		
		$crlf = "\r\n";
		
		/**
		 * Wichtig: In den Headern darf nur 7-bit-Codierung genutzt werden.
		 * Alles andere muss Codiert werden (vgl. mb_encode_mimeheader() )
		 **/
		mb_internal_encoding($charset);
		
		/* Standart-Header */
		$data  = "Message-ID: " . $this->getMessageID() . $crlf;
		$data .= "From: " . $this->getAuthor()->getIMFString() . $crlf;
		$data .= "Date: " . date("r", $this->getDate()) . $crlf;
		$data .= "Subject: " . mb_encode_mimeheader($this->getSubject($charset), $charset) . $crlf;
		$data .= "Newsgroups: " . $this->getGroup() . $crlf;
		if ($this->hasParent()) {
			$data .= "References: " . $this->getParentID() . $crlf;
		}
		$data .= "User-Agent: " . "NNTPBoard" . $crlf;
		if ($this->isMime()) {
			/* MIME-Header */
			// Generiere den Boundary - er sollte _nicht_ im Text vorkommen
			$boundary = "--" . md5(uniqid());
			$data .= "Content-Type: multipart/" . $this->getMimeType() . "; boundary=\"" . addcslashes($boundary, "\"") . "\"" . $crlf;
			$data .= $crlf;
			$data .= "This is a MIME-Message." . $crlf;

			$parts = $this->getBodyParts();
		} else {
			// Sicherstellen, dass wir nur einen BodyPart fuer Nicht-MIME-Nachrichten haben
			$parts = array( array_shift($this->getBodyParts()) );
		}
		
		$disposition = false;
		foreach ($parts AS $part) {
			// MIME-Boundary nur, wenn die Nachricht MIME ist
			if ($this->isMime()) {
				$data .= "--" . $boundary . $crlf;
			}
			/* Ab hier Content-Header einbringen */
			$data .= "Content-Type: " . $part->getMimeType() . "; Charset=\"" . addcslashes($charset, "\"") . "\"" . $crlf;
			// Der Erste Abschnitt kriegt keinen Disposition-Header (Haupttext)
			if ($disposition) {
				$data .= "Content-Disposition: " . $part->getDisposition() . ($part->hasFilename() ? "; filename=\"".addcslashes($part->getFilename(), "\"")."\"" : "") . $crlf;
				$disposition = true;
			}
			/* Waehle das Encoding aus - Base64 geht immer, aber fuer Text ist quoted-printable doch schoener */
			$encoding = "base64";
			if ($part->isText()) {
				$encoding = "quoted-printable";
			}
			$data .= "Content-Transfer-Encoding: " . $encoding . $crlf;
			$data .= $crlf;

			/* Body */
			$body = $part->getText($charset);
			switch ($encoding) {
			case "base64":
				$body = chunk_split(base64_encode($body), 76, $crlf);
				break;
			case "quoted-printable":
				$body = quoted_printable_encode($body);
				break;
			case "7bit":
			case "8bit":
			case "binary":
				// Do nothing!
				break;
			}
			
			$data .= rtrim($body, $crlf) . $crlf;
			$data .= $crlf;
		}
		// MIME-Abschluss einbringen
		if ($this->isMime()) {
			$data .= "--" . $boundary . "--" . $crlf;
		}

		return $data;
	}
}

?>
