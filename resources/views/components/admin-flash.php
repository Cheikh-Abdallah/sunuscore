<?php
use App\Core\Session;
$success = Session::get('flash_success'); Session::remove('flash_success');
$error   = Session::get('flash_error');   Session::remove('flash_error');
?>
<?php if ($success): ?>
    <div class="admin-flash admin-flash-success"><?= e($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="admin-flash admin-flash-error"><?= e($error) ?></div>
<?php endif; ?>