<?php
/**
 * ClosureTableBehavior class file.
 * Provides tree set functionality for a model.
 *
 * @author Aidas Klimas
 * @link https://github.com/AidasK/yii-closure-table-behavior/
 * @version 1.0
 */
class ClosureTableBehavior extends CActiveRecordBehavior
{
    public $closureTableName;
    public $childAttribute = 'child';
    public $parentAttribute = 'parent';
    public $depthAttribute = 'depth';
    public $isLeafParameter = 'leaf';

    /**
     * Finds descendants
     * @param int $primaryKey.
     * @param int $depth the depth.
     * @return CActiveRecord the owner.
     */
    public function descendantsOf($primaryKey, $depth = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        $db = $owner->getDbConnection();
        $criteria = $owner->getDbCriteria();
        $alias = $db->quoteColumnName($owner->getTableAlias());
        $closureTable = $db->quoteTableName($this->closureTableName);
        $childAttribute = $db->quoteColumnName($this->childAttribute);
        $parentAttribute = $db->quoteColumnName($this->parentAttribute);
        $primaryKeyName = $owner->tableSchema->primaryKey;
        $criteria->mergeWith(array(
            'join' => 'JOIN ' . $closureTable
                    . ' ON ' . $closureTable . '.' . $db->quoteColumnName($this->childAttribute) . '='
                    . $alias . '.' . $primaryKeyName,
            'condition' => $closureTable . '.' . $db->quoteColumnName($this->parentAttribute) . '=' . $primaryKey
        ));
        if ($depth === null) {
            $criteria->addCondition(
                $closureTable . '.' . $childAttribute . '!=' . $closureTable . '.' . $parentAttribute
            );
        } else {
            $criteria->addCondition(
                $closureTable . '.' . $db->quoteColumnName($this->depthAttribute) . ' BETWEEN 1 AND ' . (int) $depth
            );
        }
        return $owner;
    }

    /**
     * Named scope. Gets descendants for node.
     * @param int $depth the depth.
     * @return CActiveRecord the owner.
     */
    public function descendants($depth = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        return $this->descendantsOf($owner->getPrimaryKey(), $depth);
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @param int $primaryKey
     * @return CActiveRecord the owner.
     */
    public function childrenOf($primaryKey)
    {
        return $this->descendantsOf($primaryKey, 1);
    }

    /**
     * Named scope. Gets children for node (direct descendants only).
     * @return CActiveRecord the owner.
     */
    public function children()
    {
        return $this->descendants(1);
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param int $primaryKey primary key
     * @param int $depth the depth.
     * @return CActiveRecord the owner.
     */
    public function ancestorsOf($primaryKey, $depth = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        $db = $owner->getDbConnection();
        $criteria = $owner->getDbCriteria();
        $closureTable = $db->quoteTableName($this->closureTableName);
        $this->pathOf($primaryKey);
        if ($depth == null) {
            $criteria->addCondition(
                $closureTable . '.' . $db->quoteColumnName($this->childAttribute)
                . '!=' . $closureTable . '.' . $db->quoteColumnName($this->parentAttribute)
            );
        } else {
            $criteria->addCondition(
                $closureTable . '.' . $db->quoteColumnName($this->depthAttribute) . ' BETWEEN 1 AND ' . (int) $depth
            );
        }
        return $owner;
    }

    /**
     * Named scope. Gets ancestors for node.
     * @param int $depth the depth.
     * @return CActiveRecord the owner.
     */
    public function ancestors($depth = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        return $this->ancestorsOf($owner->getPrimaryKey(), $depth);
    }

    /**
     * Named scope. Gets parent of node.
     * @param int $primaryKey primary key
     * @return CActiveRecord the owner.
     */
    public function parentOf($primaryKey)
    {
        return $this->ancestorsOf($primaryKey, 1);
    }

    /**
     * Named scope. Gets parent of node.
     * @return CActiveRecord the owner.
     */
    public function parent()
    {
        return $this->ancestors(1);
    }

    /**
     * Named scope. Gets path to the node.
     * @param int $primaryKey primary key
     * @return CActiveRecord the owner.
     */
    public function pathOf($primaryKey)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        $db = $owner->getDbConnection();
        $criteria = $owner->getDbCriteria();
        $alias = $db->quoteColumnName($owner->getTableAlias());
        $closureTable = $db->quoteTableName($this->closureTableName);
        $primaryKeyName = $owner->tableSchema->primaryKey;
        $criteria->mergeWith(array(
            'join' => 'JOIN ' . $closureTable
                . ' ON ' . $closureTable . '.' . $db->quoteColumnName($this->parentAttribute) . '='
                . $alias . '.' . $primaryKeyName,
            'condition' => $closureTable . '.' . $db->quoteColumnName($this->childAttribute) . '=' . $primaryKey
        ));
        return $owner;
    }

    /**
     * Named scope. Gets path to the node.
     * @return CActiveRecord the owner.
     */
    public function path()
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        return $this->pathOf($owner->getPrimaryKey());
    }

