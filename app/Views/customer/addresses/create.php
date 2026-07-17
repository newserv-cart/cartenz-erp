<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Address</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">📍 Add Address</h3>
        <a href="<?= site_url('customer/addresses') ?>" class="btn btn-outline-secondary">Back</a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= site_url('customer/addresses/store') ?>" class="card shadow-sm">
        <?= csrf_field() ?>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Address Type *</label>
                    <select name="address_type" class="form-select" required>
                        <option value="SENDER" <?= old('address_type') === 'SENDER' ? 'selected' : '' ?>>Sender</option>
                        <option value="RECEIVER" <?= old('address_type') === 'RECEIVER' ? 'selected' : '' ?>>Receiver</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Label *</label>
                    <input type="text" name="address_label" class="form-control" required value="<?= esc(old('address_label')) ?>" placeholder="Rumah / Kantor / Gudang">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault" <?= old('is_default') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="isDefault">Set as default</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contact Name *</label>
                    <input type="text" name="contact_name" class="form-control" required value="<?= esc(old('contact_name')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="<?= esc(old('company_name')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control" required value="<?= esc(old('phone')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= esc(old('email')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Identity Number</label>
                    <input type="text" name="identity_number" class="form-control" value="<?= esc(old('identity_number')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Country *</label>
                    <select name="country_id" class="form-select" required>
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= (int) $country['id'] ?>" <?= (string) old('country_id') === (string) $country['id'] ? 'selected' : '' ?>><?= esc($country['country_name'] ?? ('Country #' . $country['id'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">State / Province</label>
                    <input type="text" name="state" class="form-control" value="<?= esc(old('state')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?= esc(old('city')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Postal Code</label>
                    <input type="text" name="postal_code" class="form-control" value="<?= esc(old('postal_code')) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Full Address *</label>
                    <textarea name="address" class="form-control" rows="4" required><?= esc(old('address')) ?></textarea>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white">
            <button type="submit" class="btn btn-primary">💾 Save Address</button>
        </div>
    </form>
</div>
</body>
</html>
