<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "actbook_self_set".
 *
 * @property int $id
 * @property int $aid 关联活动ID
 * @property string $danhang 单行文本
 * @property string $danxuan_txt 单选题目
 * @property string $danxuan_opt 单选题目选项
 * @property string $duoxuan_txt 多选题目
 * @property string $duoxuan_opt 多选题目选项
 */
class ActbookSelfSet extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'actbook_self_set';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid'], 'required'],
            [['aid'], 'integer'],
            [['danhang', 'danxuan_txt', 'danxuan_opt', 'duoxuan_txt', 'duoxuan_opt'], 'string'],
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
            'danhang' => 'Danhang',
            'danxuan_txt' => 'Danxuan Txt',
            'danxuan_opt' => 'Danxuan Opt',
            'duoxuan_txt' => 'Duoxuan Txt',
            'duoxuan_opt' => 'Duoxuan Opt',
        ];
    }
}
