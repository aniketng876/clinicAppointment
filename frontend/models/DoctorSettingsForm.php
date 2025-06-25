<?php
namespace frontend\models;

use yii\base\Model;

class DoctorSettingsForm extends Model
{
    public $working_days; // array for form use
    public $start_time;
    public $end_time;
    public $break_time_start;
    public $break_time_end;

    public function rules()
    {
        return [
            [['working_days', 'start_time', 'end_time'], 'required'],
            ['working_days', 'safe'],
            [['start_time', 'end_time', 'break_time_start', 'break_time_end'], 'safe'], // Remove regex validation for time fields
        ];
    }

    public function load($data, $formName = null)
    {
        $result = parent::load($data, $formName);
        // Fix: HTML5 input type="time" returns value as "HH:MM" or "HH:MM:SS" (sometimes browser-dependent)
        foreach (['start_time', 'end_time', 'break_time_start', 'break_time_end'] as $field) {
            if (isset($this->$field) && preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/', $this->$field)) {
                $this->$field = substr($this->$field, 0, 5); // convert to HH:MM
            }
        }
        if (isset($this->working_days) && is_array($this->working_days)) {
            $this->working_days = implode(',', $this->working_days);
        }
        return $result;
    }
}
