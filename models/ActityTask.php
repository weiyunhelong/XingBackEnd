<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity_task".
 *
 * @property int $id
 * @property int $aid 关联活动ID
 * @property int $name 视频模块名称
 * @property int $video_num 视频数量
 * @property int $ismarking 是否手动打分
 * @property int $is_order 是否名次排行
 * @property int $is_delete 是否允许删除
 * @property int $type 类型
 */
class ActityTask extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actity_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'video_num', 'ismarking', 'is_order', 'is_delete'], 'required'],
            [['aid', 'video_num', 'ismarking', 'is_order', 'is_delete', 'type'], 'integer'],
            [['name'], 'string', 'max' => 255],
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
            'video_num' => 'Video Num',
            'ismarking' => 'Ismarking',
            'is_order' => 'Is Order',
            'is_delete' => 'Is Delete',
            'type' => 'Type',
        ];
    }
}
