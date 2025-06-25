<?php
namespace frontend\models;

use yii\base\Model;

class DoctorHolidayForm extends Model
{
    public $date;
    public $description;

    public function rules()
    {
        return [
            [['date', 'description'], 'required'],
            ['date', 'date', 'format' => 'php:Y-m-d'],
            ['description', 'string', 'max' => 255],
        ];
    }
}
