<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;

/* @var $this yii\web\View */
$this->title = '活动推广';
?>

<script type="text/javascript">
$(function(){
    $("#ninemenu").addClass("active");
})
//删除数据
function deleteopt(obj){    
   layer.confirm('你确定要删除这条数据吗？', {       
     btn: ['确定','取消'] //按钮
   }, 
   function(){
       $.ajax({
           type:'post',
           url:'/spread/delete',
           data:{
               id:obj
           },
           success:function(res){
              if(res.status=="success"){
                  window.location.href="/spread/index";
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
        <h1 class="page-header">活动推广</h1>
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
                    'label'=>'推广图片',
                    'attribute'=>'imgpath',
                    'format' => [
                     'image', 
                      [
                        'width'=>'50',
                        'height'=>'50'
                      ]
                    ],
                     'value' => function ($model) { 
                        return "/". $model->imgpath; 
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
