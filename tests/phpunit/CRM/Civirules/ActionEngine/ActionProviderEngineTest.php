<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * Tests to test the action engine for action provider actions
 * 
 * @group headless
 */
class CRM_Civirules_ActionEngine_ActionProviderEngineTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {
	
	protected $contactId;
  protected $groupId;
	
	public function setUpHeadless() {
    // Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
    // See: https://github.com/civicrm/org.civicrm.testapalooza/blob/master/civi-test.md
    return \Civi\Test::headless()
      ->installMe(__DIR__)
			->install('action-provider')
      ->apply();
  }
	
	public function setUp() {
    $result = civicrm_api3("Contact","create",array(
      'contact_type' => 'Individual',
      'first_name' => 'Adele',
      'last_name'  => 'Jensen'
    ));
    $this->contactId=$result['id'];

    $result = civicrm_api3('Group','create',array(
      'title' => "TestGroup",
      'name' => "test_group",
    ));
    $this->groupId = $result['id'];

    parent::setUp();
  }
	
	public function tearDown() {
    parent::tearDown();
  }
	
	public function testActionEngineExecutionWithoutAnyDelay() {
		$actionProviderContainer = civirules_get_action_provider();
		$this->assertInstanceOf('\Civi\ActionProvider\Provider', $actionProviderContainer, 'The action-provider extension is not installed');
		
		// Fake the execution of an action AddContactToGroup
		$ruleAction = array(
			'id' => microtime(), // use time as a unique identifier
			'action_id' => null,
			'action_name' => 'Civi\\ActionProvider\\Action\\AddToGroup',
			'action_params' => json_encode(array('group_id' => $this->groupId)),
			'delay' => null,
			'ignore_condition_with_delay' => 0,
			'is_active' => 1,
		);
		
		$contact = civicrm_api3('Contact', 'getsingle', array('id' => $this->contactId));
		$triggerData = new CRM_Civirules_TriggerData_Post('Individual', $contact['id'], $contact);
		
		$actionEngine = CRM_Civirules_ActionEngine_Factory::getEngine($ruleAction, $triggerData);
		$this->assertInstanceOf('CRM_Civirules_ActionEngine_ActionProviderEngine', $actionEngine, 'Could not find valud engine for rule_action');
		$actionEngine->execute();
		// Now test whether the contact is added to the group
		$groupContactParams = array(
      'contact_id' => $this->contactId,
      'group_id' => $this->groupId,
      'version' => 3,
    );
    $groupContact = civicrm_api('group_contact', 'getsingle', $groupContactParams);
		$this->assertEquals($this->groupId, $groupContact['group_id'], 'There was an error getting the group. Possibly the engine failed and the contact was not added to the group');
	}
	
}