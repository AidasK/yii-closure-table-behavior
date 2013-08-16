Yii Closure Table behavior
==========================

Extension allows managing trees stored in database.

Configuration
-------------

Add behavior to model as follows:

```php
public function behaviors()
{
    return array(
        'closureTableBehavior'=>array(
  			'class'=>'ext.ClosureTableBehavior',
			'closureTableName'=>'table_name',
		)
    );
}
```

Examples
--------

Model configuration: /tests/models/Folder.php

Schema example: /tests/schema/db.sql

Behavior usage: /tests/unit/ClosureTableBehaviorTest.php

Contribution
------------

Your participation to Yii Closure Table behavior development is very welcome!

To ensure consistency throughout the source code, keep these rules in mind as you are working:
 * All features or bug fixes must be tested by one or more specs.
 * Your code should follow [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) coding style guide



Literature:
-----------

http://www.slideshare.net/billkarwin/models-for-hierarchical-data

http://www.mysqlperformanceblog.com/2011/02/14/moving-subtrees-in-closure-table/
