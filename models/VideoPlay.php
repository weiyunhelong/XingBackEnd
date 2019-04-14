<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity".
 */
class VideoPlay extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'video_play';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'wid','actid'], 'required'],
            [['uid', 'wid', 'actid','createtime'], 'integer'],
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
            'wid' => 'wid',
            'createtime' => 'createtime',
        ];
    }
}
