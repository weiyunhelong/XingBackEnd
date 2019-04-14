<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Actity;
use app\models\MyBook;
use app\models\ActityTask;
use app\models\ActityInfo;
use app\models\ActbookSelfSet;
use app\models\ActbookSet;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

class ActivityController extends Controller
{

     /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }
    /**
     * 活动列表页面
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect('/manage/login');
            Yii::$app->end();
        }
        $this->layout='@app/views/layouts/newlayout.php';
        $provider = new ActiveDataProvider([
            'query' => Actity::find()->where([]),
            'sort' => ['defaultOrder' => ['createtime' => 'DESC']],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    /**
     * 删除活动
     * 
     */
    public function actionDelete()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;

        $id= Yii::$app->request->post('id');     
        $model=Actity::findOne($id);
        //判断活动是否有人报名,没有则删除，有则不删除
        $booknum=MyBook::find()->where(['aid'=> $id])->count();
        if($booknum==0)
        {   
            //删除封面
            $imageurl=$model->cover;   

            //删除封面源文件
            if(is_file($imageurl))
            {
               unlink($imageurl);
            }

            //删除模块
            $hdtasks=ActityTask::find()->where(['aid'=>$id])->all();  
            //循环遍历得到活动对应的任务
            foreach ( $hdtasks as $i => $task) { //删除活动任务
                $task->delete();               
            }
            //删除指引和详情
            $hdimgs=ActityInfo::find()->where(['aid'=>$id])->all();
            //循环遍历得到活动对应的任务
            foreach ( $hdimgs as $i => $hdimg) { //删除活动详情和指引
                $imgpath= $hdimg->img_path;
                if(is_file($imgpath))
                {
                    unlink($imgpath);
                }
                $hdimg->delete();               
            }
            //删除个人报名字段
            $actbsets=ActbookSet::find()->where(['aid'=>$id])->all();
            foreach ( $actbsets as $i => $item) { //删除活动任务
                $item->delete();               
            }
            //删除个人报名字段
            $actbssets=ActbookSelfSet::find()->where(['aid'=>$id])->all();
            foreach ( $actbssets as $i => $item) { //删除活动任务
                $item->delete();               
            }
            //删除活动
            $model->delete();
            return ['status' => 'success', 'message' => "删除成功"];
        }else{
            return ['status' => 'fail', 'message' => "删除失败"];
        }
       
    }
    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout='@app/views/layouts/blank.php';
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

}
