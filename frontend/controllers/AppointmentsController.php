<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\helpers\Url;

class AppointmentsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['dashboard'],
                'rules' => [
                    [
                        'actions' => ['dashboard'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionDashboard()
    {
        $user = \Yii::$app->user->identity;
        $db = \Yii::$app->db;
        $appointments = [];
        if ($user->role === 'doctor') {
            $appointments = $db->createCommand('SELECT * FROM appointments WHERE doctor_id=:id', [':id' => $user->id])->queryAll();
        } else if ($user->role === 'patient') {
            $appointments = $db->createCommand('SELECT * FROM appointments WHERE patient_id=:id', [':id' => $user->id])->queryAll();
        }
        return $this->render('dashboard', [
            'appointments' => $appointments,
        ]);
    }

    public function actionSettings($tab = 'working-days')
    {
        $user = \Yii::$app->user->identity;
        if ($user->role !== 'doctor') {
            throw new \yii\web\ForbiddenHttpException('Only doctors can access settings.');
        }
        $model = new \frontend\models\DoctorSettingsForm();
        $holidays = [];
        $holidayModel = new \frontend\models\DoctorHolidayForm();
        $db = Yii::$app->db;

        if ($tab === 'working-days') {
            if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
                $exists = $db->createCommand('SELECT id FROM doctor_settings WHERE doctor_id=:id', [':id' => $user->id])->queryScalar();
                if ($exists) {
                    $db->createCommand()->update('doctor_settings', [
                        'working_days' => $model->working_days,
                        'start_time' => $model->start_time,
                        'end_time' => $model->end_time,
                        'break_time_start' => $model->break_time_start,
                        'break_time_end' => $model->break_time_end,
                        'updated_at' => time(),
                    ], 'doctor_id=:id', [':id' => $user->id])->execute();
                } else {
                    $db->createCommand()->insert('doctor_settings', [
                        'doctor_id' => $user->id,
                        'working_days' => $model->working_days,
                        'start_time' => $model->start_time,
                        'end_time' => $model->end_time,
                        'break_time_start' => $model->break_time_start,
                        'break_time_end' => $model->break_time_end,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ])->execute();
                }
                Yii::$app->session->setFlash('success', 'Settings saved!');
            } else {
                $row = $db->createCommand('SELECT * FROM doctor_settings WHERE doctor_id=:id', [':id' => $user->id])->queryOne();
                if ($row) {
                    $model->working_days = $row['working_days'] ? explode(',', $row['working_days']) : [];
                    $model->start_time = $row['start_time'];
                    $model->end_time = $row['end_time'];
                    $model->break_time_start = $row['break_time_start'];
                    $model->break_time_end = $row['break_time_end'];
                }
            }
        } elseif ($tab === 'holidays') {
            if (Yii::$app->request->isPost && $holidayModel->load(Yii::$app->request->post())) {
                if ($holidayModel->validate()) {
                    $db->createCommand()->insert('doctor_holidays', [
                        'doctor_id' => $user->id,
                        'date' => $holidayModel->date,
                        'description' => $holidayModel->description,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ])->execute();
                    Yii::$app->session->setFlash('success', 'Holiday added!');
                    return $this->redirect(['settings', 'tab' => 'holidays']);
                }
            }
            $holidays = $db->createCommand('SELECT * FROM doctor_holidays WHERE doctor_id=:id ORDER BY date DESC', [':id' => $user->id])->queryAll();
        }
        return $this->render('settings', [
            'model' => $model,
            'tab' => $tab,
            'holidays' => $holidays,
            'holidayModel' => $holidayModel,
        ]);
    }

    public function actionBook()
    {
        $user = \Yii::$app->user->identity;
        $db = \Yii::$app->db;
        if ($user->role === 'doctor') {
            // Doctor can only book for themselves
            $patients = $db->createCommand('SELECT id, username FROM user WHERE role=\'patient\'')->queryAll();
            $doctors = [
                ['id' => $user->id, 'username' => $user->username]
            ];
        } else if ($user->role === 'patient') {
            $patients = [];
            $doctors = $db->createCommand('SELECT id, username FROM user WHERE role=\'doctor\'')->queryAll();
        } else {
            throw new \yii\web\ForbiddenHttpException('Only doctors and patients can book appointments.');
        }
        $doctorData = [];
        foreach ($doctors as $doc) {
            $settings = $db->createCommand('SELECT * FROM doctor_settings WHERE doctor_id=:id', [':id' => $doc['id']])->queryOne();
            $doctorData[$doc['id']] = [
                'username' => $doc['username'],
                'working_days' => $settings ? $settings['working_days'] : '',
                'start_time' => $settings ? $settings['start_time'] : '',
                'end_time' => $settings ? $settings['end_time'] : '',
                'break_time_start' => $settings ? $settings['break_time_start'] : '',
                'break_time_end' => $settings ? $settings['break_time_end'] : '',
            ];
        }
        // Handle appointment booking POST
        if (Yii::$app->request->isPost) {
            $doctor_id = $user->role === 'doctor' ? $user->id : Yii::$app->request->post('doctor_id');
            $date = Yii::$app->request->post('date');
            $time = Yii::$app->request->post('time');
            $phone = Yii::$app->request->post('phone');
            $duration = (int)Yii::$app->request->post('duration', 10);
            $patient_id = $user->role === 'doctor' ? Yii::$app->request->post('patient_id') : $user->id;
            // Prevent booking in the past
            if (strtotime($date) < strtotime(date('Y-m-d'))) {
                Yii::$app->session->setFlash('error', 'Cannot book appointment in the past.');
            } else {
                // Validate selected date is a working day for the doctor
                $settings = $db->createCommand('SELECT working_days, break_time_start, break_time_end FROM doctor_settings WHERE doctor_id=:id', [':id' => $doctor_id])->queryOne();
                if ($settings && $settings['working_days']) {
                    $workingDays = array_map('trim', explode(',', $settings['working_days']));
                    $dayShort = date('D', strtotime($date)); // e.g. Mon, Tue, etc.
                    if (!in_array($dayShort, $workingDays)) {
                        Yii::$app->session->setFlash('error', 'Selected date is not a working day for this doctor.');
                        return $this->redirect(['book']);
                    }
                }
                // Check for time slot conflict
                $exists = $db->createCommand('SELECT COUNT(*) FROM appointments WHERE doctor_id=:doctor_id AND date=:date AND time=:time', [
                    ':doctor_id' => $doctor_id,
                    ':date' => $date,
                    ':time' => $time,
                ])->queryScalar();
                if ($exists) {
                    Yii::$app->session->setFlash('error', 'This time slot is already booked. Please choose another.');
                } else {
                    // Check if time is in doctor's break time
                    $settings = $db->createCommand('SELECT break_time_start, break_time_end FROM doctor_settings WHERE doctor_id=:id', [':id' => $doctor_id])->queryOne();
                    if ($settings && $settings['break_time_start'] && $settings['break_time_end']) {
                        if ($time >= $settings['break_time_start'] && $time < $settings['break_time_end']) {
                            Yii::$app->session->setFlash('error', 'Cannot book appointment during doctor\'s break time.');
                            return $this->redirect(['book']);
                        }
                    }
                    $db->createCommand()->insert('appointments', [
                        'doctor_id' => $doctor_id,
                        'patient_id' => $patient_id,
                        'phone' => $phone,
                        'date' => $date,
                        'time' => $time,
                        'duration' => $duration,
                        'status' => 'pending',
                        'created_at' => time(),
                        'updated_at' => time(),
                    ])->execute();
                    Yii::$app->session->setFlash('success', 'Appointment booked successfully!');
                    return $this->redirect(['book']);
                }
            }
        }
        return $this->render('book', [
            'doctors' => $doctors,
            'doctorData' => $doctorData,
            'patients' => $patients,
        ]);
    }

    public function actionUpdateAppointmentStatus()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $data = json_decode(Yii::$app->request->getRawBody(), true);
        if (!isset($data['id'], $data['status'])) {
            return ['success' => false, 'error' => 'Invalid data'];
        }
        $id = (int)$data['id'];
        $status = $data['status'];
        $allowed = ['Cancelled', 'Payment Pending', 'Done'];
        if (!in_array($status, $allowed)) {
            return ['success' => false, 'error' => 'Invalid status'];
        }
        $user = Yii::$app->user->identity;
        $appt = Yii::$app->db->createCommand('SELECT * FROM appointments WHERE id=:id', [':id' => $id])->queryOne();
        if (!$appt || ($appt['doctor_id'] != $user->id && $appt['patient_id'] != $user->id)) {
            return ['success' => false, 'error' => 'Not allowed'];
        }
        $rows = Yii::$app->db->createCommand()->update(
            'appointments',
            ['status' => $status, 'updated_at' => time()],
            'id = :id',
            [':id' => $id]
        )->execute();
        if ($rows > 0) {
            return ['success' => true];
        } else {
            return ['success' => false, 'error' => 'Update failed'];
        }
    }
}
