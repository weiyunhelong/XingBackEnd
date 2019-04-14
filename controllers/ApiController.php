<?php

namespace app\controllers;

use app\models\ActbookSelfSet;
use app\models\ActbookSet;
use app\models\Actity;
use app\models\ActityInfo;
use app\models\ActityTask;
use app\models\Channel;
use app\models\ChannelBook;
use app\models\ChannelRecord;
use app\models\ChannelSee;
use app\models\MyActity;
use app\models\MyBook;
use app\models\MyTeambook;
use app\models\MyWorker;
use app\models\Spread;
use app\models\VideoPlay;
use app\models\WechatUser;
use Yii;
use yii\httpclient\Client;
use yii\web\Response;
use yii\web\UploadedFile;

class ApiController extends \yii\web\Controller
{

    public function init()
    {
        // 返回数据格式为 json
        Yii::$app->response->format = Response::FORMAT_JSON;
        // 关闭 csrf 验证
        $this->enableCsrfValidation = false;
    }

    // 1小程序授权
    public function actionLogin()
    {
        $code = Yii::$app->request->post('code', '');
        $sex = Yii::$app->request->post('sex', '');
        $avatar = Yii::$app->request->post('touxiang', '');
        $wxname = Yii::$app->request->post('wxName', '');
        $isadmin = Yii::$app->request->post('isadmin', 0);

        if (!$code) {
            return ['status' => 'fail', 'message' => 'code 不能为空'];
        }

        if (!$sex) {
            return ['status' => 'fail', 'message' => 'sex 不能为空'];
        }

        if (!$avatar) {
            return ['status' => 'fail', 'message' => 'touxiang 不能为空'];
        }

        if (!$wxname) {
            return ['status' => 'fail', 'message' => 'wxname 不能为空'];
        }
        //根据code查询，用户是否存在
        $data = $this->wechatAuth($code);

        if (isset($data['openid']) && isset($data['session_key'])) 
        {
            $openid = $data['openid'];
            $model = WechatUser::findOne(['openid' => $openid]);
            if (empty($model))
            {
                $model = new WechatUser();
                $model->created_at = time();
                $model->isadmin = $isadmin;
            }
         
            $model->sex = $sex;
            $model->avatar = $avatar;
            $model->wxname = $wxname;
            $model->openid = $openid;
            $model->updated_at = time();
         
            if (!$model->validate()) {
                   $errors = current($model->getErrors());
                   return ['status' => 'fail', 'message' => $errors[0]];
            }else{
              $model->save();
              $uid = $model->uid;
              return ['status' => 'success', 'openID' => $openid, 'uid' => $uid, 'isadmin' => $model->isadmin];
            }
        } else {
            $errmsg = isset($data['errmsg']) ? $data['errmsg'] : '授权出错';
            return ['status' => 'fail', 'message' => $errmsg];
        }
    }

    // 2获取活动
    public function actionGetactity()
    {
        $id = Yii::$app->request->post('id');
        $uid = Yii::$app->request->post('uid');

        if (is_null($id)) {
            return ['status' => 'fail', 'message' => 'id不能为空'];
        }

        if (!$uid) {
            return ['status' => 'fail', 'message' => 'uid不能为空'];
        }

        if ($id == 0) {
            $models = Actity::find()->where([
                'status' => 1,
            ])->orderBy('createtime desc')->all();
        } else {
            $models = Actity::find()->where([
                'status' => 1,
                'id' => $id,
            ])->orderBy('createtime desc')->all();
        }
        $data = [];
        foreach ($models as $k => $model) {
            // 活动的基本信息
            $data[$k]['actid'] = $model->id; //活动id
            $data[$k]['fengmian'] = Yii::$app->request->hostInfo . '/' . $model->cover; //活动封面
            $data[$k]['title'] = $model->name; //活动标题
            $data[$k]['owner'] = $model->owner; //活动所有者
            $data[$k]['status'] = $model->status; //活动状态
            $data[$k]['startdate'] = date('Y-m-d', $model->start_date); //活动开始日期
            $data[$k]['enddate'] = date('Y-m-d', $model->end_date); //活动结束日期
            $data[$k]['dhtype'] = $model->type; //活动线上线下类型
            $data[$k]['booktype'] = $model->booktype; //活动报名类型
            $data[$k]['gradetype'] = $model->gradetype; //活动打分模式
            $data[$k]['isrank'] = $model->isrank == 1 ? true : false; //活动显示排行
            // 活动任务
            $data[$k]['hdtask'] = [];
            $hdtasks = ActityTask::find()->where(['aid' => $model->id])->all();
            //循环遍历得到活动对应的任务
            foreach ($hdtasks as $i => $task) {
                $data[$k]['hdtask'][$i]['id'] = $task->id;
                $data[$k]['hdtask'][$i]['type'] = $task->type;
                $data[$k]['hdtask'][$i]['name'] = $task->name;
                $data[$k]['hdtask'][$i]['maxupload'] = $task->video_num;

                //计算用户已经上传的文件数
                $worknum = MyWorker::find()->where(['tid' => $task->id, 'uid' => $uid])->count();
                $data[$k]['hdtask'][$i]['uploadnum'] = $worknum;
            }

        }
        return $data;
    }

    // 2-1 清除无效的数据
    public function actionDelact()
    {

        $models = Actity::find()->where([
            'status' => 0,
        ])->all();
        //循环删除
        foreach ($models as $k => $model) {
            //删除模块
            $hdtasks = ActityTask::find()->where(['aid' => $model->id])->all();
            //循环遍历得到活动对应的任务
            foreach ($hdtasks as $i => $task) { //删除活动任务
                $task->delete();
            }
            //删除指引和详情
            $hdimgs = ActityInfo::find()->where(['aid' => $model->id])->all();
            //循环遍历得到活动对应的任务
            foreach ($hdimgs as $i => $hdimg) { //删除活动详情和指引
                $imgpath = $hdimg->img_path;
                if (is_file($imgpath)) {
                    unlink($imgpath);
                }
                $hdimg->delete();
            }
            //删除个人报名字段
            $actbsets = ActbookSet::find()->where(['aid' => $model->id])->all();
            foreach ($actbsets as $i => $item) { //删除活动任务
                $item->delete();
            }
            //删除个人报名字段
            $actbssets = ActbookSelfSet::find()->where(['aid' => $model->id])->all();
            foreach ($actbssets as $i => $item) { //删除活动任务
                $item->delete();
            }
            //删除活动
            $model->delete();
        }
        return ['status' => 'success', 'message' => '清除数据成功'];
    }

    // 2-2 预览获取活动
    public function actionGetpreviewact()
    {

        $id = Yii::$app->request->post('id');
        $uid = Yii::$app->request->post('uid');

        $models = Actity::find()->where([
            'id' => $id,
        ])->all();
        $data = [];
        foreach ($models as $k => $model) {
            // 活动的基本信息
            $data[$k]['actid'] = $model->id; //活动id
            $data[$k]['fengmian'] = Yii::$app->request->hostInfo . '/' . $model->cover; //活动封面
            $data[$k]['title'] = $model->name; //活动标题
            $data[$k]['owner'] = $model->owner; //活动所有者
            $data[$k]['status'] = $model->status; //活动状态
            $data[$k]['startdate'] = date('Y-m-d', $model->start_date); //活动开始日期
            $data[$k]['enddate'] = date('Y-m-d', $model->end_date); //活动结束日期
            $data[$k]['dhtype'] = $model->type; //活动线上线下类型
            $data[$k]['booktype'] = $model->booktype; //活动报名类型
            $data[$k]['isrank'] = $model->isrank == 1 ? true : false; //活动显示排行
            // 活动任务
            $data[$k]['hdtask'] = [];
            $hdtasks = ActityTask::find()->where(['aid' => $model->id])->all();
            //循环遍历得到活动对应的任务
            foreach ($hdtasks as $i => $task) {
                $data[$k]['hdtask'][$i]['id'] = $task->id;
                $data[$k]['hdtask'][$i]['type'] = $task->type;
                $data[$k]['hdtask'][$i]['name'] = $task->name;
                $data[$k]['hdtask'][$i]['maxupload'] = $task->video_num;

                //计算用户已经上传的文件数
                $worknum = MyWorker::find()->where(['tid' => $task->id, 'uid' => $uid])->count();
                $data[$k]['hdtask'][$i]['uploadnum'] = $worknum;
            }

        }
        return $data;
    }

