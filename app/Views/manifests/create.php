<?= $this->extend('admin/layouts/app') ?>

<?= $this->section('content') ?>

<?php
$selectedDestination = (int) old(
    'destination_branch_id',
    $mainBranch['id'] ?? 0
);
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1">📦 Create Branch Manifest</h3>
            <div class="text-muted">
                Origin branch otomatis mengikuti Operational Profile user.
            </div>
        </div>

        <a
            href="<?= site_url('manifests') ?>"
            class="btn btn-outline-secondary">
            Back
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form
                method="post"
                action="<?= site_url('manifests/store') ?>">

                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Origin Branch
                        </label>

                        <input
                            type="text"
                            class="form-control"
                            value="<?= esc(
                                ($originBranch['branch_code'] ?? '-') .
                                ' · ' .
                                ($originBranch['branch_name'] ?? '-')
                            ) ?>"
                            readonly>
                    </div>

                    <div class="col-md-6">
                        <label
                            for="destination_branch_id"
                            class="form-label">
                            Destination Branch *
                        </label>

                        <select
                            class="form-select"
                            id="destination_branch_id"
                            name="destination_branch_id"
                            required>

                            <option value="">
                                -- Select Destination --
                            </option>

                            <?php foreach (
                                $destinationBranches
                                as $branch
                            ): ?>
                                <option
                                    value="<?= (int) $branch['id'] ?>"
                                    <?= $selectedDestination
                                        === (int) $branch['id']
                                        ? 'selected'
                                        : '' ?>>

                                    <?= esc($branch['_label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label
                            for="manifest_date"
                            class="form-label">
                            Manifest Date *
                        </label>

                        <input
                            type="date"
                            class="form-control"
                            id="manifest_date"
                            name="manifest_date"
                            value="<?= esc(
                                old('manifest_date')
                                ?? date('Y-m-d')
                            ) ?>"
                            required>
                    </div>

                    <div class="col-12">
                        <label
                            for="remarks"
                            class="form-label">
                            Remarks
                        </label>

                        <textarea
                            class="form-control"
                            id="remarks"
                            name="remarks"
                            rows="3"
                            maxlength="1000"><?= esc(
                                old('remarks') ?? ''
                            ) ?></textarea>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    Manifest hanya dapat mengambil shipment yang
                    sedang berada di
                    <strong>
                        <?= esc(
                            $originBranch['branch_name']
                            ?? 'origin branch'
                        ) ?>
                    </strong>
                    dan berstatus
                    <strong>RECEIVED_AT_ORIGIN_BRANCH</strong>.
                </div>

                <button
                    type="submit"
                    class="btn btn-success">
                    Create Manifest
                </button>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
