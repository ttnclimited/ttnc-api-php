<?

class TTNCApi {

	private $Username = false;
	private $Password = false;
	private $VKey = false;

	public function __construct($Username = false, $Password = false, $VKey = false) {
		$this->Username = $Username;
		$this->Password = $Password;
		if($VKey) $this->VKey = $VKey;

		$this->Xml = new DOMDocument();
		$this->Xml->preserveWhiteSpace = true;
		$this->Xml->formatOutput = true;

		$this->NoveroRequest = $this->Xml->appendChild($this->Xml->createElement('NoveroRequest'));

		if($Username && $Password) $this->SessionRequest();
	}

	public function UseSession($SessionId) {
		unset($this->Requests['SessionRequest']);
		$SessionIdNode = $this->NoveroRequest->appendChild($this->Xml->createElement('SessionId'));
		$SessionIdNode->nodeValue = $SessionId;
	}

	public function SessionRequest() {
		$Request = $this->NewRequest('Auth', 'SessionLogin', 'SessionRequest');
		$Request->SetData('Username', $this->Username);
		$Request->SetData('Password', $this->Password);
		if($this->VKey) $Request->SetData('VKey', $this->VKey);
	}

	public function NewRequest($Target, $Name, $Id = false) {
		$Request = new TTNCRequest($this, $Target, $Name, $Id);
		$this->Requests[$Request->GetId()] = $Request;
		return $this->Requests[$Request->GetId()];
	}

	public function GetResponseFromId($Id) {
		foreach($this->Response->Get()->getElementsByTagName('Response') as $Response) {
			if($Response->getAttribute('RequestId') == $Id) return $this->_RequestToArray($this->_ElementToString($Response));
		}
	}

	public function MakeRequests() {

		foreach($this->Requests as $Request) {
			$this->NoveroRequest->appendChild($this->Xml->importNode($Request->Get(), true));
		}
		//var_dump($this->Xml->saveXML());
		$Context = stream_context_create(
							array(
								'http' => array(
										'method' => 'POST',
										'header'  => "Content-type: text/xml\r\n",
										'content' => $this->Xml->saveXML(),
										'timeout' => 120,
										'ignore_errors' => true
										)
								)
						);
		$Response = file_get_contents('https://xml.ttnc.co.uk/api/', false, $Context);
		//var_dump($Response);
		$this->Response = new TTNCResponse($Response);
	}

	private function _RequestToArray($Xml) {
		$Array = (is_array($Xml)) ? $Xml : json_decode(json_encode(new SimpleXMLElement($Xml, LIBXML_NOCDATA)), TRUE);
		foreach(array_slice($Array, 0) as $Key=>$Value) {
			if(empty($Value)) {
				$Array[$Key] = NULL;
			} elseif(is_array($Value) && $Key != '@attributes') {
				$Array[$Key] = $this->_RequestToArray($Value);
			}
		}
		return $Array;
	}

	private function _GetNodeValue($ValueName, $Location = false) {
		if(!$Location && is_object($this->Request)) $Location = $this->Request;
		return (is_object($Location->getElementsByTagName($ValueName)->item(0))) ? $Location->getElementsByTagName($ValueName)->item(0)->nodeValue : false;
	}

	private function _ElementToString(DOMElement $Element) {
		$Doc = new DOMDocument();
		$Cloned = $Element->cloneNode(true);
		$Doc->appendChild($Doc->importNode($Cloned, true));
		return $Doc->saveHTML();
	}
}

class TTNCRequest {
	public function __construct(TTNCApi &$API, $Target, $Name, $Id) {

		$this->API = &$API;

		$this->RequestId = ($Id) ? $Id : $this->GenerateRequestId();

		$this->Xml = new DOMDocument();
		$this->Xml->preserveWhiteSpace = true;
		$this->Xml->formatOutput = true;

		$this->Request = $this->Xml->appendChild($this->Xml->createElement('Request'));
		$this->Request->setAttribute('target', $Target);
		$this->Request->setAttribute('name', $Name);
		$this->Request->setAttribute('id', $this->RequestId);

	}

	public static function GenerateRequestId() {
		return sha1(uniqid());
	}

	public function SetData($Key, $Value) {
		$this->Data = $this->Request->appendChild($this->Xml->CreateElement($Key));
		$this->Data->nodeValue = htmlspecialchars($Value);
	}

	public function Get() {
		return $this->Request;
	}

	public function GetId() {
		return $this->RequestId;
	}

	public function GetResponse() {
		if(!isset($this->API->Response)) return false;
		return $this->API->GetResponseFromId($this->RequestId);
	}
}

class TTNCResponse {
	public function __construct($Response) {
		if(is_string($Response)) {
			$this->Xml = new DOMDocument();
			$this->Xml->preserveWhiteSpace = true;
			$this->Xml->formatOutput = true;
			$this->Xml->loadXML($Response);
		} elseif($Response instanceof DOMDocument) {
			$this->Xml = $Response;
		}
	}

	public function Get() {
		return $this->Xml;
	}
}

?>
