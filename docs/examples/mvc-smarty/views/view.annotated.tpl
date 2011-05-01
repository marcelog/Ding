<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html lang="en">
  <head>
    <title>My Annotated controller view</title>
  </head>
  <body>
<ul>
{foreach $arguments as $value}
   <li>{$value@key}: {$value}</li>
{/foreach}
</ul>
  </body>
</html>
