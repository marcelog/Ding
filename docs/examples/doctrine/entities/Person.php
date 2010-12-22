<?php
/**
 * Person entity class.
 *
 * Example entity.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Example
 * @subpackage Doctrine
 * @author     Agustín Gutiérrez <agu.gutierrez@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://www.noneyet.ar/
 *
 */

/**
 * Person entity.
 *
 * PHP Version 5.3.x
 *
 * @category   Ding
 * @package    Example
 * @subpackage Doctrine
 * @author     Agustín Gutiérrez <agu.gutierrez@gmail.com>
 * @license    http://www.noneyet.ar/ Apache License 2.0
 * @link       http://www.noneyet.ar/
 *
 * @Entity
 */
class Person
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(length=50, unique=true)
     */
    private $username;

    /**
     * @Column(length=50)
     */
    private $firstName;

    /**
     * @Column(length=50)
     */
    private $lastName;

    /**
     * Class constructor.
     *
     * @param string $username
     * @param string $firstName
     * @param string $lastName
     */
    public function __construct($username, $firstName, $lastName)
    {
        $this->username = $username;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    /**
     * Convert object to string.
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__
            . "{id:$this->id, username:$this->username, "
            . "{firstName:$this->firstName, lastName:$this->lastName]";
    }
}
