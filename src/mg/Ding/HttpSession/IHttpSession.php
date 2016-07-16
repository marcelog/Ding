<?php
namespace Ding\HttpSession;

interface IHttpSession
{
    public function destroy();

    public function hasAttribute($name);
	
    public function getAttribute($name);

    public function setAttribute($name, $value);

    public function getSession();	
}
