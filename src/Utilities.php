<?php

	/**
	 * 
     * @param string $method ('GET', 'POST', 'DELETE', 'PATCH')
     * @param string $request_url whichever API url you want to target.
     * @param array $data contains the POST data to be sent to the API.
	 * @param array $headers contains the necessary.
     * @return array decoded json returned by API.
     */
    function api_request($method, $request_url, array $data=[], array $headers=[]) 
    {
        $method = (string) $method;
        $data = (array) $data;

        $options = array();
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_RETURNTRANSFER] = true;
        
        if($method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        } else if($method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        } else if($method == 'PATCH') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);         
            $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        } else if ($method == 'GET' or $method == 'HEAD') {
            if (!empty($data)) {
                /* Update URL to container Query String of Paramaters */
                $request_url .= '?' . http_build_query($data);
            }
        }
        // $options[CURLOPT_VERBOSE] = true;
        $options[CURLOPT_URL] = $request_url;
        $options[CURLOPT_SSL_VERIFYPEER] = true;
        $options[CURLOPT_CAINFO] = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cacert.pem';

        $curl_request = curl_init();
        $setopt = curl_setopt_array($curl_request, $options);
        $response = curl_exec($curl_request);
        $headers = curl_getinfo($curl_request);

        $error_number = curl_errno($curl_request);
        $error_message = curl_error($curl_request);
        $response_obj = json_decode($response, true);

        if($error_number != 0){
            if($error_number == 60){
                throw new \Exception("Something went wrong. cURL raised an error with number: $error_number and message: $error_message. " .
                                    "Please check http://stackoverflow.com/a/21114601/846892 for a fix." . PHP_EOL);
            }
            else{
                throw new \Exception("Something went wrong. cURL raised an error with number: $error_number and message: $error_message." . PHP_EOL);
            }
        }

        if(isset($response_obj['success']) && $response_obj['success'] == false) {
            $message = json_encode($response_obj['message']);
            throw new \Exception($message . PHP_EOL);
		}
		
        return $response_obj;
    }