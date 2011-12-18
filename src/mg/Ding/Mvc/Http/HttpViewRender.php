<?php
/**
 * Http view render.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
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
namespace Ding\Mvc\Http;

use Ding\HttpSession\HttpSession;

use Ding\MessageSource\IMessageSource;

use Ding\MessageSource\IMessageSourceAware;
use Ding\Mvc\IViewRender;
use Ding\Mvc\View;

/**
 * Http view render.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Mvc
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
class HttpViewRender implements IViewRender, IMessageSourceAware
{
    /**
     * @var IMessageSource
     */
    protected $messageSource;

    public function setMessageSource(IMessageSource $messageSource)
    {
        $this->messageSource = $messageSource;
    }

    public function translate($bundle, $message, $arguments = array())
    {
        $session = HttpSession::getSession();
        if (!$session->hasAttribute('LANGUAGE')) {
            return $this->messageSource->getMessage(
                $bundle, $message, $arguments
            );
        } else {
            return $this->messageSource->getMessage(
                $bundle, $message, $arguments, $session->getAttribute('LANGUAGE')
            );
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Mvc.IViewRender::render()
     */
    public function render(View $view)
    {
        /**
         * @todo is there a better way to do this?
         */
        global $modelAndView;
        $modelAndView = $view->getModelAndView();
        /**
         * @todo render headers: does this belong here?
         */
        $objects = $modelAndView->getModel();
        if (isset($objects['headers'])) {
            foreach ($objects['headers'] as $header) {
                header($header);
            }
        }
        // Now render everything else.
        if (file_exists($view->getPath())) {
            include_once $view->getPath();
        }
    }
}