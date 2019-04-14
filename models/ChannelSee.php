<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity".
 */
class ChannelSee extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel_see';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'actid', 'cid'], 'required'],
            [['uid', 'actid', 'cid','createtime'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'uid' => 'uid',
            'actid' => 'actid',
            'cid' => 'cid',
            'createtime' => 'createtime',
        ];
    }
}
