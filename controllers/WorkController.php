<?php

namespace app\controllers;

use app\models\MyWorker;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

class WorkController extends Controller
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
     * 作品列表
     *
     * @return string
     */
    public function actionIndex()
    {
        if (Yii::$app->user->isGuest) {
            $this->redirect('/manage/login');
            Yii::$app->end();
        }
        $this->layout = '@app/views/layouts/newlayout.php';
        $provider = new ActiveDataProvider([
            'query' => MyWorker::find()->where([]),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    /**
     * 删除作品
     *
     */
    public function actionDelete()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;

        $id = Yii::$app->request->post('id');
        $model = MyWorker::findOne($id);
        //删除封面
        $imageurl = $model->path;

        if ($model->delete()) {
            //删除封面源文件
            if (is_file($imageurl)) {
                unlink($imageurl);
            }
            return ['status' => 'success', 'message' => "删除成功"];
        } else {
            return ['status' => 'fail', 'message' => "删除失败"];
        }

    }

    /**
     * 编辑作品
     *
     */
    public function actionEdit()
    {
        $this->layout = '@app/views/layouts/newlayout.php';

        $id = Yii::$app->request->get('id');
        $model = MyWorker::findOne($id);

        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * 获取作品的数据
     *
     */
    public function actionGetwork()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;

        $id = Yii::$app->request->get('id');
        $model = MyWorker::findOne($id);
        if (empty($model)) {
            return ['status' => 'fail', 'msg' => "作品不存在"];
        } else {
            //活动的名称
            $actity = \app\models\Actity::find()->where(['id' => $model->aid])->one();
            if (empty($actity)) {
                return ['status' => 'fail', 'msg' => "活动查不到"];
            }

            //任务的名称
            $acttask = \app\models\ActityTask::findOne($model->tid);
            if (empty($acttask)) {
                return ['status' => 'fail', 'msg' => "任务查不到"];
            }
            //团队的名称
            $team = \app\models\MyTeambook::find()->where(['uid' => $model->uid])->one();
            if (empty($team)) {
                return ['status' => 'fail', 'msg' => "团队查不到"];
            }
            $workpath="/" . $model->path;
            
            return ['status' => 'success', 'actname' => $actity->name,
                'taskname' => $acttask->name, 'teamname' => $team->teamname,
                'worktype' => $model->type, 'workpath' => $workpath,
                'score' => $model->score,
            ];
        }
    }

    /**
     * 保存打分
     *
     */
    public function actionSave()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;
        $id = Yii::$app->request->get('id');
        
        $model = MyWorker::findOne($id);

        if (empty($model)) {
            return false;
        } else {
            $model->score = Yii::$app->request->get('fen');
            $model->is_score = 1;
            $model->save();
            return true;
        }
    }
    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        $this->layout = '@app/views/layouts/blank.php';
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
