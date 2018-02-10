<?php

/**
 * Copyright (C) 2015 Coöperatieve CiviCooP U.A. <http://www.civicoop.org>
 * Licensed to CiviCRM under the AGPL-3.0
 */
class CRM_Civirules_Upgrader extends CRM_Civirules_Upgrader_Base {

  /**
   * Create CiviRules tables on extension install. Do not change the
   * sequence as there will be dependencies in the foreign keys
   */
  public function install() {
    $this->executeSqlFile('sql/createCiviruleAction.sql');
    $this->executeSqlFile('sql/createCiviruleCondition.sql');
    $this->executeSqlFile('sql/createCiviruleTrigger.sql');
    $this->executeSqlFile('sql/insertCiviruleTrigger.sql');
    $this->executeSqlFile('sql/createCiviruleRule.sql');
    $this->executeSqlFile('sql/createCiviruleRuleAction.sql');
    $this->executeSqlFile('sql/createCiviruleRuleCondition.sql');
    $this->executeSqlFile('sql/createCiviruleRuleLog.sql');
    $this->executeSqlFile('sql/createCiviruleRuleTag.sql');
    $ruleTagOptionGroup = CRM_Civirules_Utils_OptionGroup::getSingleWithName('civirule_rule_tag');
    if (empty($ruleTagOptionGroup)) {
      CRM_Civirules_Utils_OptionGroup::create('civirule_rule_tag', 'Tags for CiviRules', 'Tags used to filter CiviRules on the CiviRules page');
    }
  }

  public function uninstall() {
    $this->executeSqlFile('sql/uninstall.sql');
  }

