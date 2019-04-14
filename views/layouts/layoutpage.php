<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <!--引用资源-->
    <link href="/css/common.css" rel="stylesheet" />
    <script src="/jquery/jquery-2.1.1.min.js"></script>
    <script src="/bootstrap/bootstrap.js"></script>
    <link href="/bootstrap/bootstrap.min.css" rel="stylesheet" />
    <link href="/layer/skin/layer.css" rel="stylesheet" />
    <script src="/layer/layer.js"></script>
    <!--WebUploader上传插件-->
    <link href="/webuploader/webuploader.css" rel="stylesheet" />
    <script src="/webuploader/webuploader.js"></script>
    <link href="/css/upload.css" rel="stylesheet" />
</head>
<body>
<?php $this->beginBody() ?>    
  
    <!--右侧的内容-->
    <?= $content ?>

   </div>
</div>

<script type="text/javascript">
//页面的初始化函数
$(function(){
   //移除其他的选中的样式
   $(".nav .nav-sidebar li").each(function(){
      $(this).removeClass("active"); 
   })
})
</script>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>