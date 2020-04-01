<?php 
// php user proxy service for professor frontend 
//uncomment for debug 
//ini_set('display_errors', 1); error_reporting(E_ALL);

class proxyHandler {


    public function __construct(){
        $this->url = "https://web.njit.edu/~asc8/cs490/beta/middle/exam.php";
    }

    public function handleRequest($method, $body){
        switch($method){
        case 'get':
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            if($_GET['prof']){
                $body['professor'] = $_GET['prof'];
                echo $this->handlePost(JSON_encode($body));

            }else{
                echo $this->handleGet($_GET);
            }
            break;
        case 'post':
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            echo $this->handlePost($body);
            break;
        case 'put':
            header('Content-Type: application/json');
            header('Access-Control-Allow-Origin: *');
            echo $this->handlePost($body);
            break;

        default:
            http_response_code(405);
        } 
    }
    public function handleGet($getParams){
        $retval = "";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url . $getParams );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data))
        );
        $retval = curl_exec($curl);
        curl_close($curl);

        return $retval;
    } 


    public function handlePost($data){
        $retval = "";
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(       
            'Content-Type: application/json', 
            'Content-Length: ' . strlen($data))       
        );   
        $retval = curl_exec($curl);
        curl_close($curl);
   
        return $retval;
    }


}

// initalize handler class 
$http = new proxyHandler(); 

// take variables from request  
$method = strtolower($_SERVER['REQUEST_METHOD']);
$body = file_get_contents('php://input');

// handle request 
$http->handleRequest( $method, $body);
