<?php
/**
 * A definition for an annotation.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
namespace Ding\Bean;

/**
 * A definition for an annotation.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://www.noneyet.ar/ Apache License 2.0
 * @link     http://www.noneyet.ar/
 */
class BeanAnnotationDefinition
{
    /**
     * Annotation name.
     * @var string
     */
    private $_name;

    /**
     * Annotation arguments.
     * @var array
     */
    private $_args;
    
    /**
     * Returns annotation name.
     * 
	 * @return string
     */
    public function getName()
    {
        return $this->_name;
    }
    
    /**
     * Returns arguments for this annotation.
     * 
     * @return array
     */
    public function getArguments()
    {
        return $this->_args;
    }
    
    /**
     * Constructor.
     * 
     * @param string $name Annotation name.
     * @param array  $args Annotation arguments.
     * 
     * @return void
     */
    public function __construct($name, $args)
    {
        $this->_name = $name;
        $this->_args = $args;
    }
}