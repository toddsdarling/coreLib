<?php
    
    require_once('FellowshipOne.php');
	
	class FellowshipOneAttributes {
	
		//this will store a reference to the core F1 Obj so we can make API calls
		private $f1CoreObj;
		private $paths = array(
			'getAttributeModelByPerson' => '/v1/People/{personID}/Attributes/new',
			'setAttribute' => '/v1/People/{personID}/Attributes',
			'listAttributeGroups' => '/v1/People/AttributeGroups',
			'getPersonAttributes' => '/v1/People/{personID}/Attributes',
			'updatePersonAttributes'=> '/v1/People/{personID}/Attributes/{attributeID}'			
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
		 * This function will return an attribute model for the person ID you pass in. Fill that in and then
		 * you can send it back to the setAttribute function
		 */
		 
		public function getAttributeModelByPerson($id) {
			$url = str_replace('{personID}',$id, $this->f1CoreObj->baseUrl . $this->paths['getAttributeModelByPerson'] . ".json");			
			return $this->f1CoreObj->fetchGetJson($url);
		}
		
		/**
		 * This function only takes a fully baked attribute model and sets it on the person. If you want to set an attribute by group name/attribute name, use
		 * this setAttributeByName function (which actually calls this function at the end)
		 * @param array $model
		 */
		
		public function setAttribute($model) {			
			$url = str_replace('{personID}',$model['attribute']['person']['@id'], $this->f1CoreObj->baseUrl . $this->paths['setAttribute'] . ".json");
			return $this->f1CoreObj->fetchPostJson($url,json_encode($model));
		}		
		
		/**
		 * This is a utility function to get a list of all the current attribute groups out of F1.  The setAttributeByName funciton uses this function
		 */		
		
		public function listAttributeGroups() {
			$url = $this->f1CoreObj->baseUrl . $this->paths['listAttributeGroups'] . ".json";
			return $this->f1CoreObj->fetchGetJson($url);			
		}	
		
		/**
		 * This is a helper function to make it easer to set an attribute by name on a person. This does NOT check for a duplicate attribute. 
		 * It will just set the attribute regardless.
		 * @param string $attributeGroup
		 * @param string $attributeName
		 * @param string $personID
		 * @param string $comment
		 */
		 			
		function setAttributeByName($attributeGroup, $attributeName,$personID, $comment = '') {
			
			$today = new DateTime('now');
			
			//set up the attribute group, attribute name variables for current F1 people or new people.
			$attributeGroups = $this->listAttributeGroups();
									
			if ($attributeGroups) {							
				foreach ($attributeGroups['attributeGroups']['attributeGroup'] as $attrGroupObj) {

					//only look for the campus group					
					if ($attrGroupObj['name'] == $attributeGroup) {								
						//set the group ID
						$groupID = $attrGroupObj['@id'];																	
						//loop through these substatus
						foreach ($attrGroupObj['attribute'] as $attrObj) {						
							if (strstr($attrObj['name'], $attributeName)) {
								$attrid = $attrObj['@id'];
								break;
							}						
						}					
					}							
				}	
			}
			
			if (isset($groupID) && isset($attrid)) {
				
				$attributeModel = $this->getAttributeModelByPerson($personID);			
				//fill it in
				$attributeModel['attribute']['attributeGroup']['@id'] = $groupID;
				$attributeModel['attribute']['attributeGroup']['attribute']['@id'] = $attrid;
				if ($comment != '') {
					$attributeModel['attribute']['comment'] = $comment;
				}
				$attributeModel['attribute']['startDate'] = $today->format(DATE_ATOM);		
			
				//set the attributes
				$a = $this->setAttribute($attributeModel);
				return $a;				
			} else {
				//if we didn't get a group ID and attribute ID, then return false
				return false;
			}
					

			
		}	


		/**
		 * This function will get all the attributes for a person based on their F1 ID
		 * @param string $pid
		 */
		 function getPersonAttributes($pid) {
		 	$url = str_replace('{personID}',$pid, $this->f1CoreObj->baseUrl . $this->paths['getPersonAttributes'] . '.json');
			return $this->f1CoreObj->fetchGetJson($url);			
		 }
		 
		 /**
		  * This function will update an attribute for a person based on their ID. Pass the ID and the attribute model
		  * @param string $pid
		  * @param array $model
		  */
		  
		  function updatePersonAttribute($pid,$model) {
		  	$url = str_replace('{personID}',$pid, $this->f1CoreObj->baseUrl . $this->paths['updatePersonAttributes'] . '.json');
			$url = str_replace('{attributeID}',$model['attribute']['@id'], $url);					
		  	return $this->f1CoreObj->fetchPostJson($url,json_encode($model));
		  }

}
    
    
?>