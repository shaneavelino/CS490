<?php

require_once('./controller.php');
require_once('./constants.php');

/** Grader.php
 * Written by: Avel Shane Coronado
 * CS490 Spring 2020
 * POST requests to this endpoint will take in a student ID and exam name
 * It will then GET request to the completed student's exam and then it will:
 * extract each question name, answers (testCases), and raw student answers
 * 
 * For each exam question: 
 * 1. Test the function name and ensure it was written correctly (5 points)
 * 2. Test the raw answer and exec the python code to ensure it passes 2 test cases (10 points)
*/

// to store each itemized score into the put update for ser
$testCaseResponseObject = array('input' => '', 'output' => '', 'studentOutput' => '', 'score' => '', 'comment' => '');
$testCaseResponse = array();
/** initialize the PUT request body into an associative array to send to Tom's PUT service */
$put_request = array('user' => '', 'exam' => '', 'question' => '', 'autoGrade' => '', 'adjustedGrade' => '', 'testCaseResponse' => $testCaseResponse);

// response for successful grading
$grader_response = array('isFullExamGraded' => '');

/** decode the incoming question request into an associative array */
$post = file_get_contents('php://input');
$json = json_decode($post, true);

class Grader {

  private $question;
  private $answer;
  private $input;
  private $output;
  private $final;
  private $weight;
  private $testcase_amount;

  function set_question($question) {
    $this->question = $question;
  }

  function get_question() {
    return $this->question;
  }

  function set_answer($answer) {
    $this->answer = $answer;
  }

  function get_answer() {
    return $this->answer;
  }

  function set_test_input($input) {
    $this->input = $input;
  }

  function get_test_input() {
    return $this->input;
  }

  function set_test_output($output) {
    $this->output = $output;
  }

  function get_test_output() {
    return $this->output;
  }

  function set_final($final) {
    $this->final = $final;
  }

  function get_final() {
    return $this->final;
  }

  function set_weight($weight) {
    $this->weight = $weight;
  }

  function get_weight() {
    return $this->weight;
  }

  function set_testcase_amount($testcase_amount) {
    $this->testcase_amount = $testcase_amount;
  }

  function get_testcase_amount() {
    return $this->testcase_amount;
  }

  // return a grade for the student's raw answer based on correctness
  public function grade_func_name($question, $answer, $weight) {
    $score = 0;

    // the function name is 25% of the score of each question
    $function_weight = ($weight * 25)/100;
    
    // python def keyword (3 chars)
    $def_keyword = substr($answer, 0, 3);

    // returns everything before the open paren 'def squareNumber'
    $open_paren = strstr($answer, "(", true);

    // want to get the function name, def_FUNCTIONNAME()
    // its in between the space after def and the first open parenthesis

    // ++score if the question is correct, else don't add score
    if ($question == substr($open_paren, strlen($def_keyword)+1)) {
      $score += $function_weight;
    } else {
      $score += 0;
    }

    // store the student's output for the function name, store the score
    // $testCaseResponseObject['studentOutput'] = substr($open_paren, strlen($def_keyword)+1);
    // echo $testCaseResponseObject;

    return $score;
  }

  // return grade for the student's raw answer based on test cases
  public function grade_test_case($input, $output, $answer, $question, $weight, $testcase_amount) {
    $score = 0;

    // each question can have any # of test cases each so each question is worth 75% / n testCases
    $test_case_weight = (($weight * 75)/100)/$testcase_amount;

    // create python text file from input answer, append the function name with the test case input
    $callFunction = "print(" . $question . "($input))";
    $pythonListing = "$answer\n\n$callFunction";
    
    $pythonfile = file_put_contents('exam.py', $pythonListing);
    // execute python text file
    // return result of file
    $student_result = shell_exec("python3 exam.py");
    echo ' student result: ' . $student_result;

    // if testcase is correct, +75% of the question - needed to trim the newline from the python output
    if (trim($student_result) == $output) {
      $score += $test_case_weight;
      echo 'student score: ' . $score;
    } else {
      $score += 0;
    }

    return $score;
  }
}

