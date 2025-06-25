<?php
namespace backend\models;

use yii\base\Model;

class DoctorForm extends Model
{
    public $username;
    public $email;
    public $password;

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            ['password', 'required', 'on' => 'create'],
            [['username', 'email', 'password'], 'string', 'max' => 255],
            ['email', 'email'],
        ];
    }
}
