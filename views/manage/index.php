<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
$this->title = '微信用户';
?>
<script type="text/javascript">
$(function(){
    $("#onemenu").addClass("active");
})
//编辑页面
function editopt(id,type){
   window.location.href="/manage/edit?id="+id+"&type="+type;
}
</script>

<div class="col-sm-9 main">
  <h1 class="page-header">微信用户</h1>
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
                    'label'=>'OPENID',
                    'attribute'=>'openid',
                  ],
                  [
                    'label'=>'微信昵称',
                    'attribute'=>'wxname',
                  ],
                  [
                    'label'=>'头像',
                    'attribute'=>'avatar',
                    'format' => [
                        'image', 
                         [
                           'width'=>'50',
                           'height'=>'50'
                         ]
                       ],
                        'value' => function ($model) { 
                           return $model->avatar; 
                       }
                  ],
                  [
                    'label'=>'性别',
                    'attribute'=>'sex',
                    'value' => function($model) {
                        if($model->sex==1){
                            return "男";
                        }else if($model->sex==2){
                            return "女";
                        }else{
                            return "未知";
                        }                    
                    },
                  ],
                  [
                    'label'=>'管理员',
                    'attribute'=>'isadmin',                    
                    'value' => function($model) {
                      if($model->isadmin==1){
                          return "是";
                      }else{
                          return "否";
                      }                    
                  },
                  ],
                  [
                    'label'=>'创建时间',
                    'attribute' => 'created_at',
                    'value'=>function($m){
                       return date("Y-m-d H:i:s",$m->created_at);
                    }
                  ],[
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作', 
                    'template' => ' {update}',//只需要展示删除{update}
                    'headerOptions' => ['width' => '240'],
                    'buttons' => [
                        "update"=>function ($url, $model, $key) {//print_r($key);exit;
                            if($model->isadmin==0){
                              return Html::a('成为管理员', 'javascript:;', ['onclick'=>'editopt('.$model->uid.',1)']);
                            }else{
                              return Html::a('普通用户', 'javascript:;', ['onclick'=>'editopt('.$model->uid.',0)']);
                            }                          
                        }, 
                        
                    ],
                ],
            ],
       ]) ?>
  </div>
</div
