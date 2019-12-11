<?php

namespace app\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $token
 *
 * @property Publication[] $publications
 */
class User extends \yii\db\ActiveRecord implements IdentityInterface
{

    public function fields()
    {
        return [
            'id',
            'login',
            'avatar',
            'subscribers' => function ($model){
                return [
                    'subscribers' => count($model->subscribes),
                    'subscribing' => count($model->subscribes0),
                ];
            },

            'publications' => function ($model){
                return $model->publications;
            },
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }
    /**
     * {@inheritdoc}
     */
    public $image;

    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['login', 'password'], 'required'],
            [['login'], 'unique', 'on' => 'register'],
            [['login', 'password', 'token'], 'string', 'max' => 30],
            [['avatar'], 'string', 'max' => 255],
            [['image'], 'file', 'extensions' => 'jpg, png', 'maxSize' => 2*1024*1024, 'skipOnEmpty' => true, 'on' => 'register'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'password' => 'Password',
            'avatar' => 'avatar',
            'token' => 'Token',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPublications()
    {
        return $this->hasMany(Publication::className(), ['user_id' => 'id'])->orderBy('id DESC');
    }

    public function getSubscribes()
    {
        return $this->hasMany(Subscribe::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscribes0()
    {
        return $this->hasMany(Subscribe::className(), ['sub_id' => 'id']);
    }

    public function login()
    {
        $user = User::findOne(['login' => $this->login]);

        if($user && $user->password == $this->password){
            $user->token = Yii::$app->security->generateRandomString(30);
            $user->save();
            return $user;
        }
        return false;
    }
}
