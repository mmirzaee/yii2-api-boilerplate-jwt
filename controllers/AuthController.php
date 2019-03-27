<?php

namespace app\controllers;

use app\models\User;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\rest\Controller;

class AuthController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();


        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::className(),
        ];

        $behaviors['authenticator']['except'] = ['login', 'ok'];

        return $behaviors;
    }

    public function actionLogin()
    {
        $username = \Yii::$app->getRequest()->post('username', '');
        $password = \Yii::$app->getRequest()->post('password', '');

        $user = null;
        if($username && $password){
            $user = User::findOne(['username' => $username]);
        }

        if ($user && $user->validatePassword($password)) {
            $signer = new Sha256();
            $expire = time() + \Yii::$app->params['JwtExpire'];
            $jwt = \Yii::$app->jwt;
            $token = $jwt->getBuilder()
                ->setIssuer(\Yii::$app->params['JwtIssuer'])// Configures the issuer (iss claim)
                ->setAudience(\Yii::$app->params['JwtAudience'])// Configures the audience (aud claim)
                ->setId(\Yii::$app->params['TokenID'], true)// Configures the id (jti claim), replicating as a header item
                ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
                ->setExpiration($expire)// Configures the expiration time of the token (exp claim)
                ->set('uid', $user->id)// Configures a new claim, called "uid"
                ->sign($signer, $jwt->key)// creates a signature using [[Jwt::$key]]
                ->getToken(); // Retrieves the generated token

            return [
                'msg' => 'logged in successful',
                'token' => (string)$token,
                'expires_in' => $expire
            ];
        }

        \Yii::$app->response->statusCode = 401;
        return ['msg' => 'username/password is wrong!'];
    }

}