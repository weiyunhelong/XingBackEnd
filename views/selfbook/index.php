<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use app\models\Actity;
use app\models\MyBook;

/* @var $this yii\web\View */
$this->title = '个人报名';
?>
<style type="text/css">
   .optv{
    display: flex;
    justify-content: space-between;
    padding: 10px 0 20px;
   }
   .actselect{
    display: inline-block;
    padding: 6px 12px;
    line-height: 1.42857143;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    cursor: pointer;
    border: 1px solid #ccc;
    border-radius: 4px;
    width:200px;
   }
   .exportbtn{
    width: 100px;
    color: #fff;
    background-color: #337ab7;
    border-color: #2e6da4;
    display: inline-block;
    padding: 6px 12px;
    line-height: 1.42857143;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 4px;
   }
   .searchbtn{
    width: 100px;
    color: #fff;
    background-color: #337ab7;
    border-color: #2e6da4;
    display: inline-block;
    padding: 6px 12px;
    line-height: 1.42857143;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    background-image: none;
    border: 1px solid transparent;
    border-radius: 4px;
   }
</style>
<script src="/js-xlxs/xlsx.core.min.js"></script>
<script src="/js-xlxs/xlsx.full.min.js"></script>
<script type="text/javascript">

$(function(){
    $("#sevenmenu").addClass("active");
    //初始化下拉列表的数据
    InitSelect();

})

//初始化下拉列表的数据
function  InitSelect(){
  var params=window.location.search;
  var id=params.split('=')[1];
  id=parseInt(id);
  $.ajax({
      type:'get',
      url:"/selfbook/activity",
      data:{},
      success:function(res){
        console.log("获取活动的数据");
        console.log(res);
        var html="<option value='0'>全部</option>";
   
        for(var i=0;i<res.length;i++){
          if(res[i].id==id){
            html+="<option selected='selected' value='"+res[i].id+"'>"+res[i].name+"</option>";
         } else{
            html+="<option value='"+res[i].id+"'>"+res[i].name+"</option>";
          }
        }
        $("#actselect").html(html);
      }
  })
}

//导出的改变
function daochu(){

  $.ajax({
    type:"post",
    url:"/selfbook/export",    
    data:{
      id:$("#actselect").val()
    },
    success:function(res){
      
      var aoa =res.data;
      var ws = XLSX.utils.aoa_to_sheet(aoa);
      var html_string = XLSX.utils.sheet_to_html(ws, { id: "data-table", editable: true });
      document.getElementById("container").innerHTML = html_string; 
      var type='biff8';
      var fn="报名记录.xls";

	    var elt = document.getElementById('data-table');
	    var wb = XLSX.utils.table_to_book(elt, {sheet:"报名记录"});
      XLSX.writeFile(wb, fn || ('报名记录.' + (type || 'xlsx')));
     }
  })
}

//搜索查询
function searchopt(){
    window.location.href="/selfbook/index?id="+$("#actselect").val();
}
</script>
<div id="container" style="display:none;"></div>
<div class="col-sm-9 main">
  <h1 class="page-header">个人报名</h1>
  <div class="row placeholders">
  <div class="optv">
    <div>
    <select id="actselect" class="actselect"></select>
    <div onclick="searchopt()" class='searchbtn'>查询</div>
    </div>
    <div onclick="daochu()" class='exportbtn'>导出</div>
  </div>
      
  <?= GridView::widget([
            'dataProvider' => $provider,
            'columns' => [
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
                        if (empty($item)) {
                            return "";
                        } else {
                            return $item->name;
                        }
                    }
                ],
                [
                    'label'=>'用户姓名',
                    'attribute'=>'name',
                    'value' => function ($m) {
                      return explode(',', $m->name)[0];
                    }
                ],
                [
                  'label'=>'手机号',
                  'attribute'=>'name',
                  'value' => function ($m) {
                      return explode(',', $m->name)[1];
                  }
              ],
              [
                'label'=>'公司名称',
                'attribute'=>'danhang_txt',
                'value' => function ($m) {
                    return $m->danhang_txt;
                }
            ],
            [
              'label'=>'报名时间',
              'attribute'=>'createtime',
              'value'=>function($m){
                return date("Y-m-d H:m:s",$m->createtime);
              }
          ],
            ],
        ])
  ?>
  </div>
</div>
