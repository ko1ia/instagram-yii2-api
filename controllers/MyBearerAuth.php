<?php
/**
 * Created by PhpStorm.
 * User: gr475_tsna
 * Date: 28.02.2019
 * Time: 9:37
 */

namespace app\controllers;


use yii\filters\auth\HttpBearerAuth;

class MyBearerAuth extends HttpBearerAuth
{
    public function handleFailure($response)
    {
        \Yii::$app->response->setStatusCode('401', 'Unauthorized');
        \Yii::$app->response->data = [
            'message' => 'You are not authorized'
        ];
    }
}