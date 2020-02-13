<?php

  // read in a POST request
  $incoming_post_request = file_get_contents('php://input');

  // Response to send back to front end
  $response = array("njitAuthenticated" => "false", "dbAuthenticated" => "false");

  // decode the json object into a php array
  $login_json_object = json_decode($incoming_post_request, true);

  $njit_endpoint = 'https://myhub.njit.edu/vrs/ldapAuthenticateServlet';
  $db_endpoint = 'https://web.njit.edu/~tg253/userservice.php';
  
  // cURL the incoming POST request to the backend database
  $db_response = curl_request($db_endpoint, $incoming_post_request);
  $db_validation = json_decode($db_response, true);

  // cURL the incoming POST request to an NJIT login server
  $njit_response = curl_request($njit_endpoint, $incoming_post_request);
  $njit_validation = json_decode($njit_response, true);
  echo $njit_response;

  // params:
  // $url: endpoint to curl
  // $body: json object to post
  function curl_request($url, $body) {
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
  
  if ($db_validation['ValidUser'] == "true") {
    $response["dbAuthenticated"] = "true";
  } else {
    $response["dbAuthenticated"] = "false";
  }

  // echo $db_response;
  echo json_encode($response);


  // check if response authenticates the incoming request from the front end
  // if db response of validuser is true set $response['njitAuthenticated'] to true
  // else then set it to false
  //echo json_encode($response);

?>