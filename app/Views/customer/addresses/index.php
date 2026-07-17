<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Addresses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="mb-1">📍 My Addresses</h3>
            <div class="text-muted"><?= esc($customer['customer_name'] ?? '-') ?></div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= site_url('customer/addresses/create') ?>" class="btn btn-primary">➕ Add Address</a>
            <a href="<?= site_url('customer/dashboard') ?>" class="btn btn-outline-secondary">Dashboard</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>Type</th>
                        <th>Label / Contact</th>
                        <th>Location</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($addresses)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada alamat tersimpan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($addresses as $address): ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= esc($address['address_type'] ?? '-') ?></span></td>
                                <td>
                                    <strong><?= esc($address['address_label'] ?? '-') ?></strong><br>
                                    <span><?= esc($address['contact_name'] ?? '-') ?></span>
                                    <?php if (!empty($address['company_name'])): ?>
                                        <div class="text-muted small"><?= esc($address['company_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= esc($address['city'] ?? '-') ?>,
                                    <?= esc($address['country_name'] ?? '-') ?>
                                </td>
                                <td><?= esc($address['phone'] ?? '-') ?></td>
                                <td>
                                    <?php if ((int) ($address['is_default'] ?? 0) === 1): ?>
                                        <span class="badge bg-success">DEFAULT</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">ACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if ((int) ($address['is_default'] ?? 0) !== 1): ?>
                                        <form method="post" action="<?= site_url('customer/addresses/default/' . (int) $address['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-sm btn-outline-success" type="submit">⭐ Default</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="<?= site_url('customer/addresses/edit/' . (int) $address['id']) ?>" class="btn btn-sm btn-outline-primary">✏️ Edit</a>
                                    <form method="post" action="<?= site_url('customer/addresses/delete/' . (int) $address['id']) ?>" class="d-inline" onsubmit="return confirm('Nonaktifkan alamat ini?')">
                                        <?= csrf_field() ?>
                                        <button class="btn btn-sm btn-outline-danger" type="submit">🗑️ Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
