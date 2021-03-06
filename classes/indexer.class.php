<?php

interface Indexer {
	public function getResults($term);
	public function addMessage($boardid, Message $message);
	public function removeMessage($boardid, Message $message);
}

abstract class AbstractIndexer implements Indexer {
	private function formatToken($value) {
		return preg_replace('/[^a-zA-Z0-9]/', ' ', strtolower($value));
	}

	public function getResults($term) {
		$tokens = array(); $quotes = array(); $currentPrefix = null; $currentToken = "";
		for ($i = 0; $i < strlen($term); $i++) {
			$char = substr($term, $i, 1);
			if (count($quotes) > 0 && $quotes[0] == $char) {
				array_shift($quotes);
			} else if (in_array($char, array("'", '"'))) {
				array_unshift($quotes, $char);
			} else if (in_array($char, array(":")) && count($quotes) == 0 && $currentPrefix === null) {
				$currentPrefix = $currentToken;
				$currentToken = "";
			} else if (in_array($char, array(" ", "\t")) && count($quotes) == 0) {
				foreach (explode(" ", $this->formatToken($currentToken)) as $tokenPart) {
					if ($tokenPart != "") {
						$tokens[] = ($currentPrefix != null ? array($currentPrefix, $tokenPart) : array($tokenPart));
					}
				}
				$currentPrefix = null; $currentToken = "";
			} else {
				$currentToken .= $char;
			}
		}
		foreach (explode(" ", $this->formatToken($currentToken)) as $tokenPart) {
			if ($tokenPart != "") {
				$tokens[] = ($currentPrefix != null ? array($currentPrefix, $tokenPart) : array($tokenPart));
			}
		}

		return $this->search($tokens);
	}

	abstract protected function search($tokens);

	public function addMessage($boardid, Message $message) {
		$this->addTerm("boardid", $boardid, $boardid, $message->getMessageID());
		$this->addTerm("messageid", $message->getMessageID(), $boardid, $message->getMessageID());

		$this->addField("author", $message->getAuthor()->getName(), $boardid, $message->getMessageID());
		$this->addField("author", $message->getAuthor()->getAddress(), $boardid, $message->getMessageID());
		$this->addField("author", $message->getAuthor()->getComment(), $boardid, $message->getMessageID());
		$this->addField("subject", $message->getSubject(), $boardid, $message->getMessageID());
		$this->addField("body", $message->getTextBody(), $boardid, $message->getMessageID());
		$this->addField("body", $message->getHTMLBody(), $boardid, $message->getMessageID());
		$this->addField("signature", $message->getSignature(), $boardid, $message->getMessageID());
	}

	private function addField($field, $value, $boardid, $messageid) {
		$value = $this->formatToken($value);
		$terms = preg_split('/\\s+/', $value);
		foreach ($terms as $term) {
			if ($term != "") {
				$this->addTerm($field, $term, $boardid, $messageid);
			}
		}
	}

	abstract protected function addTerm($field, $term, $boardid, $messageid);
}

class IndexerResult {
	private $boardid;
	private $messageid;

	public function __construct($boardid, $messageid) {
		$this->boardid = $boardid;
		$this->messageid = $messageid;
	}

	public function getBoardID() {
		return $this->boardid;
	}

	public function getMessageID() {
		return $this->messageid;
	}
}

?>
