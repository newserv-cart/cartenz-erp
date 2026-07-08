<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

// ===============================
// SHIPMENT TREND
// ===============================
new Chart(document.getElementById('shipmentChart'), {

    type: 'line',

    data: {

        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],

        datasets: [{

            label: 'Shipment',

            data: [
                12,
                19,
                7,
                14,
                22,
                18,
                <?= $todayShipments ?>
            ],

            fill: true,

            tension: 0.4

        }]

    },

    options: {

        responsive: true,

        plugins: {

            legend: {

                display: true

            }

        }

    }

});


// ===============================
// REVENUE TREND
// ===============================

new Chart(document.getElementById('revenueChart'), {

    type: 'bar',

    data: {

        labels: [

<?php foreach($monthlyRevenue as $row): ?>

'<?= $row['bulan'] ?>',

<?php endforeach; ?>

        ],

        datasets: [{

            label: 'Revenue',

            data: [

<?php foreach($monthlyRevenue as $row): ?>

<?= $row['total'] ?>,

<?php endforeach; ?>

            ]

        }]

    },

    options: {

        responsive: true

    }

});


// ===============================
// SHIPMENT STATUS
// ===============================

new Chart(document.getElementById('statusChart'), {

    type: 'doughnut',

    data: {

        labels: [

<?php foreach($shipmentStatus as $row): ?>

'<?= $row['shipment_status'] ?>',

<?php endforeach; ?>

        ],

        datasets: [{

            data: [

<?php foreach($shipmentStatus as $row): ?>

<?= $row['total'] ?>,

<?php endforeach; ?>

            ]

        }]

    },

    options: {

        responsive: true,

        plugins: {

            legend: {

                position: 'bottom'

            }

        }

    }

});

</script>