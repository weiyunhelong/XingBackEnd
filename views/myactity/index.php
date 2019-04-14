<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;
use app\models\WechatUser;
/* @var $this yii\web\View */
$this->title = '报名记录';
?>

<script type="text/javascript">
$(function(){
    $("#fivemenu").addClass("active");
})
</script>

    <div class="col-sm-9 main">
        <h1 class="page-header">报名记录</h1>
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
                    'label'=>'报名时间',
                    'attribute'=>'created_at',
                    'value'=>function($m){
                        return date("Y-m-d H:i:s",$m->created_at);
                     }
                ]
            ],
        ]) ?>
        </div>
    </div>
