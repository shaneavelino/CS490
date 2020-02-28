<?php

/** grader.php
 * Written by: Avel Shane Coronado
 * CS490 Spring 2020 Beta Version
 * Receives POST request from front-end when instructor clicks on the auto-grade service
 * Takes in the exam answers and compares to the instructor's test cases
 */

$str = "def addTwoNumbers(i,j): return i + j";

class Grader
{
    public function gradeExam($str) {
        $def = substr($str, 0, 3);
        $func = substr($str, 4);
        echo $func;
    }
}

$grader = new Grader();
$grader->gradeExam($str);

?>