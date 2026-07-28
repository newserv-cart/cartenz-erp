<div class="topbar d-flex justify-content-between align-items-center">

<div>

<?php
$panelTitle = strtoupper((string) session('owner_type')) === 'AGENT'
    ? 'Agent Panel'
    : 'Administrator Panel';
?>

<h5 class="mb-0">

<?= esc($panelTitle) ?>

</h5>

</div>

<div>

Welcome,

<strong>

<?= auth()->loggedIn() ? esc(auth()->user()->username) : 'Guest' ?>
</strong>

</div>

</div>