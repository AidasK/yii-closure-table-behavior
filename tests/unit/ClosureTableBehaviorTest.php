<?php
class ClosureTableBehaviorTest extends CDbTestCase
{
    public $fixtures=array(
        'Folder',
        'folder_tree' => ':folder_tree',
    );

    public function testDescendants()
    {
        $folder = Folder::model()->findByPk(1);
        $this->assertTrue($folder instanceof Folder);
        $descendants = $folder->descendants()->findAll();
        $this->assertEquals(6, count($descendants));
        foreach($descendants as $descendant) {
            $this->assertTrue($descendant instanceof Folder);
        }
        $this->assertEquals(2, $descendants[0]->primaryKey);
        $this->assertEquals(3, $descendants[1]->primaryKey);
        $this->assertEquals(4, $descendants[2]->primaryKey);
        $this->assertEquals(5, $descendants[3]->primaryKey);
        $this->assertEquals(6, $descendants[4]->primaryKey);
        $this->assertEquals(7, $descendants[5]->primaryKey);
    }

    public function testDescendantsOf()
    {
        $descendantsOf = Folder::model()->descendantsOf(1)->findAll();
        $nestedSet=Folder::model()->findByPk(1);
        $this->assertTrue($nestedSet instanceof Folder);
        $descendants = $nestedSet->descendants()->findAll();
        $this->assertEquals(6, count($descendants));
        foreach ($descendants as $key => $row) {
            $this->assertEquals($descendantsOf[$key], $row);
        }
    }

    public function testChildren()
    {
        $children = Folder::model()->childrenOf(1)->findAll();
        $this->assertEquals(2, count($children));
        foreach($children as $child) {
            $this->assertTrue($child instanceof Folder);
        }
        $this->assertEquals(2, $children[0]->primaryKey);
        $this->assertEquals(4, $children[1]->primaryKey);
    }

    public function testAncestors()
    {
        $folder = Folder::model()->findByPk(6);
        $this->assertTrue($folder instanceof Folder);
        $ancestors = $folder->ancestors()->findAll();
        $this->assertEquals(2, count($ancestors));
        foreach($ancestors as $ancestor) {
            $this->assertTrue($ancestor instanceof Folder);
        }
        $this->assertEquals($ancestors[0]->primaryKey, 1);
        $this->assertEquals($ancestors[1]->primaryKey, 4);
    }

    public function testParent()
    {
        $folder = Folder::model()->findByPk(6);
        $this->assertTrue($folder instanceof Folder);
        $parent = $folder->parent()->find();
        $this->assertTrue($parent instanceof Folder);
        $this->assertEquals($parent->primaryKey, 4);
    }

    public function testPath()
    {
        $folders = Folder::model()->pathOf(1)->findAll();
        $this->assertEquals(1, count($folders));
        $this->assertEquals(1, $folders[0]->primaryKey);

        $folders = Folder::model()->pathOf(7)->findAll();
        $this->assertEquals(4, count($folders));
        $this->assertEquals(1, $folders[0]->primaryKey);
        $this->assertEquals(4, $folders[1]->primaryKey);
        $this->assertEquals(6, $folders[2]->primaryKey);
        $this->assertEquals(7, $folders[3]->primaryKey);
    }

    public function testFullPath()
    {
        $folders = Folder::model()->fullPathOf(4)->findAll();
        $this->assertEquals(4, count($folders));
        $this->assertEquals(2, $folders[0]->primaryKey);
        $this->assertEquals(4, $folders[1]->primaryKey);
        $this->assertEquals(5, $folders[2]->primaryKey);
        $this->assertEquals(6, $folders[3]->primaryKey);
    }

    public function testIsLeaf()
    {
        $leafs = array(3,5,7);
        $notLeafs = array(1,2,4,6);
        foreach ($leafs as $id) {
            $folder = Folder::model()->leaf()->findByPk($id);
            $this->assertTrue($folder instanceof Folder);
            $this->assertNotEmpty($folder->id);
            $this->assertTrue($folder->isLeaf());
        };
        foreach ($notLeafs as $id) {
            $folder = Folder::model()->leaf()->findByPk($id);
            $this->assertTrue($folder instanceof Folder);
            $this->assertNotEmpty($folder->id);
            $this->assertFalse($folder->isLeaf());
        };
    }

    public function testChildrenIsLeaf()
    {
        $folders = Folder::model()->leaf()->childrenOf(4)->findAll();
        $this->assertEquals(5, $folders[0]->primaryKey);
        $this->assertTrue($folders[0]->isLeaf());
        $this->assertEquals(6, $folders[1]->primaryKey);
        $this->assertFalse($folders[1]->isLeaf());

        $folders = Folder::model()->leaf()->childrenOf(1)->findAll();
        $this->assertEquals(2, count($folders));
    }

