Pagewize Client Library
=======================

This library can be used for those who develop Pagewize theme's.

##### Setup 
The setup is quite simple:

1. Download composer: `curl -sS https://getcomposer.org/installer | php`
1. type `php composer.phar install`, all dependencies are setup
1. Create a `config.php` file in the root fo the directory, please checkout `config.sample.php` as sample

##### Getting started
Make sure you're web server maps all the requests to the `index.php` file in the root of the directory. We have attached a `.htaccess` file in the root
of the project. If you use nginx read the online documentation.

The `PagewizeClient` will always return the data for a homepage. This is the page that belongs to the `/` url. From any other request the `$_SERVER['REQUEST_URI']` (slug) is passed to the client and will return the content that matches that slug.

Template files are stored in the `/tpl` folder. 
