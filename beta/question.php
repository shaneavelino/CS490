<?php

require_once('./controller.php');
require_once('./constants.php');

/** initialize response body into an associative array */
$response = array('validInsert' => 'false');

/** decode the incoming question request into an associative array */
$post = file_get_contents('php://input');
$json = json_decode($post, true);

/** set the JSON variable names */
$name = isset($json['name']);
$desc = isset($json['description']);
$diff = isset($json['difficulty']);
$cate = isset($json['category']);
$test = isset($json['testCases']);

/** check if incoming php is properly formatted */
if ($name && $desc && $diff && $cate && $test) {
    // cURL to Tom's db script
    $endpoint = QUESTION_URL;

    /** create an instance of controller for curl request */
    $controller = new Controller();
    $controller->setUrl($endpoint);
    $controller->setBody($post);

    // curl the backend with url and body as parameters
    $curl = $controller->curl_post_request($controller->getUrl(), $controller->getBody());

    // TODO: set up validation to send back to front end
    // check if the insert was valid to the table

    header('Content-Type: application/json');
    echo json_encode($response);

} else {
    echo 'POST error: fields \'name\', \'description\', \'difficulty\', \'category\', and \'testCases\' were not properly passed.';
}

?>