/** This is where we will do the main logic of the auto grader */
if (isset($json['exam']) && isset($json['user'])) {

  // fetch each question's score from the exam
  $score_endpoint = EXAM_URL . '?' . http_build_query(array('name' => $json['exam']));
  $score_controller = new Controller();
  $score_controller->setUrl($score_endpoint);
  $score_curl = $score_controller->curl_get_request($score_controller->getUrl());
  $get_score_validation = json_decode($score_curl, true);
  
  //print_r($get_score_validation['questions'][0]);

  /** cURL backend exam using query parameter for the name front end will pass query name through json */
  $endpoint = RESULT_URL . '?' . http_build_query(array('exam' => $json['exam'], 'user' => $json['user']));
  
  // set up the GET request to Tom's endpoint for a student's exam answers
  $get_controller = new Controller();
  $get_controller->setUrl($endpoint);
  $curl = $get_controller->curl_get_request($get_controller->getUrl());
  $db_validation = json_decode($curl, true);
  
  // instantiate a Grader object to run the grader
  $grader = new Grader();
  
  /** check if the results field is provided */
  if (isset($db_validation['results'])) {
    
    /** traverse through each result object for the question name and answer */
    for ($i = 0; $i < count($db_validation['results']); $i++) {
      
      // final_score
      $final_question_score = 0;

      // set each question, answer, score, # of test cases to pass to the grader
      $grader->set_question($db_validation['results'][$i]['question']);
      $grader->set_answer($db_validation['results'][$i]['answer']);
      $grader->set_weight($get_score_validation['questions'][$i]['score']);
      $grader->set_testcase_amount(count($get_score_validation['questions'][$i]['testCases']));

      // set each question's test cases' input and output to the grader
      for ($j = 0; $j < count($db_validation['results'][$i]['testCases']); $j++) {
        $grader->set_test_input($db_validation['results'][$i]['testCases'][$j]['input']);
        $grader->set_test_output($db_validation['results'][$i]['testCases'][$j]['output']);

        // call the test case grader and store the grade
        $func_score = $grader->grade_test_case($grader->get_test_input(), $grader->get_test_output(), $grader->get_answer(), $grader->get_question(), $grader->get_weight(), $grader->get_testcase_amount());
        $final_question_score += $func_score;
      }
      
      // call the function grader and store the grade
      $test_score = $grader->grade_func_name($grader->get_question(), $grader->get_answer(), $grader->get_weight());
      $final_question_score += $test_score;

      // map to the body for put request
      $put_request['user'] = $db_validation['user'];
      $put_request['exam'] = $db_validation['exam'];
      $put_request['question'] = $db_validation['results'][$i]['question'];
      $put_request['autograde'] = $final_question_score;
      $put_request['adjustedGrade'] = $final_question_score;
      $put_json_request = json_encode($put_request);

      // Once the exam is graded, send the autoGrade and adjustedGrade along with the user, examname, and question
      $put_exam_result = RESULT_URL;
      $put_controller = new Controller();
      $put_controller->setUrl($put_exam_result);
      $put_controller->setBody($put_json_request);
      $put_curl = $put_controller->curl_put_request($put_controller->getUrl(), $put_controller->getBody());
      
      // validate each question was graded successfully
      $put_validation = json_decode($put_curl, true);
      if ($put_validation['update'] == 'true') {
        $grader_response['isFullExamGraded'] = 'true';  
      } else {
        $grader_response['isFullExamGraded'] = 'false';
      }
    }
  } else {
    echo 'STUDENT_EXAM error: could not find the results property.';
  }
  // header('Content-Type: application/json');
  // echo json_encode($grader_response);
} else {
  echo 'POST error: fields \'user\' and \'exam\' were not properly passed.';
}

?>