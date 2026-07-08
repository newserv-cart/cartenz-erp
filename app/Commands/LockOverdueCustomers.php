<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class LockOverdueCustomers extends BaseCommand
{
    protected $group = 'Cartenz';

    protected $name = 'cartenz:lock-customers';

    protected $description =
        'Lock customers with overdue invoices';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $rows = $db->query("
            SELECT DISTINCT
                invoices.customer_id
            FROM invoices
            INNER JOIN billing_profiles
                ON billing_profiles.customer_id = invoices.customer_id
            WHERE invoices.balance_amount > 0
            AND invoices.due_date < CURDATE()
            AND billing_profiles.auto_lock = 1
            AND billing_profiles.status = 1
        ")->getResultArray();

        if (empty($rows)) {

            CLI::write(
                'No overdue customers found',
                'green'
            );

            return;
        }

        foreach ($rows as $row) {

            $db->table('customers')
                ->where('id', $row['customer_id'])
                ->update([
                    'account_locked' => 1
                ]);

            CLI::write(
                'Locked Customer ID : '
                . $row['customer_id'],
                'yellow'
            );
        }

        CLI::write(
            'Process Finished',
            'green'
        );
    }
}