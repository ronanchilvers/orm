# ronanchilvers/orm

ORM is a small and simple database layer intended to be simple, fast and useful. It
implements a mix of the [active record] and [data mapper] patterns.

## Overview

### Things it does

* PDO underneath so should work with any PDO compatible database
* Provides a full query builder interface using [clancats hydrahon]
* Supports full save, destroy, update, insert functionality through the active record implementation
* Supports model finder objects to help avoid 'fat model' syndrome
* Supports fine grained model hooks for precise control of model lifecycle data
* Supports model validation using [respect/validation]

### Things it does NOT do

* Migrations - you can use whatever you like to manage your schema. We recommend [phinx]

## Installation

You'll need at least PHP7.0 to use the library. The recommended way to install is using [composer]:

```bash
composer install ronanchilvers/orm
```

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

##


[active record]: https://en.wikipedia.org/wiki/Active_record_pattern
[data mapper]: https://en.wikipedia.org/wiki/Data_mapper_pattern
[clancats/hydrahon]: https://clancats.io/hydrahon/master/
[respect/validation]: https://github.com/respect/validation
[phinx]: https://phinx.org/
[composer]: https://getcomposer.org/
