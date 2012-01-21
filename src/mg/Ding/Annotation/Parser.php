<?php
/**
 * Annotation parser.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Annotation
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
namespace Ding\Annotation;

/**
 * Annotation parser.
 *
 * PHP Version 5
 *
 * @category   Ding
 * @package    Annotation
 * @author     Marcelo Gornstein <marcelog@gmail.com>
 * @license    http://marcelog.github.com/ Apache License 2.0
 * @link       http://marcelog.github.com/
 */
class Parser
{
    protected static $ignoredAnnotations = array(
        'access', 'author', 'copyright', 'deprecated',
        'example', 'ignore', 'internal', 'link', 'see',
        'since', 'tutorial', 'version', 'package',
        'subpackage', 'name', 'global', 'param',
        'return', 'staticvar', 'category', 'staticVar',
        'static', 'throws', 'inheritdoc',
        'inheritDoc', 'license', 'todo', 'deprecated',
        'deprec', 'author', 'property' , 'method' ,
        'abstract', 'exception', 'magic' , 'api' ,
        'final', 'filesource', 'throw' , 'uses' ,
        'usedby', 'private' , 'Annotation' , 'override' ,
        'codeCoverageIgnoreStart' , 'codeCoverageIgnoreEnd' ,
        'Attribute' , 'Attributes' , 'Entity', 'Table', 'Column',
        'ManyToMany', 'OneToMany', 'ManyToOne', 'OneToOne', 'Index',
        'JoinColumn', 'InheritanceType', 'DiscriminatorMap',
        'DiscriminatorColumn', 'Id', 'GeneratedValue', 'HasLifeCycleCallbacks',
        'Target' , 'SuppressWarnings'
    );
    private function _parseOptions($options)
    {
        $total = preg_match_all(
    		'/([^=,]*)=[\s]*([\s]*"[^"]+"|\{[^\{\}]+\}|[^,"]*[\s]*)/', $options, $matches
        );
        $options = array();
        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                $key = trim($matches[1][$i]);
                $value = str_replace('"', '', trim($matches[2][$i]));
                $options[$key] = array();
                if (strpos($value, '{') === 0) {
                    $value = substr($value, 1, -1);
                    $value = explode(',', $value);
                    foreach ($value as $k => $v) {
                        $options[$key][] = trim($v);
                    }
                } else {
                    $options[$key][] = $value;
                }
            }
        }
        return $options;
    }

    public function parse($text)
    {
        $ret = new Collection();
        if (preg_match_all('/@([^@\n\r\t]*)/', $text, $globalMatches) > 0) {
            foreach ($globalMatches[1] as $annotationText) {
                preg_match('/([a-zA-Z0-9]+)/', $annotationText, $localMatches);
                if (in_array($localMatches[1], self::$ignoredAnnotations)) {
                    continue;
                }
                $annotation = new Annotation($localMatches[1]);
                $optsStart = strpos($annotationText, '(');
                if ($optsStart !== false) {
                    $optsEnd = strrpos($annotationText, ')');
                    $optsLength = $optsEnd - $optsStart - 1;
                    $opts = trim(substr($annotationText, $optsStart + 1, $optsLength));
                    foreach ($this->_parseOptions($opts) as $key => $values) {
                        foreach ($values as $value) {
                            $annotation->addOption($key, $value);
                        }
                    }
                }
                $ret->add($annotation);
            }
        }
        return $ret;
    }
}