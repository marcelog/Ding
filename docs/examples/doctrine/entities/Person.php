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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @version    SVN: $Id$
 * @link       http://marcelog.github.com/
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
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
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
