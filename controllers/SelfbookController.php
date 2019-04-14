<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Actity;
use app\models\MyBook;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

class SelfbookController extends Controller
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
     * 个人报名
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
        $actid = Yii::$app->request->get('id');
       
        $result=Mybook::find()->where([]);
        if ($actid!=0) {
            $result=$result->andWhere(['aid'=>$actid]);
        }

        $provider = new ActiveDataProvider([
            'query' => $result,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }
    //获取下拉列表
    public function actionActivity()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;
        
        $data = Actity::find()->where([
            'status' => 1,
        ])->orderBy('createtime desc')->all();
        return $data;
    }
    //导出Excel操作
    public function actionExport()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;

        $actid = Yii::$app->request->post('id');
       
        $result=Mybook::find();
        if ($actid!=0) {
            $result=$result->where(['aid'=>$actid]);
        }
        // 更强的导出功能: 自定义导出数据的格式
        $data=$result->all();
        $result=[["活动名称","主办方","用户姓名","手机号码","公司名称"]];
        foreach ($data as $k=>$v) {
            $actobj=Actity::find()->where(['id'=>$v->aid])->one();
            $name=explode(',', $v->name)[0];
            $phone=explode(',', $v->name)[1];
            $compnay= $v->danhang_txt;
            $result[$k+1][0]=$actobj==null?"":$actobj->name;
            $result[$k+1][1]=$actobj==null?"":$actobj->owner;
            $result[$k+1][2]=$name;
            $result[$k+1][3]=$phone;
            $result[$k+1][4]=$compnay;
        }
        return ['status' => 'success', 'data' => $result];
        exit();
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
