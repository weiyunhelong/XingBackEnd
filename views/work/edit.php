<?php
use yii\widgets\ActiveForm;
$this->title = '作品管理';

?>

<script type="text/javascript">
  $(function(){
    $("#teenthreemenu").addClass("active");
    //得到作品的数据
    initData();
  })

  //得到作品的数据
  function initData(){
    $.ajax({
      type:'get',
      url:'/work/getwork',
      data:{
          id:$("#myworker-id").val()
      },
      success:function(res){
        console.log("作品的数据:");
        console.log(res);

        if(res.status=="success"){

        //赋值部分
        $("#actname").html(res.actname);
        $("#taskname").html(res.taskname);
        $("#teamname").html(res.teamname);
        if(res.worktype==1){
            $("#videoworkpath").attr("src",res.workpath);
            $("#imgworkpath").hide();
        }else{
            $("#imgworkpath").attr("src",res.workpath);
            $("#videoworkpath").hide();
        }
        $("#workfen").val(res.score);
        }else{
            layer.msg(res.msg+"请检查数据！");
        }
      }
    })

  }

  //保存数据
  function saveopt(){
     //得到参数
     var name=$("#name").val();
     var tid=$("#tid").val();

     $.ajax({
       type:'get',
       url:'/work/save',
       data:{
        id:$("#myworker-id").val(),
        fen:$("#workfen").val()
       },
       success:function(res){
         if(res){
           window.location.href="/work/index";
         }else{
           layer.msg("保存失败");
         }
       },
     })
  }
</script>

<div style="display:none;">
   <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]);?>
     <?=$form->field($model, 'id')->label('作品id:')?>
   <?php ActiveForm::end();?>
</div>

<div class="col-sm-9 col-md-4 col-md-4 main">
  <h1 class="page-header">作品打分</h1>
  <div id="w0">
      <div class="form-group field-testpaper-name">
        <label class="control-label" for="testpaper-name">活动名称:</label>
        <div class="txtv" id="actname"></div>
        <div class="help-block"></div>
      </div>
      <div class="form-group field-testpaper-tid">
        <label class="control-label" for="testpaper-tid">任务名称:</label>
        <div class="txtv" id="taskname"></div>
        <div class="help-block"></div>
      </div>
      <div class="form-group field-testpaper-tid">
        <label class="control-label" for="testpaper-tid">团队名称:</label>
        <div class="txtv" id="teamname"></div>
        <div class="help-block"></div>
      </div>
      <div class="form-group field-testpaper-tid">
        <label class="control-label" for="testpaper-tid">上传作品:</label>
        <img src="" alt="" id="imgworkpath" class="workimg"/>
        <video src="" id="videoworkpath" class="workimg" controls autoplay></video>
        <div class="help-block"></div>
      </div>
      <div class="form-group field-testpaper-tid">
        <label class="control-label" for="testpaper-tid">作品打分:</label>
        <input type="number" id="workfen" class="form-control" value="" />
        <div class="help-block"></div>
      </div>
      <p>
         <button onclick="saveopt()" class="btn btn-primary" name="submit-button">保存</button>
       </p>
       </div>
    </div>
