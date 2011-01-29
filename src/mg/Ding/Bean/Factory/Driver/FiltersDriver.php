<?php
/**
 * This driver will apply all filters to property values.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 */
namespace Ding\Bean\Factory\Driver;

use Ding\Bean\BeanPropertyDefinition;

use Ding\Bean\Lifecycle\IAfterDefinitionListener;
use Ding\Bean\BeanDefinition;
use Ding\Bean\BeanAnnotationDefinition;
use Ding\Bean\Factory\IBeanFactory;
use Ding\Reflection\ReflectionFactory;
use Ding\Bean\Factory\Filter\PropertyFilter;

/**
 * This driver will apply all filters to property values.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Bean
 * @subpackage Factory.Driver
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 */
class FiltersDriver implements IAfterDefinitionListener
{
    /**
     * Holds current instance.
     * @var DependsOnDriver
     */
    private static $_instance = false;

    /**
     * Registered filters to apply.
     * @var IFilter[]
     */
    private $_filters;

    /**
     * Recursively, apply filter to property or constructor arguments values.
     *
     * @param BeanPropertyDefinition|BeanConstructoruArgumentDefinition $def
     *
     * @return void
     */
    private function _applyFilter(&$def)
    {
        $value = $def->getValue();
        if (is_array($value)) {
            foreach ($value as $otherDef) {
                $this->_applyFilter($otherDef);
            }
        } else {
            foreach ($this->_filters as $filter) {
                $def->setValue($filter->apply($value));
            }
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Bean\Lifecycle.ILifecycleListener::afterDefinition()
     */
    public function afterDefinition(IBeanFactory $factory, BeanDefinition &$bean)
    {
        foreach ($bean->getProperties() as $property) {
            $this->_applyFilter($property);
        }
        foreach ($bean->getArguments() as $argument) {
            $this->_applyFilter($argument);
        }
        return $bean;
    }

    /**
     * Returns an instance.
     *
     * @param array $options Optional options.
     *
     * @return FiltersDriver
     */
    public static function getInstance(array $options)
    {
        if (self::$_instance == false) {
            self::$_instance = new FiltersDriver($options);
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @param array $options Optional options.
     *
     * @return void
     */
    private function __construct(array $options)
    {
        $this->_filters = array(PropertyFilter::getInstance($options));
    }
}