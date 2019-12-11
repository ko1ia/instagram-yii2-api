<?php
/**
 * Created by PhpStorm.
 * User: Николай
 * Date: 27.02.2019
 * Time: 22:18
 */

namespace app\controllers;


use app\models\Publication;
use app\models\PublicationLike;
use app\models\User;
use app\models\Help;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\UploadedFile;

class PublicationController extends ActiveController
{
    public $modelClass = 'app\models\Publication';


    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => MyBearerAuth::className(),
            'only' => ['create', 'delete', 'update', 'like', 'view']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['view']);

        return $actions;
    }

    public function actionView($id)
    {
        $publ = Publication::findOne($id);

        if(!$publ){
            return Help::response('404', 'Not Found', false, ['message' => 'Publication not found']);
        }
        return Help::response('200', 'Single Publication', true, ['body' => $publ]);
    }

    public function actionCreate()
    {
        $publ = new Publication();

        $publ->load(\Yii::$app->request->post(), '');
        $publ->user_id = \Yii::$app->user->id;
        $publ->image = UploadedFile::getInstanceByName('image');
        if($publ->validate()){
            $path = 'publ_photos/'.\Yii::$app->security->generateRandomString(10). '.'.$publ->image->extension;
            $publ->photo = Url::base(true). '/'. $path;
            if($publ->save()){
                $publ->image->saveAs($path);
                return Help::response('201', 'Publication create', true, ['body' => $publ]);
            }
        }
        return Help::response('400', 'Create error', false, ['message' => $publ->firstErrors]);
    }

    public function actionUpdate($id)
    {
        $publ = Publication::findOne($id);

        if(!$publ){
            return Help::response('404', 'Not Found', false, ['message' => 'Publication not found']);
        }
        $publ->scenario = 'edit';
        $publ->load(\Yii::$app->request->post(), '');
        if(\Yii::$app->user->id != 1 &&  $publ->user_id != \Yii::$app->user->id ){
            return Help::response('400', 'This is not your', false, ['message' => 'This is not you publication']);
        }
        if($publ->validate()){
            if($publ->save()){
                return Help::response('201', 'Edit success', true, ['body' => $publ]);
            }
        }
        return Help::response('400', 'Edit error', false, ['message' => $publ->firstErrors]);
    }

    public function actionDelete($id)
    {
        $publ = Publication::findOne($id);

        if($publ){
            if($publ->delete()){
                return Help::response('201', 'Delete success', true, ['message' => 'Delete success']);
            }
        }
        return Help::response('404', 'Not Found', false, ['message' => 'Publication not found']);

    }

    public function actionUser($id)
    {
        $publ = Publication::findAll(['user_id' => $id]);
        $user = User::findOne($id);

        if(!$user){
            return Help::response('404', 'User not found', false, ['message' => 'User not found']);
        }

        return Help::response('200', 'User posts', true, ['posts' => $publ]);
    }

    public function actionLike($id)
    {
        $like = new PublicationLike();
        $publ = Publication::findOne($id);
        if(!$publ){
            return Help::response('404', 'Publication not found', false, ['message' => 'Publication not found']);
        }
        $like->publication_id = $id;
        $like->user_id = \Yii::$app->user->id;

        if(!PublicationLike::find()->where(['publication_id' => $id, 'user_id' => \Yii::$app->user->id])->exists()){
            if($like->save()){
                return Help::response('200', 'Put like success', true, ['message' => 'Put like success']);
            }
        } else {
            $like_puted = PublicationLike::find()->where(['publication_id' => $id, 'user_id' => \Yii::$app->user->id])->one();
            if($like_puted->delete()){
                return Help::response('200', 'Removed like success', true, ['message' => 'Removed like success']);
            }
        }
    }

    public function actionLikes($id)
    {
        $likes = PublicationLike::findAll(['publication_id' => $id]);

        if(!$likes){
            return Help::response('404', 'Likes not found', true, ['message' => 'Likes not found']);
        }

        return Help::response('200', 'List likes', true, ['likes' => $likes]);
    }
}