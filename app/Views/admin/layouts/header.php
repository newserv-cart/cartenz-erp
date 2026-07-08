<div class="topbar d-flex justify-content-between align-items-center">

<div>

<h5 class="mb-0">

Administrator Panel

</h5>

</div>

<div>

Welcome,

<strong>

<?= auth()->loggedIn() ? esc(auth()->user()->username) : 'Guest' ?>
</strong>

</div>

</div>