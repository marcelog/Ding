<?php
/**
 * Default implementation for a MessageSource. Will try to find a file called
 * bundle_locale.properties and resolve message id's as standard key values in
 * a properties file.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    MessageSource
 * @subpackage Impl
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
namespace Ding\MessageSource\Impl;

use Ding\MessageSource\IMessageSource;
use Ding\Resource\IResourceLoader;
use Ding\Resource\IResourceLoaderAware;

/**
 * Default implementation for a MessageSource. Will try to find a file called
 * bundle_locale.properties and resolve message id's as standard key values in
 * a properties file.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    MessageSource
 * @subpackage Impl
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
class MessageSourceImpl implements IMessageSource, IResourceLoaderAware
{
    /**
     * Indexed by bundle name, locale, message id.
     * @var string[][][]
     */
    private $_basenames;

    /**
     * Resource loader so we can access the bundle files.
     * @var IResourceLoader
     */
    private $_resourceLoader;

    /**
     * (non-PHPdoc)
     * @see Ding\MessageSource.IMessageSource::getMessage()
     */
    public function getMessage($bundle, $message, array $arguments, $locale = 'default')
    {
        if (!isset($this->_basenames[$bundle][$locale])) {
            $resource = $this->_resourceLoader->getResource(
                'includepath://' .
                implode('_', array($bundle, $locale)) . '.properties'
            );
            $contents = stream_get_contents($resource->getStream());
            $contents = parse_ini_string($contents);
            $this->_basenames[$bundle][$locale] = $contents;
        }
        if (isset($this->_basenames[$bundle][$locale][$message])) {
            $message = $this->_basenames[$bundle][$locale][$message];
            $i = 1;
            foreach ($arguments as $argument) {
                $message = str_replace('{' . $i . '}', $argument, $message);
                $i++;
            }
        } else {
            return false;
        }
        return $message;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResourceLoaderAware::setResourceLoader()
     */
    public function setResourceLoader(IResourceLoader $resourceLoader)
    {
        $this->_resourceLoader = $resourceLoader;
    }

    /**
     * Set bundle names.
     *
     * @param string[] $basenames
     */
    public function setBasenames(array $basenames)
    {
        $this->_basenames = $basenames;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_basenames = array();
        $this->_resourceLoader = false;
    }
}