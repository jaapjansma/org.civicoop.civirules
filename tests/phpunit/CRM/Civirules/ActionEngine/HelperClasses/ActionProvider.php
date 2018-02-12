<?php

/**
 * This is a helper class for the unit test.
 * This class emulates an action provider class as we cannot be sure 
 * whether the action provider extension is installed on the test environment.
 * However we want to test whether action provider engine in CiviRules works as expected.
 * 
 * This class is a stub and implements only the methods getActions and getActionByName.
 * It does contain one stub action which is an add to group action.
 * 
 * We have almost recreated the action-provider extension in this file. And that only for
 * the puprose of unit testing ;-)
 */
class CRM_Civirules_ActionEngine_HelperClasses_ActionProvider {
	
	private $availableActions;
	
	public function __construct() {
		$this->availableActions = array(
			'stub' => new CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_StubAction_AddToGroup(),
		);
	}
	
	/**
	 * Returns all available actions
	 */
	public function getActions() {
		return $this->availableActions;
	}
	
	/**
	 * Returns an action by its name.
	 * 
	 * @return \Civi\ActionProvider\Action\AbstractAction|null when action is not found.
	 */
	public function getActionByName($name) {
		if (isset($this->availableActions[$name])) {
			return $this->availableActions[$name];
		}
		return null;
	}
	
	/**
	 * Returns a new ParameterBag
	 * 
	 * This function exists so we can encapsulate the creation of a ParameterBag to the provider.
	 * 
	 * @return ParameterBagInterface
	 */
	public function createParameterBag() {
		return new CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_ParameterBag();
	}
}

/**
 * This is a stub class which emulates the action provider container.
 */
class CRM_Civirules_ActionEngine_HelperClasses_ActionProviderContainer {
	
	/**
	 * @var Provider
	 */
	protected $defaultProvider;
	
	public function __construct() {
		$this->defaultProvider = new CRM_Civirules_ActionEngine_HelperClasses_ActionProvider();
	}
	
	/**
	 * return Provider
	 */
	public function getDefaultProvider() {
		return $this->defaultProvider;
	}
	
	/**
	 * Returns the provider object the name of the context.
	 * 
	 * @param string $context
	 * @return Provider
	 */
	public function getProviderByContext($context) {
		return $this->defaultProvider;
	}
	
	/**
	 * Returns whether the container has already a particulair context.
	 * 
	 * @param string $context
	 * @return bool
	 */
	public function hasContext($context) {
		return true;
	}
	
}

/**
 * This is the stub class for the action.
 */
class CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_StubAction_AddToGroup {
	
	private $configuration;
	
	/**
	 * Returns the specification of the configuration options for the actual action.
	 * 
	 * @return SpecificationBag
	 */
	public function getConfigurationSpecification() {
		return new CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_SpecificationBag(array(
			new CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_Specification('group_id', 'Integer', 'Select group', true, null, 'Group', null, FALSE),
		));
	} 
	
	public function setConfiguration($configuration) {
		$this->configuration = $configuration;
	}
	
	public function getConfiguration() {
		return $this->configuration;
	}
	
	public function getTitle() {
		return 'Stub Action';
	}
	
	public function execute($parameters) {
		civicrm_api3('GroupContact', 'create', array(
			'contact_id' => $parameters->getParameter('contact_id'),
			'group_id' => $this->configuration->getParameter('group_id'),
		));
	}
	
}

class CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_ParameterBag implements \IteratorAggregate {
	
	protected $parameters = array();
	
	/**
	 * Get the parameter.
	 */
	public function getParameter($name) {
		if (isset($this->parameters[$name])) {
			return $this->parameters[$name];
		}
		return null;
	}	
	/**
	 * Tests whether the parameter with the name exists.
	 */
	public function doesParameterExists($name) {
		if (isset($this->parameters[$name])) {
			return true;
		}
		return false;
	}
	
	/**
	 * Sets parameter. 
	 */
	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}
	
	public function getIterator() {
    return new \ArrayIterator($this->parameters);
  }
	
}

class CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_SpecificationBag implements \IteratorAggregate  {
	
	protected $parameterSpecifications = array();
	
