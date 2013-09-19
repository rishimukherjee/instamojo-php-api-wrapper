<?php

class Instamojo{
	
	// internal constant to enable/disable debugging.
	const debug = true;
	
	// Instamojo API base URL.
	const API_URL = 'https://www.instamojo.com/api/1/';

	// API version.
	const version = '0.0';

	// A curl instance.
	protected $curl;

	// The username of the User.
	protected $username = null;

	// The password of the User
	protected $password = null; 

	// Token provided by Instamojo.
	protected $APP_TOKEN = null;

	//  APP_ID provided by Instamojo.
	protected $APP_ID = null;

	// Time-out.
	protected $timeout = 10;

	public $title = null;
	public $description = null;
	public $currency  = null;
	public $base_price = null;
	public $quantity = null;
	public $start_date = null;
	public $end_date = null;
	public $timezone = null;
	public $venue = null;
	public $redirect_url = null;
	public $note = null;
	public $file_path = null;
	public $cover_path = null;

	private $currencies = array("INR", "USD");

	/**
		* Default constructor
		* @param string $username Instamojo username of the user.
		* @param string $password Instamojo password of the user.
	*/
	public function __construct($username, $password, $id){
		$this->setUsername($username);
		$this->setPassword($password);
		$this->setID($id);
	}

	/**
		* Default destructor.
	*/
	public function __destruct(){
		if($this->curl != null) curl_close($this->curl);
	}

	protected function allowed_currency($currency){
		if(in_array($currency, $this->currencies)) return true;
		return false;
	}

	/**
		* Set teh Username.
		* @param string $username Instamojo username of the user.
	*/
	private function setUsername($username){
		$this->username = (string) $username;
	}

	/**
		* Set the password.
		* @param string $password Instamojo username of the password.
	*/
	private function setPassword($password){
		$this->password = (string) $password;
	}

	/**
		* Set the ID.
		* @param string $id Instamojo APP_ID provided by Instamojo.
	*/

	private function setID($id){
		$this->APP_ID = (string) $id;
	}

	/**
		* Create the absolute path for the request.
		* @param string $url The base URL (Here it is used by API_URL)
		* @param string $path The relative path.
	*/
	private function buildPath($url, $path){
		return $url . $path;
	}

	/**
		* Request the instamojo API.
		* @param string $path The relative path.
		* @param string $method POST/GET/POST/DELETE
		* @param array $data Data to be passed. 
	*/
	private function apiRequest($path, $method, array $data = null){
		$path = (string) $path;
		$method = (string) $method;
		$data = (array) $data;

		$headers = array("X-App-Id: $this->APP_ID");

		if($this->APP_TOKEN){
			$headers[] = "X-Auth-Token: $this->APP_TOKEN";
		}

		$request_path = $this->buildPath(self::API_URL, $path);

		$options = array();
		$options[CURLOPT_HTTPHEADER] = $headers;
		$options[CURLOPT_RETURNTRANSFER] = true;
		$options[CURLOPT_URL] = $request_path;

		if($method == 'POST'){
			$data_string = "";

			foreach ($data as $key => $value) {
				$data_string .= $key.'='.$value.'&';
			}

			$data_string = rtrim($data_string, '&');

			$options[CURLOPT_POST] = count($data);
			$options[CURLOPT_POSTFIELDS] = $data_string;
		}

		else if($method == 'GET'){
			// Nothing to be done here.
		}

		else if($method == 'PUT'){

		}

		else if($method == 'DELETE'){
			$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
		}

		else if($method == 'PATCH'){
			$data_string = "";

			foreach ($data as $key => $value) {
				$data_string .= $key.'='.$value.'&';
			}

			$data_string = rtrim($data_string, '&');

			$options[CURLOPT_POST] = count($data);
			$options[CURLOPT_POSTFIELDS] = $data_string;
			$options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
		}

		$this->curl = curl_init();
		curl_setopt_array($this->curl, $options);
		$response = curl_exec($this->curl);
		$headers = curl_getinfo($this->curl);

		$errorNumber = curl_errno($this->curl);
		$errorMessage = curl_error($this->curl);

		return array('response' => $response, 'headers' => $headers, 'error' => $errorMessage, 'errno' => $errorNumber);
	}


	/**
		* Authenticate the application.
		* @return array PHP array of the JSON response.
	*/
	public function apiAuth(){
		$response = $this->apiRequest('auth/', 'POST', $data = array('username' => $this->username, 'password' => $this->password));
		$json = @json_decode($response['response'], true);
		
		if($response['errno']) throw new Exception("Exception: " . $response['error']);
		if(!$json["success"]) throw new Exception("Application authentication failed. Check credentials");
		
		$this->APP_TOKEN = $json["token"];
		return $json;
	}

	/**
		* List all the offers of the user.
		* @return array PHP array of the JSON response.
	*/
	public function listAllOffers(){
		if(!$this->APP_TOKEN) throw new Exception("Please authenticate your application.");
		$response = $this->apiRequest('offer/', 'GET');
		$json = @json_decode($response['response'], true);
		if(!$json['success']) throw new Exception("Error in listing all offers.");
		return $json;
	}

	/**
		* List the complete offer details of the offer id mentioned in $slug.
		* @param array $slug The offer id.
		* @return array PHP array of the JSON response.
	*/
	public function listOneOfferDetail($slug){
		if(!$this->APP_TOKEN) throw new Exception("Please authenticate your application.");
		$response = $this->apiRequest("offer/$slug/", 'GET');
		$json = @json_decode($response['response'], true);
		if(!$json['success']) throw new Exception("Error in listing offer of $slug.");
		return $json;
	}

