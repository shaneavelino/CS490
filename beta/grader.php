<?php

require_once('./controller.php');

class Grader {

  private $question;
  private $answer;
  private $results;
  private $test_cases;

  public function __construct($question) {
    
  }

  public function gradeQuestion($question) {
    $def = substr($str, 0, 3);
    return $def;
  }

}

$str = "def addTwoNumbers(i,j): return i + j";
$test_case = "";

$grader = Grader::gradeQuestion($str);
echo $grader;


?>