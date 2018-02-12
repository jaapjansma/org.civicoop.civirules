<?php

require_once 'civirules.civix.php';
if (!interface_exists("\\Psr\\Log\\LoggerInterface")) {
  require_once('psr/log/LoggerInterface.php');
}
if (!class_exists("\\Psr\\Log\\LogLevel")) {
  require_once('psr/log/LogLevel.php');
}

/**
 * Returns the action provider container. This container only exists 
 * if the action-provider extension is installed.
 */
function civirules_get_action_provider() {
	$civi_container = \Civi::container();
	if ($civi_container->has('action_provider')) {
		if (!$civi_container->get('action_provider')->hasContext('civirules')) {
			$civi_container->get('action_provider')->addProviderWithContext('civirules', new CRM_ActionProvider_CivirulesProvider());
		}
		return $civi_container->get('action_provider')->getProviderByContext('civirules');
	}
	return false;
}

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function civirules_civicrm_config(&$config) {
  _civirules_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function civirules_civicrm_xmlMenu(&$files) {
  _civirules_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function civirules_civicrm_install() {
  return _civirules_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function civirules_civicrm_postInstall() {
  _civirules_civix_civicrm_postInstall();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function civirules_civicrm_uninstall() {
  return _civirules_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function civirules_civicrm_enable() {
  return _civirules_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function civirules_civicrm_disable() {
  return _civirules_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function civirules_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _civirules_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function civirules_civicrm_managed(&$entities) {
  return _civirules_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function civirules_civicrm_caseTypes(&$caseTypes) {
  _civirules_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function civirules_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _civirules_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implementation of hook civicrm_navigationMenu
 * to create a CiviRules menu item in the Administer menu
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function civirules_civicrm_navigationMenu( &$params ) {
  //  Get the maximum key of $params
  $maxKey = CRM_Civirules_Utils::getMenuKeyMax($params);
  $childKey = 1;
  // retrieve the custom search id of the find rules search
  $customSearchID = CRM_Civirules_Utils::getFindRulesCsId();
  // retrieve the option group id of the rule tags option group
  $optionGroup = CRM_Civirules_Utils_OptionGroup::getSingleWithName('civirule_rule_tag');

  $params[$maxKey + 1] = array(
    'attributes' => array(
      'label' => 'CiviRules',
      'name' => 'CiviRules',
      'url' => null,
      'permission' => 'administer CiviCRM',
      'operator' => null,
      'separator' => null,
      'parentID' => null,
      'navID' => $maxKey + 1,
      'active' => 1
    ));
  // add child menu for find rules if custom search id set
  if (!empty($customSearchID)) {
    $params[$maxKey + 1]['child'][$childKey] = array(
      'attributes' => array(
        'label' => ts('Find Rules'),
        'name' => ts('Find Rules'),
        'url' => CRM_Utils_System::url('civicrm/contact/search/custom', 'reset=1&csid=' . $customSearchID, true),
        'permission' => 'administer CiviCRM',
        'operator' => null,
        'separator' => 0,
        'parentID' => $maxKey + 1,
        'navID' => $childKey,
        'active' => 1
      ),
      'child' => null
    );
    $childKey++;
  }
  $params[$maxKey + 1]['child'][$childKey] = array(
    'attributes' => array(
      'label' => ts('New Rule'),
      'name' => ts('New Rule'),
      'url' => CRM_Utils_System::url('civicrm/civirule/form/rule', 'reset=1&action=add', true),
      'permission' => 'administer CiviCRM',
      'operator' => null,
      'separator' => 0,
      'parentID' => $maxKey + 1,
      'navID' => $childKey,
      'active' => 1
    ),
    'child' => null
  );
  $childKey++;
  // add child menu for rule tags if option group id set with version check because 4.4 has other url pattern
  if (isset($optionGroup['id']) && !empty($optionGroup['id'])) {
    try {
      $apiVersion = civicrm_api3('Domain', 'getvalue', array('current_domain' => "TRUE", 'return' => 'version'));
      $civiVersion = (float) substr($apiVersion, 0, 3);
      if ($civiVersion < 4.6) {
        $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/optionValue', 'reset=1&gid='.$optionGroup['id'], true);
      } else {
        $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/options', 'reset=1&gid='.$optionGroup['id'], true);
      }
    } catch (CiviCRM_API3_Exception $ex) {
      $ruleTagUrl = CRM_Utils_System::url('civicrm/admin/options', 'reset=1&gid='.$optionGroup['id'], true);
    }

    $params[$maxKey + 1]['child'][$childKey] = array(
      'attributes' => array (
        'label'      => ts('CiviRule Tags'),
        'name'       => ts('CiviRules Tags'),
        'url'        => $ruleTagUrl,
        'permission' => 'administer CiviCRM',
        'operator'   => null,
        'separator'  => 0,
        'parentID'   => $maxKey+1,
        'navID'      => $childKey,
        'active'     => 1
      ),
      'child' => null
    );
  }
}

function civirules_civicrm_pre($op, $objectName, $objectId, &$params) {
  CRM_Civirules_Utils_PreData::pre($op, $objectName, $objectId, $params);
  CRM_Civirules_Utils_CustomDataFromPre::pre($op, $objectName, $objectId, $params);
}

function civirules_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  CRM_Civirules_Trigger_Post::post($op, $objectName, $objectId, $objectRef);
}

function civirules_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  CRM_CivirulesPostTrigger_CaseCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_ContactCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_IndividualCustomDataChanged::validateForm($form);
  CRM_CivirulesPostTrigger_OrganizationCustomDataChanged::validateForm($form);
}

function civirules_civicrm_custom($op, $groupID, $entityID, &$params) {
  CRM_CivirulesPostTrigger_CaseCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_ContactCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_IndividualCustomDataChanged::custom($op, $groupID, $entityID, $params);
  CRM_CivirulesPostTrigger_OrganizationCustomDataChanged::custom($op, $groupID, $entityID, $params);
}

function civirules_civirules_alter_trigger_data(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
  //also add the custom data which is passed to the pre hook (and not the post)
  CRM_Civirules_Utils_CustomDataFromPre::addCustomDataToTriggerData($triggerData);
}

/**
 * Register extensions entities.
 *
 * Required for api calls.
 *
 * @param array $entityTypes
 */
function civirules_civicrm_entityTypes(&$entityTypes) {
  $entityTypes['CRM_Civirules_BAO_Action'] = array(
    'name' => 'CiviRuleAction',
    'class' => 'CRM_Civirules_BAO_Action',
    'table' => 'civirule_action',
  );
  $entityTypes['CRM_Civirules_BAO_Condition'] = array(
    'name' => 'CiviRuleCondition',
    'class' => 'CRM_Civirules_BAO_Condition',
    'table' => 'civirule_condition',
  );
  $entityTypes['CRM_Civirules_BAO_Trigger'] = array(
    'name' => 'CiviRuleTrigger',
    'class' => 'CRM_Civirules_BAO_Trigger',
    'table' => 'civirule_trigger',
  );
  $entityTypes['CRM_Civirules_BAO_Rule'] = array(
    'name' => 'CiviRuleRule',
    'class' => 'CRM_Civirules_BAO_Rule',
    'table' => 'civirule_rule',
  );
}