	/**
		* Used to get an upload URL for the files to be uploaded, i.e. The cover image and the File.
		* @return array PHP array of the JSON response.
	*/
	public function getUploadUrl(){
		if(!$this->APP_TOKEN) throw new Exception("Please authenticate your application.");
		$response = $this->apiRequest('offer/get_file_upload_url/', 'GET');
		$json = @json_decode($response['response'], true);
		if(!$json['success']) throw new Exception("Cannot get an URL.");
		return $json["upload_url"];
	}

	/**
		* Deletes the authentication toekn recieved from Instamojo.
	*/
	public function deleteAuthToken(){
		if(!$this->APP_TOKEN) throw new Exception("No token loaded, unable to delete.");
		$response = $this->apiRequest("auth/$this->APP_TOKEN/", 'DELETE');
		$json = @json_decode($response['response'], true);
		if(!$json['success']) throw new Exception("Could not delete auth token.");
		$this->APP_TOKEN = null;
	}

	/**
		* Archives(Deletes) the offer whos id is supplied.
		* @param string $slug Id of the offer.
	*/
	public function archiveOffer($slug){
		if(!$this->APP_TOKEN) throw new Exception("No token loaded, unable to archive.");
		$response = $this->apiRequest("offer/$slug/", 'DELETE');
		$json = @json_decode($response['response'], true);
		if(!$json['success']) throw new Exception("Could not archive offer.");
	}

	/**
		* Set the title of the offer.
	*/
	public function setTitle($title){
		if(strlen($title) > 200) throw new Exception("Title size not more than 200 allowed.");
		$this->title = (string) $title;
	}

	public function setDescription($description){
		$this->description = (string) $description;
	}

	public function setCurrency($currency){
		if(!$this->allowed_currency($currency)) throw new Exception("Invalid currency.");
		$this->currency = (string) $currency;
	}

	public function setBasePrice($base_price){
		if(!(is_numeric($base_price) && (int)$base_price >= 0)) throw new Exception("The base_price should be a positive number or zero.");
		$this->base_price = (string) $base_price;
	}

	public function setQuantity($quantity){
		if(!(is_numeric($quantity) && (int)$quantity == $quantity && (int)$quantity >= 0))
			throw new Exception("The quantity should be a positive number or zero.");
		$this->quantity = (string) $quantity;
	}

	public function setStartDate($start_date){
		$this->start_date = $start_date;
	}

	public function setEndDate($end_date){
		$this->end_date = $end_date;
	}
	public function setTimeZone($timezone){
		$this->timezone = $timezone;
	}

	public function setVenue($venue){
		$this->venue = $venue;
	}

	public function setRedirectURL($redirect_url){
		$this->redirect_url = $redirect_url;
	}

	public function setNote($note){
		$this->note = $note;
	}

	public function setFilePath($file_path){
		$this->file_path = $file_path;
	}

	public function setCoverPath($cover_path){
		$this->cover_path = $cover_path;
	}

	private function getFileUploadJson($file_upload_url, $file_path){
		$file_path = realpath($file_path);
		$file_name = basename($file_path);
		$ch = curl_init();
		$data = array('fileUpload' => '@'.$file_path);
		curl_setopt($ch, CURLOPT_URL, $file_upload_url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$uploadJson = curl_exec($ch);
		return $uploadJson;
	}

	private function buildDataArray(){
		$data = array();
		if(!$this->title) throw new Exception("title is a must for creating an offer.");
		$data['title'] = $this->title;
		if(!$this->description) throw new Exception("description is a must for creating an offer.");
		$data['description'] = $this->description;
		if(!$this->currency) throw new Exception("currency is a must for creating an offer.");
		$data['currency'] = $this->currency;
		if(!$this->base_price && $this->base_price != '0') throw new Exception("base_price is a must for creating an offer.");
		$data['base_price'] = $this->base_price;
		if($this->quantity)
			$data['quantity'] = $this->quantity;
		if($this->start_date)
			$data['start_date'] = $this->start_date;
		if($this->end_date)
			$data['end_date'] = $this->end_date;
		if($this->timezone) 
			$data['timezone'] = $this->timezone;
		if($this->venue)
			$data['venue'] = $this->venue;
		if($this->redirect_url)
			$data['redirect_url'] = $this->redirect_url;
		if($this->note)
			$data['note'] = $this->note;
		if(!$this->file_path) throw new Exception("file is a must for creating an offer.");

		$upload_url = $this->getUploadUrl();
		$file_upload_json = $this->getFileUploadJson($upload_url, $this->file_path);
		$data['file_upload_json'] = $file_upload_json;

		if($this->cover_path){
			$upload_url = $this->getUploadUrl();
			$cover_upload_json = $this->getFileUploadJson($upload_url, $this->cover_path);
			$data['cover_image_json'] = $cover_upload_json;
		}
		return $data;
	}

	public function createOffer(){
		$offer_array = $this->buildDataArray();
		$request = $this->apiRequest('offer/', 'POST', $data = $offer_array);
		$json = @json_decode($request['response'], true);
		if(!$json['success']) throw new Exception("Connot create offer.");
		return $request;
	}

	public function editOffer($slug){
		$offer_array = $this->buildDataArray();
		$request = $this->apiRequest("offer/$slug/", 'PATCH', $data = $offer_array);
		$json = @json_decode($request['response'], true);
		if(!$json['success']) throw new Exception("Connot edit offer.");
		return $request;
	}
}

$instance = new Instamojo('username', 'password', 'token');
$auth = $instance->apiAuth();
$instance->setTitle('Kolkata');
$instance->setDescription('Fast life of people at Kolkata.');
$instance->setCurrency('INR');
$instance->setBasePrice('50');
$instance->setFilePath('IMG_3240.jpg');
$instance->setCoverPath('rsz_img_3240.jpg');
$offer = $instance->editOffer('kolkata-dc780');
print_r($offer);
?>