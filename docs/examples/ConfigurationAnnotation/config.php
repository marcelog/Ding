<?php
/**
 * @Configuration
 */
class SomeBeanProviderClass
{
    /**
     * @Bean(class=MyBean,scope=singleton)
     */
    public function someBean()
    {
        $ret = new MyBean();
        $ret->setSomeProperty('hello world');
        return $ret;
    }
}