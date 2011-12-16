<?php
/**
 * Example using ding. See also beans.xml.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Examples
 * @subpackage i18n
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
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
 */

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
declare(ticks=1);
ini_set(
    'include_path',
    implode(
        PATH_SEPARATOR,
        array(
            implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'src', 'mg')),
            ini_get('include_path'),
        )
    )
);
require_once 'Ding/Autoloader/Autoloader.php'; // Include ding autoloader.
\Ding\Autoloader\Autoloader::register(); // Call autoloader register for ding autoloader.
use Ding\Container\Impl\ContainerImpl;
use Ding\MessageSource\IMessageSourceAware;
use Ding\MessageSource\IMessageSource;

error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class AClass implements IMessageSourceAware
{
    private $_messageSource;

    public function setMessageSource(IMessageSource $messageSource)
    {
        $this->_messageSource = $messageSource;
    }

    public function something()
    {
        return $this->_messageSource->getMessage(
        	'default', 'message.example', array('argument1'), 'es'
       );
    }
    public function something2()
    {
        return $this->_messageSource->getMessage(
        	'another_bundle', 'message.one', array('argument1'), 'es'
       );
    }
    public function __construct()
    {
    }
}


////////////////////////////////////////////////////////////////////////////////
try
{
    $properties = array(
        'ding' => array(
            'log4php.properties' => './log4php.properties',
            'factory' => array(
                'bdef' => array(
                	'xml' => array('filename' => 'beans.xml'),
                ),
            ),
    		'cache' => array(
    			'proxy' => array('impl' => 'dummy'),
            	'annotation' => array('impl' => 'dummy'),
            	'bdef' => array('impl' => 'dummy'),
              	'beans' => array('impl' => 'dummy'),
                'aspect' => array('impl' => 'dummy')
            )
        )
    );
    $a = ContainerImpl::getInstance($properties);
    $bean = $a->getBean('dummyBean');
    var_dump($bean->something());
    var_dump($bean->something2());
} catch(Exception $exception) {
    echo $exception . "\n";
}
////////////////////////////////////////////////////////////////////////////////
