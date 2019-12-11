<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "publication_like".
 *
 * @property int $id
 * @property int $publication_id
 * @property int $user_id
 *
 * @property Publication $publication
 */
class PublicationLike extends \yii\db\ActiveRecord
{
    public function fields()
    {
        return [
            'id' => function ($model){
                return $model->user_id;
            },
            'login' => function ($model) {
                return $model->user->login;
            }
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'publication_like';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['publication_id', 'user_id'], 'required'],
            [['publication_id', 'user_id'], 'integer'],
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
            'user_id' => 'User ID',
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
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
