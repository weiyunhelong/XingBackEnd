<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;
use app\models\Channel;
use app\models\WechatUser;
/* @var $this yii\web\View */
$this->title = '渠道浏览';
?>

<script type="text/javascript">
$(function(){
    $("#teenmenu").addClass("active");
})
</script>

    <div class="col-sm-9 main">
        <h1 class="page-header">渠道浏览</h1>
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
                    'label'=>'浏览活动',
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
                    'label'=>'浏览用户',
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
                    'label'=>'所属渠道',
                    'attribute'=>'cid',
                    'value' => function ($m) { 
                        $item=Channel::find()->where(['id'=>$m->cid])->one();
                        if(empty($item))
                        {
                            return "";
                        }else{
                            return $item->username;
                        }
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
