<?php

abstract class CRM_Civirules_ActionEngine_AbstractActionEngine {
	
	/**
	 * @var array
	 */
	protected $ruleAction;
	
	/**
	 * @var CRM_Civirules_TriggerData_TriggerData
	 */
	protected $triggerData;
	
	/**
	 * Function to execute the rule action.
	 * 
	 * @return void
	 */
	abstract public function execute();
	
	/**
	 * Function to calculate the delay of the action.
	 * 
	 * @param $delayedTo
	 * @return false|DateTime
	 */
	abstract public function delayTo($delayedTo);
	
	/**
	 * Returns the title of the action
	 * 
	 * @return string
	 */
	abstract public function getTitle();
	
	/**
	 * Returns the formatted configuration of the action
	 * 
	 * @return string
	 */
	abstract public function getFormattedConfiguration();
	
	/**
	 * Returns the url for extra data input or false when no extra input is required.
	 * 
	 * @return false|string
	 */
	abstract public function getExtraDataInputUrl();
	
	/**
   * This function validates whether this action works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether an action is possible in the current setup. 
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return true;
  }
	
	/**
	 * @param array $ruleAction
	 *   Data from the ruleAction object.
	 * @param CRM_Civirules_TriggerData_TriggerData $triggerData
	 *   Data from the trigger.
	 */
	public function __construct($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData = null) {
		$this->ruleAction = $ruleAction;
		$this->triggerData = $triggerData;
	}
	
	/**
	 * @return array
	 */
	public function getRuleAction() {
		return $this->ruleAction;
	}
	
	/**
	 * @return CRM_Civirules_TriggerData_TriggerData
	 */
	public function getTriggerData() {
		return $this->triggerData;
	}
	
}
