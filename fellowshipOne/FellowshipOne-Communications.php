<?php
    
    require_once('FellowshipOne.php');
	
	class FellowshipOneCommunications {
	
		//this will store a reference to the core F1 Obj so we can make API calls
		private $f1CoreObj;
		private $paths = array(
			'createPersonAddress' => '/v1/People/{personID}/Addresses',
			'createHouseholdAddress' => '/v1/Households/{householdID}/Addresses',
			'newPersonCommunication' => '/v1/People/{personID}/Communications/new',
			'newHouseholdCommunication' =>'v1/Households/{householdID}/Communications/new',
			'createPersonCommunication' => '/v1/People/{personID}/Communications',
			'createHouseholdCommunication' => '/v1/Households/{householdID}/Communications',
			'listCommunication' => '/v1/People/{personID}/Communications',			
		);		
		
	
		/**
		 * contruct make a new F1 Attribute class and pass a core F1Obj to it. This
		 * is how you control whether or not it's pulling from live or staging.
		 * @param object $f1CoreObj
		 */
		public function __construct($f1CoreObj){
			//create the core F1 Obj that will let us make API calls
			$this->f1CoreObj = $f1CoreObj;			
		}	
		
		/** 
		 * This function takes a fully baked Address model and sets it for either the household or the person ID, depending on the type
		 * parameter you pass in. Type should either be 'person' or 'household'.  Make sure that whichever type you select, you're passing
		 * in that ID type (personID or householdID)
		 * @param string $id
		 * @param string $type
		 * @param array $model
		 * 
		 */

		 public function createAddress($id,$type,$model) {
		 	if ($type == 'person') {
			 	$url = str_replace('{personID}',$id, $this->f1CoreObj->baseUrl . $this->paths['createPersonAddress'] . ".json");		 		
		 	} else {
		 		$url = str_replace('{householdID}',$id, $this->f1CoreObj->baseUrl . $this->paths['createHouseholdAddress'] . ".json");
		 	}
			
			return $this->f1CoreObj->fetchPostJson($url,json_encode($model));
			
			
		 }
		 
		/**
		 * This function gets a new communication model for a person or household so you can fill it out. Just pass in type 'person' or 'household'
		 * @param string $type
		 */ 
		 public function newCommunicationModel($type,$id) {
		 	
			if ($type == 'person') {
				$url = str_replace('{personID}',$id, $this->f1CoreObj->baseUrl . $this->paths['newPersonCommunication'] . ".json");
			} else {
				$url = str_replace('{householdID}',$id, $this->f1CoreObj->baseUrl . $this->paths['newHouseholdCommunication'] . ".json");
			}
			
			return $this->f1CoreObj->fetchGetJson($url);
						
		 }
		 
		 /**
		  * This function takes a fully baked Communication model and sets it on either the person or household depending on the type parameter you pass
		  * (either 'person' or 'household').  
		  */
		 public function createCommunication($type,$model,$id) {
		 	
			if ($type == 'person') {
				$url = str_replace('{personID}',$id, $this->f1CoreObj->baseUrl . $this->paths['createPersonCommunication'] . ".json");
			} else {
				$url = str_replace('{householdID}',$id, $this->f1CoreObj->baseUrl . $this->paths['createHouseholdCommunication'] . ".json");
			}
			
			return $this->f1CoreObj->fetchPostJson($url,json_encode($model));			
			
		 }
		 
		 
		 /** This function takes an email value and a PID and creates an email for that person. Make sure you pass the type, person or household
		  * @param string $type
		  * @param string $email
		  * @param string $id (be sure to pass the right ID for the right type)
		  */
		  
		  public function createEmail($type,$email,$id) {
		  	
			//add in communications for that person
			//get the communications json model from F1
			$commModel = $this->newCommunicationModel($type, $id);
				
			//Update the json model with the email address
			$commModel['communication']['communicationType']['@id'] = "4";
			$commModel['communication']['communicationType']['name'] = "email";
			$commModel['communication']['communicationValue'] = $email;
			
			//Write the email communication into F1
			$r = $this->createCommunication($type, $commModel, $id);
			
			return $r;			
			
		  }
		 
		 
		 		
		
}
    
    
?>