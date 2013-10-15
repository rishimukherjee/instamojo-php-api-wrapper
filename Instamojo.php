<?php

class Instamojo{
	
	// internal constant to enable/disable debugging.
	const debug = true;
	
	// Instamojo API base URL.
	const API_URL = 'https://www.instamojo.com/api/1/';

	// API version.
	const version = '0.1';

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

	/**
		* Get the version of the API wrapper.
		* @return string Version of the API wrapper.
	*/
	public function getVersion(){
		return self::version;
	}

	private function allowed_currency($currency){
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
		* Title, keep concise since slug is auto-generated
		* from the title [max: 200 char, required]
		* @param string $title Title of the offer.
	*/
	public function setTitle($title){
		if(strlen($title) > 200) throw new Exception("Title size not more than 200 allowed.");
		$this->title = (string) $title;
	}

	/**
		* Detailed description of the offer, can contain markdown.
		* @param string $description Description of the offer.
	*/
	public function setDescription($description){
		$this->description = (string) $description;
	}

	/**
		* Currency of the offer. Can be INR or USD.
		* @param string $currency Currency of the offer.
	*/
	public function setCurrency($currency){
		if(!$this->allowed_currency($currency)) throw new Exception("Invalid currency.");
		$this->currency = (string) $currency;
	}

	/**
		* Price of the offer as a decimal (up to 2 decimal places)
		* @param string $base_price Base price of the offer.
	*/
	public function setBasePrice($base_price){
		if(!(is_numeric($base_price) && (int)$base_price >= 0)) throw new Exception("The base_price should be a positive number or zero.");
		$this->base_price = (string) $base_price;
	}

	/**
		* Keep zero for unlimited quantity, 
		* any other positive number will limit sales/claims of the offer 
		* and make it unavailable afterwards.
		* @param string $quantity of the offer. 0 for unlimited.
	*/
	public function setQuantity($quantity){
		if(!(is_numeric($quantity) && (int)$quantity == $quantity && (int)$quantity >= 0))
			throw new Exception("The quantity should be a positive number or zero.");
		$this->quantity = (string) $quantity;
	}

	/**
		* Required for events, date-time when the event begins. 
		* Format: YYYY-MM-DD HH:mm
		* @param string $start_date Start date of the offer.
	*/
	public function setStartDate($start_date){
		$this->start_date = $start_date;
	}

	/**
		* Required for events, date-time when the event begins. 
		* Format: YYYY-MM-DD HH:mm
		* @param string $end_date End date of the offer.
	*/
	public function setEndDate($end_date){
		$this->end_date = $end_date;
	}

	/**
		* Timezone of the event. Example: Asia/Kolkata
		* @param string $timezone Timezone of the offer.
	*/
	public function setTimeZone($timezone){
		$this->timezone = $timezone;
	}

	/**
		* Required for events, location where the event will be held.
		* @param string $venue Venue of the offer.
	*/
	public function setVenue($venue){
		$this->venue = $venue;
	}

	/**
		* You can set this to a thank-you page on your site. 
		* Buyers will be redirected here after successful payment.
		* @param string $redirect_url The URL to be redirected to after a buyer downloads the digital file.
	*/
	public function setRedirectURL($redirect_url){
		$this->redirect_url = $redirect_url;
	}

	/**
		* A note to be displayed to buyer after successful 
		* payment. This will also be sent via email and 
		* in the receipt/ticket that is sent as attachment 
		* to the email.
	*/
	public function setNote($note){
		$this->note = $note;
	}

	/**
		* Path to the file you want to sell.
		* @param string $file_path Path to the file.
	*/
	public function setFilePath($file_path){
		$this->file_path = $file_path;
	}

	/**
		* Path to the cover image.
		* @param string $cover_path Path to the cover image.
	*/
	public function setCoverPath($cover_path){
		$this->cover_path = $cover_path;
	}

	/**
		* A utility function to send POST request to the URL recieved from
		* getUploadUrl() and upload a file.
		* @param string $file_upload_url The URL recieved from getUploadUrl().
		* @param string $file_path The path to the file in your computer.
		* @return JSON The JSON recieved from the request. 
	*/
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

	/**
		* Utility function to build the data array which will be used
		* to send data for creating offer through apiRequest().
		* @return array The array to be used later to send data about the offer to Instamojo API.
	*/
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

	/**
		* Function to create an instamojo offer.
		* @return JSON The response resieved from Instamojo API.
	*/
	public function createOffer(){
		$offer_array = $this->buildDataArray();
		$request = $this->apiRequest('offer/', 'POST', $data = $offer_array);
		$json = @json_decode($request['response'], true);
		if(!$json['success']) throw new Exception("Connot create offer.");
		return $request;
	}

	/**
		* Function to to edit an offer.
		* @param string $slug The offer ID.
		* @return JSON The response recieved from Instamojo API.
	*/
	public function editOffer($slug){
		$offer_array = $this->buildDataArray();
		$request = $this->apiRequest("offer/$slug/", 'PATCH', $data = $offer_array);
		$json = @json_decode($request['response'], true);
		if(!$json['success']) throw new Exception("Connot edit offer.");
		return $request;
	}
}

?>