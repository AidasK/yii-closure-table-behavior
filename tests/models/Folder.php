<?php
/**
 * Class Folder
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
 * @method Folder leaf() Named scope. Fills leaf attribute
 *
 * @method bool isLeaf() If node is a leaf
 * @method Folder appendTo($target, $node = null) Appends node to target as child (Only for new records).
 * @method Folder append(CActiveRecord $target) Appends node to target as child (Only for new records).
 * @method Folder moveTo($target, $node = null) Move node
 * @method Folder deleteNode($primaryKey = null) Delete node
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
