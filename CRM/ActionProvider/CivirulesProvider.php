<?php

class CRM_ActionProvider_CivirulesProvider extends \Civi\ActionProvider\Provider {
	
	/**
	 * @var array
	 */
	protected $civirulesActionNames = null;
	
	public function __construct() {
		$this->getCiviRulesActionNames();
		parent::__construct();
	}
	
	/**
	 * Filter the actions array and keep certain actions.
	 * 
	 * This function might be override in a child class to filter out certain actions which do
	 * not make sense in that context. E.g. for example CiviRules has already a AddContactToGroup action 
	 * so it does not make sense to use the one provided by us.
	 * 
	 * @param \Civi\ActionProvider\Action\AbstractAction $action
	 *   The action to filter.
	 * @return bool
	 *   Returns true when the element is valid, false when the element should be disregarded.
	 */
	protected function filterActions(\Civi\ActionProvider\Action\AbstractAction $action) {
		$civirulesActionNames = $this->getCiviRulesActionNames();
		
		// Check whether an action with the same name already exists in Civirules
		// if so we filter out the action provided by the action-provider extension.
		$actionName = strtolower($action->getName());
		if (in_array($actionName, $civirulesActionNames)) {
			return false; 
		}
		
		// Filter out data-manupilation tags
		if (in_array(\Civi\ActionProvider\Action\AbstractAction::DATA_MANIPULATION_TAG, $action->getTags())) {
			return false;
		}
		
		// Check whether one of the tags exists as an existing civirule action.
		// If so remove the action providedby the action-provider extension.
		// For testing purposes we keep both actions.
		/*foreach($action->getTags() as $tag) {
			if (in_array(strtolower($tag), $civirulesActionNames)) {
				return false;
			}
		}*/
		
		return true;
	}
	
	/**
	 * Get all civirules action names so we can use it in the filter
	 * to filter out duplicate actions.
	 */
	protected function getCiviRulesActionNames() {
		if (!is_array($this->civirulesActionNames)) {
			$actions = CRM_Civirules_BAO_Action::getValues(array());
    	foreach ($actions as $actionId => $action) {
    		$name = strtolower($action['name']);
      	$this->civirulesActionNames[] = $name;
    	}
		}
		return $this->civirulesActionNames;
	}
	
}
