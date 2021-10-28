<?php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

include "ecdsa.php";
include "config.php";

// Check connection
if ($mysqli -> connect_errno) {
  echo "Failed to connect to MySQL: " . $mysqli->connect_error;
  exit();
}

$email_activation_code = "1234";

if($_GET["do"]=="get_profile"){
	$data = [];
	
	$id = isset($_GET["id"]) ? $_GET["id"] : "";
	$address = isset($_GET["address"]) ? $_GET["address"] : "";
	$json_post = json_decode( file_get_contents('php://input') );
	$hash = $json_post->hash;

	if($id !== "")
		$sql = "SELECT * FROM `profiles` where `id`='".$id."'";
	else
		$sql = "SELECT * FROM `profiles` where `address`='".$address."'";

	$result = $mysqli->query($sql);
	$row = $result->fetch_array(MYSQLI_ASSOC);
	
	if($result->num_rows == 1){
		$data["result"] = true;
		$data["id"] = $row["id"];
		$data["email"] = $row["email"];
		$data["content"] = $row["content"];
		$data["address"] = $row["address"];
		$data["hash"] = $hash;
		$data["picture_hash"] = hash_file('md5', $upload_dir.$data["address"].".png");
		
		$to_hash = array(
			"id"=> $data["id"],
			"content" => $data["content"],
			"email" => $data["email"],
			"profile_picture" => $data["picture_hash"]
		);
		$data["verified"] = hash_verify($to_hash, $hash);
	}
	else{
		$data["result"] = false;
	}

	http_response_code(200);
	echo (json_encode($data));
}

if($_GET["do"]=="hash"){
	$content = isset($_GET["content"]) ? $_GET["content"] : "";
	$data["hash"] = hash_value(array("value" => $content));
	http_response_code(200);
	echo (json_encode($data));	
}

if($_GET["do"]=="verify_hash"){
	$content = isset($_GET["content"]) ? $_GET["content"] : "";
	$signature = isset($_GET["signature"]) ? $_GET["signature"] : "";
	$data["verify"] = hash_verify(array("value" => $content), $signature);
	http_response_code(200);
	echo (json_encode($data));	
}

if($_GET["do"]=="update"){
	$data = [];

	$address = isset($_GET["address"]) ? $_GET["address"] : "";
	$id = isset($_GET["id"]) ? $_GET["id"] : "";
	$email = isset($_GET["email"]) ? $_GET["email"] : "";

	$json_post = json_decode( file_get_contents('php://input') );
	$content = $json_post->content;

	$to_hash = array(
		"id"=> $id,
		"content" => $content,
		"email" => $email,
		"profile_picture" => hash_file('md5', $upload_dir.$address.".png")
	);
	$hash = "";

	$sql = "SELECT * FROM `profiles` where `address`='".$address."'";
	$result = $mysqli->query($sql);

	if($result->num_rows == 1){
		$row = $result->fetch_array(MYSQLI_ASSOC);

		$id = $id != "" ? $id : $row["id"];
		$email = $email != "" ? $email : $row["email"];
		$content = $content != "" ? $content : $row["content"];
		$profile_picture_hash = hash_file('md5', $upload_dir.$address.".png");
		
		$sql = "Update `profiles` set `id`='".$id."', `email` = '".$email."', `content` = '".$content."', `profile_picture_hash` = '".$profile_picture_hash."' where `address`='".$address."'";
		$result = $mysqli->query($sql);
		$data["result"] = $result;
		$data["hash"] = $hash;
	}
	else{
		$sql = "Insert into `profiles` (`id`, `hash`, `content`, `address`, `email`, `phone`, `profile_picture_hash`) 
		values ('".$id."', '".$hash."', '".$content."', '".$address."', '".$email."', 0, '".hash_file('md5', $upload_dir.$address.".png")."')";
		$result = $mysqli->query($sql);
		$data["result"] = $result;
		$data["hash"] = $hash;
	}

	http_response_code(200);
	echo (json_encode($data));
}


