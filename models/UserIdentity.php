<?php

namespace app\models;

use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * UserIdentity class for "user" table.
 * This is a base user class that is implementing IdentityInterface.
 * User model should extend from this model, and other user related models should
 * extend from User model.
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $account_activation_token
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class UserIdentity extends ActiveRecord implements IdentityInterface
{
    /**
     * Declares the name of the database table associated with this AR class.
     *
     * @return string
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

//------------------------------------------------------------------------------------------------//
// IDENTITY INTERFACE IMPLEMENTATION
//------------------------------------------------------------------------------------------------//

    /**
     * Finds an identity by the given ID.
     *
     * @param  int|string $id The user id.
     * @return IdentityInterface|static
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => User::STATUS_ACTIVE]);
    }

    /**
     * Finds an identity by the given access token.
     *
     * @param  mixed $token
     * @param  null $type
     * @return void|IdentityInterface
     *
     * @throws null
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {

        $signer = new Sha256();
        $decoded_token = (new Parser())->parse((string)$token); // Parses from a string
        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer(Yii::$app->params['JwtIssuer']);
        $data->setAudience(Yii::$app->params['JwtAudience']);
        $data->setId(Yii::$app->params['TokenID']);
        if ($decoded_token->verify($signer, Yii::$app->params['TokenEncryptionKey'])) {
            if ($decoded_token->validate($data)) {
                $user = User::findOne(['id' => $decoded_token->getClaim('uid')]);
                if ($user) {
                    return $user;
                } else {
                    return null;
                }
            }

        }

    }


    /**
     * Returns an ID that can uniquely identify a user identity.
     *
     * @return int|mixed|string
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * Returns a key that can be used to check the validity of a given
     * identity ID. The key should be unique for each individual user, and
     * should be persistent so that it can be used to check the validity of
     * the user identity. The space of such keys should be big enough to defeat
     * potential identity attacks.
     *
     * @return string
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * Validates the given auth key.
     *
     * @param  string $authKey The given auth key.
     * @return boolean          Whether the given auth key is valid.
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

//------------------------------------------------------------------------------------------------//
// IMPORTANT IDENTITY HELPERS
//------------------------------------------------------------------------------------------------//

    /**
     * Generates "remember me" authentication key.
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Validates password.
     *
     * @param  string $password
     * @return bool
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model.
     *
     * @param  string $password
     *
     * @throws \yii\base\Exception
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
}
