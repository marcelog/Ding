<?php
/**
 * A generic url resource. All php scheme's are supported.
 * See http://www.php.net/manual/en/wrappers.php
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
 * A generic url resource. All php scheme's are supported.
 * See http://www.php.net/manual/en/wrappers.php
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
class URLResource implements IResource
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
     * Holds scheme.
     * @var string
     */
    private $_scheme;

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
        return file_exists($this->_filename);
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
        return $this->_scheme . $this->_filename;
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
                ? @fopen($this->_scheme . $this->_filename, 'r', false)
                : @fopen($this->_scheme . $this->_filename, 'r', false, $this->_context)
            ;
            if ($this->_fd === false) {
                throw new ResourceException('Could not open: ' . $this->_scheme . $this->_filename);
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
       return $this->_filename . DIRECTORY_SEPARATOR . $relativePath;
    }

    /**
     * (non-PHPdoc)
     * @see Ding\Resource.IResource::getFilename()
     */
    public function getFilename()
    {
        return $this->_filename;
    }

    /**
     * Constructor.
     *
     * @param string $filename Filename with or without includepath://.
     * @param resource $context  Context created with stream_context_create().
     *
     * @return void
     */
    public function __construct($filename, $context = false)
    {
        $pos = strpos($filename, '://') + 3;
        if ($pos === false) {
            $pos = strlen($filename);
        }
        $this->_filename = substr($filename, $pos);
        $this->_scheme = substr($filename, 0, $pos);
        $this->_fd = false;
        $this->_context = $context;
    }
}