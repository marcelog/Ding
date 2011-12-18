<?php
/**
 * A MessageSource must be able to return messages given some parameters.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  MessageSource
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
namespace Ding\MessageSource;

/**
 * A MessageSource must be able to return messages given some parameters.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  MessageSource
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
interface IMessageSource
{
    /**
     * Returns a message for a specific bundle/locale. False if none found.
     *
     * @param string   $bundle    Bundle name.
     * @param string   $message   Message id.
     * @param string[] $arguments Arguments. Will be replaced by order, from {1} to {n}
     * @param string   $locale    Locale id (i.e: en_US, en_GB, es_AR, etc).
     *
     * @throws IResourceException
     * @return string
     */
    public function getMessage($bundle, $message, array $arguments, $locale = 'default');
}