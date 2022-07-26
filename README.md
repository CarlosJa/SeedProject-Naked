SeedProject Framework - is a super lite framework i built for my web applications. I looked into CI, Laravel, and a few others, didn't felt like they were too heavy for what i needed.  SeedProject is also good for beginner developers that need to get something up quickly and learn more about MVC frameworks and helps them get setup quickly to build out APIs for their Mobile Applications.

---

## Setting Up

Follow the steps:

1. clone the repo: git clone https://github.com/CarlosJa/SeedProject.git
2. You're ready to go.
   Note: If you're getting error regardineg the Vendor files  go into your putty and type:  composer dump-autoload

---

## Setting Up Routing

Routing is handled in 2 ways. I have default router provided by the Bootstrap.php which is basic /controller/method/param/param.

I also have AltoRouter which allows you to customize your routing.

To use custom routing go to **/plugins/Router.php**  you can add your Router Mapping.

```php
        $router->map('GET|POST','/game/info/[i:id]',  array('c' => 'games', 'a' => 'index'));
```
---

To learn more about AltoRouter visit (https://altorouter.com/usage/mapping-routes.html) 