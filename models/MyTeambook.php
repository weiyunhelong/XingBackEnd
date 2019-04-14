<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "my_teambook".
 *
 * @property int $id
 * @property int $uid
 * @property int $aid
 * @property string $teamname 团队名称
 * @property string $teachername 指导老师
 * @property string $name 队员
 */
class MyTeambook extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'my_teambook';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'aid', 'teamname'], 'required'],
            [['uid', 'aid','teacheryear','createtime'], 'integer'],
            [['teamname','teachername','teacherphone','teacherowner','teacheraddress','teamuser'], 'string'],
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
            'aid' => 'aid',
            'teamname' => 'teamname',
            'teachername' => 'teachername',
            'teacheryear' => 'teacheryear',
            'teacherphone' => 'teacherphone',
            'teacheremail' => 'teacheremail',
            'teacherowner' => 'teacherowner',
            'teacheraddress' => 'teacheraddress',
            'teamuser' => 'teamuser',
            'createtime'=>'createtime'
        ];
    }
}
