<?php

require_once('./controller.php');
require_once('./constants.php');

/** Grader.php
 * Written by: Avel Shane Coronado
 * CS490 Spring 2020
 * POST requests to this endpoint will take in a list of completed student exams,
 * extract each exam question name, answers (testCases), and raw student answers
 * 
 * For each exam question: 
 * 1. Test the function name and ensure it was written correctly 
 * 2. Test the raw answer and exec the python code to ensure it passes 2 test cases
*/

/** initialize response body into an associative array */
$response = array('examResults' => array('student' => 'asc8', 'results' => array('name' => 'foobar', 'score' => '75')));

/** decode the incoming question request into an associative array */
$post = file_get_contents('php://input');
$json = json_decode($post, true);

class Grader {

  private $question;
  private $answer;
  private $score;
  private $student;

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

  function set_score($score) {
    $this->score = score;
  }

  function get_score() {
    return $this->score;
  }

  function set_student($student) {
    $this->student = student;
  }

  function get_student() {
    return $this->student;
  }

  public function grade_results($score) {
  
  }

  // returns a grade for the student's raw answer based on correctness
  public function grade_func_name($question, $answer) {
    $raw_answer = set_answer($answer);
    
    // python def keyword (3 chars)
    $def = substr($raw_answer, 0, 3);

    // returns everything before the open paren 'def squareNumber'
    $open_paren = strstr($raw_answer, "(", true);

    // want to get the function name, def_FUNCTIONNAME()
    // its in between the space after def and the first open parenthesis
    // returns the name of the answer's function

    // response of the string of the function name
    echo substr($open_paren, strlen($def));


  }
}



/** cURL backend exam using query parameter for the name
 * front end will pass query name through json
*/
//$endpoint = EXAM_URL . '?' . http_build_query('');

//$controller = new Controller();
//$controller->setUrl($endpoint);

//$curl = $controller->curl_get_request($endpoint);




$grades = isset($json['student']);
$student = isset($json['student']['0']['student']);

$raw_answer = "def square(i): return i * i";
$test_cases = "";

// python def keyword (3 chars)
$def = substr($raw_answer, 0, 3);

// returns everything before the open paren 'def squareNumber'
$open_paren = strstr($raw_answer, "(", true);

// want to get the function name, def_FUNCTIONNAME()
// its in between the space after def and the first open parenthesis
// returns the name of the answer's function

// uncomment this line to enable the string of the function name
$student_answer_func_name = substr($open_paren, strlen($def));

// get the student object
foreach($json as $item) {
  //print_r( 'Student name: ' . '\'' . $item['student'] . '\' ');
  //print_r( 'Exam name: ' . '\'' .  $item['name'] . '\' ');
  // get the exam object array from the json object
  foreach($json as $item) {
    foreach($item['exam'] as $exam_item) {
      // python def keyword (3 chars)
      $def = substr($exam_item['answer'], 0, 3);

      // returns everything before the open paren 'def squareNumber'
      $open_paren = strstr($exam_item['answer'], "(", true);
      //echo 'the exam question: ' . '\'' . $exam_item['question'] . '\' ';
      //echo 'before (: ' . '\'' . $open_paren . '\' ';
      //echo 'the answer func: ' . '\'' . substr($open_paren, strlen($def)+1) . '\' ';
      
      if ($exam_item['question'] == substr($open_paren, strlen($def)+1)) {
        echo 'true';
      } else {
        echo 'false';
      }

      
    }
  }
}

// return result as our 'response'
header('Content-type: application/json');
//echo json_encode($response);

?>