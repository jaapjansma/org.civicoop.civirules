<?php

class CRM_Civirules_ActionEngine_Factory {
	
	private static $instances = array();
	
	/**
	 * Returns the engine for executing actions.
	 * 
	 * @param array $ruleAction
	 *   Data from the ruleAction object.
	 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
	 *   Data from the trigger.
	 */
	public static function getEngine($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData = null) {
		$id = $ruleAction['id'];
		if (!isset(self::$instances[$id])) {
			// This is the place where could add other engine to the system.
			if (!empty($ruleAction['action_id'])) {
				self::$instances[$id] = new CRM_Civirules_ActionEngine_RuleActionEngine($ruleAction, $triggerData);
			} elseif (!empty($ruleAction['action_name'])) {
				self::$instances[$id] = new CRM_Civirules_ActionEngine_ActionProviderEngine($ruleAction, $triggerData);
			}
		}
		return self::$instances[$id];
	}
	
	/**
   * Function to build the action list
   *
   * @return array $actionList
   * @access public
   * @static
   */
  public static function buildActionList() {
    $actionList = array();
    $actionProvider = civirules_get_action_provider();
		if ($actionProvider) {
    	$actions = $actionProvider->getActions();
			foreach($actions as $action) {
				$actionList[$action->getName()] = $action->getTitle();
			}
		}
		
		$actions = CRM_Civirules_BAO_Action::getValues(array());
    foreach ($actions as $actionId => $action) {
      $actionList[$actionId] = $action['label'];
    }
		
		asort($actionList);
    return $actionList;
  }
	
}
