<?php
namespace backend\models;

use yii\base\Model;

class PatientForm extends Model
{
    public $username;
    public $email;
    public $password;

    public function rules()
    {
        return [
            [['username', 'email'], 'required'],
            [['username', 'email', 'password'], 'string', 'max' => 255],
            ['email', 'email'],
        ];
    }
}
