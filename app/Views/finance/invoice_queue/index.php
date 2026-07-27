<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<div class="container-fluid">

    <div class="mb-4">
        <h3>Customer Invoice Queue</h3>

        <div class="text-muted">
            Shipment dengan pricing FINAL dan payment type INVOICE.
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>

    <form
        method="post"
        action="<?= site_url('inbound-intake/reprice/79') ?>"
        class="mb-3"
    >
        <?= csrf_field() ?>

        <button
            type="submit"
            class="btn btn-warning"
        >
            Recalculate Pricing CTZ260700039
        </button>
    </form>

    <?php if (empty($groups)): ?>

        <div class="alert alert-info">
            Tidak ada shipment yang menunggu invoice.
        </div>

    <?php else: ?>

        <?php foreach ($groups as $group): ?>

            <?php
            $invoiceDate = date('Y-m-d');

            $creditDays = max(
                0,
                (int) ($group['credit_days'] ?? 0)
            );

            $dueDate = date(
                'Y-m-d',
                strtotime('+' . $creditDays . ' days')
            );
            ?>

            <form
                method="post"
                action="<?= site_url('finance/invoice-queue/create') ?>"
                class="card shadow-sm mb-4"
            >
                <?= csrf_field() ?>

                <div class="card-header">
                    <strong>
                        <?= esc($group['customer_name']) ?>
                    </strong>

                    <span class="text-muted">
                        &middot; <?= count($group['shipments']) ?> shipment
                    </span>
                </div>

                <div class="card-body">

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th width="50">Select</th>
                                    <th>AWB</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Chargeable</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php foreach ($group['shipments'] as $shipment): ?>
                                <tr>
                                    <td>
                                        <input
                                            type="checkbox"
                                            name="shipment_ids[]"
                                            value="<?= (int) $shipment['id'] ?>"
                                            checked
                                        >
                                    </td>

                                    <td>
                                        <?= esc($shipment['awb_no']) ?>
                                    </td>

                                    <td>
                                        <?= esc($shipment['shipment_status']) ?>
                                    </td>

                                    <td>
                                        <?= esc($shipment['shipment_date'] ?? '-') ?>
                                    </td>

                                    <td>
                                        <?= number_format(
                                            (float) ($shipment['chargeable_weight'] ?? 0),
                                            2,
                                            ',',
                                            '.'
                                        ) ?> Kg
                                    </td>

                                    <td class="text-end">
                                        Rp <?= number_format(
                                            (float) ($shipment['billable_amount'] ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-end">
                                        Available Total
                                    </th>

                                    <th class="text-end">
                                        Rp <?= number_format(
                                            (float) ($group['total'] ?? 0),
                                            0,
                                            ',',
                                            '.'
                                        ) ?>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row g-3 align-items-end">

                        <div class="col-md-3">
                            <label class="form-label">
                                Invoice Date
                            </label>

                            <input
                                type="date"
                                name="invoice_date"
                                class="form-control"
                                value="<?= esc($invoiceDate) ?>"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">
                                Due Date
                            </label>

                            <input
                                type="date"
                                name="due_date"
                                class="form-control"
                                value="<?= esc($dueDate) ?>"
                                required
                            >
                        </div>

                        <div class="col-md-3">
                            <button
                                type="submit"
                                class="btn btn-primary"
                                onclick="return confirm(
                                    'Buat invoice dari shipment terpilih?'
                                );"
                            >
                                Create Invoice
                            </button>
                        </div>

                    </div>

                </div>
            </form>

        <?php endforeach; ?>

    <?php endif; ?>

</div>

<?= $this->endSection() ?>