	public function __construct($specifcations = array()) {
		foreach($specifcations as $spec) {
			$this->parameterSpecifications[$spec->getName()] = $spec;
		}
	}
	
	/**
	 * @param string $name
	 *   The name of the parameter.
	 * @return Specification|null
	 */
	public function getSpecificationByName($name) {
		foreach($this->parameterSpecifications as $key => $spec) {
			if ($spec->getName() == $name) {
				return $this->parameterSpecifications[$key];
			}
		}
		return null;
	}
	
	public function getIterator() {
    return new \ArrayIterator($this->parameterSpecifications);
  }
	
}


class CRM_Civirules_ActionEngine_HelperClasses_ActionProvider_Specification {
	
	 /**
   * @var mixed
   */
  protected $defaultValue;
  /**
   * @var string
   */
  protected $name;
  /**
   * @var string
   */
  protected $title;
  /**
   * @var string
   */
  protected $description;
  /**
   * @var bool
   */
  protected $required = FALSE;
  /**
   * @var array
   */
  protected $options = array();
	/**
	 * @var bool
	 */
	protected $multiple = FALSE;
  /**
   * @var string
   */
  protected $dataType;
  /**
   * @var string
   */
  protected $fkEntity;
	
	  /**
   * @param $name
   * @param $dataType
   */
  public function __construct($name, $dataType = 'String', $title='', $required = false, $defaultValue = null, $fkEntity = null, $options = array(), $multiple = false) {
    $this->setName($name);
    $this->setDataType($dataType);
		$this->setTitle($title);
		$this->setRequired($required);
		$this->setDefaultValue($defaultValue);
		$this->setFkEntity($fkEntity);
		$this->setOptions($options);
		$this->setMultiple($multiple);
  }
	
  /**
   * @return mixed
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }
	
  /**
   * @param mixed $defaultValue
   *
   * @return $this
   */
  public function setDefaultValue($defaultValue) {
    $this->defaultValue = $defaultValue;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }
	
  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }
	
  /**
   * @param string $title
   *
   * @return $this
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }
	
  /**
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }
	
  /**
   * @return bool
   */
  public function isRequired() {
    return $this->required;
  }
	
  /**
   * @param bool $required
   *
   * @return $this
   */
  public function setRequired($required) {
    $this->required = $required;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getDataType() {
    return $this->dataType;
  }
	
  /**
   * @param $dataType
   *
   * @return $this
   * @throws \Exception
   */
  public function setDataType($dataType) {
    if (!in_array($dataType, $this->getValidDataTypes())) {
      throw new \Exception(sprintf('Invalid data type "%s', $dataType));
    }
    $this->dataType = $dataType;
    return $this;
  }
	
	  /**
   * Add valid types that are not not part of \CRM_Utils_Type::dataTypes
   *
   * @return array
   */
  private function getValidDataTypes() {
    $extraTypes =  array('Boolean', 'Text', 'Float');
    $extraTypes = array_combine($extraTypes, $extraTypes);
    return array_merge(\CRM_Utils_Type::dataTypes(), $extraTypes);
  }
	
	 /**
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }
	
  /**
   * @param array $options
   *
   * @return $this
   */
  public function setOptions($options) {
    $this->options = $options;
    return $this;
  }
	
  /**
   * @param $option
   */
  public function addOption($option) {
    $this->options[] = $option;
  }
	
	/**
   * @return bool
   */
  public function isMultiple() {
    return $this->multiple;
  }
	
  /**
   * @param bool $multiple
   *
   * @return $this
   */
  public function setMultiple($multiple) {
    $this->multiple = $multiple;
    return $this;
  }
	
  /**
   * @return string
   */
  public function getFkEntity() {
    return $this->fkEntity;
  }
	
  /**
   * @param string $fkEntity
   *
   * @return $this
   */
  public function setFkEntity($fkEntity) {
    $this->fkEntity = $fkEntity;
    return $this;
  }
	
  public function toArray() {
    $ret = array();
    foreach (get_object_vars($this) as $key => $val) {
      $key = strtolower(preg_replace('/(?=[A-Z])/', '_$0', $key));
      $ret[$key] = $val;
    }
    return $ret;
  }
	
}
