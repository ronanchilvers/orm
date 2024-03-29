# ronanchilvers/orm

[![Actions Status](https://github.com/ronanchilvers/orm/workflows/Unit%20Tests/badge.svg)](https://github.com/ronanchilvers/orm/actions)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ronanchilvers/orm/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ronanchilvers/orm/?branch=master)

`orm` is a small and simple database layer implementing the [active record] pattern. Its aim is to be simple, fast and useful. It relies on the [clancats hydrahon] query builder for database query access.

## Overview

### Things it does

* PDO underneath so should work with any PDO compatible database
* Provides a full query builder interface using [clancats/hydrahon]
* Supports full save, destroy, update, insert functionality through the active record implementation
* Supports model finder objects to help avoid 'fat model' syndrome
* Supports fine grained model hooks for precise control of model lifecycle data
* Supports model validation using [respect/validation]
* Simple relationship handling - belongsTo, hasMany, hasOne
* Configurable data type conversion for model properties

### Things it does NOT do

* Migrations - you can use whatever you like to manage your schema. We recommend [phinx]
* Multiple database connections - currently `orm` only supports a single PDO connection.

## Installation

You'll need at least PHP7.0 to use the library. The recommended way to install is using [composer]:

```bash
composer install ronanchilvers/orm
```

## Configuring the database

Since `orm` uses PDO, it's up to you how you create and instantiate your PDO object. This
will probably be in your bootstrap somewhere. Once your PDO object is available you
will need to give it to `orm`. Here's an example:

```php
$pdo = new PDO('sqlite::memory:');

Ronanchilvers\Orm\Orm::setConnection($pdo);
```

Clearly you will almost certainly not be using a `:memory:` DSN in practice. However
you create your PDO object, the crucial point here is that you call `Orm::setConnection`
to provide `orm` with your connection object.

## Basic usage

`orm` doesn't make any pre-judgments about your database schema. When building models
from a database it assumes it maps columns to properties and when saving it assumes
that any property has a corresponding database table column. Its up to you to make
sure the data makes sense.

Here we assume that we have a database table that looks like the following. We're
using MySQL / MariaDB syntax here but whatever PDO supports should be fine.

```sql
CREATE TABLE `books` (
  `book_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `book_author` int(11) NOT NULL,
  `book_name` varchar(1024) NOT NULL DEFAULT '',
  `book_created` datetime DEFAULT CURRENT_TIMESTAMP,
  `book_updated` datetime DEFAULT NULL,
  PRIMARY KEY (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Defining a model

First create your model class. It should extend the `Ronanchilvers\Orm\Model`
class. As with many [active record] implementations we assume here that the table
has a plural name (books) and its corresponding model class will have a singular
name (book).

```php
class Book extends Model
{}
```

If your columns have no column prefix, that's all you need to do. The table name
will be inferred from the class name. If however, like the example above, you have
column prefixes then you can tweak your model to suit.

```php
class Book extends Model
{
    static protected $columnPrefix = 'book';
}
```

Similarly if your table doesn't map to the model name you can specufy that too.

```php
class Book extends Model
{
    static protected $table        = 'my_books_table';
    static protected $columnPrefix = 'book';
}
```

Now you're ready to use the model.

### Finding models

`orm` supports a query builder interface provided by [clancats/hydrahon]. In order
to retrieve models from the database, first obtain a finder object.

```php
$finder = Orm::finder(Book::class);
```

Then you can use the finder object to retrieve models.

```php
$books = $finder->all();
```

There are several standard finder methods you can use:

```php
// Get all the records in one go
$books = $finder->all();

// Get the third page of models when there are 30 records per page
// (10 per page is the default)
$books = $finder->all(3, 30);

// Get a specific model by its primary key, here assumed to be numeric
$book = $finder->one(23);
```

You can use the full query builder to gain more control over the query:

```php
// Get all the books for author id 20
$books = $finder->select()->where('book_author', 20);

// Get all books added since last week - here we're using the excellent Carbon wrapper
// for DateTime
$recentBooks = $finder->select()->where('book_created', '>', Carbon::now()->subWeek());
```

You can read more about the capabilities of the query builder over at
the [clancats/hydrahon] site.

If you want complete control over the SQL that is executed you can do:

```php
$sql = "SELECT *
FROM books
  LEFT JOIN authors ON author_id = book_author
WHERE author_name LIKE :name
  AND author_created < :created";
$params = [
  'name'    => 'Fred%',
  'created' => Carbon::now()->subYear()->format('Y-m-d H:i:s'),
];
$books = $finder->query($sql, $params);
```

#### Custom Finder Classes

By default `Orm::finder` will give you back a vanilla `Ronanchilvers\Orm\Finder` object tied to given model class. However you can also override the finder class for a given model by setting the `$finder` static property on your model. This can be very useful if you want to add custom find methods (for example).

Let's imagine you've created a `BookFinder` class:

```php
class BookFinder extends Finder
{
    public function forAuthorId(int $id): array
    {
        return $this
          ->select()
          ->where(Book::prefix('author'), $id)
          ->execute();
    }
}
```

You can see that we've created a subclass of the default finder and we've added a `forAuthorId` method which returns an array of books for a given author id.

Then we tell the model about our new finder class:

```php
class Book extends Model
{
    static protected $finder = BookFinder::class;
}
```

and then finally you're readdy to use it:

```php
// $finder will be an instance of BookFinder here
$finder = Orm::finder(Book::class);
$authors = $finder->forAuthorId(2);
```


### Persistence

Saving a model back to the database is simply a matter of calling the `save` method.

```php
$finder = Orm::finder(Book::class);
$book = $finder->one(23);
$book->save();
```

Similarly destroying a model is also simple:

```php
$book->delete();
```

### Accessing model data

As in any active record implementation, database table columns are expressed as properties
on the model instance. Consequently data can be accessed by accessing those properties.

```php
$finder = Orm::finder(Book::class);
$book = $finder->one(72);
echo $book->name;
```

[active record]: https://en.wikipedia.org/wiki/Active_record_pattern
[data mapper]: https://en.wikipedia.org/wiki/Data_mapper_pattern
[clancats/hydrahon]: https://clancats.io/hydrahon/master/
[respect/validation]: https://github.com/respect/validation
[phinx]: https://phinx.org/
[composer]: https://getcomposer.org/
