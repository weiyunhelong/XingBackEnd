<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;
use app\models\WechatUser;
use app\models\ActityTask;
/* @var $this yii\web\View */
$this->title = '作品管理';
?>

<script type="text/javascript">
$(function(){
    $("#teenthreemenu").addClass("active");
})
//更新打分
function editopt(obj){
    window.location.href="/work/edit?id="+obj;
}
//删除数据
function deleteopt(obj){    
   layer.confirm('你确定要删除这条数据吗？', {       
     btn: ['确定','取消'] //按钮
   }, 
   function(){
       $.ajax({
           type:'post',
           url:'/work/delete',
           data:{
               id:obj
           },
           success:function(res){
              if(res.status=="success"){
                  window.location.href="/work/index";
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
        <h1 class="page-header">作品管理</h1>
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
                    'label'=>'所属任务',
                    'attribute'=>'tid',
                     'value' => function ($m) { 
                        $item=ActityTask::find()->where(['id'=>$m->tid])->one();
                        if(empty($item))
                        {
                            return "";
                        }else{
                            return $item->name;
                        }
                    }
                ],
                [
                    'label'=>'上传用户',
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
                    'label'=>'作品路径',
                    'attribute'=>'path'
                ],             
                [
                    'label'=>'作品类型',
                    'attribute'=>'type',
                    'value'=>function($m){
                        if($m->type==1)
                        {
                            return "视频文件";
                        }else{
                            return "图片文件";
                        }
                     }
                ],  
                [
                    'label'=>'作品分数',
                    'attribute'=>'score',
                ],               
                [
                    'class' => 'yii\grid\ActionColumn',
                    'header' => '操作', 
                    'template' => '{update} {delete}',//只需要展示删除{update}
                    'headerOptions' => ['width' => '240'],
                    'buttons' => [    
                        'update' => function ($url, $model, $key) {
                            return Html::a('打分', 'javascript:;', ['onclick'=>'editopt('.$model->id.')']);
                        },      
                        'delete' => function ($url, $model, $key) {
                            return Html::a('删除', 'javascript:;', ['onclick'=>'deleteopt('.$model->id.')']);
                        }, 
                        
                        
                    ],
                ],
            ],
        ]) ?>
        </div>
    </div>
