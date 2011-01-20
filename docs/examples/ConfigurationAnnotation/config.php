<?php
/**
 * @Configuration
 */
class SomeBeanProviderClass
{
    /**
     * @Bean
     */
    public function someBean()
    {
        $ret = new MyBean();
        $ret->setSomeProperty('hello world');
        return $ret;
    }
}