<?php

require_once('./controller.php');
require_once('./constants.php');

/** initialize response body into an associative array */
$response = array('dbAuthenticated' => 'false', 'role' => '');

/** decode the incoming login request into an associative array */
$db_post = file_get_contents('php://input');
echo $db_post;
$db_json = json_decode($db_post, true);

/** check if incoming php is properly formatted */
if (isset($db_json['user']) && isset($db_json['password'])) {

  // cURL to Tom's db script
  $db_endpoint = USER_URL;

  /** create an instance of controller for curl request */
  $controller = new Controller();
  $controller->setUrl($db_endpoint);
  $controller->setBody($db_post);

  // curl the backend
  $curl = $controller->curl_post_request($controller->getUrl(), $controller->getBody()); 
  $db_validation = json_decode($curl, true);

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

?>