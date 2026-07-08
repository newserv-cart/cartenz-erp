<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <form action="<?= site_url('master/express-charge-rules/update/'.$rule['id']) ?>" method="post">

        <div class="card shadow">

            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">✏ Edit Express Charge Rule</h4>
            </div>

            <div class="card-body">

                <?php if(session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach(session()->getFlashdata('errors') as $e): ?>
                                <li><?= esc($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?= $this->include('master/express_charge_rules/_form') ?>

                <hr>
                <div class="text-end">
                    <a href="<?= site_url('master/express-charge-rules') ?>" class="btn btn-secondary">← Back</a>
                    <button type="submit" class="btn btn-primary">💾 Update Rule</button>
                </div>

            </div>
        </div>

    </form>

</div>

<?= $this->endSection() ?>