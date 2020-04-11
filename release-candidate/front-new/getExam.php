<?php

	//GET----------------
	$url = 'https://web.njit.edu/~asc8/cs490/exam.php';
	$data = file_get_contents('php://input');

	$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

	$response = curl_exec($curl);

	header('Content-type: application/json');
	echo $response;

	if($response == FALSE)
	{
		echo "Curl Error";
	}

	curl_close($curl);

?>