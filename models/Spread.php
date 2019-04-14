<?php

namespace app\models;

use Yii;

class Spread extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'spread';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['actid', 'imgpath'], 'required'],
            [['actid', 'createtime'], 'integer'],
            [['imgpath'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'actid' => 'actid',
            'imgpath' => 'imgpath',
            'createtime' => 'createtime',
        ];
    }
}
