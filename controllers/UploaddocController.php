<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;
use app\models\DocModel;
use app\models\MyWorker;


class UploaddocController extends Controller
{

    
    public function init()
    {
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;
    }
    

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
     * 上传作品
     *
     * @return string
     */
    public function actionIndex()
    {

        $aid=Yii::$app->request->get('aid'); 
        $tid=Yii::$app->request->get('tid'); 
        $uid= Yii::$app->request->get('uid');
        $maxnum=Yii::$app->request->get('maxnum');
        $uploadnum=Yii::$app->request->get('uploadnum');
        
        
        $model=new DocModel();

        $model->aid=(int)$aid;
        $model->tid=(int)$tid;
        $model->uid=(int)$uid;
        $model->maxnum=(int)$maxnum;
        $model->uploadnum=(int)$uploadnum;
        
        $params="aid:".$model->aid.",tid:".$model->tid.",uid:".$model->uid.
        ",maxnum:".$model->maxnum.",uploadnum:".$model->uploadnum;

        echo $params;
        $this->layout='@app/views/layouts/layoutpage.php';
        return $this->render('index', [ 
            'model' =>$model
        ]);
    }
    
     /**
     * 保存作品
     * 
     */
    public function actionSave()
    {       
        Yii::$app->response->format = Response::FORMAT_JSON;

        $aid= Yii::$app->request->post('aid');    
        $uid= Yii::$app->request->post('uid');  
        $tid= Yii::$app->request->post('tid');  
        $upload= UploadedFile::getInstanceByName('file');
       
        $params="活动id:".$aid."用户id:".$uid."任务id:".$tid."上传文件:".$upload->name;
        //return $params;
        // 处理文件
        $path = 'uploads/workdoc/' . uniqid() . '.' . $upload->extension;
        $upload->saveAs($path);
  
        $model = new MyWorker();
        $model->aid =(int) $aid;
        $model->uid =(int) $uid;        
        $model->tid =(int) $tid;
        $model->path =$path;
        $model->score = 0;
        $model->is_score = 0;
        $model->type = 3;
        $url = Yii::$app->request->hostInfo .'/'. $model->path;
        if($model->save()) {
            return ['status' => 'success', 'workid' => $model->id, 'url' => $url];
        }
        return $model->getErrors();
       
    }

     /**
     * 得到作品列表
     * 
     */
    public function actionWorklist()
    {    

        Yii::$app->response->format = Response::FORMAT_JSON;

        $aid= Yii::$app->request->post('aid');    
        $uid= Yii::$app->request->post('uid');  
        $tid= Yii::$app->request->post('tid');  

        $params="aid:".$aid.",uid:".$uid.",tid:".$tid;
        //return $params;
        $list=MyWorker::find()->where(['aid'=>$aid,'uid'=>$uid,'tid'=>$tid])->all();
        
        $uploadnum=$list->count();
        return ['status' => 'success', 'result' => $list,'uploadnum'=>$uploadnum];
       
    }

     /**
     * 删除作品
     * 
     */
    public function actionDelwork()
    {       
        try
        {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id= Yii::$app->request->post('id');    
            //通过id得到我的作品
            $model=MyWorker::find()->where(['id'=>$id])->one();
    
            //删除作品源文件
            $filepath=$model->path;
            if(is_file($filepath))
            {
              unlink($filepath);
            }
            $model->delete();  
            return ['status' => 'success','message'=>"删除成功"];
        }
        catch(Exception $e) {
            return ['status' => 'fail','message'=>$e];
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
