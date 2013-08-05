<?php

class Related extends CActiveRecord
{

    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function rules()
    {
        return array(

        );
    }

    public function relations()
    {
        return array(
            'parent' => array(self::BELONGS_TO, 'Folder', 'parent_folder_id'),
        );
    }

}