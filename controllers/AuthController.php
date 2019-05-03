<?php

namespace app\controllers;

use app\models\User;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use sizeg\jwt\JwtHttpBearerAuth;
use yii\filters\Cors;
use yii\rest\Controller;

class AuthController extends Controller
{

    public function behaviors()
    {
        $behaviors = parent::behaviors();


        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
        ];

        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];

        $behaviors['authenticator']['except'] = ['login', 'signup', 'options'];

        return $behaviors;
    }

    public function actionLogin()
    {
        $username = \Yii::$app->getRequest()->post('username', '');
        $password = \Yii::$app->getRequest()->post('password', '');

        $user = null;
        if ($username && $password) {
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

    public function actionRefresh()
    {
        $user = \Yii::$app->user->getIdentity();

        if ($user) {
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
                'msg' => 'ok',
                'token' => (string)$token,
                'expires_in' => $expire
            ];
        }

        \Yii::$app->response->statusCode = 401;
        return ['msg' => 'token is not valid!'];
    }

    public function actionSignup()
    {
        $user = new User();
        $params = \Yii::$app->getRequest()->getBodyParams();
        $user->load($params, '');
        if (isset($params['password']) && $params['password']) {
            $user->setPassword($params['password']);
        }
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = $user->updated_at = time();
        if ($user->save()) {
            $auth = \Yii::$app->authManager;
            $role = $auth->getRole(\Yii::$app->params['DefaultSignupRole']);
            $auth->assign($role, $user->id);

            \Yii::$app->response->statusCode = 201;
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
                'msg' => 'ok',
                'token' => (string)$token,
                'expires_in' => $expire
            ];
        }

        if ($user->hasErrors('password_hash')) {
            $user->clearErrors('password_hash');
            $user->addError('password', 'Password cannot be blank.');
        }
        return $user;
    }

    public function actionMe()
    {
        $user = User::findOne(\Yii::$app->user->getIdentity()->getId());
        return [
            'username' => $user->username,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role' => $user->getRoleName(),
        ];
    }

    public function actionOptions($x1 = '', $x2 = '', $x3 = '', $x4 = '')
    {
        return ['msg' => 'ok'];
    }


}
