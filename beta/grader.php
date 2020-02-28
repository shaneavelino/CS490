<?php

class Grader {

  public function gradeQuestion($str) {
    $def = substr($str, 0, 3);
    return $def;
  }

}

$str = "def addTwoNumbers(i,j): return i + j";
$test_case = "";

$grader = Grader::gradeQuestion($str);
echo $grader;




/** params:
  * $url: endpoint to curl
  * $body: post json object
  * return: curl response
  */
function curl_post_request($url, $body) {
  $ch = curl_init();
  curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => 1,
    CURLOPT_POST => 1,
    CURLOPT_POSTFIELDS => $body
  ));
  return curl_exec($ch);
  curl_close($ch);
}
?>