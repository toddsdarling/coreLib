<?php
    
    require_once('FellowshipOne.php');
	
	class FellowshipOneGroups {
	
		//this will store a reference to the core F1 Obj so we can make API calls
		private $f1CoreObj;
		private $paths = array(			
			'search'=>'/groups/v1/groups/search.json?',
			'getGroupTypes' => '/groups/v1/grouptypes/search',
			'listGroupsByType'=>'/groups/v1/grouptypes/{groupTypeID}/groups',
			'getMemberModel'=>'/groups/v1/groups/{id}/members/new'	
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
		 * search groups in F1 using parameters supplied as an array
		 * params array should have latitude, longitude, and then optional childcare parameters
		 * the 'issearchable' parameter will always default to true
		 * @param array $searchParams
		 */
		public function searchGroupsByLatLong($params){
			//parse out params and add them to the url	
			//latitude and longitude params are required
			if (!$params['latitude'] || !$params['longitude']) {
				return false;
			} else {
					
				$paramString = 'latitude=' . $params['latitude'] . '&longitude=' . $params['longitude'];
				
				if ($params['childcare']) {
					$paramString .= '&' . $params['childcare'];
				}
				
				if ($params['searchable']) {
					$paramString .= '&' . $params['searchable'];
				}
				
				//add the other params in at the end
				$paramString .= '&radius=15&radiusUnit=mi&recordsPerPage=500'; 
			
			}
		
			$url = $this->f1CoreObj->baseUrl . $this->paths['search'] . $paramString;
			return $this->f1CoreObj->fetchGetJson($url);
		}

		/**
		search groups in F1 with just a supplied array of params (lat/lng CAN be included or not)
		pass in an array of params to search. NOTE: These MUST match the param names you can search for in F1. IsSearchable will always default to true unless passed in
		@param array $searchParams			
		*/

		public function searchGroupsByParams($params) {
			//parse out params and add them to the url	
			$paramString = '';
			$paramArray = array();


			//latitude and longitude params are required
			foreach ($params as $key => $value) {
				//build array with each key value string
				array_push($paramArray, $key . '=' . $value);
			}

			//then, join that array with the '&' to make the URL string
			$paramString = implode('&', $paramArray);

			//check to see if isSearchable was explicity passed in
			//if not, default it to true
			if (!array_key_exists('searchable',$params)) {
				$paramString .= '&issearchable=true';
			}
						
			$url = $this->f1CoreObj->baseUrl . $this->paths['search'] . '&' . $paramString;
			return $this->f1CoreObj->fetchGetJson($url);			

		}


		/*
		 * list groups within a certain type.  If you don't specify a type or it can't find that group type, it will return false.
		 * If there are no groups within a certain type, it will just return false
		 * @param $type string
		 */
		public function listGroupsByType($type) {
					
			//need to make the API call to get the group types		
			//build URL			
			$groupTypeURL = $this->f1CoreObj->baseUrl . $this->paths['getGroupTypes'] . '.json?searchFor=' . rawurlencode($type);
			$groupTypes = $this->fetchGetJson($groupTypeURL);
			
			//we just look at the first group type that comes out.  If there's multiple we only take one
			if ($groupTypes) {
				if ($groupTypes['groupTypes']['@count'] == 0) {
					return false;
				} else {
					$typeID = $groupTypes['groupTypes']['groupType'][0]['@id'];
				}								
			} else {
				return false;
			}
			
			//now make the API call to get the groups in this type
			$url = str_replace('{groupTypeID}',$typeID,$this->f1CoreObj->baseUrl . $this->paths['listGroupsByType'] . ".json");
			$groups = $this->fetchGetJson($url);
			
			return $groups;					
		}
		
		/**
		 * This will get a blank member model for the group ID that you pass in. Then, just fill in the person ID in the member model 
		 * and then you can add that person to a group
		 * @param $gid string
		 */
		public function getGroupMemberModel($gid) {
			
			if (!$gid) {
				//must pass GID or return false
				return false;
			}
			
			$modelURL = str_replace('{groupID}',$gid,$this->f1CoreObj->baseUrl . $this->paths['getMemberModel'] . ".json");
			$memberModel = $this->fetchGetJson($modelURL);
			
			return $memberModel;		
			
		}
		
		
	}
    
    
    
    
    
?>