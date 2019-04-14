<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
    body {
    padding-top: 40px;
    padding-bottom: 40px;
    background-color: #eee;
    }
    .form-signin {
    max-width: 355px;
    padding: 15px;
    margin: 10% auto;
    }
    
   .title{
     width:100%;
     height:30px;
     font-size:25px;
     font-weight:600;
     line-height:30px;
     text-align:center;
     margin-bottom:30px;
    }
    .form-signin .form-signin-heading,
    .form-signin .checkbox {
    margin-bottom: 10px;
    }
    .form-signin .checkbox {
    font-weight: normal;
    margin-top:20px;
    }
    .form-signin .form-control {
    position: relative;
    height: auto;
    padding: 10px;
    font-size: 16px;
    width: 275px;
    border-radius: 10px;
    border: 0;
    outline: 0;
    }
    .form-signin .form-control:focus {
    z-index: 2;
    }
    .form-signin input[type="email"] {
    margin-bottom: -1px;
    border-bottom-right-radius: 0;
    border-bottom-left-radius: 0;
    }
    .form-signin input[type="password"] {
    margin-bottom: 10px;
    }
    .loginbtn{
        width: 200px;
    background-color: #337AB7;
    height: 40px;
    border: 0;
    outline: 0;
    margin-left: 77.5px;
    border-radius: 10px;
    color: #fff;
    margin-top: 30px;        
    }
</style>
<div class="container">
    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-signin'],
        // 'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <div class="title">欢迎使用后台管理系统</div>
    <div>
      <label for="inputEmail" class="sr-only">账户:</label>
      <input type="text" id="inputEmail" class="form-control" name="LoginForm[username]" placeholder="账户" required autofocus>
    </div>
   <div style="margin-top:20px;">
     <label for="inputPassword" class="sr-only">密码:</label>
     <input type="password" id="inputPassword" class="form-control" placeholder="密码" name="LoginForm[password]" required>
   </div>
    <div class="checkbox">
        <label>
        <input type="checkbox" value="remember-me" name="LoginForm[rememberMe]"> 记住我
        </label>
    </div>
    <button class="loginbtn" type="submit">登录</button>

    <?php ActiveForm::end(); ?>
</div> 
