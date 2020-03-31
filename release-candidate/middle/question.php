<?php

/** Question.php
 * Written by: Avel Shane Coronado
 * CS490 Spring 2020
 * POST/GET requests to this endpoint will curl to the backend database
 *          in order to insert a question object into the question bank
*/

require_once('./controller.php');
require_once('./constants.php');

$response = array('questionInsertValid' => 'false');

$post = file_get_contents('php://input');
$json = json_decode($post, true);

$name = isset($json['name']);
$desc = isset($json['description']);
$diff = isset($json['difficulty']);
$cate = isset($json['category']);
$test = isset($json['testCases']);

$endpoint = QUESTION_URL;

if ($name && $desc && $diff && $cate && $test) {

    $controller = new Controller();
    $controller->setUrl($endpoint);
    $controller->setBody($post);

    $curl = $controller->curl_post_request($controller->getUrl(), $controller->getBody());
    
    $validate_curl = json_decode($curl, true);
    if ($validate_curl['insert'] == 'true') {
        $response['questionInsertValid'] = 'true';
    }

    header('Content-Type: application/json');
    echo json_encode($response);

} else {

    $controller = new Controller();
    $controller->setUrl($endpoint);
    $curl = $controller->curl_get_request($controller->getUrl());

    header('Content-Type: application/json');
    echo $curl;
}

?>