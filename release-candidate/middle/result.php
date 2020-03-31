<?php
/** Result.php
 * Written by: Avel Shane Coronado
 * CS490 Spring 2020
 * POST request to this script will invoke a pass-through to the results
 * backend service for:
 * 1. fetch exam results by student and exam
 * 2. insert an exam answer by student, exam, results array
 * 2. update an exam grade by user, exam, question and adjusted grade
 * 3. fetch all exam results by exam name
 */

require_once('./controller.php');
require_once('./constants.php');

$post = file_get_contents('php://input');
$json = json_decode($post, true);

$isGradeUpdated = array('gradeUpdated' => '');
$examResultsInserted = array('examResultsInserted' => '');

$endpoint = RESULT_URL;

$user = isset($json['user']);
$exam = isset($json['exam']);
$results = isset($json['results']);
$question = isset($json['question']);
$autograde = isset($json['autograde']);
$adjustedGrade = isset($json['adjustedGrade']);

if ($user && $exam && !$results && !$question && !$autograde && !$adjustedGrade) {

  /** cURL backend exam using query parameter for the name front end will pass query name through json */
  $endpoint = $endpoint . '?' . http_build_query(array('exam' => $json['exam'], 'user' => $json['user']));

  // set up the GET request to Tom's endpoint for an exam by user and exam name
  $get_controller = new Controller();
  $get_controller->setUrl($endpoint);
  $curl = $get_controller->curl_get_request($get_controller->getUrl());

  header('Content-Type: application/json');
  echo $curl;

} else if ($user && $exam && $results && !$question && !$autograde && !$adjustedGrade) {

  $controller = new Controller();
  $controller->setUrl($endpoint);
  $controller->setBody($post);

  $curl = $controller->curl_post_request($controller->getUrl(), $controller->getBody());

  $validate_post = json_decode($curl, true);
  if ($validate_post['insert'] == 'true') {
    $examResultsInserted['examResultsInserted'] = 'true';
  } else {
    $examResultsInserted['examResultsInserted'] = 'false';
  }

  header('Content-Type: application/json');
  echo json_encode($examResultsInserted);

} else if ($user && $exam && $question && $autograde && $adjustedGrade) {

  /** PUT request to update a result on an exam  */
  $controller = new Controller();
  $controller->setUrl($endpoint);
  $controller->setBody($post);

  $curl = $controller->curl_put_request($controller->getUrl(), $controller->getBody());

  $validate_put = json_decode($curl, true);
  if ($validate_put['update'] == 'true') {
    $isGradeUpdated['gradeUpdated'] = 'true';
  } else {
    $isGradeUpdated['gradeUpdated'] = 'false';
  }

  header('Content-Type: application/json');
  echo json_encode($isGradeUpdated);

} else if (isset($json['fetchAllResultsByExam'])) {

    // cURL backend result service to GET all results by exam name
    $endpoint = $endpoint . '?' . http_build_query(array('exam' => $json['fetchAllResultsByExam']));

    // set up the GET request to Tom's endpoint for an exam by user and exam name
    $get_controller = new Controller();
    $get_controller->setUrl($endpoint);
    $curl = $get_controller->curl_get_request($get_controller->getUrl());

    header('Content-Type: application/json');
    echo $curl;

} else {

    echo 'Not a proper request JSON for result service.';
}

?>