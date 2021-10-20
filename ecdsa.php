<?php
require_once('vendor/autoload.php');

class Sign{
	private $privateKey;
	private $publicKey;
	
	function __construct(){
		# Generate privateKey from PEM string
		$this->privateKey = EllipticCurve\PrivateKey::fromPem("
		-----BEGIN EC PARAMETERS-----
		BgUrgQQACg==
		-----END EC PARAMETERS-----
		-----BEGIN EC PRIVATE KEY-----
		MHQCAQEEIEaC/MS3PZm3jyMd8wz7QoejmqF8gqbX5lriHhpzXmHToAcGBSuBBAAK
		oUQDQgAEMCg/+PCT2nL+p1xXZpB7kMybjf1EHgq/RSx/RpDRUjtX7YrYxTHDpAoh
		UAQFUk3nzAXpnJ3nA8KPYJ41xev5yw==
		-----END EC PRIVATE KEY-----
		");

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