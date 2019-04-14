<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;
/* @var $this yii\web\View */
$this->title = '活动渠道';
?>

<script type="text/javascript">
$(function(){
    $("#eightmenu").addClass("active");
})
</script>

    <div class="col-sm-9 main">
        <h1 class="page-header">活动渠道</h1>
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
                    'label'=>'所属活动',
                    'attribute'=>'aid',
                     'value' => function ($m) { 
                        $item=Actity::find()->where(['id'=>$m->actid])->one();
                        if(empty($item))
                        {
                            return "";
                        }else{
                            return $item->name;
                        }
                    }
                ], 
                [
                    'label'=>'用户名',
                    'attribute'=>'username',
                ],
                [
                    'label'=>'登录密码',
                    'attribute'=>'password',
                ],
                [
                    'label'=>'手机号码',
                    'attribute'=>'phone',
                ],
                [
                    'label'=>'真实姓名',
                    'attribute'=>'truename',
                ],
                [
                    'label'=>'小程序地址',
                    'attribute'=>'id',
                    'value' => function ($model) { 
                        return "pages/login/index?cid=" .$model->id."&actid=".$model->actid; 
                     }
                ],
                [
                    'label'=>'创建时间',
                    'attribute' => 'createtime',
                    'value'=>function($m){
                       return date("Y-m-d H:i:s",$m->createtime);
                    }
                ],  
            ],
        ]) ?>
        </div>
    </div>
