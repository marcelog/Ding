<?php
/**
 * Example using ding mvc.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  global
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

////////////////////////////////////////////////////////////////////////////////
// Mandatory stuff to bootstrap ding. (START)
////////////////////////////////////////////////////////////////////////////////
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
use Ding\HttpSession\HttpSession;
use Ding\Helpers\ErrorHandler\ErrorInfo;
use Ding\Mvc\ModelAndView;
use Ding\Mvc\ForwardModelAndView;
use Ding\Mvc\RedirectModelAndView;
use Ding\Mvc\Http\HttpFrontController;
////////////////////////////////////////////////////////////////////////////////
// Normal operation follows...
////////////////////////////////////////////////////////////////////////////////
date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_errors', 1);
////////////////////////////////////////////////////////////////////////////////
class AnException extends \Exception
{
}

class MyController
{
    public function _ExceptionException($exception)
    {
        $modelAndView = new ModelAndView('exception');
        $modelAndView->add(array('exception' => $exception->getMessage()));
        return $modelAndView;
    }

    public function MainAction()
    {
        return new ForwardModelAndView('/MyController/some');
    }
    public function formAction()
    {
        $session = HttpSession::getSession();
        $arguments['sessionStuff'] = $session->getAttribute('aSessionVariable');
        return $this->someAction(func_get_args());
    }

    public function redirectAction()
    {
        $modelAndView = new RedirectModelAndView('http://github.com/marcelog/Ding');
        return $modelAndView;
    }

    public function forwardAction()
    {
        $arguments['Forwarded-From'] = 'forwardAction';
        return new ForwardModelAndView('/MyController/some', func_get_args());
    }

    public function someAction()
    {
        $session = HttpSession::getSession();
        $session->setAttribute('aSessionVariable', array('user' => 'aUser'));
        $modelAndView = new ModelAndView('some');
        $modelAndView->add(array(
        	'somestring' => 'Hello World',
            'arguments' => func_get_args()
        ));
        $modelAndView->add(
            array(
            	'headers' => array(
            		'Cache-Control: no-cache',
                    'Pragma: no-cache'
                )
            )
        );
        return $modelAndView;
    }

    public function someExceptionAction()
    {
        throw new AnException('Woooooow!');
    }
}

class MyErrorHandler
{
    public function onDingError($error)
    {
        echo "This is your custom error handler: " . print_r($error, true);
    }
}

$properties = array(
    'ding' => array(
    	'log4php.properties' => realpath('/tmp/log4php.properties'),
        'factory' => array(
            'properties' => array(
                'twig.debug' => false,
                'twig.charset' => 'utf-8',
                'twig.base_template_class' => 'Twig_Template',
                'twig.cache' => '/tmp/Ding/twigcache',
                'twig.auto_reload' => true,
                'twig.strict_variables' => false,
                'twig.autoescape' => 0
            ),
            'bdef' => array(
             	'xml' => array('filename' => 'beans.xml', 'directories' => array(__DIR__)),
                'annotation' => array('scanDir' => array(realpath(__DIR__)))
            ),
        ),
        'cache' => array(
            'proxy' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/proxy'),
            'bdef' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/bdef'),
            'beans' => array('impl' => 'dummy', 'directory' => '/tmp/Ding/beans'),
        )
    )
);
HttpFrontController::handle($properties, '/Some/Mapped/Path');
////////////////////////////////////////////////////////////////////////////////