    public function testAppend()
    {
        $folder = Folder::model()->findByPk(5);
        $newFolder = new Folder();
        $newFolder->name = 'Folder 1.4.5.8';
        $this->assertTrue($newFolder->save());
        $this->assertGreaterThan(0, $folder->append($newFolder));
        $parent = $newFolder->parent()->find();
        $this->assertTrue($parent instanceof Folder);
        $this->assertEquals(5, $parent->primaryKey);
        $parent = $parent->parent()->find();
        $this->assertTrue($parent instanceof Folder);
        $this->assertEquals(4, $parent->primaryKey);
        $parent = $parent->parent()->find();
        $this->assertTrue($parent instanceof Folder);
        $this->assertEquals(1, $parent->primaryKey);
    }

    public function testMoveTo()
    {
        $folders = Folder::model()->descendantsOf(5)->findAll();
        $this->assertEquals(0, count($folders));
        Folder::model()->moveTo(5, 2);
        $folders = Folder::model()->descendantsOf(5)->findAll();
        $this->assertEquals(2, count($folders));
        $this->assertEquals(2, $folders[0]->primaryKey);
        $this->assertEquals(3, $folders[1]->primaryKey);
        $folders = Folder::model()->descendantsOf(2)->findAll();
        $this->assertEquals(1, count($folders));
        $this->assertEquals(3, $folders[0]->primaryKey);

        $parent = Folder::model()->parentOf(2)->find();
        $this->assertTrue($parent instanceof Folder);
        $this->assertEquals(5, $parent->primaryKey);
    }

    public function testMoveToInvalid()
    {
        // from -> to
        $moves = array(
            array(1, 0), array(0, 1), array(0, 0), array(3, 0),
            array(1, 1), array(1, 2), array(1, 3), array(1, 7),
            array(2, 3), array(2, 2),
            array(3, 3),
        );
        foreach ($moves as $move) {
            try {
                Folder::model()->moveTo($move[1], $move[0]);
                $this->fail();
            } catch (CDbException $e) {

            }
        }
    }

    public function testDeleteNode()
    {
        $folder = Folder::model()->findByPk(4);
        $this->assertTrue($folder instanceof Folder);
        $folder->deleteNode();
        $folder = Folder::model()->findByPk(4);
        $this->assertTrue($folder === null);
        $folder = Folder::model()->findByPk(5);
        $this->assertTrue($folder === null);
        $folder = Folder::model()->findByPk(6);
        $this->assertTrue($folder === null);
        $folder = Folder::model()->findByPk(7);
        $this->assertTrue($folder === null);
        $folder = Folder::model()->findByPk(2);
        $this->assertTrue($folder instanceof Folder);
        $folder = Folder::model()->findByPk(3);
        $this->assertTrue($folder instanceof Folder);

        $this->assertEquals(0, Folder::model()->deleteNode(0));

        $this->assertEquals(3, Folder::model()->deleteNode(1));
        $this->assertEquals(0, count(Folder::model()->findAll()));
    }

    public function testSaveAsRoot()
    {
        $newFolder = new Folder();
        $newFolder->name = 'Folder 1';
        $this->assertTrue($newFolder->saveNodeAsRoot());
        $this->assertEquals(0, count($newFolder->descendants()->findAll()));
        $this->assertEquals(0, count($newFolder->ancestors()->findAll()));
    }

    public function testMixed()
    {
        $folder5 = Folder::model()->findByPk(5);
        $newFolder = new Folder();
        $newFolder->name = 'Folder 1.4.5.8';
        $this->assertTrue($newFolder->save());
        $this->assertGreaterThan(0, $folder5->append($newFolder));

        $folder5->moveTo(2);

        $folder2Childs = Folder::model()->childrenOf(2)->findAll();
        $this->assertEquals(2, count($folder2Childs));

        $folder2Descendants = Folder::model()->descendantsOf(2)->findAll();
        $this->assertEquals(3, count($folder2Descendants));

        $folders = Folder::model()->pathOf(8)->findAll();
        $this->assertEquals(4, count($folders));
        $this->assertEquals(1, $folders[0]->primaryKey);
        $this->assertEquals(2, $folders[1]->primaryKey);
        $this->assertEquals(5, $folders[2]->primaryKey);
        $this->assertEquals(8, $folders[3]->primaryKey);
    }
}