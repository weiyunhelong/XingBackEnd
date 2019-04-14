<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "my_actity".
 *
 * @property int $id
 * @property int $aid 关联活动ID
 * @property int $uid 用户ID
 * @property int $created_at
 */
class MyActity extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'my_actity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'uid', 'created_at'], 'required'],
            [['aid', 'uid', 'created_at'], 'integer'],
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
            'created_at' => 'Created At',
        ];
    }

    public function getActity()
    {
        return $this->hasOne(Actity::className(), ['id' => 'aid']);
    }

    public function getUser()
    {
        return $this->hasOne(WechatUser::className(), ['uid' => 'uid']);
    }
}
