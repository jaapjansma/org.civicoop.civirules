<?php

use \Civi\ActionProvider\Action\AbstractAction;
use \Civi\ActionProvider\Parameter\ParameterBag;
use \Civi\ActionProvider\Provider;

/**
 * Class to execute actions provided by the action-provider extension.
 */ 
 class CRM_Civirules_ActionEngine_ActionProviderEngine extends CRM_Civirules_ActionEngine_AbstractActionEngine {
	
	/**
	 * @var AbstractAction
	 */
	protected $action;
	
	public function __construct($ruleAction, CRM_Civirules_TriggerData_TriggerData $triggerData=null) {
		parent::__construct($ruleAction, $triggerData);
		
		$actionProvider = civirules_get_action_provider();
		$this->action = $actionProvider->getActionByName($ruleAction['action_name']);
		
		if (!$this->action) {
			throw new Exception('Could not instanciate action for ruleAction with action_name: '.$ruleAction['action_name']);
		}
		
		if (isset($ruleAction['action_params'])) {
			$configurationData = json_decode($ruleAction['action_params'], true);
			$configuration = new ParameterBag();
			foreach($this->action->getConfigurationSpecification() as $config_field) {
	  		if (isset($configurationData[$config_field->getName()])) {
	  			$configuration->setParameter($config_field->getName(), $configurationData[$config_field->getName()]);
				}
			}
			$this->action->setConfiguration($configuration);
		}
	}
 	
	/**
	 * Function to execute the rule action.
	 * 
	 * @return void
	 */
	public function execute() {
		$parameters = $this->getParameterBag(); 
		$this->action->execute($parameters);
	}
	
	/**
	 * @return ParameterBag
	 */
	protected function getParameterBag() {
		$parameters = new ParameterBag();
		$parameters->setParameter('contact_id', $this->triggerData->getContactId());
		
		$data = $this->triggerData->getAllData();
		foreach($data as $entity => $entity_data) {
			foreach($entity_data as $field => $value) {
				$name = $entity.'_'.$field;
				$parameters->setParameter($name, $value);
			}
		}
		
		// Convert all custom field data.
		// If a field is a multi record field then only add the first record.
		// 
		// It is not sure whether the code below works as expected. That also depends on how the action is using custom fields
		// and as soon as there is a working implementation for that we can see whether we have to refactor the code below.
		// Therefor the code below is in comment.
		//$customFieldData = $this->triggerData->getCustomFieldData();
		//foreach($customFieldData as $custom_field_id => $records) {
		//	if (!empty($records)) {
		//		$parameters->setParameter('custom_'.$custom_field_id, reset($records));
		//	}
		//}
		
		return $parameters;
	}
	
	/**
	 * Function to calculate the delay of the action.
	 * 
	 * @return DateTime|false
	 */
	public function delayTo($delayedTo) {
		return false;
	}
	
	/**
	 * Returns the title of the action
	 * 
	 * @return string
	 */
	public function getTitle() {
		return $this->action->getTitle();
	}
	
	/**
	 * Returns the formatted configuration of the action
	 * 
	 * @return string
	 */
	public function getFormattedConfiguration() {
		$return = '';	
		foreach($this->action->getConfiguration() as $field => $value) {
			$config = $this->action->getConfigurationSpecification()->getSpecificationByName($field);
			if (strlen($return)) {
				$return .= ', ';
			}
			if (is_array($value)) {
				$value = implode(',', $value);
			}
			$return .= $config->getName() . ' = ' . $value;
		}
		return $return;
	}
	
	/**
	 * Returns the url for extra data input or false when no extra input is required.
	 * 
	 * @return false|string
	 */
	public function getExtraDataInputUrl() {
		return CRM_Utils_System::url('civicrm/civirules/actionprovider/config', 'rule_action_id='.$this->ruleAction['id']);
	}
	
 }
