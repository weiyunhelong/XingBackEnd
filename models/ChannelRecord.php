<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity".
 */
class ChannelRecord extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'actid', 'cid', 'type'], 'required'],
            [[ 'actid', 'cid','createtime', 'type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'actid' => 'actid',
            'cid' => 'cid',
            'type' => 'type',
            'createtime' => 'createtime',
        ];
    }
}