    /**
     * Named scope. Selects leaf column which indicates if record is a leaf
     * @return CActiveRecord the owner.
     */
    public function leaf()
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        $db = $owner->getDbConnection();
        $criteria = $owner->getDbCriteria();
        $alias = $db->quoteColumnName($owner->getTableAlias());
        $closureTable = $db->quoteTableName($this->closureTableName);
        $leafColumn = $db->quoteColumnName($this->isLeafParameter);
        $parentAttribute =  $db->quoteColumnName($this->parentAttribute);
        $closureTableAlias = 'ctleaf';
        $primaryKeyName = $owner->tableSchema->primaryKey;
        $criteria->mergeWith(array(
            'join' => 'LEFT JOIN ' . $closureTable . ' ' . $closureTableAlias
                    . ' ON ' . $closureTableAlias . '.' . $parentAttribute . '=' . $alias . '.' . $primaryKeyName
                    . ' AND '. $closureTableAlias . '.' . $parentAttribute . '!='
                    . $closureTableAlias . '.' . $db->quoteColumnName($this->childAttribute),
            'select' => array(
                'ISNULL(' . $closureTableAlias . '.' . $parentAttribute . ') as ' . $leafColumn
            )
        ));
        return $owner;
    }

    /**
     * leaf scope is required
     * @return bool
     */
    public function isLeaf()
    {
        return (boolean)$this->getOwner()->{$this->isLeafParameter};
    }

    /**
     * Appends node to target as child (Only for new records).
     * @param CActiveRecord|int $target where to append
     * @param CActiveRecord|int $node node to append
     * @return number of rows inserted, on fail - 0
     */
    public function appendTo($target, $node = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        $db = $owner->getDbConnection();
        $closureTable = $db->quoteTableName($this->closureTableName);
        if ($target instanceof CActiveRecord) {
            $primaryKey = $db->quoteValue($target->primaryKey);
        } else {
            $primaryKey = $db->quoteValue($target);
        }
        if ($node === null) {
            $node = $owner;
        }
        if ($node instanceof CActiveRecord) {
            $nodeId = $node->primaryKey;
        } else {
            $nodeId = $node;
        }
        $childAttribute = $db->quoteColumnName($this->childAttribute);
        $parentAttribute = $db->quoteColumnName($this->parentAttribute);
        $depthAttribute = $db->quoteColumnName($this->depthAttribute);
        $cmd = $db->createCommand(
            'INSERT INTO ' . $closureTable
            . '(' . $parentAttribute . ',' . $childAttribute . ',' . $depthAttribute . ') '
            . 'SELECT ' . $parentAttribute . ',:nodeId'
            . ',' . $depthAttribute . '+1 '
            . 'FROM ' . $closureTable
            . 'WHERE ' . $childAttribute . '=' . $primaryKey
            . 'UNION ALL SELECT :nodeId,:nodeId,\'0\''
        );
        return $cmd->execute(array(':nodeId'=>$nodeId));
    }


    /**
     * Appends target to node as child.
     * @param CActiveRecord $target the target.
     * @return boolean whether the appending succeeds.
     */
    public function append(CActiveRecord $target)
    {
        return $target->appendTo($this->getOwner());
    }

    /**
     * Move node
     * @param CActiveRecord|int $target
     * @param CActiveRecord|int $node if null, owner id will be used
     * @throws CDbException|Exception
     */
    public function moveTo($target, $node = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        $db = $owner->getDbConnection();
        $closureTable = $db->quoteTableName($this->closureTableName);
        if ($target instanceof CActiveRecord) {
            $targetId = $db->quoteValue($target->primaryKey);
        } else {
            $targetId = $db->quoteValue($target);
        }
        if ($node === null) {
            $node = $owner;
        }
        if ($node instanceof CActiveRecord) {
            $nodeId = $node->primaryKey;
        } else {
            $nodeId = $node;
        }
        $childAttribute = $db->quoteColumnName($this->childAttribute);
        $parentAttribute = $db->quoteColumnName($this->parentAttribute);
        $depthAttribute = $db->quoteColumnName($this->depthAttribute);
        if ($db->getCurrentTransaction() === null) {
            $transaction = $db->beginTransaction();
        }
        try {
            $cmd = $db->createCommand(
                'DELETE a FROM ' . $closureTable . ' a '
                . 'JOIN ' . $closureTable . ' d ON a.' . $childAttribute . '=d.' . $childAttribute
                . 'LEFT JOIN ' . $closureTable . ' x ON x.' . $parentAttribute . '=d.' . $parentAttribute
                . 'AND x.' . $childAttribute . '=a.' . $parentAttribute
                . 'WHERE d.' . $parentAttribute . '=? AND x.' . $parentAttribute . ' IS NULL'
            );
            if (!$cmd->execute(array($nodeId))) {
                throw new CDbException('Node had no records in closure table');
            }
            $cmd = $db->createCommand(
                'INSERT INTO ' . $closureTable . '(' . $parentAttribute . ',' . $childAttribute . ',' . $depthAttribute . ')'
                . 'SELECT u.' . $parentAttribute . ',b.' . $childAttribute
                . ',u.' . $depthAttribute . '+b.' . $depthAttribute . '+1 '
                . 'FROM ' . $closureTable . ' u JOIN ' . $closureTable . ' b '
                . 'WHERE b.' . $parentAttribute . '=? AND u.' . $childAttribute . '=?'
            );
            if (!$cmd->execute(array($nodeId, $targetId))) {
                throw new CDbException('Target node does not exist');
            }
            if (isset($transaction)) {
                $transaction->commit();
            }
        } catch (CDbException $e) {
            if (isset($transaction)) {
                $transaction->rollback();
            }
            throw $e;
        }
    }

    /**
     * Deletes node and it's descendants.
     * @param $primaryKey
     * @return int number of rows deleted
     */
    public function deleteNode($primaryKey = null)
    {
        /* @var $owner CActiveRecord */
        $owner = $this->getOwner();
        if ($primaryKey === null) {
            $primaryKey = $owner->primaryKey;
        }
        $db = $owner->getDbConnection();
        $closureTable = $db->quoteTableName($this->closureTableName);
        $childAttribute = $db->quoteColumnName($this->childAttribute);
        $primaryKeyName = $db->quoteColumnName($owner->tableSchema->primaryKey);
        $cmd = $db->createCommand(
            'DELETE t, f '
            . 'FROM ' . $closureTable . ' t '
            . 'JOIN ' . $closureTable . ' tt ON t.' . $childAttribute . '= tt.' . $childAttribute
            . 'JOIN ' . $owner->tableName() . ' f ON t.' . $childAttribute . '=f.'.$primaryKeyName
            . 'WHERE tt.' . $db->quoteColumnName($this->parentAttribute) . '=?'
        );
        return $cmd->execute(array($primaryKey));
    }
}