<?php
/**
 * @Configuration
 */
class SomeBeanProviderClass
{
    /**
     * @Bean
     * @Scope(value='prototype')
     * @InitMethod(method=aMethod)
     * @DestroyMethod(method=bMethod)
     */
    public function someBean()
    {
        $ret = new MyBean();
        $ret->setSomeProperty('hello world');
        return $ret;
    }
}