if($_GET["do"]=="get_hash"){
	$data = [];

	$address = isset($_GET["address"]) ? $_GET["address"] : "";
	$id = isset($_GET["id"]) ? $_GET["id"] : "";
	$email = isset($_GET["email"]) ? $_GET["email"] : "";
	$json_post = json_decode( file_get_contents('php://input') );
	$content = $json_post->content;
	
	$to_hash = array(
		"id"=> $id,
		"content" => $content,
		"email" => $email,
		"profile_picture" => hash_file('md5', $upload_dir.$address.".png")
	);
	$hash = hash_value($to_hash);

	$data["result"] = true;
	$data["hash"] = $hash;
	$data["picture_hash"] = hash_file('md5', $upload_dir.$address.".png");

	http_response_code(200);
	echo (json_encode($data));
}


if($_GET["do"]=="get_hash_for_user"){
	$data = [];

	$address = isset($_GET["address"]) ? $_GET["address"] : "";
	$content = isset($_POST["content"]) ? $_POST["content"] : "";
	
	$sql = "SELECT * FROM `profiles` where `address`='".$address."'";
	$result = $mysqli->query($sql);

	if($result->num_rows == 1){
		$row = $result->fetch_array(MYSQLI_ASSOC);	
		$to_hash = array(
			"id"=> $row["id"],
			"content" => $content,
			"email" => $row["email"],
			"profile_picture" => hash_file('md5', $upload_dir.$address.".png")
		);
		$hash = hash_value($to_hash);
	}

	$data["result"] = true;
	$data["hash"] = $hash;

	http_response_code(200);
	echo (json_encode($data));
}

if($_GET["do"]=="verify_email"){
	$data = [];

	$code = isset($_GET["code"]) ? $_GET["code"] : "";
	$email = isset($_GET["email"]) ? $_GET["email"] : "";

	//$sql = "SELECT * FROM `profiles` where `email`='".$email."' and `email_verificatiom_code`='".$code."'";
	//$result = $mysqli->query($sql);

	if($code == $email_activation_code){
		$data["result"] = true;		
	}
	else{
		$data["result"] = false;
	}

	http_response_code(200);
	echo (json_encode($data));
}

// deprecated
if($_GET["do"]=="check_email"){ 
	$data = [];

	$email = isset($_GET["email"]) ? $_GET["email"] : "";

	$sql = "SELECT * FROM `profiles` where `email`='".$email."'";
	$result = $mysqli->query($sql);

	if($result->num_rows == 1){
		$data["result"] = false;		
	}
	else{
		$data["result"] = true;
		$to = $_GET["email"];
		$subject = "Serapeum :: Activation Code";
		$txt = $email_activation_code;
		$headers = "From: webmaster@serapeum.io";

		mail($to,$subject,$txt,$headers);
	}

	http_response_code(200);
	echo (json_encode($data));
}

if($_GET["do"]=="check_id"){
	$data = [];

	$id = isset($_GET["id"]) ? $_GET["id"] : "";

	$sql = "SELECT * FROM `profiles` where `id`='".$id."'";
	$result = $mysqli->query($sql);

	if($result->num_rows == 1){
		$data["result"] = false;		
	}
	else{
		$data["result"] = true;
	}

	http_response_code(200);
	echo (json_encode($data));
}

if($_GET["do"]=="search"){
	$data = [];

	$value = isset($_GET["value"]) ? $_GET["value"] : "";

	$sql = "SELECT * FROM `profiles` where `id` like '%".$value."%' or `email` like '%".$value."%' or `address` like '%".$value."%' or `content` like '%".$value."%'";
	$query = $mysqli->query($sql);


	$i=0;
	while($row = mysqli_fetch_array($query)){
		$data[$i]["id"] = $row["id"];
		$data[$i]["email"] = $row["email"];
		$data[$i]["content"] = $row["content"];
		$data[$i]["address"] = $row["address"];
		$i++;
	}
	

	http_response_code(200);
	echo (json_encode($data));
}

function hash_value($content){
	global $privateKey_string;
	$sign = new Sign($privateKey_string);
	return $sign->signJSON($content);
}

function hash_verify($content,$signature){
	global $privateKey_string;
	$sign = new Sign($privateKey_string);
	return $sign->verify($content, $signature);
}
?>