<?php

class Grader {

  public function gradeQuestion($str) {
    $def = substr($str, 0, 3);
    return $def;
  }

}

$str = "def addTwoNumbers(i,j): return i + j";

$grader = Grader::gradeQuestion($str);
echo $grader;

?>