<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;
use backend\models\DoctorForm;

class DoctorController extends Controller
{
    public function actionIndex()
    {
        $doctors = Yii::$app->db->createCommand('SELECT * FROM user WHERE role = "doctor"')->queryAll();
        return $this->render('index', ['doctors' => $doctors]);
    }

    public function actionCreate()
    {
        $model = new DoctorForm(['scenario' => 'create']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $hash = Yii::$app->security->generatePasswordHash($model->password);
            Yii::$app->db->createCommand()->insert('user', [
                'username' => $model->username,
                'email' => $model->email,
                'password_hash' => $hash,
                'auth_key' => Yii::$app->security->generateRandomString(),
                'role' => 'doctor',
                'status' => 10,
                'created_at' => time(),
                'updated_at' => time(),
            ])->execute();
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model, 'isNew' => true]);
    }

    public function actionUpdate($id)
    {
        $doctor = Yii::$app->db->createCommand('SELECT * FROM user WHERE id=:id AND role="doctor"', [':id' => $id])->queryOne();
        if (!$doctor) throw new \yii\web\NotFoundHttpException('Doctor not found');
        $model = new DoctorForm();
        $model->username = $doctor['username'];
        $model->email = $doctor['email'];
        $model->password = '';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $fields = [
                'username' => $model->username,
                'email' => $model->email,
                'updated_at' => time(),
            ];
            if ($model->password) {
                $fields['password_hash'] = Yii::$app->security->generatePasswordHash($model->password);
            }
            Yii::$app->db->createCommand()->update('user', $fields, 'id=:id', [':id' => $id])->execute();
            return $this->redirect(['index']);
        }
        return $this->render('form', ['model' => $model, 'isNew' => false]);
    }

    public function actionDelete($id)
    {
        Yii::$app->db->createCommand()->delete('user', 'id=:id AND role="doctor"', [':id' => $id])->execute();
        return $this->redirect(['index']);
    }

    public function actionAppointments($id)
    {
        $doctor = Yii::$app->db->createCommand('SELECT * FROM user WHERE id=:id AND role="doctor"', [':id' => $id])->queryOne();
        if (!$doctor) throw new \yii\web\NotFoundHttpException('Doctor not found');
        $today = date('Y-m-d');
        $appointments = Yii::$app->db->createCommand('SELECT * FROM appointments WHERE doctor_id=:id', [':id' => $id])->queryAll();
        $todays = [];
        $past = [];
        $future = [];
        foreach ($appointments as $appt) {
            if ($appt['date'] == $today) {
                $todays[] = $appt;
            } elseif ($appt['date'] < $today) {
                $past[] = $appt;
            } else {
                $future[] = $appt;
            }
        }
        $patientIds = array_unique(array_column($appointments, 'patient_id'));
        $patientNames = [];
        if ($patientIds) {
            $in = implode(',', array_map('intval', $patientIds));
            $rows = Yii::$app->db->createCommand("SELECT id, username FROM user WHERE id IN ($in)")->queryAll();
            foreach ($rows as $row) {
                $patientNames[$row['id']] = $row['username'];
            }
        }
        return $this->renderPartial('appointments_popup', [
            'doctor' => $doctor,
            'todays' => $todays,
            'past' => $past,
            'future' => $future,
            'patientNames' => $patientNames,
        ]);
    }
}
