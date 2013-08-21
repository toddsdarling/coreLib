<?php
    
    require_once('FellowshipOne.php');
	
	class FellowshipOneGroups {
	
		//this will store a reference to the core F1 Obj so we can make API calls
		private $f1CoreObj;
		
		private $baseUrl = 'https://gethope.fellowshiponeapi.com';
		private $paths = array(
			'groups' => array(
				'search'=>'/groups/v1/groups/search.json?',
			),			
		);		
		
	
		/**
		 * contruct make a new F1 Group class and pass the core F1 Obj to it. This should
		 * already be created in the application using this
		 * @param unknown_type $settings
		 */
		public function __construct($f1Obj){
			$this->f1CoreObj = $f1Obj;
		}	
	
		
		/**
		 * search groups in F1 using parameters supplied as an array
		 * @param array $searchParams
		 */
		public function searchGroupsByLatLong($params){
			//parse out params and add them to the url
			var_dump($this->f1CoreObj->settings);
			$url = $this->baseUrl . $this->paths['groups']['search'] . 'latitude=' . $params['latitude'] . '&longitude=' . $params['longitude'] . '&' . $params['childcare'] . '&' . $params['searchable'] . '&radius=5&radiusUnit=mi&recordsPerPage=500';
			return $this->f1CoreObj->fetchGetJson($url);
		}		
		
		
		
	}
    
    
    
    
    
?>