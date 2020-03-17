<?php
/** Exam.php
 * Written by: Avel Shane Coronado
 * CS490 Spring 2020
 * POST/GET request to this script will invoke a pass-through to the exam
 * backend service for:
 * 1. insert an exam by instructor name, exam name, question array
 * 2. fetch an exam by name
 * 3. fetch exam results by exam name
 * 4. update to assign an exam to a student by name and exam name
 * 5. fetch all exams assigned to a student by name
 */

require_once('./controller.php');
require_once('./constants.php');

$isExamInsertedResponse = array('examInserted' => '');
$isGradedResponse = array('examGraded' => '');
$isExamAssignedToUser = array('examAssigned' => '');

$post = file_get_contents('php://input');
$json = json_decode($post, true);

$endpoint = EXAM_URL;

if (isset($json['name']) && isset($json['creator']) && isset($json['questions'])) {

    $controller = new Controller();
    $controller->setUrl($endpoint);
    $controller->setBody($post);

    $curl = $controller->curl_post_request($controller->getUrl(), $controller->getBody());

    $validate_curl = json_decode($curl, true);
    if ($validate_curl['insert'] == 'true') {
        $isExamInsertedResponse['examInserted'] = 'true';
    } else {
        $isExamInsertedResponse['examInserted'] = 'false';
    }

    header('Content-Type: application/json');
    echo json_encode($isExamInsertedResponse);
    
} else if (isset($json['examName'])) {

    // curl backend GET request for the exam name
    $endpoint = $endpoint . '?' . http_build_query(array('name' => $json['examName']));
    $controller = new Controller();
    $controller->setUrl($endpoint);
    $curl = $controller->curl_get_request($controller->getUrl());

    header('Content-Type: application/json');
    echo $curl;

} else if (isset($json['examGraded'])){

    // PUT to exam service backend to mark an exam graded
    $controller = new Controller();
    $controller->setUrl($endpoint);
    $controller->setBody($post);

    $curl = $controller->curl_put_request($controller->getUrl(), $controller->getBody());
    
    $validate_put = json_decode($curl, true);
    if ($validate_put['update'] == 'true') {
        $isGradedResponse['examGraded'] = 'true';
    } else {
        $isGradedResponse['examGraded'] = 'false';
    }

    header('Content-Type: application/json');
    echo json_encode($isGradedResponse);

} else if (isset($json['user']) && isset($json['exam'])) {

    // PUT to exam service backend to assign an exam to a student
    $controller = new Controller();
    $controller->setUrl($endpoint);
    $controller->setBody($post);

    $curl = $controller->curl_put_request($controller->getUrl(), $controller->getBody());
    
    $validate_put = json_decode($curl, true);
    if ($validate_put['update'] == 'true') {
        $isExamAssignedToUser['examAssigned'] = 'true';
    } else {
        $isExamAssignedToUser['examAssigned'] = 'false';
    }

    header('Content-Type: application/json');
    echo json_encode($isExamAssignedToUser);

} else if (isset($json['fetchExamsByUser'])) {

    // GET list of exams based on user name query parameter
    $endpoint = $endpoint . '?' . http_build_query(array('user' => $json['fetchExamsByUser']));
    $controller = new Controller();
    $controller->setUrl($endpoint);
    $curl = $controller->curl_get_request($controller->getUrl());

    header('Content-Type: application/json');
    echo $curl;

} else {

    echo 'Not a proper request JSON for exam service.';
}

?>