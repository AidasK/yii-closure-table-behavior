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


Literature:
-----------

http://www.slideshare.net/billkarwin/models-for-hierarchical-data

http://www.mysqlperformanceblog.com/2011/02/14/moving-subtrees-in-closure-table/
