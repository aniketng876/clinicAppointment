<?php
use yii\helpers\Html;
use yii\helpers\Url;
$this->title = 'Doctors';
?>
<h1>Doctors</h1>
<p>
    <?= Html::a('Add Doctor', ['create'], ['class' => 'btn btn-success']) ?>
</p>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($doctors as $doctor): ?>
        <tr>
            <td><?= Html::encode($doctor['username']) ?></td>
            <td><?= Html::encode($doctor['email']) ?></td>
            <td>
                <?= Html::a('Update', ['update', 'id' => $doctor['id']], ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('Delete', ['delete', 'id' => $doctor['id']], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this doctor?',
                        'method' => 'post',
                    ],
                ]) ?>
                <button class="btn btn-info btn-sm view-appts" data-id="<?= $doctor['id'] ?>" data-url="<?= Url::to(['appointments', 'id' => $doctor['id']]) ?>">Appointments</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<script>
document.querySelectorAll('.view-appts').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = this.getAttribute('data-url');
        var win = window.open(url, '_blank', 'width=900,height=600,scrollbars=yes');
    });
});
</script>
