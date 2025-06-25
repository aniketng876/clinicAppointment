<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;

class PatientController extends Controller
{
    public function actionIndex()
    {
        $patients = Yii::$app->db->createCommand('SELECT * FROM user WHERE role = "patient"')->queryAll();
        return $this->render('index', ['patients' => $patients]);
    }

    public function actionUpdate($id)
    {
        $patient = Yii::$app->db->createCommand('SELECT * FROM user WHERE id=:id AND role="patient"', [':id' => $id])->queryOne();
        if (!$patient) throw new \yii\web\NotFoundHttpException('Patient not found');
        $model = new \backend\models\PatientForm();
        $model->username = $patient['username'];
        $model->email = $patient['email'];
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

    public function actionAppointments($id)
    {
        $patient = Yii::$app->db->createCommand('SELECT * FROM user WHERE id=:id AND role="patient"', [':id' => $id])->queryOne();
        if (!$patient) throw new \yii\web\NotFoundHttpException('Patient not found');
        $today = date('Y-m-d');
        $appointments = Yii::$app->db->createCommand('SELECT * FROM appointments WHERE patient_id=:id', [':id' => $id])->queryAll();
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
        $doctorIds = array_unique(array_column($appointments, 'doctor_id'));
        $doctorNames = [];
        if ($doctorIds) {
            $in = implode(',', array_map('intval', $doctorIds));
            $rows = Yii::$app->db->createCommand("SELECT id, username FROM user WHERE id IN ($in)")->queryAll();
            foreach ($rows as $row) {
                $doctorNames[$row['id']] = $row['username'];
            }
        }
        return $this->renderPartial('appointments_popup', [
            'patient' => $patient,
            'todays' => $todays,
            'past' => $past,
            'future' => $future,
            'doctorNames' => $doctorNames,
        ]);
    }
}
