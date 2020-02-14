<?php

  /** initialize response body into an associative array */
  $response = array('njitAuthenticated' => 'false', 'dbAuthenticated' => 'false');
  
  /** decode the incoming login request into an associative array */
  $db_post = file_get_contents('php://input');
  $db_json = json_decode($db_post, true);

  /** spoof njit html text body */
  $njit_post = http_build_query(array('user_name' => $db_json['user'], 'passwd' => $db_json['password'], 'SUBMIT' => 'Login'));

  /** cURL to db script */
  $db_endpoint = 'https://web.njit.edu/~tg253/userservice.php';
  $db_response = curl_post_request($db_endpoint, $db_post);
  $db_validation = json_decode($db_response, true);
  
  // cURL the incoming POST request to an NJIT login server
  $njit_endpoint = 'https://myhub.njit.edu/vrs/ldapAuthenticateServlet';
  $njit_response = curl_post_request($njit_endpoint, $njit_post);
  $njit_string = html_entity_decode($njit_response);
  // no need to validate because njit response is in HTML for a bad login $njit_validation = json_decode($njit_response, true);

    /** set up validation to send back to front end */
  if ($db_validation['ValidUser'] == 'true') {
    $response['dbAuthenticated'] = 'true';
  } else {
    $response['dbAuthenticated'] = 'false';
  }

  /** set up njit validation to send back to front end */
  $missing_creds = 'Please';
  $bad_creds = 'Invalid';
  if (strpos($njit_string, $bad_creds) || strpos($njit_string, $missing_creds)) {
    $response['njitAuthenticated'] = 'false';
  } else {
    $response['njitAuthenticated'] = 'true';
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

  echo json_encode($response);

?>