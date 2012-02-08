<?php
/**
 * Bean Definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 *
 * Copyright 2011 Marcelo Gornstein <marcelog@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */
namespace Ding\Bean;

/**
 * Bean Definition.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class BeanDefinition
{
    /**
     * Specifies scope prototype for beans, meaning that a new instance will
     * be returned every time.
     * @var integer
     */
    const BEAN_PROTOTYPE = 0;

    /**
     * Specifies scope singleton for beans, meaning that the same instance will
     * be returned every time.
     * @var integer
     */
    const BEAN_SINGLETON = 1;

    /**
     * Bean name
     * @var string
     */
    private $_name;

    /**
     * Bean class name.
     * @var string
     */
    private $_class;

    /**
     * Bean type (scope). See this class constants.
     * @var integer
     */
    private $_scope;

    /**
     * Properties to be di'ed to this bean.
     * @var BeanPropertyDefinition[]
     */
    private $_properties;

    /**
     * Aspects mapped to this bean.
     * @var string[]
     */
    private $_aspects;

    /**
     * Constructor arguments.
     * @var BeanConstructorArgumentDefinition[]
     */
    private $_constructorArgs;

    /**
     * Factory method name (if any).
     * @var string
     */
    private $_factoryMethod;

    /**
     * Factory bean name (if any).
     * @var string
     */
    private $_factoryBean;

    /**
     * Init method (if any).
     * @var string
     */
    private $_initMethod;

    /**
     * Destroy method (called when container is destroyed).
     * @var string
     */
    private $_destroyMethod;

    /**
     * Dependency beans literally specified in the configuration.
     * @var string[]
     */
    private $_dependsOn;

    /**
     * Methods injection.
     * @var string[]
     */
    private $_lookupMethods;

    /**
     * True if this bean cant be instantiated.
     * @var boolean
     */
    private $_isAbstract;

    /**
     * Parent bean, if any.
     * @var string
     */
    private $_parent;

    /**
     * Bean aliases.
     * @var string[]
     */
    private $_aliases;

    /**
     * Holds the name of the proxy class that was generated for this bean, only
     * valid for those who actually have aspects applied.
     * @var string
     */
    private $_proxyClassName;

    /**
     * When wiring by type, this will mark this bean definition as the primary
     * source when multiple candidates are found.
     * @var boolean;
     */
    private $_isPrimaryCandidate;

    /**
     * Mark this bean definition as primary candidate when multiple candidates
     * are found for wiring by type.
     *
	 * @return void
     */
    public function markAsPrimaryCandidate()
    {
        $this->_isPrimaryCandidate = true;
    }
    /**
     * True if this bean definition is the primary candidate when wiring by type.
     *
     * @return boolean
     */
    public function isPrimaryCandidate()
    {
        return $this->_isPrimaryCandidate;
    }

    /**
     * Aliases for this bean.
     *
     * @return string[]
     */
    public function getAliases()
    {
        return $this->_aliases;
    }

    /**
     * This is needed when creating a child bean.
     *
     * @return void
     */
    public function clearAliases()
    {
        $this->_aliases = array();
    }
    /**
     * Add an alias to this bean.
     *
     * @param string $name New alias for this bean.
     *
     * @return void
     */
    public function addAlias($name)
    {
        $this->_aliases[$name] = $name;
    }

    /**
     * Returns true if this bean definition is abstract and cant be instantiated.
     *
     * @return boolean
     */
    public function isAbstract()
    {
        return $this->_isAbstract;
    }

    /**
     * Makes this bean an abstract bean definition.
     *
     * @return void
     */
    public function makeAbstract()
    {
        $this->_isAbstract = true;
    }

    /**
     * Makes this bean concrete, that is, not abstract.
     *
     * @return void
     */
    public function makeConcrete()
    {
        $this->_isAbstract = false;
    }

    /**
     * Returns true if this bean definition is for a bean of type singleton.
     *
     * @return boolean
     */
    public function isSingleton()
    {
        return $this->_scope == BeanDefinition::BEAN_SINGLETON;
    }

    /**
     * Returns true if this bean definition is for a bean of type prototype.
     *
     * @return boolean
     */
    public function isPrototype()
    {
        return $this->_scope == BeanDefinition::BEAN_PROTOTYPE;
    }

    /**
     * Returns true if this bean has mapped aspects.
     *
     * @return boolean
     */
    public function hasAspects()
    {
        return $this->_aspects !== false;
    }

    /**
     * Sets new aspects for this bean.
     *
     * @param string[] $aspects New aspects.
     *
     * @return void
     */
    public function setAspects(array $aspects)
    {
        $this->_aspects = $aspects;
    }

    /**
     * Returns aspects for this bean.
     *
     * @return string[]
     */
    public function getAspects()
    {
        return $this->_aspects;
    }

    /**
     * Changes the scope for this bean.
     *
     * @param string $scope New scope.
     *
     * @return void
     */
    public function setScope($scope)
    {
        $this->_scope = $scope;
    }

    /**
     * Sets new method injections.
     *
     * @param string[] $methods Methods injected.
     *
     * @return void
     */
    public function setMethodInjections($methods)
    {
        $this->_lookupMethods = $methods;
    }

    /**
     * Returns the method injections.
     *
     * @return string[]
     */
    public function getMethodInjections()
    {
        return $this->_lookupMethods;
    }

    /**
     * Sets a new name for this bean.
     *
     * @param string $name New name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * Returns bean name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets a new class name for this bean.
     *
     * @param string $class New class name.
     *
     * @return void
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * Returns bean class.
     *
     * @return string
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Sets new properties for this bean.
     *
     * @param BeanPropertyDefinition[] $properties New properties.
     *
     * @return void
     */
    public function setProperties(array $properties)
    {
        $this->_properties = $properties;
    }

    /**
     * Returns properties for this bean.
     *
     * @return BeanPropertyDefinition[]
     */
    public function getProperties()
    {
        return $this->_properties;
    }

    /**
     * Sets new arguments for this bean.
     *
     * @param BeanConstructorDefinition[] $arguments New arguments.
     *
     * @return void
     */
    public function setArguments(array $arguments)
    {
        $this->_constructorArgs = $arguments;
    }

    /**
     * Returns arguments for this bean.
     *
     * @return BeanConstructorArgumentDefinition[]
     */
    public function getArguments()
    {
        return $this->_constructorArgs;
    }

    /**
     * Sets a new factory method for this bean.
     *
     * @param string $factoryMethod New factory method.
     *
     * @return void
     */
    public function setFactoryMethod($factoryMethod)
    {
        $this->_factoryMethod = $factoryMethod;
    }
    /**
     * Factory method, false if none was set.
     *
     * @return string
     */
    public function getFactoryMethod()
    {
        return $this->_factoryMethod;
    }

    /**
     * Sets a new factory bean for this bean.
     *
     * @param string $factoryBean New factory bean.
     *
     * @return void
     */
    public function setFactoryBean($factoryBean)
    {
        $this->_factoryBean = $factoryBean;
    }

    /**
     * Factory bean, false if none was set.
     *
     * @return string
     */
    public function getFactoryBean()
    {
        return $this->_factoryBean;
    }

    /**
     * Sets a new init method for this bean.
     *
     * @param string $initMethod New init method.
     *
     * @return void
     */
    public function setInitMethod($initMethod)
    {
        $this->_initMethod = $initMethod;
    }

    /**
     * Init method, false if none was set.
     *
     * @return string
     */
    public function getInitMethod()
    {
        return $this->_initMethod;
    }

    /**
     * Sets a new destroy method for this bean.
     *
     * @param string $destroyMethod New destroy method.
     *
     * @return void
     */
    public function setDestroyMethod($destroyMethod)
    {
        $this->_destroyMethod = $destroyMethod;
    }

    /**
     * Destroy method, false if none was set.
     *
     * @return string
     */
    public function getDestroyMethod()
    {
        return $this->_destroyMethod;
    }

    /**
     * Returns all beans marked as dependencies for this bean.
     *
     * @return string[]
     */
    public function getDependsOn()
    {
        return $this->_dependsOn;
    }

    /**
     * Set bean dependencies.
	 *
     * @param string[] $dependsOn Dependencies (bean names).
     *
     * @return void
     */
    public function setDependsOn(array $dependsOn)
    {
        $this->_dependsOn = $dependsOn;
    }

    /**
     * Returns a new bean definition, a copy of this one.
     *
     * @param string $childName The name for the new bean
     *
     * @return BeanDefinition
     */
    public function makeChildBean($childName)
    {
        $bean = serialize($this);
        $bean = unserialize($bean);
        $bean->setName($childName);
        $bean->clearAliases();
        $bean->makeConcrete();
        return $bean;
    }

    /**
     * Creates a new unique bean name.
     *
     * @param string $prefix A name prefix
     *
     * @return string
     */
    public static function generateName($prefix)
    {
        return $prefix . mt_rand(1, microtime(true));
    }

    /**
     * Returns true if this bean is created by calling the constructor.
     *
     * @return boolean
     */
    public function isCreatedByConstructor()
    {
        return empty($this->_factoryMethod);
    }

    /**
     * Returns true if this bean is created by calling a factory bean (as
     * opposed to a factory class)
     *
     * @return boolean
     */
    public function isCreatedWithFactoryBean()
    {
        return !empty($this->_factoryBean);
    }

    public function setProxyClassName($name)
    {
        $this->_proxyClassName = $name;
    }

    public function getProxyClassName()
    {
        return $this->_proxyClassName;
    }

    public function hasProxyClass()
    {
        return !is_null($this->_proxyClassName);
    }

    public function hasInitMethod()
    {
        return !is_null($this->_initMethod);
    }
    public function hasDestroyMethod()
    {
        return !is_null($this->_destroyMethod);
    }

    /**
     * Constructor.
     *
     * @param string $name Bean name.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->_name = $name;
        $soullessString = '';
        $soullessArray = array();
        $this->_class = 'stdclass';
        $this->_aliases = $soullessArray;
        $this->_scope = BeanDefinition::BEAN_SINGLETON;
        $this->_factoryMethod = $soullessString;
        $this->_factoryBean = $soullessString;
        $this->_initMethod = null;
        $this->_lookupMethods = $soullessArray;
        $this->_destroyMethod = null;
        $this->_dependsOn = $soullessArray;
        $this->_properties = $soullessArray;
        $this->_aspects = false;
        $this->_constructorArgs = $soullessArray;
        $this->_isAbstract = false;
        $this->_parent = null;
        $this->_proxyClassName = null;
        $this->_isPrimaryCandidate = false;
    }
}
