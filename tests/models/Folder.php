<?php
/**
 * Class Folder
 * ClosureTableBehavior scopes
 * @method Folder descendantsOf($primaryKey, $depth = null) Named scope. Finds descendants
 * @method Folder descendants($depth=null) Named scope. Finds descendants
 * @method Folder childrenOf($primaryKey) Named scope. Finds children
 * @method Folder children() Named scope. Finds children
 * @method Folder ancestorsOf($primaryKey, $depth=null) Named scope. Gets ancestors for node
 * @method Folder ancestors($depth=null) Named scope. Gets ancestors for node
 * @method Folder parentOf($primaryKey) Named scope. Gets parent
 * @method Folder parent() Named scope. Gets parent
 * @method Folder pathOf($primaryKey) Named scope. Gets path
 * @method Folder path() Named scope. Gets path
 * @method Folder unorderedPathOf($primaryKey) Named scope. Gets path to the node.
 * @method Folder fullPathOf($primaryKey) Named scope. Get path with its children. (Warning: root node isn't returned.)
 * @method Folder fullPath() Named scope. Get path with its children. (Warning: root node isn't returned.)
 * @method Folder leaf() Named scope. Fills leaf attribute
 * ClosureTableBehavior methods
 * @method bool isLeaf() If node is a leaf
 * @method bool saveNodeAsRoot($runValidation = true, $attributes = null) Save node and insert closure table records
 * with transaction.
 * @method int markAsRoot($primaryKey) Insert closure table records
 * @method int appendTo($target, $node = null) Appends node to target as child (Only for new records).
 * @method int append(CActiveRecord $target) Appends node to target as child (Only for new records).
 * @method int moveTo($target, $node = null) Move node
 * @method int deleteNode($primaryKey = null) Delete node
 */
class Folder extends CActiveRecord
{
    public $leaf;

    /**
     * @param string $className
     * @return Folder
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors()
    {
        return array(
            'tree'=>array(
                'class'=>'ext.ClosureTableBehavior',
                'closureTableName'=>'folder_tree',
            ),
        );
    }

    public function rules()
    {
        return array(
            array('name','required'),
        );
    }
}