  public function upgrade_1001() {
    if (CRM_Core_DAO::checkTableExists('civirule_rule')) {
      if (CRM_Core_DAO::checkFieldExists('civirule_rule', 'event_id')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE `civirule_rule` ADD event_params TEXT NULL AFTER event_id");
      }
    }

    if (CRM_Core_DAO::checkTableExists("civirule_event")) {
      CRM_Core_DAO::executeQuery("
        INSERT INTO civirule_event (name, label, object_name, op, cron, class_name, created_date, created_user_id)
        VALUES
          ('groupmembership', 'Daily trigger for group members', NULL, NULL, 1, 'CRM_CivirulesCronTrigger_GroupMembership',  CURDATE(), 1);
        ");
    }
    return true;
  }
  /**
   * Method for upgrade 1002
   * (rename events to trigger, check https://github.com/CiviCooP/org.civicoop.civirules/issues/42)
   * - rename table civirule_event to civirule_trigger
   * - rename columns event_id, event_params in table civirule_rule to trigger_id, trigger_params
   * - remove index on event_id
   * - add index on trigger_id
   */
  public function upgrade_1002() {
    // rename table civirule_event to civirule_trigger
    if (CRM_Core_DAO::checkTableExists("civirule_event")) {
      CRM_Core_DAO::executeQuery("RENAME TABLE civirule_event TO civirule_trigger");
    } else {
      $this->executeSqlFile('sql/createCiviruleTrigger.sql');
      $this->executeSqlFile('sql/insertCiviruleTrigger.sql');
    }
    // rename columns event_id and event_params in civirule_rule
    if (CRM_Core_DAO::checkTableExists("civirule_rule")) {
      $this->ctx->log->info('civirules 1002: Drop fk_rule_event, fk_rule_event_idx.');
      if (CRM_Core_DAO::checkConstraintExists('civirule_rule', 'fk_rule_event')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civirule_rule DROP FOREIGN KEY fk_rule_event;");
      }
      if (CRM_Core_DAO::checkConstraintExists('civirule_rule', 'fk_rule_event_idx')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civirule_rule DROP INDEX fk_rule_event_idx;");
      }
      if (CRM_Core_DAO::checkFieldExists('civirule_rule', 'event_id')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civirule_rule CHANGE event_id trigger_id INT UNSIGNED;");
      }
      if (CRM_Core_DAO::checkFieldExists('civirule_rule', 'event_params')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civirule_rule CHANGE event_params trigger_params TEXT;");
      }
      if (!CRM_Core_DAO::checkConstraintExists('civirule_rule', 'fk_rule_trigger')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civirule_rule ADD CONSTRAINT fk_rule_trigger FOREIGN KEY (trigger_id) REFERENCES civirule_trigger(id);");
      }
      if (!CRM_Core_DAO::checkConstraintExists('civirule_rule', 'fk_rule_trigger_idx')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE civirule_rule ADD INDEX fk_rule_trigger_idx (trigger_id);");
      }
    }
    return true;
  }

  /**
   * Executes upgrade 1003
   *
   * Changes the class names in civirule_trigger table becasue those have been changed as well
   *
   * @return bool
   */
  public function upgrade_1003() {
    $this->executeSqlFile('sql/update_1003.sql');
    return true;
  }

  /**
   * Executes upgrade 1004
   *
   * Changes the class for entity triggers
   *
   * @return bool
   */
  public function upgrade_1004() {
    CRM_Core_DAO::executeQuery("update `civirule_trigger` set `class_name` = 'CRM_CivirulesPostTrigger_EntityTag' where `object_name` = 'EntityTag';");
    if (!CRM_Core_DAO::checkFieldExists('civirule_rule_action', 'ignore_condition_with_delay')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE `civirule_rule_action` ADD COLUMN `ignore_condition_with_delay` TINYINT NULL default 0 AFTER `delay`");
    }
    return true;
  }

  public function upgrade_1005() {
    CRM_Core_DAO::executeQuery("update `civirule_trigger` SET `class_name` = 'CRM_CivirulesPostTrigger_Case' where `object_name` = 'Case'");
    return true;
  }

  /**
   * Update for a trigger class for relationships
   *
   * See https://github.com/CiviCooP/org.civicoop.civirules/issues/83
   * @return bool
   */
  public function upgrade_1006() {
    CRM_Core_DAO::executeQuery("update `civirule_trigger` SET `class_name` = 'CRM_CivirulesPostTrigger_Relationship' where `object_name` = 'Relationship'");
    return true;
  }

  /**
   * Update for issue 97 - add description and help_text to civirule_rule
   * See https://github.com/CiviCooP/org.civicoop.civirules/issues/97
   * @return bool
   */
  public function upgrade_1007() {
    if (CRM_Core_DAO::checkTableExists('civirule_rule')) {
      if (!CRM_Core_DAO::checkFieldExists('civirule_rule', 'description')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE `civirule_rule` ADD COLUMN `description` VARCHAR(256) NULL AFTER `is_active`");
      }
      if (!CRM_Core_DAO::checkFieldExists('civirule_rule', 'help_text')) {
        CRM_Core_DAO::executeQuery("ALTER TABLE `civirule_rule` ADD COLUMN `help_text` TEXT NULL AFTER `description`");
      }
    }
    return true;
  }

  /**
   * Update for changed recurring contribution class names
   */
  public function upgrade_1008() {
    $query = 'UPDATE civirule_condition SET class_name = %1 WHERE class_name = %2';
    $paramsRecurCount = array(
      1 => array('CRM_CivirulesConditions_ContributionRecur_Count', 'String'),
      2 => array('CRM_CivirulesConditions_Contribution_CountRecurring', 'String'));
    CRM_Core_DAO::executeQuery($query, $paramsRecurCount);

    $paramsRecurIs = array(
      1 => array('CRM_CivirulesConditions_ContributionRecur_DonorIsRecurring', 'String'),
      2 => array('CRM_CivirulesConditions_Contribution_DonorIsRecurring', 'String'));
    CRM_Core_DAO::executeQuery($query, $paramsRecurIs);

    $paramsRecurEnd = array(
      1 => array('CRM_CivirulesConditions_ContributionRecur_EndDate', 'String'),
      2 => array('CRM_CivirulesConditions_Contribution_RecurringEndDate', 'String'));
    CRM_Core_DAO::executeQuery($query, $paramsRecurEnd);

    return true;
  }

  /**
   * Update to insert the trigger for Activity Date reached
   */
  public function upgrade_1009() {
    CRM_Core_DAO::executeQuery("
      INSERT INTO civirule_trigger (name, label, object_name, op, cron, class_name, created_date, created_user_id)
      VALUES ('activitydate', 'Activity Date reached', null, null, 1, 'CRM_CivirulesCronTrigger_ActivityDate',  CURDATE(), 1);"
    );
    return true;
  }

  /**
   * Update to insert the trigger for Case Activity changed
   */
  public function upgrade_1010() {
    CRM_Core_DAO::executeQuery("
      INSERT INTO civirule_trigger (name, label, object_name, op, class_name, created_date, created_user_id)
      VALUES ('changed_case_activity', 'Case activity is changed', 'Activity', 'edit', 'CRM_CivirulesPostTrigger_CaseActivity', CURDATE(), 1);"
    );
    return TRUE;
  }

  /**
   * Update to insert the trigger for Custom Data Changed on case.
   */
  public function upgrade_1011() {
    CRM_Core_DAO::executeQuery("
    INSERT INTO civirule_trigger (name, label, object_name, op, class_name, created_date, created_user_id)
    VALUES ('changed_case_custom_data', 'Custom data on case changed', null, null, 'CRM_CivirulesPostTrigger_CaseCustomDataChanged', CURDATE(), 1);
    ");
    return TRUE;
  }

  public function upgrade_1012() {
    CRM_Core_DAO::executeQuery("
    INSERT INTO civirule_trigger (name, label, object_name, op, class_name, created_date, created_user_id)
    VALUES ('added_case_activity', 'Case activity is added', 'Activity', 'create', 'CRM_CivirulesPostTrigger_CaseActivity', CURDATE(), 1);
    ");
    return TRUE;
  }

  /**
   * Update for rule tag (check <https://github.com/CiviCooP/org.civicoop.civirules/issues/98>)
   */
  public function upgrade_1020() {
    $this->executeSqlFile('sql/createCiviruleRuleTag.sql');
    $ruleTagOptionGroup = CRM_Civirules_Utils_OptionGroup::getSingleWithName('civirule_rule_tag');
    if (empty($ruleTagOptionGroup)) {
      CRM_Civirules_Utils_OptionGroup::create('civirule_rule_tag', 'Tags for CiviRules', 'Tags used to filter CiviRules on the CiviRules page');
    }
    return TRUE;
  }

  /**
   * Update to update class for entity tag triggers
   */
  public function upgrade_1021() {
    $query = 'UPDATE civirule_trigger SET class_name = %1 WHERE name LiKE %2';
    CRM_Core_DAO::executeQuery($query, array(
      1 => array('CRM_CivirulesPostTrigger_EntityTag', 'String'),
      2 => array('%entity_tag%', 'String'),
    ));
    $query = 'UPDATE civirule_trigger SET label = %1 WHERE name LiKE %2';
    CRM_Core_DAO::executeQuery($query, array(
      1 => array('Contact is tagged (tag is added to contact)', 'String'),
      2 => array('new_entity_tag', 'String'),
    ));
    $query = 'UPDATE civirule_trigger SET label = %1 WHERE name LiKE %2';
    CRM_Core_DAO::executeQuery($query, array(
      1 => array('Contact is un-tagged (tag is removed from contact)', 'String'),
      2 => array('deleted_entity_tag', 'String'),
    ));
    return TRUE;
  }
	
	public function upgrade_1022() {
		CRM_Core_DAO::executeQuery("
			UPDATE civirule_trigger
			SET class_name = 'CRM_CivirulesPostTrigger_Contribution'
			WHERE object_name = 'Contribution'
		");
		return TRUE;
	}
	
	public function upgrade_1023() {
		CRM_Core_DAO::executeQuery("ALTER TABLE `civirule_rule_action` ADD `action_name` VARCHAR(255) NOT NULL DEFAULT '' AFTER `rule_id`;");
		return TRUE;
	}
}
