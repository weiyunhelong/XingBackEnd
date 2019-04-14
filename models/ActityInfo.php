<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity_info".
 *
 * @property int $id
 * @property int $aid 关联活动ID
 * @property string $img_path 图片路径
 * @property int $type 1活动详情；2活动指引
 */
class ActityInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actity_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'img_path', 'type'], 'required'],
            [['aid', 'type'], 'integer'],
            [['img_path'], 'string', 'max' => 255],
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
            'img_path' => 'Img Path',
            'type' => 'Type',
        ];
    }
}
