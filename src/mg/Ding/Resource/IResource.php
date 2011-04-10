<?php
/**
 * A generic Resource.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Resource
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
namespace Ding\Resource;

/**
 * A generic Resource.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Resource
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
interface IResource
{
    /**
     * Returns true if this resource physically exists.
     *
     * @return boolean
     */
    public function exists();

    /**
     * Returns true if this resource is already open.
     *
     * @return boolean
     */
    public function isOpen();

    /**
     * Opens this resource.
     *
     * @throws ResourceException
     * @return stream
     */
    public function getStream();

    /**
     * Created a directory relative to this resource path.
     *
     * @param string $relativePath Directory to create.
     *
     * @throws ResourceException
     * @return IResource
     */
    public function createRelative($relativePath);

    /**
     * Returns filename for this resource.
     *
     * @return string
     */
    public function getFilename();

    /**
     * Returns the full url for this resource.
     *
     * @return string
     */
    public function getURL();
}