<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actity".
 *
 * @property int $id
 * @property string $name
 * @property string $owner
 * @property int $start_date
 * @property int $end_date
 * @property int $type 1线上活动;2线上 + 线下;3线下活动
 * @property string $cover 封面图片
 * @property int $status 0：未开始；1已开始；2已结束。默认未开始
 * @property int $isbook 1:报名开启；2：报名关闭。默认报名关闭
 * @property int $booktype 1:个人 2：团队
 */
class Actity extends \yii\db\ActiveRecord
{
    public $file;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'owner', 'start_date', 'end_date', 'type', 'cover'], 'required'],
            [['booktype','start_date', 'end_date', 'type', 'status', 'isbook','isrank','createtime','gradetype'], 'integer'],
            [['name', 'owner', 'cover'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'owner' => 'Owner',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'type' => 'Type',
            'cover' => 'Cover',
            'status' => 'Status',
            'isbook' => 'Isbook',
            'booktype' => 'booktype',
            'isrank' => 'isrank',
            'gradetype' => 'gradetype',
            'createtime'=>'createtime'
        ];
    }

    public function getTasks()
    {
        return $this->hasMany(ActityTask::className(), ['aid' => 'id']);
    }
}
