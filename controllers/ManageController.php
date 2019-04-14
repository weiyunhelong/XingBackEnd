<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\WechatUser;
use yii\data\ActiveDataProvider;

class ManageController extends Controller
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
     * Displays homepage.
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
            'query' => WechatUser::find()->where([]),
            'sort' => ['defaultOrder' => ['created_at' => 'DESC']],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }
    //设置成员的身份（管理员、普通用户）
    public function actionEdit()
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect('/manage/login');
            Yii::$app->end();
        }
        $id= Yii::$app->request->get('id');
        $id=(int)$id;
        $type= Yii::$app->request->get('type');
        $type=(int)$type;
        //通过id得到用户
        $model=WechatUser::find()->where(['uid'=>$id])->one();
        $model->isadmin=$type;
        $model->save();

        $this->layout='@app/views/layouts/newlayout.php';
        $provider = new ActiveDataProvider([
            'query' => WechatUser::find()->where([]),
            'sort' => ['defaultOrder' => ['created_at' => 'DESC']],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
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
