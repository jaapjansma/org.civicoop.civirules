<?php

/**
 * Class to execute actions provided by civirules
 */
 
 class CRM_Civirules_ActionEngine_RuleActionEngine extends CRM_Civirules_ActionEngine_AbstractActionEngine {
 	
	/**
	 * @var CRM_Civirules_Action
	 */
	protected $actionClass;
	
	public function __construct($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData=null) {
		parent::__construct($ruleAction, $triggerData);
		$this->actionClass = CRM_Civirules_BAO_Action::getActionObjectById($ruleAction['action_id'], true);
		if (!$this->ruleAction) {
			throw new Exception('Could not instanciate action for ruleAction with action_id: '.$ruleAction['action_id']);
		}
		$this->actionClass->setRuleActionData($ruleAction);
	}
 	
	/**
	 * Function to execute the rule action.
	 * 
	 * @return void
	 */
	public function execute() {
		$this->actionClass->processAction($this->triggerData);
	}
	
	/**
	 * Function to calculate the delay of the action.
	 * 
	 * @return void
	 */
	public function delayTo($delayedTo) {
		return $this->actionClass->delayTo($delayedTo, $this->triggerData);
	}
	
	/**
	 * Returns the title of the action
	 * 
	 * @return string
	 */
	public function getTitle() {
		return CRM_Civirules_BAO_Action::getActionLabelWithId($this->ruleAction['action_id']);
	}
	
	/**
	 * Returns the formatted configuration of the action
	 * 
	 * @return string
	 */
	public function getFormattedConfiguration() {
		return $this->actionClass->userFriendlyConditionParams();
	}
	
	/**
	 * Returns the url for extra data input or false when no extra input is required.
	 * 
	 * @return false|string
	 */
	public function getExtraDataInputUrl() {
		return $this->action->getExtraDataInputUrl($this->ruleAction['id']);
	}
	
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
    return $this->actionClass->doesWorkWithTrigger($trigger, $rule);
  }
	
 }
