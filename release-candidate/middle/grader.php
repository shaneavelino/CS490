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

/** initialize the PUT request body into an associative array to send to Tom's PUT service */
$put_request = array('user' => '', 'exam' => '', 'question' => '', 'autograde' => '', 'adjustedGrade' => '', 'testCaseResponse' => array());

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
  private $student_result;
  private $student_func;

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

  function set_student_result($student_result) {
    $this->student_result = $student_result;
  }

  function get_student_result() {
    return $this->student_result;
  }

  function set_student_func($student_func) {
    $this->student_func = $student_func;
  }

  function get_student_func() {
    return $this->student_func;
  }

  function set_constraint($constraint) {
    $this->constraint = $constraint;
  }

  function get_constraint() {
    return $this->constraint;
  }

  // return the student's function name output
  // TODO: remove $question as input
  public function get_student_named_function($question, $answer) {
    $student_named_function = "";

    // python def keyword (3 chars)
    $def_keyword = substr($answer, 0, 3);

    // returns everything before the open paren 'def squareNumber'
    $open_paren = strstr($answer, "(", true);

    // want to get the function name, def_FUNCTIONNAME()
    // its in between the space after def and the first open parenthesis
    $student_named_function = substr($open_paren, strlen($def_keyword)+1);
    
    return $student_named_function;
  }

  // return a grade for the student's raw answer based on correctness
  public function grade_func_name($student_func, $question, $weight, $testcase_amount) {
    $score = 0;

    // the function name is 25% of the score of each question
    $function_weight =  $weight / $testcase_amount;

    // ++score if the question is correct, else don't add score
    if ($question == $student_func) {
      $score += $function_weight;
    } else {
      $score += 0;
    }

    return $score;
  }

  // return a grade for colon
  public function grade_colon($answer, $weight, $testcase_amount) {
    $score = 0;

    $first_line = strstr($answer, "\n", true);

    if (strpos($first_line, ":") !== false) {
      $score += $weight / $testcase_amount;
    } else {
      $score += 0;
    }

    return $score;
  }

  // return a grade for constraints
  public function grade_constraint($constraint, $answer, $weight, $testcase_amount) {
    $score = 0;

    if (strpos($answer, $constraint) !== false) {
      $score += $weight / $testcase_amount;
    } else {
      $score += 0;
    }

    return $score;
  }

  // return the student's result from the python script
  public function get_student_output($input, $question, $answer) {
    // create python text file from input answer, append the function name with the test case input
    $callFunction = "print(" . $question . "($input))";
    $pythonListing = "$answer\n\n$callFunction";
    
    $filename = $question . '.py';
    // use the student's function name to run the python interpreter
    $pythonfile = file_put_contents($filename, $pythonListing);
    // execute python text file
    // return result of file
    $student_result = shell_exec("python " . $filename . " 2>&1");

    return trim($student_result);
  }

  // return grade for the student's raw answer based on test cases
  public function grade_test_case($student_result, $output, $weight, $testcase_amount) {
    $score = 0;

    // each question can have any # of test cases each so each question is worth 75% / n testCases
    $test_case_weight = $weight / $testcase_amount;

    // if testcase is correct, +75% of the question - needed to trim the newline from the python output
    if (trim($student_result) == $output) {
      $score += $test_case_weight;
      //echo 'student score: ' . $score;
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

      // initialize each testCaseResponse object into the testCaseResponse array 
      $testCaseResponseObject = array();
      $testCaseResponse = array();
      
      // final_score
      $final_question_score = 0;
      $subitem_count = 0;

      // set each question, answer, score, # of test cases to pass to the grader
      $grader->set_question($db_validation['results'][$i]['question']);
      $grader->set_answer($db_validation['results'][$i]['answer']);
      $grader->set_weight($get_score_validation['questions'][$i]['score']);
      $grader->set_constraint($db_validation['results'][$i]['questionConstraint']);
      $check_constraint = $grader->get_constraint();
      if ($check_constraint == null || $check_constraint == 'N/A') {
        $grader->set_testcase_amount(count($get_score_validation['questions'][$i]['testCases']) + 2);
      } else {
        $grader->set_testcase_amount(count($get_score_validation['questions'][$i]['testCases']) + 3);
      }

      // get the student's answer to the function name to store in the update result service
      $student_named = $grader->get_student_named_function($grader->get_question(), $grader->get_answer());
      $grader->set_student_func($student_named);
      $testCaseResponseObject['studentOutput'] = $student_named;
      
      // call the function grader and store the grade
      $func_score = $grader->grade_func_name($grader->get_student_func(), $grader->get_question(), $grader->get_weight(), $grader->get_testcase_amount());
      $final_question_score += round($func_score, 2);
      $testCaseResponseObject['subItem'] = 'Function spelling';
      $testCaseResponseObject['input'] = 'N/A';
      $testCaseResponseObject['expectedOutput'] = $db_validation['results'][$i]['question'];
      $testCaseResponseObject['score'] = round($func_score, 2);
      $testCaseResponseObject['comments'] = '';

      // add itemized function name score to response
      $testCaseResponse[] = $testCaseResponseObject;
      
      $first_line_expected = 'Colon located at the end of line 1';
      // test the colon
      $colon_score = $grader->grade_colon($grader->get_answer(), $grader->get_weight(), $grader->get_testcase_amount());
      $final_question_score += round($colon_score, 2);
      $testCaseResponseObject['score'] = round($colon_score, 2);
      $testCaseResponseObject['subItem'] = 'Colon check';
      $testCaseResponseObject['input'] = 'N/A';
      $testCaseResponseObject['expectedOutput'] = $first_line_expected;
      if ($colon_score > 0) {
        $testCaseResponseObject['studentOutput'] = 'Colon located';
      } else {
        $testCaseResponseObject['studentOutput'] = 'Colon missing';
      }
      
      // add itemized colon score to response
      $testCaseResponse[] = $testCaseResponseObject;
    
      // test the constraint
      $constraint_score = $grader->grade_constraint($grader->get_constraint(), $grader->get_answer(), $grader->get_weight(), $grader->get_testcase_amount());
      $final_question_score += round($constraint_score, 2);
      $testCaseResponseObject['score'] = round($constraint_score, 2);
      $testCaseResponseObject['subItem'] = 'Constraint check';
      $testCaseResponseObject['input'] = 'N/A';
      if ($constraint_score > 0) {
        $testCaseResponseObject['expectedOutput'] = $db_validation['results'][$i]['questionConstraint'];
        $testCaseResponseObject['studentOutput'] = 'Constraint located';
      } else {
        if ($check_constraint == null || $check_constraint == 'N/A') {
          $testCaseResponseObject['expectedOutput'] = 'Constraint not applied in this question';
          $testCaseResponseObject['studentOutput'] = 'Constraint not applied in this question';
        } else {
          $testCaseResponseObject['studentOutput'] = 'Constraint missing';
        }
      }

      // add itemized constraint score to response
      $testCaseResponse[] = $testCaseResponseObject;

      // set each question's test cases' input and output to the grader
      for ($j = 0; $j < count($db_validation['results'][$i]['testCases']); $j++) {
        $grader->set_test_input($db_validation['results'][$i]['testCases'][$j]['input']);
        $grader->set_test_output($db_validation['results'][$i]['testCases'][$j]['output']);

        // get the student's output result
        $student_output = $grader->get_student_output($grader->get_test_input(), $grader->get_student_func(), $grader->get_answer());
        $grader->set_student_result($student_output);

        // call the test case grader and store the grade
        $testcase_score = $grader->grade_test_case($grader->get_student_result(), $grader->get_test_output(), $grader->get_weight(), $grader->get_testcase_amount());
        $final_question_score += round($testcase_score, 2);

        $test_case_id = $j + 1;
        $testCaseResponseObject['subItem'] = 'Test case ' . $test_case_id;
        $testCaseResponseObject['input'] = $grader->get_test_input();
        $testCaseResponseObject['expectedOutput'] = $db_validation['results'][$i]['testCases'][$j]['output'];
        $testCaseResponseObject['studentOutput'] = $student_output;
        $testCaseResponseObject['score'] = round($testcase_score, 2);

        // add each test case itemization to the json array for the question
        $testCaseResponse[] = $testCaseResponseObject;
      }

      // map to the body for put request
      $put_request['user'] = $db_validation['user'];
      $put_request['exam'] = $db_validation['exam'];
      $put_request['question'] = $db_validation['results'][$i]['question'];
      $put_request['autograde'] = round($final_question_score);
      $put_request['adjustedGrade'] = round($final_question_score);
      $put_request['testCaseResponse'] = $testCaseResponse;
      $put_json_request = json_encode($put_request);

      // Once the exam is graded, send the autoGrade and adjustedGrade along with the user, examname, and question
      $put_exam_result = RESULT_URL;
      $put_controller = new Controller();
      $put_controller->setUrl($put_exam_result);
      $put_controller->setBody($put_json_request);
      $put_curl = $put_controller->curl_put_request($put_controller->getUrl(), $put_controller->getBody());
      //echo $put_curl;
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
  header('Content-Type: application/json');
  echo json_encode($grader_response);
} else {
  echo 'POST error: fields \'user\' and \'exam\' were not properly passed.';
}

?>