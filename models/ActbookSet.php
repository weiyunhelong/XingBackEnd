<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actbook_set".
 *
 * @property int $id
 * @property int $aid 关联活动ID
 * @property string $name 逗号分割
 */
class ActbookSet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actbook_set';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'name'], 'required'],
            [['aid'], 'integer'],
            [['name'], 'string'],
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
            'name' => 'Name',
        ];
    }
}
