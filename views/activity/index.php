<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
$this->title = '活动管理';
?>

<script type="text/javascript">
$(function(){
    $("#twomenu").addClass("active");
})
//删除数据
function deleteopt(obj){    
   layer.confirm('你确定要删除这条数据吗？', {       
     btn: ['确定','取消'] //按钮
   }, 
   function(){
       $.ajax({
           type:'post',
           url:'/activity/delete',
           data:{
               id:obj
           },
           success:function(res){
              if(res.status=="success"){
                  window.location.href="/activity/index";
              }else{
                  layer.msg("该活动下已有报名，无法删除");
              }
           }
       })
   
   },
   function(){
    layer.closeAll();   
   })
} 

</script>

    <div class="col-sm-9 main">
        <h1 class="page-header">活动管理</h1>
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
                    'label'=>'活动封面',
                    'attribute'=>'cover',
                    'format' => [
                     'image', 
                      [
                        'width'=>'50',
                        'height'=>'50'
                      ]
                    ],
                     'value' => function ($model) { 
                        return "/" . $model->cover; 
                    }
                ], 
                [
                    'label'=>'活动标题',
                    'attribute'=>'name',
                ],
                [
                    'label'=>'主办方',
                    'attribute'=>'owner'
                ], 
                [
                    'label'=>'开始日期',
                    'attribute'=>'start_date',
                    'value'=>function($m){
                        return date("Y-m-d",$m->start_date);
                     }
                ], 
                [
                    'label'=>'结束日期',
                    'attribute'=>'end_date',
                    'value'=>function($m){
                        return date("Y-m-d",$m->end_date);
                     }
                ],
                [
                    'label'=>'是否排行',
                    'attribute'=>'isrank',
                    'value'=>function($m){
                        if($m->isrank==1){
                            return "显示排行";
                        }else{
                            return "隐藏排行";
                        }
                     }
                ],
                [
                    'label'=>'活动类型',
                    'attribute'=>'booktype',
                    'value'=>function($m){
                        if($m->booktype==1){
                            return "个人形式";
                        }else{
                            return "团队形式";
                        }
                     }
                ],  
                [
                    'label'=>'活动类型',
                    'attribute'=>'booktype',
                    'value'=>function($m){
                        if($m->booktype==1){
                            return "个人形式";
                        }else{
                            return "团队形式";
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
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作', 
                    'template' => ' {delete}',//只需要展示删除{update}
                    'headerOptions' => ['width' => '240'],
                    'buttons' => [             
                        'delete' => function ($url, $model, $key) {
                            return Html::a('删除', 'javascript:;', ['onclick'=>'deleteopt('.$model->id.')']);
                        }, 
                        
                    ],
                ],
            ],
        ]) ?>
        </div>
    </div>
