<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $publication_id
 * @property int $author
 * @property string $comment
 * @property string $datatime
 *
 * @property Publication $publication
 */
class Comment extends \yii\db\ActiveRecord
{

    public function fields()
    {
        return [
            'id',
            'author' => function ($model){
                return $model->user->login;
            },
            'comment',
            'datatime' => function ($model){
                return Yii::$app->formatter->asDatetime($model->datatime, 'hh:mm dd.MM.yyyy');
            }
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['publication_id', 'author', 'comment'], 'required'],
            [['publication_id', 'author'], 'integer'],
            [['datatime'], 'safe'],
            [['comment'], 'string', 'max' => 255],
            [['publication_id'], 'exist', 'skipOnError' => true, 'targetClass' => Publication::className(), 'targetAttribute' => ['publication_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'publication_id' => 'Publication ID',
            'author' => 'Author',
            'comment' => 'Comment',
            'datatime' => 'Datatime',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublication()
    {
        return $this->hasOne(Publication::className(), ['id' => 'publication_id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'author']);
    }
}
