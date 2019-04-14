<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;

/* @var $this yii\web\View */
$this->title = '活动任务管理';
?>

<script type="text/javascript">
$(function(){
    $("#threemenu").addClass("active");
})
//删除数据
function deleteopt(obj){    
   layer.confirm('你确定要删除这条数据吗？', {       
     btn: ['确定','取消'] //按钮
   }, 
   function(){
       $.ajax({
           type:'post',
           url:'/acttask/delete',
           data:{
               id:obj
           },
           success:function(res){
              if(res.status=="success"){
                  window.location.href="/acttask/index";
              }else{
                  layer.msg("该活动任务下已有作品，无法删除");
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
        <h1 class="page-header">活动任务管理</h1>
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
                    'label'=>'任务类型',
                    'attribute'=>'type',
                    'value'=>function($m){
                        if($m->type==1)
                        {
                            return "视频模块";
                        }else if($m->type==2){
                            return "图片模块";
                        }else{
                            return "文档模块";
                        }
                     }
                ], 
                [
                    'label'=>'手动打分',
                    'attribute'=>'ismarking',
                    'value'=>function($m){
                        if($m->ismarking==1)
                        {
                            return "是";
                        }else{
                            return "否";
                        }
                     }
                ],               
                [
                    'label'=>'允许排行',
                    'attribute'=>'is_order',
                    'value'=>function($m){
                        if($m->is_order==1)
                        {
                            return "是";
                        }else{
                            return "否";
                        }
                     }
                ], 
                [
                    'label'=>'允许删除',
                    'attribute'=>'is_delete',
                    'value'=>function($m){
                        if($m->is_delete==1)
                        {
                            return "是";
                        }else{
                            return "否";
                        }
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
