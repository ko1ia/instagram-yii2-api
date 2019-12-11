<?php
/**
 * Created by PhpStorm.
 * User: gr475_tsna
 * Date: 28.02.2019
 * Time: 10:35
 */

namespace app\models;


class Help
{
    public function response($code, $status, $statusOnArray, $array = [])
    {
        \Yii::$app->response->setStatusCode($code, $status);

        if(is_bool($statusOnArray)){
            return array_merge([
                'status' => $statusOnArray,
            ], $array);
        }

        return $statusOnArray;
    }
}