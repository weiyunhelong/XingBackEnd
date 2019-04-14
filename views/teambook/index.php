<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;
use app\models\WechatUser;
/* @var $this yii\web\View */
$this->title = '团队报名';
?>

<script type="text/javascript">
$(function(){
    $("#sixmenu").addClass("active");
})
</script>

    <div class="col-sm-9 main">
        <h1 class="page-header">团队报名</h1>
        <div class="row placeholders">
       
        <?= GridView::widget([
            'dataProvider' => $provider,
            'columns' => [
                [
                  'class' => 'yii\grid\CheckboxColumn',
                ],                
                [
                    'label'=>'序号',
                    'value' => function ($model, $key, $index, $grid) { 
                      return $index+1; 
                    }
                  ],
                [
                    'label'=>'报名活动',
                    'attribute'=>'aid',
                     'value' => function ($m) { 
                        $item=Actity::find()->where(['id'=>$m->aid])->one();
                        if(empty($item))
                        {
                            return "";
                        }else{
                            return $item->name;
                        }
                    }
                ], 
                [
                    'label'=>'报名用户',
                    'attribute'=>'uid',
                     'value' => function ($m) { 
                        $item=WechatUser::find()->where(['uid'=>$m->uid])->one();
                        if(empty($item))
                        {
                            return "";
                        }else{
                            return $item->wxname;
                        }
                    }
                ],
                [
                    'label'=>'团队名称',
                    'attribute'=>'teamname',
                ],
                [
                    'label'=>'教练姓名',
                    'attribute'=>'teachername',
                ],
                [
                    'label'=>'教练年龄',
                    'attribute'=>'teacheryear',
                ],
                [
                    'label'=>'手机号码',
                    'attribute'=>'teacherphone',
                ],
                [
                    'label'=>'邮箱地址',
                    'attribute'=>'teacheremail',
                ],
                [
                    'label'=>'所属机构',
                    'attribute'=>'teacherowner',
                ],
                [
                    'label'=>'机构地址',
                    'attribute'=>'teacheraddress',
                ],
                [
                    'label'=>'团队队员',
                    'attribute'=>'teamuser',
                ],
            ],
        ]) ?>
        </div>
    </div>
