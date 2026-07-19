<!DOCTYPE html>
<html>
<head>
    <title>Invoice Detail</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Tambahan agar tampilan lebih rapi saat dicetak juga */
        @media print {
            .btn { display: none !important; }
            .card { border: none !important; }
            .card-header { border-bottom: 2px solid #dee2e6 !important; }
        }
    </style>
</head>
<body>

<div class="container mt-4 mb-4">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <!-- Logo Perusahaan -->
                <div>
                    <?php if(!empty($company['logo'])): ?>
                        <img src="<?= base_url('uploads/company/'.$company['logo']) ?>" style="height:80px; object-fit:contain;">
                    <?php endif; ?>
                </div>

                <!-- Judul & Nomor Invoice -->
                <div class="text-center">
                    <h2 class="mb-0">INVOICE</h2>
                    <small class="text-muted fs-6"><?= esc($invoice['invoice_no'] ?? '-') ?></small>
                </div>

                <!-- Status & Tombol -->
                <div class="text-end">
                    <p class="mb-2">
                        <strong>Status:</strong><br>
                        <?php
                        $status = $invoice['invoice_status'] ?? '';
                        $badgeClass = match($status) {
                            'PAID' => 'success',
                            'PARTIAL' => 'warning',
                            default => 'danger'
                        };
                        ?>
                        <span class="badge bg-<?= $badgeClass ?> fs-6">
                            <?= esc($status ?: 'UNPAID') ?>
                        </span>
                    </p>

                    <div class="d-flex gap-2">
                        <a href="#" onclick="window.print();" class="btn btn-primary btn-sm">
                            Print Invoice
                        </a>
                        <a href="<?= base_url('invoices/pdf/'.$invoice['id']) ?>" class="btn btn-danger btn-sm" target="_blank">
                            PDF Invoice
                        </a>
                        <?php if (($invoice['invoice_status'] ?? '') !== 'PAID'): ?>
                            <a href="<?= base_url('payments/create/'.($invoice['id'] ?? '')) ?>" class="btn btn-success btn-sm">
                                Receive Payment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-body">

            <!-- Info Perusahaan & Customer -->
            <div class="row mb-4 align-items-start">
                <div class="col-md-7">
                    <h4 class="mb-1"><?= esc($company['company_name']) ?></h4>
                    <strong><?= esc($company['legal_name'] ?? '') ?></strong><br>
                    <?= esc($company['address'] ?? '-') ?><br>
                    Tel: <?= esc($company['phone'] ?? '-') ?><br>
                    Email: <?= esc($company['email'] ?? '-') ?>
                </div>

                <div class="col-md-5 mt-3 mt-md-0">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td width="130">Customer / Agen</td>
                            <td width="10">:</td>
                            <td><strong><?= esc($invoice['customer_name'] ?? '-') ?></strong></td>
                        </tr>
                        <tr>
                            <td>Alamat</td>
                            <td>:</td>
                            <td><?= nl2br(esc($invoice['address'] ?? '-')) ?></td>
                        </tr>
                        <tr>
                            <td>Kontak</td>
                            <td>:</td>
                            <td><?= esc($invoice['phone'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>NPWP</td>
                            <td>:</td>
                            <td><?= esc($invoice['tax_number'] ?? '-') ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <hr>

            <!-- Tanggal Invoice -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td width="120">No Invoice</td>
                            <td width="10">:</td>
                            <td><?= esc($invoice['invoice_no'] ?? '-') ?></td>
                        </tr>
                        <tr>
                            <td>Invoice Date</td>
                            <td>:</td>
                            <td><?= !empty($invoice['invoice_date']) ? date('d-m-Y', strtotime($invoice['invoice_date'])) : '-' ?></td>
                        </tr>
                        <tr>
                            <td>Due Date</td>
                            <td>:</td>
                            <td><?= !empty($invoice['due_date']) ? date('d-m-Y', strtotime($invoice['due_date'])) : '-' ?></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6 text-end">
                    <!-- Tempat untuk Barcode nanti -->
                </div>
            </div>

            <!-- Tabel Daftar Pengiriman -->
            <table class="table table-bordered table-sm">
                <thead>
                    <tr class="table-light">
                        <th>Date</th>
                        <th>AWB</th>
                        <th>Receiver</th>
                        <th>Country</th>
                        <th>Package Type</th>
                        <th class="text-end">Weight</th>
                        <th class="text-end">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($items)): ?>
                        <?php foreach($items as $item): ?>
                            <?php
                            $receiverName = trim((string) ($item['receiver_name'] ?? ''));
                            $countryName = trim((string) ($item['country_name'] ?? ''));
                            $packageTypeName = trim((string) ($item['package_type_name'] ?? ''));
                            $shipmentDate = $item['shipment_date'] ?? $item['shipment_created_at'] ?? null;
                            ?>
                            <tr>
                                <td><?= $shipmentDate ? date('d-m-Y', strtotime($shipmentDate)) : '-' ?></td>
                                <td><?= esc($item['awb_no'] ?? '-') ?></td>
                                <td><?= esc($receiverName !== '' ? $receiverName : '-') ?></td>
                                <td><?= esc($countryName !== '' ? $countryName : '-') ?></td>
                                <td><?= esc($packageTypeName !== '' ? $packageTypeName : '-') ?></td>
                                <td class="text-end"><?= number_format($item['invoice_weight'] ?? 0, 2, ',', '.') ?></td>
                                <td class="text-end"><?= number_format($item['amount'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Subtotal</th>
                        <th class="text-end"><?= number_format($invoice['total_amount'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-end">VAT (<?= $invoice['vat_percent'] ?? 0 ?>%)</th>
                        <th class="text-end"><?= number_format($invoice['vat_amount'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-end">Extra Charge</th>
                        <th class="text-end"><?= number_format($invoice['extra_charge'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                    <tr class="table-secondary">
                        <th colspan="6" class="text-end">GRAND TOTAL</th>
                        <th class="text-end"><?= number_format($invoice['grand_total'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-end">Paid</th>
                        <th class="text-end"><?= number_format($invoice['paid_amount'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                    <tr>
                        <th colspan="6" class="text-end">Balance</th>
                        <th class="text-end"><?= number_format($invoice['balance_amount'] ?? 0, 0, ',', '.') ?></th>
                    </tr>
                </tfoot>
            </table>

            <hr class="my-4">

            <!-- Payment Information -->
            <?php
            $bankName = trim(
                (string) ($bankAccount['bank_name'] ?? '')
            );

            $accountName = trim(
                (string) ($bankAccount['account_name'] ?? '')
            );

            $accountNumber = trim(
                (string) ($bankAccount['account_number'] ?? '')
            );

            $swiftCode = trim(
                (string) ($bankAccount['swift_code'] ?? '')
            );

            $currency = trim(
                (string) ($bankAccount['currency'] ?? '')
            );
            ?>

            <div class="mt-4">
                <h5 class="mb-3">
                    Payment Information
                </h5>

                <?php if ($bankAccount !== []): ?>

                    <table class="table table-sm table-borderless">
                        <tr>
                            <td style="width: 160px;">Bank</td>
                            <td style="width: 15px;">:</td>
                            <td>
                                <strong><?= esc($bankName) ?></strong>
                            </td>
                        </tr>

                        <tr>
                            <td>Account Name</td>
                            <td>:</td>
                            <td>
                                <strong><?= esc($accountName) ?></strong>
                            </td>
                        </tr>

                        <tr>
                            <td>Account Number</td>
                            <td>:</td>
                            <td>
                                <strong><?= esc($accountNumber) ?></strong>
                            </td>
                        </tr>

                        <tr>
                            <td>Currency</td>
                            <td>:</td>
                            <td><?= esc($currency ?: 'IDR') ?></td>
                        </tr>

                        <?php if ($swiftCode !== ''): ?>
                            <tr>
                                <td>SWIFT Code</td>
                                <td>:</td>
                                <td><?= esc($swiftCode) ?></td>
                            </tr>
                        <?php endif; ?>
                    </table>

                    <div class="alert alert-light border">
                        Mohon cantumkan nomor invoice pada berita transfer.
                    </div>

                <?php else: ?>

                    <div class="alert alert-warning">
                        Rekening pembayaran belum dikonfigurasi.
                    </div>

                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>