<?php
namespace Some\Namespaces\Clazz;

/**
 * @Configuration
 */
class SomeNamespacedClass
{
    /**
     * @Bean(class="Some\Namespaces\Clazz\SomeOtherNamespacedClass")
     * Enter description here ...
     */
    public function aBeanFromANamespacedClass()
    {
        return new SomeOtherNamespacedClass();
    }
}

class SomeOtherNamespacedClass
{

}
