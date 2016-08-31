PropelJS
=============

Interact with your [Propel](http://propelorm.org/) database, in JavaScript.

Example
=======

Write the following JavaScript:
```
var db = bookstore.propelJS({baseAddress:'/api/'});

myNewBook = db.books();
myNewBook.setTitle('Grapes of Wrath II: Shareholders' Revenge).setAuthorId(1).save()
     .then(
         function(visitor) {
             console.log(visitor.getId());
         }
     );
```

Given the following schema:
```
<database name="bookstore" defaultIdMethod="native">

  <table name="author" phpName="Author">
    <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true"/>
    <column name="first_name" type="varchar" size="128" required="true"/>
    <column name="last_name" type="varchar" size="128" required="true"/>
  </table>

  <table name="book" phpName="Book">
    <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true"/>
    <column name="title" type="varchar" size="255" required="true" />

    <column name="author_id" type="integer" required="true"/>
    <foreign-key foreignTable="author">
      <reference local="author_id" foreign="id"/>
    </foreign-key>
  </table>

  <behavior name="propel_js" />

</database>
```


Start Guide
===========

PropelJS is a behavior plugin for the [Propel](http://propelorm.org/) PHP ORM. You must be using Propel in order to use
this plugin. You can read more about [Propel behaviors](http://propelorm.org/documentation/06-behaviors.html).

This guide assumes that you're using [Composer](https://getcomposer.org/) for dependency management, although it is
possible to use PropelJS without using Composer.

This guide will use the schema given above and the following project structure:

```
├── composer.json
├── propel.inc
├── schema.xml
├── generated-api/
│   └── API.php
├── generated-classes/
│   └── Bookstore
│       └── ...
├── generated-js/
│   └── bookstore.js
├── generated-migrations/
├── generated-sql/
├── vendor/
│   └── ...
└── webroot/
    ├── about.php
    └── index.php
```

The `webroot/` directory will serve as the root of our web domain, with `index.php` and `about.php` addressed as
`http://example.net/index.php` and `http://example.net/about.php`.


Step 1: Installation
--------------------

This library is published on packagist. To install using Composer, add the `"athens/propel-js": "0.*"` line to your `"require"` dependencies:

```
{
    "require": {
        ...
        "athens/propel-js": "0.*",
        ...
    }
}
```

Step 2: Propel Schema
---------------------

PropelJS is a database-level behavior for Propel. To use it, you must insert `<behavior name="propel_js" />` into
your database schema. For example:

```
<database name="bookstore" defaultIdMethod="native">

  <table name="author" phpName="Author">
    <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true"/>
    <column name="first_name" type="varchar" size="128" required="true"/>
    <column name="last_name" type="varchar" size="128" required="true"/>
  </table>

  <table name="book" phpName="Book">
    <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true"/>
    <column name="title" type="varchar" size="255" required="true" />

    <column name="author_id" type="integer" required="true"/>
    <foreign-key foreignTable="author">
      <reference local="author_id" foreign="id"/>
    </foreign-key>
  </table>

  <behavior name="propel_js" />

</database>
```

Take note that `<behavior name="propel_js" />` should *not* be placed inside your `<table></table>` tags.

Step 3: Rebuild Your Models
---------------------------

Perform a `propel model:build` as usual.

You should now have a `generated-api/` directory and a `generated-js/` directory living alongside your usual
`generated-classes/` directory.

```
├── composer.json
├── propel.inc
├── schema.xml
├── generated-api/        <- New
│   └── API.php          <- New
├── generated-classes/
│   └── Bookstore
│       └── ...
├── generated-js/         <- New
│   └── bookstore.js     <- New
├── generated-migrations/
├── generated-sql/
├── vendor
│   └── ...
└── webroot
    ├── about.php
    └── index.php
```

Step 4: Add API.php to Autoload
-------------------------------

If you're using Composer or any other autoloading scheme, then you need to add the `API` class inside `API.php` to that
autoloader. For Composer, add the `generated-api/` directory to the `autoload` section of your `composer.json`. For example:

```
...
"autoload": {
        "classmap": [
            "generated-classes/",
            "generated-api/",
        ]
    }
...
```

Step 5: Create an API Endpoint
------------------------------

Create an `api` directory and an `index.php` to serve API requests.

```
├── composer.json
├── propel.inc
├── schema.xml
├── generated-api/
│   └── API.php
├── generated-classes/
│   └── Bookstore
│       └── ...
├── generated-js/
│   └── bookstore.js
├── generated-migrations/
├── generated-sql/
├── vendor
│   └── ...
└── webroot
    ├── about.php
    ├── api              <- This directory
    │   ├── .htaccess   <- This file
    │   └── index.php   <- And this file
    └── index.php
```

```
<?php
/** api/index.php */

require_once dirname(__FILE__) ."/../vendor/autoload.php";

echo \Bookstore\API::handle();
```

For your own project, the namespace for `API` won't be `Bookstore`. Change `Bookstore` to the namespace of your Propel
files. If in doubt, check the namespace declaration in `generated-api/API.php`.

The `.htaccess` file is explained in the next step.

Step 6: Request Routing
-------------------

In order to serve API requests such as `GET /api/authors/2`, we have to tell Apache to direct any requests within the
'api/' directory to the 'api/index.php' file.

If you're using an Apache web server, then this can be accomplished with a `.htaccess` file. The following `api\.htaccess`
example is likely to work on your server:

```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

# Change '/api/index.php' to the actual address of your API index.
RewriteRule ^(.*)$ /api/index.php [L]
```

However *this may not work on your particular server*: your server might not have mod-rewrite, or your Apache config
might not allow you to use it on your site.

Consult your local `.htaccess` wizard if you need help.

Step 7: Include the JavaScript
------------------------------

In order for our web pages to include the `bookstore.js`, it has to be accessible to the web. To accomplish this.

Compatibility
=============

* PHP 5.5, 5.6, 7.0

Todo
====

See GitHub [issue tracker](https://github.com/AthensFramework/PropelJS/issues/).

Getting Involved
================

Feel free to open pull requests or issues. [GitHub](https://github.com/AthensFramework/core/) is the canonical location of this project.

Here's the general sequence of events for code contribution:

1. Open an issue in the [issue tracker](https://github.com/AthensFramework/core/issues/).
2. In any order:
 Submit a pull request with a **failing** test that demonstrates the issue/feature.
 Get acknowledgement/concurrence.
3. Revise your pull request to pass the test in (2). Include documentation, if appropriate.

[PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) compliance is
enforced by [CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) in Travis.
