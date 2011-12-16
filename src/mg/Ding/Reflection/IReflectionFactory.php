<?php
/**
 * Internal reflection manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Reflection
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
namespace Ding\Reflection;

/**
 * Internal reflection manager.
 *
 * PHP Version 5
 *
 * @category Ding
 * @package  Reflection
 * @author   Marcelo Gornstein <marcelog@gmail.com>
 * @license  http://marcelog.github.com/ Apache License 2.0
 * @link     http://marcelog.github.com/
 */
interface IReflectionFactory
{
    /**
     * Returns all php classes found in a code block. Multiple namespaces in one file are not supported.
     *
     * @param string $code PHP Code.
     *
     * @return string[]
     */
    public function getClassesFromCode($code);
    /**
     * Parses all annotations in the given text.
     *
     * @param string $text
     *
     * @return BeanAnnotationDefinition[]
     */
    public function getAnnotations($text);
    /**
     * Returns all classes annotated with the given annotation.
     *
     * @param string $annotation Annotation name.
     *
     * @return string[]
     */
    public function getClassesByAnnotation($annotation);
    /**
     * Returns all annotations for the given class.
     *
     * @param string $class Class name.
     *
     * @return string[]
     */
    public function getClassAnnotations($class);
    /**
     * Returns a (cached) reflection class.
     *
     * @param string $class Class name
     *
     * @throws ReflectionException
     * @return ReflectionClass
     */
    public function getClass($class);
    /**
     * Returns a (cached) reflection class method.
     *
     * @param string $class  Class name.
     * @param string $method Method name.
     *
     * @throws ReflectionException
     * @return ReflectionClass
     */
    public function getMethod($class, $method);
}
