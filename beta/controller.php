<?php

/** 
 * Written by: Avel Shane Coronado
 * CS490 Middle Beta
 * 
 * Controller class will redirect POST/GET requests from frontend to the backend
 * Sends response from backend to frontend, based on POST request
 * 
 * Services to request:
 *  user
 *  question
 *  answer
 *  grader
 *  result
 */


class Controller {

  private $url;
  private $body;
  
  public function setUrl($url) {
    $this->url = $url;
  }

  public function getUrl() {
    return $this->url;
  }

  public function setBody($body) {
    $this->body = $body;
  }

  public function getBody() {
    return $this->body;
  }

  /** params:
    * $url: endpoint to curl
    * $body: post json object
    * return: curl response
    */
  public function curl_post_request($url, $body) {
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

  /** params:
    * $url: endpoint to curl
    * return: curl response
    */
  public function curl_get_request($url) {
    $ch = curl_init();

    curl_setopt_array($ch, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_HTTPGET => 1
    ));

    return curl_exec($ch);
    curl_close($ch);
  }
}

?>