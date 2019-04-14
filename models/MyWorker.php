<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "my_worker".
 *
 * @property int $id
 * @property int $aid 关联活动ID
 * @property int $uid
 * @property string $path 作品路径
 * @property int $type 作品类型1:视频；2：图片；3：文档
 * @property int $score
 * @property int $is_score
 */
class MyWorker extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'my_worker';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'uid', 'tid', 'path', 'type'], 'required'],
            [['aid', 'uid', 'tid', 'type', 'score', 'is_score'], 'integer'],
            [['path'], 'string', 'max' => 255],
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
            'tid' => 'Tid',
            'path' => 'Path',
            'type' => 'Type',
            'score' => 'Score',
            'is_score' => 'Is Score',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(WechatUser::className(), ['uid' => 'uid']);
    }
}
