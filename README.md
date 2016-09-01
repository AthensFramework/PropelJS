PropelJS
=============

Interact with your [Propel](http://propelorm.org/) database, in JavaScript.

PropelJS generates a JavaScript library from your Propel schema.

PropelJS is a behavior plugin for the [Propel](http://propelorm.org/) PHP ORM. You must be using Propel in order to use
this package.

Example
=======

Write the following JavaScript:
```
var db = bookstore.propelJS({baseAddress:'/api/'});

// Retrieve a book and log its title.
db.books(2).get().then(
    function(book) {
        console.log(book.getTitle());
    }

// Create an author and a book they've written
db.authors()
    .setFirstName('John')
    .setLastName('Steinbeck Jr.')
    .save()
    .then(
        function(author) {
            db.books()
                .setTitle('Grapes of Wrath II')
                .setAuthorId(author.getId())
                .save();
        }
    );

// Remove a book from the database.
db.books(5).delete();
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

PropelJS creates the JavaScript library automatically every time you run `propel model:build`.

How it Works
============




Example Setup
=============

This example demonstrates the basic steps that you'll need to complete to use PropelJS. We'll use the
[LAMP stack](https://en.wikipedia.org/wiki/LAMP_%28software_bundle%29) plus [Composer](https://getcomposer.org/)
for dependency management.

The specific details of this example may or may not work on your server, depending on its configuration. And I've
chosen some details that favor easy security over easy deployment; adapt this example to your own practices.

This example will use the following project structure:

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

This library is published on Packagist. To install using Composer, add the `"athens/propel-js": "0.*"` line to
your `"require"` section:

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

Take note that `<behavior name="propel_js" />` should be placed inside your `<database></database>` tags, but *not*
inside your `<table></table>` tags.

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

/**
 * The 'Bookstore' namespace comes from our database schema definition.
 * Your project will probably have a different namespace.
 */
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

# You may need to change '/api/index.php' to the actual absolute address of your API index.
RewriteRule ^(.*)$ /api/index.php [L]
```

However *this may not work on your particular server*: your server might not have mod-rewrite, or your Apache config
might not allow you to use it on your site.

Consult your local `.htaccess` guru if you need help.

Step 7: Include the JavaScript
------------------------------

In order for our web pages to include the `bookstore.js`, it has to be accessible to the web. There are two ways to
accomplish this:

1. Configure your server/project so that the `generated-js/` directory is accessible to the web.
2. Copy the `bookstore.js` file into a web-accessible directory.

I would normally choose (1) to ease deployment, but for demonstration purposes we'll demonstrate (2) by copying
`bookstore.js` into the `webroot/` directory:

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
    ├── api
    │   ├── .htaccess
    │   └── index.php
    ├── bookstore.js      <- Paste bookstore.js here
    └── index.php
```

Now `bookstore.js` is addressable as `http://example.net/bookstore.js`, and you can include it in the head
of your html files:

```
<head>
...
    <!-- PropelJS requires JQuery -->
    <script src="http://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="/bookstore.js"></script>
...
</head>

```

Step 8: Configure a Connection
-------------------------------

Finally, we configure a PropelJS connection. This can go right below the `<script src="/bookstore.js"></script>` tag
we created in Step 7:

```
<head>
...
    <!-- PropelJS requires JQuery -->
    <script src="http://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="/bookstore.js"></script>

    <script type="text/javascript">
        var db = bookstore.propelJS({baseAddress:'/api/'});
    </script>
...
</head>

```

That's it! Now you can create, retrieve, update, and delete authors and books as in the example above.


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
