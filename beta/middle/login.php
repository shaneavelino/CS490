<?php

/** Login controller
 * Handles POST request from the front-end to login a user
 * Will return based on cURL to backend
 * Student or Instructor
 */
  /** initialize response body into an associative array */
  $user_response = array('dbAuthenticated' => '', 'role' => '');
  
  /** decode the incoming login request into an associative array */
  $db_post = file_get_contents('php://input');
  $db_json = json_decode($db_post, true);
  
  /** check if incoming php is properly defined */
  if (isset($db_json['user']) && isset($db_json['password'])) {

    /** cURL to db script */
    $db_endpoint = 'https://web.njit.edu/~tg253/490/userservice.php';
    $db_response = curl_post_request($db_endpoint, $db_post);
    // for validating the response
    $db_validation = json_decode($db_response, true);

    /** set up validation to send back to front end */
    if ($db_validation['ValidUser'] == 'true') {
      $user_response['dbAuthenticated'] = 'true';
      $user_response['role'] = $db_validation['role'];
    } else {
      $user_response['dbAuthenticated'] = 'false';
    }

    header('Content-type: application/json');
    echo json_encode($user_response);

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