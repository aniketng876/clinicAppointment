<?php
use yii\helpers\Html;
use yii\helpers\Url;
$this->title = 'Patients';
?>
<h1>Patients</h1>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($patients as $patient): ?>
        <tr>
            <td><?= Html::encode($patient['username']) ?></td>
            <td><?= Html::encode($patient['email']) ?></td>
            <td>
                <?= Html::a('Update', ['update', 'id' => $patient['id']], ['class' => 'btn btn-primary btn-sm']) ?>
                <button class="btn btn-info btn-sm view-appts" data-url="<?= Url::to(['appointments', 'id' => $patient['id']]) ?>">Appointments</button>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<script>
document.querySelectorAll('.view-appts').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var url = this.getAttribute('data-url');
        window.open(url, '_blank', 'width=900,height=600,scrollbars=yes');
    });
});
</script>
