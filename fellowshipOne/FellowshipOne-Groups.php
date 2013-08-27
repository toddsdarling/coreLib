<?php
    
    require_once('FellowshipOne.php');
	
	class FellowshipOneGroups {
	
		//this will store a reference to the core F1 Obj so we can make API calls
		private $f1CoreObj;
		private $paths = array(			
			'search'=>'/groups/v1/groups/search.json?',			
		);		
		
	
		/**
		 * contruct make a new F1 Group class and pass an F1 Settings array to it. This
		 * is how you control whether or not it's pulling from live or staging.
		 * @param array $settings
		 */
		public function __construct($f1Settings){
			//create the core F1 Obj that will let us make API calls
			$this->f1CoreObj = new FellowshipOne($f1Settings);
			//attempt to login right when the object is created
			if(($r = $this->f1CoreObj->login() === false)) {
				//return false
				return false;		
			} 			

		}	
	
		
		/**
		 * search groups in F1 using parameters supplied as an array
		 * @param array $searchParams
		 */
		public function searchGroupsByLatLong($params){
			//parse out params and add them to the url			
			$url = $this->f1CoreObj->settings['baseUrl'] . $this->paths['search'] . 'latitude=' . $params['latitude'] . '&longitude=' . $params['longitude'] . '&' . $params['childcare'] . '&' . $params['searchable'] . '&radius=5&radiusUnit=mi&recordsPerPage=500';
			return $this->f1CoreObj->fetchGetJson($url);
		}		
		
		
		
	}
    
    
    
    
    
?>