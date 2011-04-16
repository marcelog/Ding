<?php
/**
 * A filesystem resource inside the include path (includepath:// ... ).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Resource
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
namespace Ding\Resource\Impl;

use Ding\Resource\Exception\ResourceException;
use Ding\Resource\IResource;

/**
 * A filesystem resource inside the include path (includepath:// ... ).
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Resource
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
class IncludePathResource extends FilesystemResource implements IResource
{
    /**
     * This scheme identifies this resource.
     * @var string
     */
    const SCHEME = 'includepath://';

    /**
     * Length for self::SCHEME
     * @var integer
     */
    const SCHEMELEN = 14;

    /**
     * Constructor.
     *
     * @param string   $filename Filename with or without file://.
     * @param resource $context  Context created with stream_context_create().
     *
     * @return void
     */
    public function __construct($filename, $context = false)
    {
        $filename = str_replace(self::SCHEME, '', $filename);
        $filename = str_replace(FilesystemResource::SCHEME, '', $filename);
        $this->filename = $filename;
        foreach(explode(PATH_SEPARATOR, ini_get('include_path')) as $path) {
            $path = realpath($path . DIRECTORY_SEPARATOR . $filename);
            if (file_exists($path)) {
                $this->filename = $path;
                break;
            }
        }
        $this->fd = false;
        $this->context = $context;
    }
}