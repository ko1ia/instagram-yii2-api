<?php

namespace app\models;

use app\controllers\MyBearerAuth;
use Yii;

/**
 * This is the model class for table "subscribe".
 *
 * @property int $id
 * @property int $user_id
 * @property int $sub_id
 *
 * @property User $user
 */
class Subscribe extends \yii\db\ActiveRecord
{

    public function fields()
    {

        $fields = parent::fields();

        unset($fields['id']);
        unset($fields['user_id']);
        unset($fields['sub_id']);


        if(Yii::$app->controller->action->id == 'subscribelist'){
            $fields['id'] = function ($model) {
                return $model->sub_id;
            };
            $fields['subscriber'] = function ($model){
                return [
                    'id' => User::findOne(['id' => $model->sub_id])->id,
                    'name' => User::findOne(['id' => $model->sub_id])->login,
                    'avatar' => User::findOne(['id' => $model->sub_id])->avatar
                ];
            };
            $fields['sub_me'] = function ($model) {
                return Subscribe::find()->where(['user_id' => $model->sub_id, 'sub_id' => \Yii::$app->user->id])->exists();
            };
        } else {
            $fields['id'] = function ($model) {
                return $model->user_id;
            };
            $fields['subscribing'] = function ($model){
                return [
                    'id' => User::findOne(['id' => $model->user_id])->id,
                    'name' => User::findOne(['id' => $model->user_id])->login,
                    'avatar' => User::findOne(['id' => $model->user_id])->avatar
                ];
            };

        }


        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscribe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'sub_id'], 'required'],
            [['user_id', 'sub_id'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['sub_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['sub_id' => 'id']],
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
            'sub_id' => 'Sub ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSub()
    {
        return $this->hasOne(User::className(), ['id' => 'sub_id']);
    }
}
