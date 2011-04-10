<?php
/**
 * A filesystem resource (file:// ... ). Can be absolute or relative.
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
 * A filesystem resource (file:// ... ). Can be absolute or relative.
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
class FilesystemResource implements IResource
{
    /**
     * Holds true filename.
     * @var string
     */
    protected $filename;

    /**
     * Holds file resource.
     * @var stream
     */
    protected $fd;

    /**
     * Holds context, created with stream_context_create()
     * @var resource
     */
    protected $context;

    /**
     * This scheme identifies this resource.
     * @var string
     */
    const SCHEME = 'file://';

    /**
     * Length for self::SCHEME
     * @var integer
     */
    const SCHEMELEN = 7;

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::exists()
     */
    public function exists()
    {
        return $this->filename !== false && file_exists($this->filename);
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::isOpen()
     */
    public function isOpen()
    {
        return $this->fd !== false;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getURL()
     */
    public function getURL()
    {
        return self::SCHEME . $this->filename;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getStream()
     */
    public function getStream()
    {
        if ($this->fd === false) {
            $this->fd
                = $this->context === false
                ? @fopen($this->getURL(), 'r', false)
                : @fopen($this->getURL(), 'r', false, $this->context);
            if ($this->fd === false) {
                throw new ResourceException('Could not open: ' . $this->filename);
            }
        }
        return $this->fd;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::createRelative()
     */
    public function createRelative($relativePath)
    {
        return new FilesystemResource(
            self::SCHEME . $this->getFilename() . DIRECTORY_SEPARATOR . $relativePath
        );
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getFilename()
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Constructor.
     *
     * @param string   $filename Filename with or without file://.
     * @param resource $context  Context created with stream_context_create().
     *
     * @throws ResourceException
     * @return void
     */
    public function __construct($filename, $context = false)
    {
        $filename = str_replace(self::SCHEME, '', $filename);
        $realpath = realpath($filename);
        $this->filename = $realpath ? $realpath : $filename;
        $this->fd = false;
        $this->context = $context;
    }
}