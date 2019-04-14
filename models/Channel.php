<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity".
 */
class Channel extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password', 'phone','truename','actid'], 'required'],
            [['actid','createtime'], 'integer'],
            [['username','password','phone','truename'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'username' => 'username',
            'password' => 'password',
            'phone' => 'phone',
            'truename' => 'truename',
            'actid' => 'actid',
            'createtime' => 'createtime',
        ];
    }
}
