<?php
/**
 * Created by PhpStorm.
 * User: gr475_tsna
 * Date: 28.02.2019
 * Time: 12:43
 */

namespace app\controllers;


use app\models\Comment;
use app\models\Publication;
use app\models\Help;
use yii\rest\ActiveController;

class CommentController extends ActiveController
{
    public $modelClass = 'app\models\Comment';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => MyBearerAuth::className(),
            'only' => ['create', 'delete', 'update']
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }


    public function actionCreate($id)
    {
        $comment = new Comment();
        $publ = Publication::findOne($id);

        if($publ){
            $comment->load(\Yii::$app->request->post(), '');
            $comment->publication_id = $id;
            $comment->author = \Yii::$app->user->id;
            if($comment->save()){
                return Help::response('201', 'Comment created', true, ['comment_id' => $comment->id, 'publication_id' => $publ->id]);
            }
            return Help::response('400', 'Comment create error', false, ['message' => $comment->firstErrors]);
        }
        return Help::response('404', 'Publication not found', false, ['message' => 'Publication not found']);
    }

    public function actionUpdate($id_publ, $id_comment)
    {
        $publ = Publication::findOne($id_publ);
        $comment = Comment::findOne($id_comment);

        if(!$publ){
            return Help::response('404', 'Publication not found', false, ['message' => 'Publication not found']);
        }
        if(!$comment){
            return Help::response('404', 'Comment not found', false, ['message' => 'Comment not found']);
        }

        if(\Yii::$app->user->id != 1 &&  $comment->author != \Yii::$app->user->id ){
            return Help::response('400', 'This is not your', false, ['message' => 'This is not you publication']);
        }

        $comment->load(\Yii::$app->request->post(), '');

        if($comment->validate() && $comment->save(false)){
            return Help::response('201', 'Comment update success', false, ['body' => $comment]);
        }
    }

    public function actionDelete($id_publ, $id_comment)
    {
        $publ = Publication::findOne($id_publ);
        $comment = Comment::findOne($id_comment);

        if(!$publ){
            return Help::response('404', 'Publication not found', false, ['message' => 'Publication not found']);
        }
        if(!$comment){
            return Help::response('404', 'Comment not found', false, ['message' => 'Comment not found']);
        }

        if(\Yii::$app->user->id != 1 &&  $comment->author != \Yii::$app->user->id ){
            return Help::response('400', 'This is not your', false, ['message' => 'This is not you publication']);
        }

        if($comment->delete()){
            return Help::response('201', 'Comment deleted', false, ['message' => 'Comment deleted']);
        }
    }
}