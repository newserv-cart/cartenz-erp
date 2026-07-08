<div class="row mt-4">

    <!-- Top Customer -->
    <div class="col-lg-6">

        <div class="card shadow h-100">

            <div class="card-header bg-primary text-white d-flex justify-content-between">

                <span>🏆 Top Customer</span>

                <a href="<?= site_url('reports/customer') ?>"
                   class="text-white text-decoration-none">

                    View All →

                </a>

            </div>

            <table class="table table-hover mb-0">

                <thead>

                    <tr>

                        <th>Customer</th>
                        <th class="text-end">Shipment</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach($topCustomers as $row): ?>

                    <tr>

                        <td><?= esc($row['customer_name']) ?></td>

                        <td class="text-end">

                            <?= number_format($row['total']) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- Top Agent -->

    <div class="col-lg-6">

        <div class="card shadow h-100">

            <div class="card-header bg-success text-white d-flex justify-content-between">

                <span>🏢 Top Agent</span>

                <a href="<?= site_url('reports/agent') ?>"
                   class="text-white text-decoration-none">

                    View All →

                </a>

            </div>

            <table class="table table-hover mb-0">

                <thead>

                    <tr>

                        <th>Agent</th>
                        <th class="text-end">Shipment</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach($topAgents as $row): ?>

                    <tr>

                        <td><?= esc($row['agent_name']) ?></td>

                        <td class="text-end">

                            <?= number_format($row['total']) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<div class="row mt-4">

    <!-- Destination -->

    <div class="col-lg-6">

        <div class="card shadow h-100">

            <div class="card-header bg-warning">

                🌍 Top Destination

            </div>

            <table class="table table-hover mb-0">

                <thead>

                    <tr>

                        <th>Country</th>
                        <th class="text-end">Shipment</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach($topDestination as $row): ?>

                    <tr>

                        <td><?= esc($row['country_name']) ?></td>

                        <td class="text-end">

                            <?= number_format($row['total']) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

    <!-- Revenue -->

    <div class="col-lg-6">

        <div class="card shadow h-100">

            <div class="card-header bg-info text-white">

                💰 Revenue by Country

            </div>

            <table class="table table-hover mb-0">

                <thead>

                    <tr>

                        <th>Country</th>
                        <th class="text-end">Revenue</th>

                    </tr>

                </thead>

                <tbody>

                <?php foreach($countryRevenue as $row): ?>

                    <tr>

                        <td><?= esc($row['country_name']) ?></td>

                        <td class="text-end">

                            Rp <?= number_format($row['total']) ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>