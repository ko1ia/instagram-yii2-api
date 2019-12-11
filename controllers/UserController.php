<?php
/**
 * Created by PhpStorm.
 * User: Николай
 * Date: 27.02.2019
 * Time: 22:02
 */

namespace app\controllers;
use app\models\Help;
use app\models\Publication;
use app\models\Subscribe;
use app\models\User;
use yii\filters\Cors;
use yii\helpers\Url;
use yii\web\UploadedFile;

class UserController extends \yii\rest\ActiveController
{
    public $modelClass = 'app\models\User';


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => MyBearerAuth::className(),
            'only' => ['subscribe', 'unsubscribe', 'feed'],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create']);
        unset($actions['view']);

        return $actions;
    }

    public function actionCreate()
    {
        $user = new User();
        $user->scenario = 'register';
        $user->load(\Yii::$app->request->post(), '');
        $user->token = '';
        $user->image = UploadedFile::getInstanceByName('image');
        if($user->validate()){
            $path = 'avatars/'.\Yii::$app->security->generateRandomString(10) . '.' . $user->image->extension;
            $user->avatar = Url::base(true). '/'. $path;
            if($user->save()){
                $user->image->saveAs($path);
                return Help::response('201', 'Registration success', true, ['message' => 'Registration success']);
            }
        }
        return Help::response('400', 'Registration error', false, ['message' => $user->firstErrors]);
    }

    public function actionLogin()
    {
        $user = new User();

        $user->load(\Yii::$app->request->post(), '');

        if($user->validate()){
            if($user = $user->login()){
                return Help::response('200', 'Authorization success', true, ['user_id' => $user->id, 'token' => $user->token]);
            }
        }
        return Help::response('400', 'Authorization error', false, ['message' => 'Invalid Authorization']);
    }

    public function actionView($id)
    {
        if((string)strlen($id) > 1){
            $user = User::findOne(['login' => $id]);

            return Help::response('200', 'Profile info', true, ['body' => $user]);
        }
        $user = User::findOne($id);

        if(!$user){
            return Help::response('404', 'User not found', false, ['message' => 'User not found']);
        }
        return Help::response('200', 'Profile info', true, ['body' => $user]);
    }



    public function actionSubscribe($id)
    {
        $sub = new Subscribe();
        $user = User::findOne($id);
        if(!$user){
            return Help::response('404', 'User not found', false, ['message' => 'User not found']);
        }
        $sub->user_id = $id;
        $sub->sub_id = \Yii::$app->user->id;

        if(!Subscribe::find()->where(['user_id' => $id, 'sub_id' => \Yii::$app->user->id])->exists()){
            if($sub->save()){
                return Help::response('200', 'Subscribe success', true, ['message' => 'Subscribe success']);
            }
        }
        return Help::response('400', 'Subscribe error', true, ['message' => 'You are already subscribed']);
    }

    public function actionUnsubscribe($id)
    {
        if(Subscribe::find()->where(['user_id' => $id, 'sub_id' => \Yii::$app->user->id])->exists()){
            $sub = Subscribe::find()->where(['user_id' => $id, 'sub_id' => \Yii::$app->user->id])->one();
            if($sub){
                $sub->delete();
                return Help::response('200', 'Unsub success', true, ['message' => 'Unsubscribe success']);
            }
        }

        return Help::response('404', 'Subscribe not found', true, ['message' => 'Subscribe not found']);
    }

    public function actionSubscribelist($id)
    {
        if((string)strlen($id) > 1){
            $id = User::findOne(['login' => $id])->id;
            $sub = Subscribe::findAll(['user_id' => $id]);

            return Help::response('200', 'List subscribers', true, ['subs' => $sub]);
        }
        $sub = Subscribe::findAll(['user_id' => $id]);

        if(!$sub){
            return Help::response('404', 'Subscribers not found', true, ['message' => 'Subscribers not found']);
        }

        return Help::response('200', 'List subscribers', true, ['subs' => $sub]);
    }

    public function actionSubscribetolist($id)
    {
        if((string)strlen($id) > 1){
            $id = User::findOne(['login' => $id])->id;
            $subto = Subscribe::findAll(['sub_id' => $id]);

            return Help::response('200', 'List of signatories ', true, ['subto' => $subto]);
        }
        $subto = Subscribe::findAll(['sub_id' => $id]);

        if(!$subto){
            return Help::response('200', 'Signatories not found', true, ['message' => 'List of signatories not found']);
        }

        return Help::response('200', 'List of signatories ', true, ['subto' => $subto]);
    }

    public function actionFeed()
    {
        $users = Subscribe::findAll(['sub_id' => \Yii::$app->user->id]);
        $users_array = [\Yii::$app->user->id];
        for ($i = 0; $i < count($users); $i++){
            array_push($users_array, $users[$i]->user_id);
        }

        $pubs = Publication::find()->where(['user_id' => $users_array])->orderBy('id DESC')->all();
        return Help::response('200', 'Feed user ', true, ['feed' => $pubs]);
    }


}