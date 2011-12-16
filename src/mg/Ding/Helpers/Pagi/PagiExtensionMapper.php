<?php
/**
 * This will map an extension to a specific application (bean).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pagi
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
namespace Ding\Helpers\Pagi;

/**
 * This will map an extension to a specific application (bean).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Helpers
 * @subpackage Pagi
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class PagiExtensionMapper
{
    /**
     * Extensions/Applications map.
     * @var array
     */
    private $_map;

    /**
     * Sets the map to use.
     *
     * @param array $map Map to set.
     *
     * @return void
     */
    public function setMap(array $map)
    {
        $this->_map = $map;
    }

    /**
     * Resolve extension to application (bean).
     *
     * @param string $extension Extension to map.
     *
     * @return PAGIApplication
     */
    public function resolve($extension)
    {
        if (is_array($this->_map)) {
            foreach ($this->_map as $map) {
                if (isset($map['extension']) && $map['extension'] == $extension) {
                    return $map['application'];
                }
            }
        }
        return $this->_map['default']['application'];
    }

    /**
     * Consctructor.
     *
     * @return void
     */
    public function __construct()
    {
    }
}