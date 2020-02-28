<?php

require_once('./controller.php');

/** create an instance of controller for curl request */
$controller = new Controller();
$curl = $controller->curl_post_request();

/** initialize response body into an associative array */
$response = array('dbAuthenticated' => 'false', 'role' => '');

/** decode the incoming login request into an associative array */
$db_post = file_get_contents('php://input');
$db_json = json_decode($db_post, true);

/** check if incoming php is properly formatted */
if (isset($db_json['user']) && isset($db_json['password'])) {

  // cURL to Tom's db script
  $db_endpoint = 'https://web.njit.edu/~tg253/490/userservice.php';
  $db_response = curl($db_endpoint, $db_post);
  $db_validation = json_decode($db_response, true);

  /** set up validation to send back to front end */
  if ($db_validation['ValidUser'] == 'true') {
    $response['dbAuthenticated'] = 'true';
    $response['role'] = $db_validation['role'];
  } else {
    $response['dbAuthenticated'] = 'false';
  }

  // return result as our 'response'
  header('Content-Type: application/json');
  echo json_encode($response);

} else {
  echo 'POST error: fields \'user\' and \'password\' were not properly passed.';
}

  /** params:
    * $url: endpoint to curl
    * $body: post json object
    * return: curl response
    */
  function curl_post_request($url, $body) {
    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_POST => 1,
      CURLOPT_POSTFIELDS => $body
    ));

    return curl_exec($ch);
    curl_close($ch);
  }

?>