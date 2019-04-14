<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;

/* @var $this yii\web\View */
$this->title = '详情指引';
?>

<script type="text/javascript">
$(function(){
    $("#fourmenu").addClass("active");
})
//删除数据
function deleteopt(obj){    
   layer.confirm('你确定要删除这条数据吗？', {       
     btn: ['确定','取消'] //按钮
   }, 
   function(){
       $.ajax({
           type:'post',
           url:'/actinfo/delete',
           data:{
               id:obj
           },
           success:function(res){
              if(res.status=="success"){
                  window.location.href="/actinfo/index";
              }else{
                  layer.msg("删除失败");
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
        <h1 class="page-header">详情指引</h1>
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
                    'label'=>'活动图片',
                    'attribute'=>'cover',
                    'format' => [
                     'image', 
                      [
                        'width'=>'50',
                        'height'=>'50'
                      ]
                    ],
                     'value' => function ($model) { 
                        return "/". $model->img_path; 
                    }
                ],             
                [
                    'label'=>'归属类型',
                    'attribute'=>'is_order',
                    'value'=>function($m){
                        if($m->type==1)
                        {
                            return "活动详情";
                        }else{
                            return "活动指引";
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
