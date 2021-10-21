<?php
require_once('vendor/autoload.php');
include "config.php";

class Sign{
	private $privateKey;
	private $publicKey;
	
	private $privateKey_string = "";

	function __construct($privateKey_string){
		# Generate privateKey from PEM string
		$this->privateKey = EllipticCurve\PrivateKey::fromPem($privateKey_string);

		# To double check if message matches the signature
		$this->publicKey = EllipticCurve\PublicKey::fromPem("
		-----BEGIN PUBLIC KEY-----
		MFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEMCg/+PCT2nL+p1xXZpB7kMybjf1EHgq/
		RSx/RpDRUjtX7YrYxTHDpAohUAQFUk3nzAXpnJ3nA8KPYJ41xev5yw==
		-----END PUBLIC KEY-----
		");	
	}


	function signJSON($message){
		$message = json_encode($message, JSON_PRETTY_PRINT);

		$signature = EllipticCurve\Ecdsa::sign($message, $this->privateKey);

		# Generate Signature in base64. This result can be sent to Stark Bank in header as Digital-Signature parameter
		return $signature->toBase64();
	}


	function verify($message, $signature){
		$message = json_encode($message, JSON_PRETTY_PRINT);
		$signature2 = EllipticCurve\Signature::fromBase64($signature);
		return EllipticCurve\Ecdsa::verify($message, $signature2, $this->publicKey);
	}
}
?>