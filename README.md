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
├── generated-api/
│   ├── API.php
├── generated-classes/
│   └── Bookstore/
│       ├── Author.php
│       ├── AuthorQuery.php
│       ├── Book.php
│       ├── BookQuery.php
│       ├── Base/
│       │   ├── Author.php
│       │   ├── AuthorQuery.php
│       │   ├── Book.php
│       │   ├── BookQuery.php
│       ├── Map/
│       │   ├── AuthorTableMap.php
│       │   ├── BookTableMap.php
├── generated-js/
│   ├── bookstore.js
├── generated-migrations/
├── generated-sql/
├── propel.inc
└── schema.xml
```


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
