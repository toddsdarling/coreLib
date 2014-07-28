<?php
    
    require_once('FellowshipOne.php');
	
	class FellowshipOnePeople {
	
		//this will store a reference to the core F1 Obj so we can make API calls
		private $f1CoreObj;
		private $paths = array(
			'households' => array(
				'newHousehold' => '/v1/Households/new',
				'createHousehold' => '/v1/Households',
				'getHouseholdMembers' => '/v1/Households/{householdID}/People',
				'listHouseholdMemberTypes' => '/v1/People/HouseholdMemberTypes',
				'searchHouseholdsByName' => '/v1/Households/Search',													
			),
			
			'people' => array(
				'peopleSearch' => '/v1/People/Search',
				'newPerson' => '/v1/People/new',
				'createPerson' => '/v1/People',
				'updatePerson' => '/v1/People/{personID}',
				'getPersonById' => '/v1/People/{personID}',
				'searchPeople' => '/v1/People/Search',
				'getStatuses' => '/v1/People/Statuses'
			),
		);		
		
	
		/**
		 * contruct make a new F1 Group class and pass an F1 Settings array to it. This
		 * is how you control whether or not it's pulling from live or staging.
		 * @param array $settings
		 */
		public function __construct($f1CoreObj){
			//create the core F1 Obj that will let us make API calls
			$this->f1CoreObj = $f1CoreObj;
		}	
	

		/**
		 * fetch a blank person model from F1. This should always be done before creating
		 */
		public function getPersonModel(){
			$url = $this->f1CoreObj->baseUrl . $this->paths['people']['newPerson'] . ".json";
			return $this->f1CoreObj->fetchGetJson($url);
		}
		
		/**
		 * create new person record. Pass in the person model you get from calling 'getPersonModel
		 * @param object $model
		 */
		public function createPerson($model){
			$url = $this->f1CoreObj->baseUrl . $this->paths['people']['createPerson'] . ".json";
			//always json_encode the model 
			$model = json_encode($model);
			return $this->f1CoreObj->fetchPostJson($url,$model);
		}

		/**
		 * This function will update a person. You pass in their person object from the F1 API and it will update any fields
		 * that you've changed in your application. Remember, "PERSON" needs to be the root node of the model you pass in!
		 * @param array $model
		 */
		public function updatePerson($model) {
								
			//build url with id
			$url = str_replace('{personID}',$model['person']['@id'], $this->f1CoreObj->baseUrl . $this->paths['people']['updatePerson'] . ".json");
			
			return $this->f1CoreObj->fetchPostJson($url,json_encode($model));			
		}
		
		/**
		 * This function will get a person's record by their ID. Pass in an F1 ID and it will return a fully
		 * baked person model that you can edit/change and the send to the update function or elsewhere.
		 * @param string $pid
		 */
		public function getPersonById($pid) {					
			//build url with id
			$url = str_replace('{personID}',$pid, $this->f1CoreObj->baseUrl . $this->paths['people']['getPersonById'] . ".json");						
			return $this->f1CoreObj->fetchGetJson($url);			
		}		
		
		/**
		 * search people by searching the various attributes you can search by. See this page in the F1 docs to see
		 * how you can search. Pass an include string 'addresses,communications,attributes' to get back additional
		 * information with your search
		 * http://developer.fellowshipone.com/docs/v1/People.help#search
		 * @param array $attributes
		 * @param string $include		 
		 */
		public function searchPeople($attributes,$include = ''){
			
			$url = $this->f1CoreObj->baseUrl . $this->paths['people']['searchPeople'] . ".json";
			$url .= "?" . http_build_query($attributes);
			
			if ($include != '') {
				//tack on the include string if one was passed in
				$url .= '&include=' . $include;
			}

			$url .= '&includeInactive=False';
			
			return $this->f1CoreObj->fetchGetJson($url);	
		}
		
		/** 
		 * Get a list of all the current status from F1.  This can be useful when you need to set a status or look for a particular status
		 */
		public function listStatuses() {
			$url = $this->f1CoreObj->baseUrl . $this->paths['people']['getStatuses'] . '.json';			
			return $this->f1CoreObj->fetchGetJson($url);						
		}
		
		/**
		 * This function takes a status name and sub-status and returns you the status obj (which will include any sub-statuses) out of F1 so you can use it to set a person object status. 
		 * Be sure the status is already created in F1, as you cannot create new statuses through the API. You can optionally pass a sub-status filter. It will return an array of status ID and 
		 * sub-status ID
		 * @param string $status
		 * @param string $subStatus 
		 */
		
		public function getStatusByName($statusName,$subStatus = '') {
			
			$returnArr = array();
			
			$statusArr = $this->listStatuses();
			
			foreach ($statusArr['statuses']['status'] as $statusObj) {
				if ($statusObj['name'] == $statusName) {
					//if the status name matched, add it to the array, then look at the substatus if need be
					$returnArr['statusID'] = $statusObj['@id'];

					if ($subStatus != '') {
						//make sure there are sub statuses to loop through
						if (is_array($statusObj['subStatus'])) {
							foreach ($statusObj['subStatus'] as $subStatusObj) {							
								if ($subStatusObj['name'] == $subStatus) {
									$returnArr['subStatusID'] = $subStatusObj['@id'];								
									return $returnArr;
								}
							}											
						} else {
							return $returnArr;
						}

					} else {
						return $returnArr;
					}
 
				}
				
			}
			
			return false;
					
		}
		
	
		/**********************************
		 HOUSEHOLD FUNCTIONS FROM HERE DOWN
		 * 
		 **********************************/
	
		/**
		 * fetch household model from F1
		 */
		public function getHouseholdModel(){
			$url = $this->f1CoreObj->baseUrl . $this->paths['households']['newHousehold'] . ".json";
			return $this->f1CoreObj->fetchGetJson($url);
		}
		
		/**
		 * create new household. Need to pass in a fully baked model obj from F1 (see 'getHouseholdModel')
		 * @param object $model
		 */
		public function createHousehold($model){
			$url = $this->f1CoreObj->baseUrl . $this->paths['households']['createHousehold'] . ".json";
			//always json_encode the model 
			$model = json_encode($model);
			return $this->f1CoreObj->fetchPostJson($url,$model);
		}	

		/**
		 * fetch household member types (Head, Spouse, Child, etc.) from F1. This is used when you're
		 * creating people and you need to know the type to set them to.
		 */
		public function listHouseholdMemberTypes(){
			$url = $this->f1CoreObj->baseUrl . $this->paths['households']['listHouseholdMemberTypes'] . ".json";
			return $this->f1CoreObj->fetchGetJson($url);
		}
		
		/**
		 * fetch households by name search. Pass in any part of the name
		 * @param string $name
		 */
		public function searchHouseholdsByName($name){
			$url = $this->f1CoreObj->baseUrl . $this->paths['households']['searchHouseholdsByName'] . ".json";
			$url .= "?searchFor=" . urlencode($name);
			return $this->f1CoreObj->fetchGetJson($url);	
		}	
		
		/**
		 * This function will get a household by ID and get members listed in whatever positions ('head', 'spouse', etc.)
		 * are passed in. If nothing is passed in, it will return the entire household. Pass in TRUE for the include inactive
		 * parameters, if you want inactive people back. Otherwise, it will NOT include inactive
		 * @param string $hid
		 * @param array $members
		 * @param boolean $includeInactive
		 */
		
		function getHouseholdMembers($hid,$members = array(), $includeInactive=false) {
		
			$people = array();

			if (!$hid) {
				//if no ID passed in, return ''
				return false;
			}
			
			$url = str_replace('{householdID}',$hid, $this->f1CoreObj->baseUrl . $this->paths['households']['getHouseholdMembers'] . ".json");			
			$householdMembers = $this->f1CoreObj->fetchGetJson($url);
			
			if (is_array($members) && count($members) == 0) {
				//if no household positions passed in, then return everybody
				return $householdMembers;	
			} else {
				//loop through household members looking for people in positions that match the array
				//passed in	
				if (is_array($householdMembers['people']['person'])) {
						
					foreach ($householdMembers['people']['person'] as $memberObj) {
																							
						if (in_array($memberObj['householdMemberType']['name'],$members)) {
							
							//if you've passed the first test of just being in the members array 
							//then check for inactive
							if ($includeInactive) {
								//if we're not checking for inactive, then just add it into the array
								array_push($people,$memberObj);																
							} else {
								//check for inactive
								if ($memberObj['status']['name'] != 'Inactive Member' && $memberObj['status']['name'] != 'Dropped' && $memberObj['status']['name'] != 'Deceased' ) {
																			
									array_push($people,$memberObj);		
								}																
							}							
													
						}
				
					}				
				} else {
					//if you get back a bad response from API
					return false;
				}
				
				return $people;
				
			}
		}		
		
			
		
		
		
		
	}
    
    
    
    
    
?>