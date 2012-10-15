# Transparent Proxy for CoffeeScript on Apache and PHP

If you want to use CoffeeScript easily on general Apache and PHP environment, use CoffeePot proxy.

## How does it work?

* CoffeePot handles request for *.js via mod_proxy.
* The proxy converts *.js.coffee to *.js with coffeescript-php, and responses the result.

## Usage

* Put .haccess to where you write CoffeeScript. ex) /coffee/.
* Put CoffeePot directory on your DocumentRoot.
 * If you hope anywhere else, change RewriteRule in .haccess.
* Write CoffeeScript as *.js.coffee. ex) /coffee/example.js.coffee, /coffee/path/to.js.coffee
* Open URL to *.js ex) /coffee/example.js

## Freezing script

* Proxy also saves *.js as a file.
* Even if you remove .htaccess or disable mod_proxy, URI for a script never change.
* And the scripts are portable to any web server.

# Including

* coffeescript-php by alxlit https://github.com/alxlit/coffeescript-php
