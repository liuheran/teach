<?php
namespace api\models;

use yii\base\Model;
use api\models\User;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $phoneNumber;
    public $passWord;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['phoneNumber', 'trim'],
            ['phoneNumber', 'required'],
            ['phoneNumber', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['phoneNumber', 'string', 'min' => 2, 'max' => 255],

            ['passWord', 'required'],
            ['passWord', 'string', 'min' => 6],
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }
        $user = new User();
        $user->phoneNumber = $this->phoneNumber;
        $user->setPassword($this->passWord);
        $user->generateAuthKey();
        $user->generateAccessToken();
        
        return $user->save() ? $user : null;
    }
}
