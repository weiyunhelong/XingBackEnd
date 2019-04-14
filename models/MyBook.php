<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "my_book".
 *
 * @property int $id
 * @property int $aid
 * @property int $uid
 * @property string $danhang_txt
 * @property string $danxuan
 * @property string $duoxuan
 * @property string $name
 */
class MyBook extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'my_book';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'uid'], 'required'],
            [['aid', 'uid','createtime'], 'integer'],
            [['danhang_txt', 'danxuan', 'duoxuan', 'name'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'uid' => 'Uid',
            'danhang_txt' => 'Danhang Txt',
            'danxuan' => 'Danxuan',
            'duoxuan' => 'Duoxuan',
            'name' => 'Name',
            'createtime' => 'createtime',
        ];
    }
}
