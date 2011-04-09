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
    private $_filename;

    /**
     * Holds file resource.
     * @var stream
     */
    private $_fd;

    /**
     * Holds context, created with stream_context_create()
     * @var resource
     */
    private $_context;

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::exists()
     */
    public function exists()
    {
        return $this->_filename !== false && file_exists($this->_filename);
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::isOpen()
     */
    public function isOpen()
    {
        return $this->_fd !== false;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getURL()
     */
    public function getURL()
    {
        return $this->_filename;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getStream()
     */
    public function getStream()
    {
        if ($this->_fd === false) {
            $this->_fd
                = $this->_context === false
                ? @fopen($this->_filename, 'r', false)
                : @fopen($this->_filename, 'r', false, $this->_context)
            ;
            if ($this->_fd === false) {
                throw new ResourceException('Could not open: ' . $this->_filename);
            }
        }
        return $this->_fd;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::createRelative()
     */
    public function createRelative($relativePath)
    {
        $path = realpath($this->getFilename() . DIRECTORY_SEPARATOR . $relativePath);
        $result = @mkdir($path, 0755, true);
        if ($result === false) {
            throw new ResourceException('Could not create: ' . $path);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getFilename()
     */
    public function getFilename()
    {
        return substr($this->_filename, strlen('file://'));
    }

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
        if (strpos($filename, 'file://') !== 0) {
            $filename = 'file://' . $filename;
        }
        $this->_filename = 'file://' . realpath(substr($filename, strlen('file://')));
        $this->_fd = false;
        $this->_context = $context;
    }
}