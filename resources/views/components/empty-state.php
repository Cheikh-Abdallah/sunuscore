<?php
/**
 * Variables attendues : $title, $message (optionnels)
 */
$title = $title ?? 'Rien à afficher';
$message = $message ?? 'Les données apparaîtront ici dès qu\'elles seront disponibles.';
?>
<div class="empty-state">
    <h3><?= e($title) ?></h3>
    <p><?= e($message) ?></p>
</div>