    // 3获取活动详情
    public function actionGethdinfo()
    {
        $actid = Yii::$app->request->post('actid');
        $type = Yii::$app->request->post('type');
        if (!$actid) {
            return ['status' => 'fail', 'message' => 'actid不能为空'];
        }

        if (!$type) {
            return ['status' => 'fail', 'message' => 'type不能为空'];
        }

        $models = ActityInfo::find()->where([
            'aid' => $actid,
            'type' => $type,
        ])->all();
        $data = [];
        foreach ($models as $k => $model) {
            $data[$k]['id'] = $model->id;
            $data[$k]['imgpath'] = Yii::$app->request->hostInfo . '/' . $model->img_path;
        }
        return $data;
    }

    // 4报名信息
    public function actionGetbook()
    {
        $actid = Yii::$app->request->get('actid');

        if (!$actid) {
            return ['status' => 'fail', 'message' => 'actid不能为空'];
        }
        $model = ActbookSet::findOne(['aid' => $actid]);
        $self = ActbookSelfSet::findOne(['aid' => $actid]);
        if (!$model || !$self) {
            return ['status' => 'fail', 'message' => '报名信息不存在'];
        }
        $data['name'] = $model->name;
        $data['isdanhang'] = $self->danhang ? true : false;
        $data['isdanxuan'] = $self->danxuan_txt ? true : false;
        $data['isduoxuan'] = $self->duoxuan_txt ? true : false;
        $data['danhangtxt'] = $self->danhang;
        $data['danxuantxt'] = $self->danxuan_txt;
        $data['danxuanopt'] = $self->danxuan_opt;
        $data['duanxuantxt'] = $self->duoxuan_txt;
        $data['duoxuanopt'] = $self->duoxuan_opt;
        return $data;
        // name:['报名字段A'，'报名字段B'，'报名字段C'],isdanhang:true,danghangtxt:'单行文本的内容',isdanxuan:true,danxuantxt:'单选的标题',danxuanopt:['单选A','单选B','单选C','单选D'],isduoxuan:true,duoxuantxt:'多选的标题',duoxuanopt:['多选A','多选B','多选C','多选D']
    }

    // 5用户报名
    public function actionBook()
    {
        $uid = Yii::$app->request->post('uid');
        $actid = Yii::$app->request->post('actid');
        $name = Yii::$app->request->post('name');
        $danhangtxt = Yii::$app->request->post('danhangtxt');
        $danxuan = Yii::$app->request->post('danxuan');
        $duoxuan = Yii::$app->request->post('duoxuan');

        if (!$actid) {
            return ['status' => 'fail', 'message' => 'actid不能为空'];
        }

        $model = new MyBook();
        $model->aid = $actid;
        $model->uid = $uid;
        $model->danhang_txt = $danhangtxt;
        $model->danxuan = $danxuan;
        $model->duoxuan = $duoxuan;
        $model->name = $name;
        $model->createtime = time();
        if ($model->save()) {
            $myActity = new MyActity();
            $myActity->aid = $actid;
            $myActity->uid = $uid;
            $myActity->created_at = time();
            if ($myActity->save()) {
                return ['status' => 'success'];
            }
            return $myActity->getErrors();
        }
        return $model->getErrors();
    }

    // 6我的活动列表
    public function actionMyactlist()
    {
        $uid = Yii::$app->request->post('uid');
        $isadmin = Yii::$app->request->post('isadmin');
        $type = Yii::$app->request->post('type');

        if (!$uid || is_null($isadmin) || is_null($type)) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        if ($type == 0) {
            $where = ['my_actity.uid' => $uid];
        }
        if ($type == 1) {
            $where = ['actity.status' => 1, 'my_actity.uid' => $uid];
        }
        if ($type == 2) {
            $where = ['actity.status' => 2, 'my_actity.uid' => $uid];
        }
        $models = MyActity::find()->joinWith('actity')->where($where)->all();
        $data = [];
        foreach ($models as $k => $model) {
            $data[$k]['actid'] = $model->actity->id;
            $data[$k]['fengmian'] = Yii::$app->request->hostInfo . '/' . $model->actity->cover;
            $data[$k]['title'] = $model->actity->name;
            $data[$k]['owner'] = $model->actity->owner;
            $data[$k]['status'] = $model->actity->status;
            $data[$k]['startdate'] = date('Y-m-d', $model->actity->start_date);
            $data[$k]['enddate'] = date('Y-m-d', $model->actity->end_date);
            $data[$k]['dhtype'] = $model->actity->type;
        }
        return $data;
    }

