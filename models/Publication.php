<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "publication".
 *
 * @property int $id
 * @property int $user_id
 * @property string $photo
 * @property string $description
 *
 * @property Comment[] $comments
 * @property User $user
 */
class Publication extends \yii\db\ActiveRecord
{

    public function fields()
    {
        if(Yii::$app->controller->action->id != 'view' && Yii::$app->controller->action->id != 'feed'){
            return [
                'id',
                'author' => function ($model) {
                    return [
                        'id' => $model->user->id,
                        'name' => $model->user->login,
                        'avatar' => $model->user->avatar
                    ];
                },
                'photo',
                'description',
            ];
        }
        return [
            'id',
            'author' => function ($model) {
                return [
                    'id' => $model->user->id,
                    'name' => $model->user->login,
                    'avatar' => $model->user->avatar
                ];
            },
            'photo',
            'description',
            'likes' => function ($model){
                return count($model->publicationLikes);
            },
            'put_like' => function($model){
                return PublicationLike::find()->where(['publication_id' => $model->id, 'user_id' => \Yii::$app->user->id])->exists();
            },
            'comments' => function ($model) {
                return $model->comments;
            }
        ];
    }

    public $image;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'publication';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'description'], 'required'],
            [['user_id'], 'integer'],
            [['photo', 'description'], 'string', 'max' => 255],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['image'], 'file', 'extensions' => 'jpg, png', 'maxSize' => 2*1024*1024, 'skipOnEmpty' => false, 'on' => 'default'],
            [['image'], 'file', 'extensions' => 'jpg, png', 'maxSize' => 2*1024*1024, 'skipOnEmpty' => true, 'on' => 'edit'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'photo' => 'Photo',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['publication_id' => 'id']);
    }

    public function getPublicationLikes()
    {
        return $this->hasMany(PublicationLike::className(), ['publication_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
