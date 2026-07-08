<!DOCTYPE html>
<html>
<head>
    <title>Company Profile</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>

<div class="container mt-4">

    <div class="card">

        <div class="card-header">
            <h3>Company Profile</h3>
        </div>

        <div class="card-body">

            <form method="post"
                  action="<?= base_url('company/update') ?>">

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Brand Name
                        </label>

                        <input
                            type="text"
                            name="company_name"
                            class="form-control"
                            value="<?= esc($company['company_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Legal Name
                        </label>

                        <input
                            type="text"
                            name="legal_name"
                            class="form-control"
                            value="<?= esc($company['legal_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">
                            Address
                        </label>

                        <textarea
                            name="address"
                            class="form-control"
                            rows="3"><?= esc($company['address'] ?? '') ?></textarea>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            City
                        </label>

                        <input
                            type="text"
                            name="city"
                            class="form-control"
                            value="<?= esc($company['city'] ?? '') ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Country
                        </label>

                        <input
                            type="text"
                            name="country"
                            class="form-control"
                            value="<?= esc($company['country'] ?? '') ?>">
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            value="<?= esc($company['phone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="<?= esc($company['email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Website
                        </label>

                        <input
                            type="text"
                            name="website"
                            class="form-control"
                            value="<?= esc($company['website'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            NPWP
                        </label>

                        <input
                            type="text"
                            name="tax_number"
                            class="form-control"
                            value="<?= esc($company['tax_number'] ?? '') ?>">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            VAT Number
                        </label>

                        <input
                            type="text"
                            name="vat_number"
                            class="form-control"
                            value="<?= esc($company['vat_number'] ?? '') ?>">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label">
                            Invoice Notes
                        </label>

                        <textarea
                            name="invoice_notes"
                            class="form-control"
                            rows="3"><?= esc($company['invoice_notes'] ?? '') ?></textarea>
                    </div>

                </div>

                <button
                    type="submit"
                    class="btn btn-primary">

                    Save Company Profile

                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>