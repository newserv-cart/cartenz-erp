<h2>
📦 Scan Manifest
</h2>

<?php if(session()->getFlashdata('error')): ?>

<div style="color:red">

<?= session()->getFlashdata('error') ?>

</div>

<?php endif; ?>

<form method="post">

<input
    type="text"
    name="manifest_no"
    autofocus
    style="
        width:400px;
        height:40px;
        font-size:20px;
    "
>

<button>
    Cari Manifest
</button>

</form>