    // 7上传视频作品
    public function actionUploadvideo()
    {
        $actid = Yii::$app->request->post('actid'); //活动id
        $uid = Yii::$app->request->post('uid'); //用户id
        $tid = Yii::$app->request->post('tid'); //任务id
        $upload = UploadedFile::getInstanceByName('files');
        if (!$uid || !$actid || !$upload || !$tid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        // 处理文件
        $teamname=MyTeamBook::find()->where(['uid'=>$uid])->one()->teamname;
        $taskname=ActityTask::find()->where(['id'=>$tid])->one()->name;
        
        $dir_path='uploads/workvideo/' . $teamname .'/' . $taskname .'/';
        
        //$dir_path='uploads/workvideo/' . $uid .'/' . $tid .'/';
        if (!file_exists($dir_path)) {
            $this->createDir($dir_path);
        }
        $path =  $dir_path . uniqid() . '.' . $upload->extension;
        $upload->saveAs($path);

        $model = new MyWorker();
        $model->aid = $actid;
        $model->uid = $uid;
        $model->path = $path;
        $model->type = 1;
        $model->tid = $tid;
        $url = Yii::$app->request->hostInfo . '/' . $model->path;
        if ($model->save()) {
            return ['status' => 'success', 'workid' => $model->id, 'url' => $url];
        }
        return $model->getErrors();
    }

    // 7上传图片作品
    public function actionUploadimg()
    {
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');
        $tid = Yii::$app->request->post('tid'); //任务id
        $upload = UploadedFile::getInstanceByName('files');
        if (!$uid || !$actid || !$upload || !$tid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        // 处理文件
        $teamname=MyTeamBook::find()->where(['uid'=>$uid])->one()->teamname;
        $taskname=ActityTask::find()->where(['id'=>$tid])->one()->name;
        
        $dir_path='uploads/workimg/' . $teamname .'/' . $taskname .'/';
        //$dir_path='uploads/workvideo/' . $uid .'/' . $tid .'/';
        if (!file_exists($dir_path)) {
            $this->createDir($dir_path);
        }
        $path =  $dir_path . uniqid() . '.' . $upload->extension;
        $upload->saveAs($path);

        $model = new MyWorker();
        $model->aid = $actid;
        $model->uid = $uid;
        $model->path = $path;
        $model->type = 2;
        $model->tid = $tid;
        $url = Yii::$app->request->hostInfo . '/' . $model->path;
        if ($model->save()) {
            return ['status' => 'success', 'workid' => $model->id, 'url' => $url];
        }
        return $model->getErrors();
    }

    // 7-1上传文档图片作品
    public function actionUploaddoc()
    {
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');
        $tid = Yii::$app->request->post('tid'); //任务id
        $upload = UploadedFile::getInstanceByName('files');
        if (!$uid || !$actid || !$upload || !$tid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        // 处理文件
        $teamname=MyTeamBook::find()->where(['uid'=>$uid])->one()->teamname;
        $taskname=ActityTask::find()->where(['id'=>$tid])->one()->name;
        
        $dir_path='uploads/workdoc/' . $teamname .'/' . $taskname .'/';
        //$dir_path='uploads/workdoc/' . $uid .'/' . $tid .'/';
        if (!file_exists($dir_path)) {
            $this->createDir($dir_path);
        }
        $path =  $dir_path . uniqid() . '.' . $upload->extension;
        $upload->saveAs($path);

        $model = new MyWorker();
        $model->aid = $actid;
        $model->uid = $uid;
        $model->path = $path;
        $model->type = 3;
        $model->tid = $tid;
        $url = Yii::$app->request->hostInfo . '/' . $model->path;
        if ($model->save()) {
            return ['status' => 'success', 'workid' => $model->id, 'url' => $url];
        }
        return $model->getErrors();
    }

    // 8作品列表
    public function actionGetworklist()
    {
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');
        $type = Yii::$app->request->post('type');
        $kind = Yii::$app->request->post('kind');
        $taskid = Yii::$app->request->post('taskid');
        if (!$actid || is_null($type) || !$kind || !$uid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        if ($type == 0) {
            $where = ['aid' => $actid, 'type' => $kind, 'uid' => $uid, 'tid' => $taskid];
        } elseif ($type == 1) {
            $where = ['aid' => $actid, 'is_score' => 0, 'uid' => $uid, 'type' => $kind, 'tid' => $taskid];
        } else {
            $where = ['aid' => $actid, 'is_score' => 1, 'uid' => $uid, 'type' => $kind, 'tid' => $taskid];
        }
        $models = MyWorker::find()->where($where)->orderBy('score DESC')->all();
        $data = [];
        foreach ($models as $k => $model) {
            $data[$k]['id'] = $model->id;
            $data[$k]['workpath'] = Yii::$app->request->hostInfo . '/' . $model->path;
            $data[$k]['kind'] = $model->type;
            //判断活动是单人形式还是团队形式
            $hdbtype = Actity::find()->where(['id' => $actid])->one()->booktype;
            if ($hdbtype == 1) {
                $data[$k]['wxname'] = $model->user->wxname;
            } else {
                $teamname = MyTeambook::find()->where(['uid' => $model->user->uid])->one()->teamname;
                $data[$k]['wxname'] = $teamname;
            }
            $data[$k]['no'] = $model->score;
            $data[$k]['status'] = $model->is_score;
        }
        return $data;
    }

    //8-1 所有的作品列表
    public function actionGetallwork()
    {
        $actid = Yii::$app->request->post('actid');
        $type = Yii::$app->request->post('type');
        $kind = Yii::$app->request->post('kind');
        $taskid = Yii::$app->request->post('taskid');
        if (!$actid || is_null($type) || !$kind || !$taskid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        //得到活动的类型(个人/团队)
        $actm = Actity::find()->where(['id' => $actid])->one();
        $connection = Yii::$app->getDb();
        if ($actm->booktype == 1) {
            //SQL语句查询
            $command = $connection->createCommand("
              SELECT
                 w.wxname,
                 w.uid as uid,
                 SUM( m.score ) AS sumfen
              FROM
                 `my_worker` AS m
                 LEFT JOIN wechat_user AS w ON m.uid = w.uid
                 LEFT JOIN my_teambook AS t ON t.uid = w.uid
              WHERE
                 m.aid = $actid and m.type=$kind and m.tid=$taskid
              GROUP BY
                 m.uid
              ORDER BY
                sumfen DESC
            "
            );
        } else {
            //SQL语句查询
            $command = $connection->createCommand("
              SELECT
                 t.teamname as wxname,
                 w.uid as uid,
                 SUM( m.score ) AS sumfen
              FROM
                 `my_worker` AS m
                 LEFT JOIN wechat_user AS w ON m.uid = w.uid
                 LEFT JOIN my_teambook AS t ON t.uid = w.uid
              WHERE
                 m.aid = $actid and m.type=$kind and m.tid=$taskid
              GROUP BY
                 m.uid
              ORDER BY
                sumfen DESC
            "
            );
        }
        $result = $command->queryAll();
        //全部，未打分，已打分
        if ($type == 0) {
            $data = $result;
        } else if ($type == 1) {
            $index = 0;
            foreach ($result as $key => $item) {
                if ((int) $item["sumfen"] == 0) {
                    $data[$index]["wxname"] = $item["wxname"];
                    $data[$index]["uid"] = $item["uid"];
                    $data[$index]["sumfen"] = $item["sumfen"];
                    $index++;
                }
            }
        } else {
            foreach ($result as $key => $item) {
                if ((int) $item["sumfen"] > 0) {
                    $data[$index]["wxname"] = $item["wxname"];
                    $data[$index]["uid"] = $item["uid"];
                    $data[$index]["sumfen"] = $item["sumfen"];
                    $index++;
                }
            }
        }
        return $data;
    }

    //8-20 得到活动下用户和总分
    public function actionGetactworksumfen(){
        $actid = Yii::$app->request->post('actid');  
        $tid = Yii::$app->request->post('tid');  
        $type=  Yii::$app->request->post('type');   
        if (!$actid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        //得到活动的类型(个人/团队)
        $actm = Actity::find()->where(['id' => $actid])->one();
        $connection = Yii::$app->getDb();
        if ($actm->booktype == 1) {
            $command = $connection->createCommand("
            SELECT
              w.wxname,
              m.id as id,
              m.uid as uid,
              SUM( m.score ) AS sumfen
            FROM
             `my_worker` AS m
              LEFT JOIN wechat_user AS w ON m.uid = w.uid
            WHERE
              m.aid = $actid and m.tid= $tid
            GROUP BY
              m.uid
            ORDER BY
              sumfen DESC
              LIMIT 20
            "
            );
        } else {
            $command = $connection->createCommand("
            SELECT
              t.teamname as wxname,
              m.id as id,
              m.uid as uid,
              SUM( m.score ) AS sumfen
            FROM
             `my_worker` AS m
              LEFT JOIN wechat_user AS w ON m.uid = w.uid
              LEFT JOIN my_teambook AS t ON t.uid = w.uid
            WHERE
              m.aid = $actid and m.tid= $tid
            GROUP BY
              m.uid
            ORDER BY
              sumfen DESC
              LIMIT 20
            "
            );
        }
        $result = $command->queryAll();
        //return $result;
        //全部，未打分，已打分
        if ($type == 0) {
            $index = 0;
            foreach ($result as $key => $item) {                
                $data[$index]["wxname"] = $item["wxname"];
                $data[$index]["uid"] = $item["uid"];
                $data[$index]["sumfen"] = $item["sumfen"];
                $index++;
            }
        } else if ($type == 1) {
            $index = 0;
            foreach ($result as $key => $item) {
                $sumfen=(int) $item["sumfen"];
                if ( $sumfen  == 0) {
                    $data[$index]["wxname"] = $item["wxname"];
                    $data[$index]["uid"] = $item["uid"];
                    $data[$index]["sumfen"] = $item["sumfen"];
                    $index++;
                }
            }
        } else {
            foreach ($result as $key => $item) {
                $sumfen=(int) $item["sumfen"];
                if ( $sumfen > 0) {
                    $data[$index]["wxname"] = $item["wxname"];
                    $data[$index]["uid"] = $item["uid"];
                    $data[$index]["sumfen"] = $item["sumfen"];
                    $index++;
                }
            }
        }
        return $data;
    }

    //8-21 用户对应活动所有的作品列表
    public function actionGetalltaskwork()
    {
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');
        $tid = Yii::$app->request->post('tid');
        if (!$actid&&!$uid&&!$tid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        //得到活动的类型(个人/团队)
        $actm = Actity::find()->where(['id' => $actid])->one();

        //查询获取用户对应的活动列表
        $worklist=MyWorker::find()->where(['aid'=>$actid,'uid'=>$uid,'tid'=>$tid])->all();          
        $index=0;
        $data=[];
        foreach ($worklist as $key => $item) {  
            //获取任务名称
            $taskname=""; 
            $task=ActityTask::find()->where(['id'=>$item->tid])->one();
            if(!empty($task)){
                $taskname= $task->name;
            }                   
            $data[$index]["taskname"] = $taskname;
            $data[$index]["path"] ="https://qhgf.bibiu.vip/" . $item->path;
            $data[$index]["type"] = $item->type;
            $data[$index]["id"] = $item->id;
            $index++;
        }
        return $data;
    }

    // 9作品详情
    public function actionGetworkinfo()
    {
        $actid = Yii::$app->request->post('actid');
        $kind = Yii::$app->request->post('kind');
        $taskid = Yii::$app->request->post('taskid');
        $uid = Yii::$app->request->post('uid');
        if (!$actid || !$kind || !$taskid || !$uid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $mwlist = MyWorker::find()->where(['aid' => $actid, 'type' => $kind, 'tid' => $taskid, 'uid' => $uid])->all();

        //得到活动的类型(个人/团队)
        $actm = Actity::find()->where(['id' => $actid])->one();
        foreach ($mwlist as $key => $model) {
            //判断活动是单人形式还是团队形式
            $hdbtype = Actity::find()->where(['id' => $actid])->one()->booktype;
            if ($hdbtype == 1) {
                $data[$key]['wxname'] = $model->user->wxname;
            } else {
                $teamname = MyTeambook::find()->where(['uid' => $model->user->uid])->one()->teamname;
                $data[$key]['wxname'] = $teamname;
            }
            $data[$key]['workpath'] = Yii::$app->request->hostInfo . '/' . $model->path;
            $data[$key]['fenshu'] = $model->score;
            $data[$key]['is_score'] = $model->is_score;
            $data[$key]['id'] = $model->id;
        }
        return ['status' => 'success', 'message' => '数据获取成功', 'result' => $data];
    }

    // 10作品打分
    public function actionUpdatedafen()
    {
        $id = Yii::$app->request->post('id');
        $fen = Yii::$app->request->post('fen');
        if (!$id || is_null($fen)) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = MyWorker::findOne($id);
        if (!$model) {
            return ['status' => 'fail', 'message' => '作品不存在'];
        }
        $model->score = $fen;
        $model->is_score = 1;
        $model->save();
        return ['status' => 'success'];
    }

    // 10-1作品打分
    public function actionUpdatefen()
    {
        $uid = Yii::$app->request->post('uid');        
        $actid = Yii::$app->request->post('actid');
        $tid = Yii::$app->request->post('tid');
        $id = Yii::$app->request->post('id');
        $fen = Yii::$app->request->post('fen');
        if ($id==0 || is_null($fen)||$actid==0||$uid==0||$tid==0) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        //更新所有的该活动下用户的活动的作品分数为0        
        //为最后一个作品打分，使得此值为总分
        $works=MyWorker::find()->where(["aid"=>$actid,"uid"=>$uid,"tid"=>$tid])->all();
        foreach ($works as $key => $work) {
            $work->score=0;
            $work->save();
        }
        //为最后一个作品打分，使得此值为总分
        $model = MyWorker::findOne($id);
        if (!$model) {
            return ['status' => 'fail', 'message' => '作品不存在'];
        }
        $model->score = $fen;
        $model->is_score = 1;
        
        $model->save();
        return ['status' => 'success'];
    }

    // 11排行榜
    public function actionGetranklist()
    {
        $actid = Yii::$app->request->post('actid');
        if (!$actid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }

        //得到活动的类型(个人/团队)
        $actm = Actity::find()->where(['id' => $actid])->one();
        $connection = Yii::$app->getDb();
        if ($actm->booktype == 1) {
            $command = $connection->createCommand("
            SELECT
              w.wxname,
              m.id as id,
              SUM( m.score ) AS sumfen
            FROM
             `my_worker` AS m
              LEFT JOIN wechat_user AS w ON m.uid = w.uid
            WHERE
              m.aid = $actid
            GROUP BY
              m.uid
            ORDER BY
              sumfen DESC
              LIMIT 20
            "
            );
        } else {
            $command = $connection->createCommand("
            SELECT
              t.teamname as wxname,
              m.id as id,
              SUM( m.score ) AS sumfen
            FROM
             `my_worker` AS m
              LEFT JOIN wechat_user AS w ON m.uid = w.uid
              LEFT JOIN my_teambook AS t ON t.uid = w.uid
            WHERE
              m.aid = $actid
            GROUP BY
              m.uid
            ORDER BY
              sumfen DESC
              LIMIT 20
            "
            );
        }
        $result = $command->queryAll();
        return $result;
    }

    // 12活动更新
    public function actionUpdateactity()
    {
        $actid = Yii::$app->request->post('actid');
        $upload = UploadedFile::getInstanceByName('fengmian');
        $title = Yii::$app->request->post('title');
        $startdate = Yii::$app->request->post('startdate');
        $enddate = Yii::$app->request->post('enddate');
        $type = Yii::$app->request->post('type');
        $owner = Yii::$app->request->post('owner');
        $uid = Yii::$app->request->post('uid');
        $booktype = Yii::$app->request->post('booktype');
        $gradetype = Yii::$app->request->post('gradetype');
        $status = Yii::$app->request->post('status', 0);

        if (is_null($actid)) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = Actity::findOne($actid);
        if (!$model) {
            $model = new Actity();
        }
        // 处理文件
        if ($upload) {
            $model->file = $upload;
            $fileName = 'uploads/hdimg/' . uniqid() . '.' . $model->file->extension;
            $model->file->saveAs($fileName);
            $model->cover = $fileName;
        }

        $model->status = $status;
        $model->name = $title;
        $model->booktype = $booktype;
        $model->owner = $owner;
        $model->start_date = strtotime($startdate);
        $model->end_date = strtotime($enddate);
        $model->type = $type;
        $model->gradetype = $gradetype;
        $model->createtime = time();
        if ($model->save()) {
            $myActity = MyActity::findOne(['uid' => $uid, 'aid' => $actid]);
            if (!$myActity) {
                $myActity = new MyActity();
            }
            $myActity->aid = $actid;
            $myActity->uid = $uid;
            $myActity->created_at = time();
            $myActity->save();
            return $model->id;
        }
        return $model->getErrors();

    }

    // 12-1 活动编辑更新
    public function actionEditactity()
    {
        $actid = Yii::$app->request->post('actid');
        $upload = UploadedFile::getInstanceByName('fengmian');
        $title = Yii::$app->request->post('title');
        $startdate = Yii::$app->request->post('startdate');
        $enddate = Yii::$app->request->post('enddate');
        $owner = Yii::$app->request->post('owner');
      
        
        if (is_null($actid)) {
            return ['status' => 'fail', 'message' => '活动参数错误'];
        }
        $model = Actity::findOne($actid);
        if (!$model) {
            $model = new Actity();
        }
        // 处理文件
        if ($upload) {
            $model->file = $upload;
            $fileName = 'uploads/hdimg/' . uniqid() . '.' . $model->file->extension;
            $model->file->saveAs($fileName);
            $model->cover = $fileName;
        }

        $model->name = $title;
        $model->owner = $owner;
        $model->start_date = strtotime($startdate);
        $model->end_date = strtotime($enddate);
        $model->createtime = time();
        if ($model->save()) {
            $myActity = MyActity::findOne(['uid' => $uid, 'aid' => $actid]);
            if (!$myActity) {
                $myActity = new MyActity();
            }
            $myActity->aid = $actid;
            $myActity->uid = $uid;
            $myActity->created_at = time();
            $myActity->save();
            return $model->id;
        }
        return $model->getErrors();
    }

    // 13更新活动模块
    public function actionUpdatemokuai()
    {
        $aid = Yii::$app->request->post('actid');
        $name = Yii::$app->request->post('title');
        $video_num = Yii::$app->request->post('number');
        $ismarking = Yii::$app->request->post('ispingfen');
        $is_order = Yii::$app->request->post('ispaixu');
        $is_delete = Yii::$app->request->post('isdelete');
        $type = Yii::$app->request->post('type');

        if (!$aid || !$name || is_null($video_num) || is_null($is_order) || is_null($is_delete)) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = new ActityTask();
        $model->aid = $aid;
        $model->name = $name;
        $model->type = $type;
        $model->video_num = $video_num;
        $model->ismarking = $ismarking;
        $model->is_order = $is_order;
        $model->is_delete = $is_delete;
        if ($model->save()) {
            return ['status' => 'success'];
        }
        return $model->getErrors();

    }

    // 13-1得到所有的活动模块
    public function actionGetmokuai()
    {
        $aid = Yii::$app->request->post('actid');

        if (!$aid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        }
        $result = ActityTask::find()->where(['aid' => $aid])->all();
        return ['status' => 'success', 'message' => '获取活动模块成功', 'result' => $result];
    }

    // 14更新活动详情和指引
    public function actionUpdateactinfo()
    {
        $actid = Yii::$app->request->post('actid');
        $files = UploadedFile::getInstancesByName('files');
        $type = Yii::$app->request->post('type');
        if (is_null($actid) || !$files || !$type) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        foreach ($files as $k => $file) {
            $path = 'uploads/hdinfoimg/' . uniqid() . '.' . $file->extension;
            $file->saveAs($path);

            $model = new ActityInfo();
            $model->aid = $actid;
            $model->img_path = $path;
            $model->type = $type;
            $model->save();
        }
        return ['status' => 'success'];
    }

    // 15更新活动报名
    public function actionUpdateactbook()
    {
        $actid = Yii::$app->request->post('actid');
        $name = Yii::$app->request->post('name');
        if (is_null($actid) || !$name) {
            return ['status' => 'fail', 'message' => '参数不能错误'];
        }
        $model = ActbookSet::findOne(['aid' => $actid]);
        if (!$model) {
            $model = new ActbookSet();
        }
        $model->aid = $actid;
        $model->name = $name;
        if ($model->save()) {
            return ['success'];
        }
        return $model->getErrors();

    }

    // 16更新活动报名自定义
    public function actionUpdateactbookself()
    {
        $actid = Yii::$app->request->post('actid');
        $danhangtxt = Yii::$app->request->post('danhangtxt');
        $duoxuantxt = Yii::$app->request->post('duoxuantxt');
        $danxuanOpt = Yii::$app->request->post('danxuanOpt');
        $duoxuantxt = Yii::$app->request->post('duoxuantxt');
        $duoxuanOpt = Yii::$app->request->post('duoxuanOpt');
        if (is_null($actid)) {
            return ['status' => 'fail', 'message' => '参数不能错误'];
        }
        $model = ActbookSelfSet::findOne(['aid' => $actid]);
        if (!$model) {
            $model = new ActbookSelfSet();
        }
        $model->aid = $actid;
        $model->danhang = $danhangtxt;
        $model->duoxuan_txt = $duoxuantxt;
        $model->danxuan_opt = $danxuanOpt;
        $model->duoxuan_txt = $duoxuantxt;
        $model->duoxuan_opt = $duoxuanOpt;
        if ($model->save()) {
            return ['success'];
        }
        return $model->getErrors();
    }

    // 17某个活动报名的数据
    public function actionGetactdata()
    {
        $actid = Yii::$app->request->post('actid');
        if (!$actid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $models = MyActity::find()->where(['aid' => $actid])->orderBy('created_at DESC')->all();
        $data = [];
        foreach ($models as $k => $model) {
            $data[$k]['wxname'] = $model->user->wxname;
            $data[$k]['time'] = date('y-m-d', $model->created_at);
        }
        return $data;
    }

    // 18更新活动的状态
    public function actionUpdateactstatus()
    {
        $actid = Yii::$app->request->post('actid');
        $status = Yii::$app->request->post('status');
        if (!$actid || is_null($status)) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = Actity::findOne($actid);
        $model->status = $status;
        $model->createtime = time();
        $model->save();
        return ['status' => 'success'];
    }

    // 19删除图片
    public function actionDeletepic()
    {
        $actid = Yii::$app->request->post('actid');
        $id = Yii::$app->request->post('id');
        if (!$actid || !$id) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = ActityInfo::findOne($id);
        if (!$model) {
            return ['status' => 'fail', 'message' => '图片不存在'];
        }
        $path = Yii::getAlias('@app/web/' . $model->img_path);
        if (unlink($path)) {
            $model->delete();
            return ['success'];
        }
        return ['status' => 'fail', 'message' => '图片删除失败'];

    }

    // 20删除视频
    public function actionDeletevideo()
    {
        $actid = Yii::$app->request->post('actid');
        $id = Yii::$app->request->post('id');
        if (!$actid || !$id) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = MyWorker::findOne($id);
        if (!$model) {
            return ['status' => 'fail', 'message' => '视频不存在'];
        }
        $path = Yii::getAlias('@app/web/' . $model->path);
        if (unlink($path)) {
            $model->delete();
            return ['success'];
        }
        return ['status' => 'fail', 'message' => '图片删除失败'];
    }

    // 21 团队报名
    public function actionTeambook()
    {
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');
        $teamname = Yii::$app->request->post('teamname');
        $teachername = Yii::$app->request->post('teachername');
        $teacheryear = Yii::$app->request->post('teacheryear');
        $teacherphone = Yii::$app->request->post('teacherphone');
        $teacheremail = Yii::$app->request->post('teacheremail');
        $teacherowner = Yii::$app->request->post('teacherowner');
        $teacheraddress = Yii::$app->request->post('teacheraddress');
        $teamuser = Yii::$app->request->post('teamuser');
        //团队报名 
        $model = new MyTeambook();
        $model->uid = $uid;
        $model->aid = $actid;
        $model->teamname = $teamname;
        $model->teachername = $teachername;
        $model->teacheryear = $teacheryear;
        $model->teacherphone = $teacherphone;
        $model->teacheremail = $teacheremail;
        $model->teacherowner = $teacherowner;
        $model->teacheraddress = $teacheraddress;
        $model->teamuser = $teamuser;
        $model->createtime = time();
        //我的活动报名
		$myActity = new MyActity();
        $myActity->aid = $actid;
        $myActity->uid = $uid;
        $myActity->created_at = time();
		
        if(!$model->save()){
			 return ['status' => 'fail', 'message' =>$model->getErrors()];
		}elseif(!$myActity->save()){
			 return ['status' => 'fail', 'message' =>$myActity->getErrors()] ;
		}elseif ($model->save()&&$myActity->save()) {
           return ['status' => 'success', 'message' =>'报名成功'];
        }       
    }

    // 22 上传图片作品

    //23 判断用户已报名
    public function actionIsbook()
    {
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');
        if (!$actid || !$uid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        $model = MyActity::findOne(['aid' => $actid, 'uid' => $uid]);
        if ($model) {
            return true;
        }
        return false;
    }

    //24 渠道日推数据
    public function actionActdaydata()
    {
        //活动和渠道，查询数据
        $actid = Yii::$app->request->post('actid');
        $cid = Yii::$app->request->post('cid');

        if (!$actid || !$cid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }

        //时间范围
        $timearr = [];
        $beginTime = mktime(0, 0, 0, date("m"), date("d"), date("y"));
        for ($i = 0; $i <= 24; $i++) {
            $timearr[$i] = $beginTime + ($i * 3600);
        }
        //return $timearr;
        //返回的参数
        $viewnum = [];
        $booknum = [];
        $newtimearr = [];
        $newdatearr = [];
        $sumnum = 0;
        $beginTime = mktime(0, 0, 0, date("m"), date("d"), date("y"));
        for ($i = 0; $i < 24; $i++) {
            $b = $beginTime + ($i * 3600);
            $e = $beginTime + (($i + 1) * 3600) - 1;

            //查询得到浏览量
            $seenum = ChannelSee::find()->where(['cid' => $cid, 'actid' => $actid])->andWhere(['>=', 'createtime', $b])->andWhere(['<=', 'createtime', $e])->count();
            $viewnum[$i] = $seenum;

            //查询得到报名量
            $yuenum = ChannelBook::find()->where(['cid' => $cid, 'actid' => $actid])->andWhere(['>=', 'createtime', $b])->andWhere(['<=', 'createtime', $e])->count();
            $booknum[$i] = $yuenum;

            $newtimearr[$i] = date("H", $b);
            $newdatearr[$i] = date("d", $b);
        }

        //渠道推广的总数
        $sumnum = ChannelRecord::find()->where(['cid' => $cid])->andWhere(['>=', 'createtime', strtotime("-24 hour")])->andWhere(['<=', 'createtime', strtotime("-0 hour")])->count();

        return ['status' => 'success', 'message' => '获取数据成功', 'viewnum' => $viewnum, 'booknum' => $booknum, 'categories' => $newtimearr, 'datearr' => $newdatearr, 'sumnum' => $sumnum];
    }

    //25 渠道周推数据
    public function actionActweekdata()
    {
        //活动和渠道，查询数据
        $actid = Yii::$app->request->post('actid');
        $cid = Yii::$app->request->post('cid');

        if (!$actid || !$cid) {
            return ['status' => 'fail', 'message' => '参数错误'];
        }
        //时间范围
        $timearr = [
            strtotime("-8 day"), strtotime("-7 day"),
            strtotime("-6 day"), strtotime("-5 day"),
            strtotime("-4 day"), strtotime("-3 day"),
            strtotime("-2 day"), strtotime("-1 day"),
            strtotime("0 day"),
        ];
        //返回的参数
        $viewnum = [];
        $booknum = [];
        $newtimearr = [];
        //得到渠道查看的人
        foreach ($timearr as $key => $time) {
            if ($key == 7) {
                break;
            }
            $endtime = $timearr[$key + 1];
            //查询得到浏览量
            $seenum = ChannelSee::find()->where(['cid' => $cid, 'actid' => $actid])->andWhere(['>=', 'createtime', $time])->andWhere(['<=', 'createtime', $endtime])->count();
            $viewnum[$key] = $seenum;
            //查询得到报名量
            $yuenum = ChannelBook::find()->where(['cid' => $cid, 'actid' => $actid])->andWhere(['>=', 'createtime', $time])->andWhere(['<=', 'createtime', $endtime])->count();
            $booknum[$key] = $yuenum;
            $newtimearr[$key] = date("m-d", $endtime);
        }
        //渠道推广的总数
        $sumnum = ChannelRecord::find()->where(['cid' => $cid])->andWhere(['>=', 'createtime', strtotime("-8 day")])->andWhere(['<=', 'createtime', strtotime("-1 day")])->count();

        return ['status' => 'success', 'message' => '获取数据成功', 'viewnum' => $viewnum, 'booknum' => $booknum, 'categories' => $newtimearr, 'sumnum' => $sumnum];
    }

    //26 用量和剩余
    public function actionCensus()
    {
        //得到视频上传量(通过MyWoker表查询)
        $vuploadnum = MyWorker::find()->where(['type' => 1])->count();

        //视频播放量(通过videoplay表查询)
        $vseenum = VideoPlay::find()->count();

        //图片上传量(通过MyWoker表查询)
        $imgnum = MyWorker::find()->where(['type' => 2])->count();

        //文件数量（默认0）
        $filenum = MyWorker::find()->where(['type' => 3])->count();

        return ['status' => 'success', 'message' => '获取数据成功', 'vuploadnum' => $vuploadnum, 'vseenum' => $vseenum, 'imgnum' => $imgnum, 'filenum' => $filenum];
    }

    //27 渠道登录
    public function actionQudaologin()
    {
        //参数部分
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        if (!$username) {
            return ['status' => 'fail', 'message' => '用户名不能为空'];
        } else if (!$password) {
            return ['status' => 'fail', 'message' => '密码不能为空'];
        } else {
            $model = Channel::find()->where(['username' => $username, 'password' => $password])->one();
            if (empty($model)) {
                return ['status' => 'fail', 'message' => '用户名或者密码错误'];
            } else {
                return ['status' => 'success', 'message' => '获取数据成功', 'cid' => $model->id];
            }
        }

    }

    //27-1 渠道登录url
    public function actionQudaourl()
    {
        //参数部分
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');

        if (!$username) {
            return ['status' => 'fail', 'message' => '用户名不能为空'];
        } else if (!$password) {
            return ['status' => 'fail', 'message' => '密码不能为空'];
        } else {
            $model = Channel::find()->where(['username' => $username, 'password' => $password])->one();

            if (empty($model)) {
                return ['status' => 'fail', 'message' => '用户名或者密码错误'];
            } else {
                $url = "pages/login/index?cid=" . $model->id . "&actid=" . $model->actid;
                return ['status' => 'success', 'message' => '获取数据成功', 'url' => $url];
            }
        }

    }

    //27-2 渠道推广链接
    public function actionChannelurl()
    {
        //参数部分
        $id = Yii::$app->request->post('cid');

        $model = Channel::find()->where(['id' => $id])->one();

        if (empty($model)) {
            return ['status' => 'fail', 'message' => '渠道不存在'];
        } else {
            $url = "pages/login/index?cid=" . $model->id . "&actid=" . $model->actid;
            return ['status' => 'success', 'message' => '获取数据成功', 'url' => $url];
        }

    }
    //28 渠道记录
    public function actionQudaorecord()
    {
        //参数部分
        $cid = Yii::$app->request->post('cid');
        $actid = Yii::$app->request->post('actid');
        $type = Yii::$app->request->post('type');
        if (!$cid) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else if (!$type) {
            return ['status' => 'fail', 'message' => '类型不能为空'];
        } else {
            //新增一个渠道用户
            $model = new ChannelRecord();
            $model->actid = $actid;
            $model->cid = $cid;
            $model->type = $type;
            $model->createtime = time();

            if (!$model->save()) {
                return ['status' => 'fail', 'message' => '保存失败'];
            } else {
                return ['status' => 'success', 'message' => '保存成功'];
            }
        }

    }

    //29 渠道浏览
    public function actionQudaosee()
    {
        //参数部分
        $cid = Yii::$app->request->post('cid');
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');

        if (!$cid) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else if (!$uid) {
            return ['status' => 'fail', 'message' => '用户id不能为空'];
        } else {
            //新增一个渠道浏览
            $model = new ChannelSee();
            $model->uid = $uid;
            $model->actid = $actid;
            $model->cid = $cid;
            $model->createtime = time();

            if (!$model->save()) {
                return ['status' => 'fail', 'message' => '保存失败'];
            } else {
                return ['status' => 'success', 'message' => '保存成功'];
            }
        }

    }

    //30 渠道报名
    public function actionQudaobook()
    {
        //参数部分
        $cid = Yii::$app->request->post('cid');
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');

        if (!$cid) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else if (!$uid) {
            return ['status' => 'fail', 'message' => '用户id不能为空'];
        } else {
            //新增一个渠道报名
            $model = new ChannelBook();
            $model->uid = $uid;
            $model->actid = $actid;
            $model->cid = $cid;
            $model->createtime = time();

            if (!$model->save()) {
                return ['status' => 'fail', 'message' => '保存失败'];
            } else {
                return ['status' => 'success', 'message' => '保存成功'];
            }
        }

    }

    //31 视频播放记录
    public function actionVideoplay()
    {
        //参数部分
        $wid = Yii::$app->request->post('wid');
        $actid = Yii::$app->request->post('actid');
        $uid = Yii::$app->request->post('uid');

        if (!$wid) {
            return ['status' => 'fail', 'message' => '作品id不能为空'];
        } else if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else if (!$uid) {
            return ['status' => 'fail', 'message' => '用户id不能为空'];
        } else {
            //新增一个渠道报名
            $model = new VideoPlay();
            $model->uid = $uid;
            $model->actid = $actid;
            $model->wid = $wid;
            $model->createtime = time();

            if (!$model->save()) {
                return ['status' => 'fail', 'message' => '保存失败'];
            } else {
                return ['status' => 'success', 'message' => '保存成功'];
            }
        }

    }

    //32 渠道活动
    public function actionQudaoact()
    {
        //参数部分
        $cid = Yii::$app->request->post('cid');
        $type = Yii::$app->request->post('type');
        $type = (int) $type;
        if (!$cid) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else {
            //查询渠道表，得到用户的活动
            $actmodel = Channel::find()->where(['id' => $cid])->one();
            //return   $actmodel->actid;
            //$model=new Actity();
            if ($type == 0) { //全部的活动
                $model = Actity::find()->where(['id' => $actmodel->actid])->one();
            } else if ($type == 1) { //进行中的活动
                $model = Actity::find()->where(['id' => $actmodel->actid, 'status' => 1])->one();
            } else if ($type == 2) { //结束的活动
                $model = Actity::find()->where(['id' => $actmodel->actid, 'status' => 2])->one();
            }
            //return   $model;
            //数据整理
            if (!empty($model)) {
                $result = [];
                $result['id'] = $model->id;
                $result['cover'] = Yii::$app->request->hostInfo . '/' . $model->cover;
                $result['title'] = $model->name;
                $result['startdate'] = date('Y-m-d', $model->start_date);
                $result['enddate'] = date('Y-m-d', $model->end_date);
                $result['hdtype'] = $model->type;
                $result['booknum'] = ChannelBook::find()->where(['actid' => $model->id, 'cid' => $cid])->count();
                return ['status' => 'success', 'message' => '保存成功', 'result' => $result];
            } else {
                return ['status' => 'success', 'message' => '保存成功', 'result' => []];
            }

        }

    }

    //33 新建渠道
    public function actionAddqudao()
    {
        //参数部分
        $actid = Yii::$app->request->post('actid');
        $username = Yii::$app->request->post('username');
        $password = Yii::$app->request->post('password');
        $truename = Yii::$app->request->post('truename');
        $phone = Yii::$app->request->post('phone');
        $address = Yii::$app->request->post('address');
        $teamnum = Yii::$app->request->post('teamnum');
        $params = "活动id:" . $actid . ",用户名:" . $username . ",密码:" . $password . ",姓名:" . $truename . ",手机号:" . $phone . ",地址:" . $address . ",团队数量:" . $teamnum;
        //return  $params;

        //创建渠道
        $model = new Channel();
        $model->actid = (int) $actid;
        $model->username = $username;
        $model->password = $password;
        $model->truename = $truename;
        $model->phone = $phone;
        $model->address = $address;
        $model->teamnum = $teamnum;
        $model->createtime = time();

        //return  $model;
        if (!$model->save()) {
            return ['status' => 'fail', 'message' => '保存失败'];
        } else {
            return ['status' => 'success', 'message' => '保存成功'];
        }

    }

    //34 渠道列表
    public function actionQudaolist()
    {
        //参数部分
        $actid = Yii::$app->request->post('actid');
        if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {
            //创建渠道
            $list = Channel::find()->where(['actid' => $actid])->all();
            $result = [];
            foreach ($list as $key => $item) {
                $result[$key]["id"] = $list[$key]["id"];
                $result[$key]["qdname"] = $list[$key]["username"];
                $result[$key]["qdconcator"] = $list[$key]["truename"];
                $result[$key]["qdphone"] = $list[$key]["phone"];
                $result[$key]["txtStyle"] = "";
            }
            return ['status' => 'success', 'message' => '保存成功', 'result' => $result];
        }

    }

    //35 删除渠道
    public function actionDelqudao()
    {
        //参数部分
        $id = Yii::$app->request->post('id');
        if (!$id) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else {
            //删除渠道
            $model = Channel::find()->where(['id' => $id])->one();
            $model->delete();
            return ['status' => 'success', 'message' => '保存成功'];
        }

    }

    //36 小程序码
    public function actionMinicode()
    {
        //参数部分
        $cid = Yii::$app->request->post('cid');
        $actid = Yii::$app->request->post('actid');
        if (!$cid) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {
            //删除渠道
            //$wxcode= self::getwxcode();
            $access_token = self::gettoken();
            $path = "pages/login/index";
            $scene= $cid .','. $actid;
            $width = 430;
            $data = '{"scene":"'.$scene.'","path":"' . $path . '","width":' . $width . ',"auto_color":false,"is_hyaline":false}';
            //return $scene;
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;

            $result = self::sendCmd($url, $data);

            $dir = "uploads/wxcode/";
            $path = $dir;
            $file_name = time() . ".png";

            file_put_contents($path . $file_name, $result);

            $wxcode = Yii::$app->request->hostInfo . '/' . $path . $file_name;

            return ['status' => 'success', 'message' => '保存成功', 'result' => $wxcode];
        }

    }

    //37 编辑渠道推广
    public function actionEditspread()
    {
        //参数部分
        $actid = Yii::$app->request->post('actid');
        $files = UploadedFile::getInstancesByName('files');

        if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {

            foreach ($files as $k => $file) {
                $model = new Spread();
                $path = 'uploads/spreadimg/' . uniqid() . '.' . $file->extension;
                $file->saveAs($path);
                $model->actid = $actid;
                $model->imgpath = $path;
                $model->createtime = time();
                $model->save();
            }
            //保存成功
            return ['status' => 'success', 'message' => '保存成功'];
        }

    }

    //38 获取推广
    public function actionGetspread()
    {
        //参数部分
        $actid = Yii::$app->request->post('id');
        //return $actid;
        $actid = (int) $actid;
        //return $actid;
        if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {
            $list = Spread::find()->where(['actid' => $actid])->all();
            //return $list;
            $result = [];
            foreach ($list as $key => $item) {
                $result[$key]['id'] = $item->id;
                $result[$key]['imgpath'] = Yii::$app->request->hostInfo . '/' . $item->imgpath;
            }
            //保存成功
            return ['status' => 'success', 'message' => '保存成功', 'result' => $result];
        }

    }

    //39 删除推广
    public function actionDelspread()
    {
        //参数部分
        $id = Yii::$app->request->post('id');

        if (!$id) {
            return ['status' => 'fail', 'message' => '渠道id不能为空'];
        } else {
            $model = Spread::find()->where(['id' => $id])->one();
            //删除源文件
            $path = $model->imgpath;
            if (unlink($path)) {
                $model->delete();
                return ['status' => 'success', 'message' => '删除成功'];
            }
        }

    }

    //40 显示隐藏排行榜
    public function actionActshowrank()
    {
        //参数部分
        $actid = Yii::$app->request->post('actid');
        $type = Yii::$app->request->post('type');
        if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {
            $model = Actity::find()->where(['id' => $actid])->one();
            $isrank = (int) $type==0?1:0;
            $model->isrank=$isrank;
            $model->save();
            //return $model;
            // return $model->getErrors();
            if (!$model->save()) {
                return $model->getErrors();
                return ['status' => 'fail', 'message' => '保存失败'];
            } else {
                return ['status' => 'success', 'message' => '保存成功'];
            }
        }

    }
    
   
    //41 团队报名数据
    public function actionTeamlist()
    {
        //参数部分
        $actid = Yii::$app->request->post('actid',"");
        $pageno = Yii::$app->request->post('pageno',"");
     
        if ($actid=="0") {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {
            $actid = (int) $actid;
            $pageno= (int) $pageno;
            //return ['status' => 'fail', 'message' => '活动id不能为空','actid'=>$actid,'pageno'=>$pageno];
            $list = MyTeambook::find()->where(['aid' => $actid])->orderBy('createtime DESC')->offset(($pageno-1)*10)->limit(10)->all();

            $result = [];
            foreach ($list as $key => $item) {
                $result[$key]['id'] = $item->id;
                $result[$key]['aid'] =$item->aid;
                $result[$key]['uid'] = $item->uid;
                $result[$key]['teamname'] = $item->teamname;
                $result[$key]['teachername'] = $item->teachername;
                $result[$key]['teacheryear'] = $item->teacheryear;
                $result[$key]['teacherphone'] = $item->teacherphone;
                $result[$key]['teacheremail'] = $item->teacheremail;
                $result[$key]['teacheraddress'] = $item->teacheraddress;
                $result[$key]['teacherowner'] = $item->teacherowner;
                $result[$key]['teamuser'] =self::getTeamuser($item->teamuser);                
                $result[$key]['createtime'] =date("Y-m-d H:i:s",$item->createtime);
            }
            return ['status' => 'success', 'message' => '获取数据成功','result'=>$result];
        }
    }

       
    //42 个人报名数据
    public function actionSinglelist()
    {
        //参数部分
        $actid = Yii::$app->request->post('actid');
        $pageno = Yii::$app->request->post('pageno');
        $pageno=(int)$pageno;
        if (!$actid) {
            return ['status' => 'fail', 'message' => '活动id不能为空'];
        } else {
            $list = MyBook::find()->where(['aid' => $actid])->orderBy('createtime DESC')->offset(($pageno-1)*10)->limit(10)->all();

            foreach ($list as $key => $item) {
                $data[$key]['id'] = $item->id;
                $data[$key]['uid'] =$item->uid;
                $wxuser=WechatUser::find()->where(['id'=> $item->uid])->one();
                $data[$key]['wxname'] =empty($wxuser)?"":$wxuser->wxname;
                $data[$key]['danhang_txt'] =$item->danhang_txt;
                $data[$key]['duoxuan'] =$item->uid;
                $data[$key]['name'] =$item->name;
            }
            return ['status' => 'success', 'message' => '获取数据成功','result'=>$list];
        }
    }

    //43 得到团队信息
    public function actionGetteam()
    {
        //参数部分
        $uid = Yii::$app->request->post('uid');
        if (!$uid) {
            return ['status' => 'fail', 'message' => 'uid不能为空'];
        } else {
            $item = MyTeambook::find()->where(['uid' => $uid])->one();
            $result['id'] = $item->id;
            $result['teamname'] = $item->teamname;
            $result['teachername'] = $item->teachername;
            $result['teacheryear'] = $item->teacheryear;
            $result['teacherphone'] = $item->teacherphone;
            $result['teacheremail'] = $item->teacheremail;
            $result['teacheraddress'] = $item->teacheraddress;
            $result['teacherowner'] = $item->teacherowner;
            $result['teamuser'] =self::getTeamuser($item->teamuser); 
            $result['createtime'] =date("Y-m-d H:i:s",$item->createtime);            
            return ['status' => 'success', 'message' => '获取数据成功','result'=>$result];
        }
    }

     //44 更新团队信息
     public function actionUpdateteam()
     {
         //参数部分
         $id=Yii::$app->request->post('id');       
         $teamname = Yii::$app->request->post('teamname');
         $teachername = Yii::$app->request->post('teachername');
         $teacheryear = Yii::$app->request->post('teacheryear');
         $teacherphone = Yii::$app->request->post('teacherphone');
         $teacheremail = Yii::$app->request->post('teacheremail');
         $teacherowner = Yii::$app->request->post('teacherowner');
         $teacheraddress = Yii::$app->request->post('teacheraddress');
         $teamuser = Yii::$app->request->post('teamuser');
         if (!$id) {
            return ['status' => 'fail', 'message' => 'id不能为空'];
        } else  if (!$teamname) {
            return ['status' => 'fail', 'message' => 'teamname不能为空'];
        }else  if (!$teacheryear) {
            return ['status' => 'fail', 'message' => 'teacheryear不能为空'];
        }else  if (!$teacherphone) {
            return ['status' => 'fail', 'message' => 'teacherphone不能为空'];
        }else  if (!$teacheremail) {
            return ['status' => 'fail', 'message' => 'teacheremail不能为空'];
        }else  if (!$teacherowner) {
            return ['status' => 'fail', 'message' => 'teacherowner不能为空'];
        }else  if (!$teacheraddress) {
            return ['status' => 'fail', 'message' => 'teacheraddress不能为空'];
        }else  if (!$teamuser) {
            return ['status' => 'fail', 'message' => 'teamuser不能为空'];
        }else{
            $myteam=MyTeambook::find()->where(['id'=>$id])->one();
            $myteam->teamname=$teamname;
            $myteam->teachername=$teachername;
            $myteam->teacheryear=$teacheryear;
            $myteam->teacherphone=$teacherphone;
            $myteam->teacheremail=$teacheremail;
            $myteam->teacherowner=$teacherowner;
            $myteam->teacheraddress=$teacheraddress;
            $myteam->teamuser=$teamuser;

            if (!$myteam->save()) {
                return $myteam->getErrors();
                return ['status' => 'fail', 'message' => '保存失败'];
            } else {
                return ['status' => 'success', 'message' => '保存成功'];
            }
        }     

     }
    // ******************************** 处理方法 *****************************

    // 微信授权获取 openid
    public function wechatAuth($code)
    {
        $url = 'https://api.weixin.qq.com/sns/jscode2session?grant_type=authorization_code';

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl($url)
            ->setData([
                'appid' => Yii::$app->params['appid'],
                'secret' => Yii::$app->params['secret'],
                'js_code' => $code,
            ])
            ->send();
        if ($response->isOk) {
            $data = $response->data;
        }

        return $data;
    }

    // 得到token
    public function gettoken()
    {

        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . Yii::$app->params['appid'] . '&secret=' . Yii::$app->params['secret'];
        $res = json_decode(self::curlGet($url));
        $access_token = $res->access_token;
        return $access_token;
    }

    //Get请求
    public static function curlGet($url = '', $options = array())
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 发起请求
     * @param  string $url  请求地址
     * @param  string $data 请求数据包
     * @return   string      请求返回数据
     */
    public function sendCmd($url, $data)
    {

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检测
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:')); //解决数据包大不能提交
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }
    

    //获取队员列表
    public function getTeamuser($teamuser)
    {
      $result=[];
      $tulist=explode(";",$teamuser);
      
      foreach ($tulist as $key => $item) {
        if($item!="")
        {
            $user=explode(",",$item);
            $result[$key]['username']=$user[0];
            $result[$key]['year']=$user[1];
        }       
      }
      return $result;
    }

    /**
     * 递归：生成目录
     */
    private function createDir($str)
    {
        $arr = explode('/', $str);
        if(!empty($arr))
        {
            $path = '';
            foreach($arr as $k=>$v)
            {
                $path .= $v.'/';
                if (!file_exists($path)) {
                    mkdir($path, 0777);
                    chmod($path, 0777);
                }
            }
        }
    }
    //结束标识符
}
