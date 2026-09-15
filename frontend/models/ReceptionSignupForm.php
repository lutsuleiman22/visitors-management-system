<?php

declare(strict_types=1);

namespace frontend\models;

use common\models\User;
use common\services\BranchCatalog;
use Yii;
use yii\base\Model;

class ReceptionSignupForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $password_repeat = '';

    public function rules(): array
    {
        return [
            [['username', 'email', 'password', 'password_repeat'], 'trim'],
            ['username', 'required'],
            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username is already registered.'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email is already registered.'],
            ['password', 'required'],
            ['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
        ];
    }

    public function createReception(): ?User
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->role = User::ROLE_RECEPTION;
        $user->branch_code = (string) Yii::$app->session->get('pbz_branch', '');
        if (!array_key_exists($user->branch_code, BranchCatalog::all())) {
            $this->addError('username', 'Please select a PBZ branch before creating an account.');
            return null;
        }
        $user->status = User::STATUS_ACTIVE;
        $user->setPassword($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
