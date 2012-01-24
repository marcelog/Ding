<?php
/**
 * Bean Definition Provider interface.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
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
namespace Ding\Bean;
use Ding\Bean\BeanDefinition;

/**
 * Bean Definition Provider interface.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Bean
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
interface IBeanDefinitionProvider
{
    /**
     * Returns a bean definition with the given name.
     *
     * @param string $name Name of the bean.
     *
     * @return Ding\Bean\BeanDefinition
     */
    public function getBeanDefinition($name);

    /**
     * Returns all bean names that match a given class
     *
     * @param string $class Class to look for.
     *
     * @return string[]
     */
    public function getBeansByClass($class);

    /**
     * Returns all names of the beans listening for the given event.
     *
     * @param string $eventName The event name.
     *
     * @return string[]
     */
    public function getBeansListeningOn($eventName);
}
