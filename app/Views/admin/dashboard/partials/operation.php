<div class="row mt-4">

    <!-- Recent Shipment -->
    <div class="col-lg-6">

        <div class="card shadow">

            <div class="card-header bg-primary text-white">

                📦 Recent Shipment

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th>AWB</th>
                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($recentShipments as $row): ?>

                        <tr>

                            <td>
                                <strong><?= esc($row['awb_no']) ?></strong>
                            </td>

                            <td>

                                <?php

                                $color='secondary';

                                switch($row['shipment_status']){

                                    case 'CREATED':
                                        $color='primary';
                                        break;

                                    case 'PICKED_UP':
                                        $color='info';
                                        break;

                                    case 'IN_TRANSIT':
                                        $color='warning';
                                        break;

                                    case 'DELIVERED':
                                        $color='success';
                                        break;

                                }

                                ?>

                                <span class="badge bg-<?= $color ?>">

                                    <?= esc($row['shipment_status']) ?>

                                </span>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- Recent Payment -->
    <div class="col-lg-6">

        <div class="card shadow">

            <div class="card-header bg-success text-white">

                💰 Recent Payment

            </div>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th>Payment</th>
                            <th>Amount</th>
                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($recentPayments as $row): ?>

                        <tr>

                            <td>

                                <?= esc($row['payment_no']) ?>

                            </td>

                            <td>

                                Rp <?= number_format($row['amount']) ?>

                            </td>

                            <td>

                                <?php if($row['status']=='APPROVED'): ?>

                                    <span class="badge bg-success">

                                        APPROVED

                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">

                                        <?= esc($row['status']) ?>

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>