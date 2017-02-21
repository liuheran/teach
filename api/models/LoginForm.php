<?php
namespace api\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $phoneNumber;
    public $passWord;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['phoneNumber', 'passWord'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['passWord', 'validatePassword'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->passWord)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), 0);
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByPhoneNumber($this->phoneNumber);
        }

        return $this->_user;
    }
    
    public function token($id)
    {
        $user = User::findOne([
            'id' => $id,
        ]);
        $user->generateAccessToken();
        return $user->save() ? $user : null;
    }
}
