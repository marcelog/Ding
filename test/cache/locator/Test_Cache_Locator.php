<?php
/**
 * This class will test the cache locator.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Locator
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

use Ding\Cache\Locator\CacheLocator;
use Ding\Cache\Exception\CacheException;

/**
 * This class will test the cache locator.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Test
 * @subpackage Cache.Locator
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Test_Cache_Locator extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @expectedException Ding\Cache\Exception\CacheException
     */
    public function cannot_get_invalid_cache()
    {
        CacheLocator::configure(array('bdef' => array('impl' => 'asdads')));
        CacheLocator::getDefinitionsCacheInstance();
    }
}
