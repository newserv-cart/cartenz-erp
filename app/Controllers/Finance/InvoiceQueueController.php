<?php

namespace App\Controllers\Finance;

use App\Controllers\BaseController;

class InvoiceQueueController extends BaseController
{
    private array $eligibleStatuses = [
        'RECEIVED_AT_ORIGIN_BRANCH',
        'SENT_TO_MAIN_WAREHOUSE',
        'RECEIVED_AT_MAIN_WAREHOUSE',
        'ASSIGNED_TO_VENDOR',
        'HANDED_OVER_TO_VENDOR',
        'BOOKED_WITH_VENDOR',
        'IN_TRANSIT_INTERNATIONAL',
        'ARRIVED_HUB',
        'IN_TRANSIT_HUB',
        'HANDED_TO_LAST_MILE',
        'DELIVERED',
    ];

    public function index()
    {
        $db = db_connect();

        $query = $db
            ->table('shipments s')
            ->select([
                's.id',
                's.awb_no',
                's.shipment_date',
                's.shipment_status',
                's.owner_type',
                's.customer_id',
                's.payment_type',
                's.pricing_status',
                's.chargeable_weight',
                's.customer_total',
                's.total_charge',
                's.grand_total',
                's.is_invoiced',
                's.invoice_id',
                'c.customer_name',
                'c.credit_days',
            ])
            ->join('customers c', 'c.id = s.customer_id')
            ->where('s.owner_type', 'CUSTOMER')
            ->where('s.payment_type', 'INVOICE')
            ->where('s.pricing_status', 'FINAL')
            ->where('s.is_invoiced', 0)
            ->where('s.invoice_id IS NULL', null, false)
            ->where('s.customer_id IS NOT NULL', null, false)
            ->whereIn('s.shipment_status', $this->eligibleStatuses)
            ->groupStart()
                ->where('s.customer_total >', 0)
                ->orWhere('s.total_charge >', 0)
                ->orWhere('s.grand_total >', 0)
            ->groupEnd()
            ->orderBy('c.customer_name', 'ASC')
            ->orderBy('s.shipment_date', 'ASC')
            ->orderBy('s.id', 'ASC')
            ->get();

        if ($query === false) {
            $error = $db->error();

            throw new \RuntimeException(
                'Query invoice queue gagal: '
                . ($error['message'] ?? 'Unknown database error')
            );
        }

        $grouped = [];

        foreach ($query->getResultArray() as $shipment) {
            $customerId = (int) $shipment['customer_id'];

            $amount = (float) (
                $shipment['customer_total']
                ?: $shipment['total_charge']
                ?: $shipment['grand_total']
                ?: 0
            );

            $shipment['billable_amount'] = $amount;

            if (!isset($grouped[$customerId])) {
                $grouped[$customerId] = [
                    'customer_id'   => $customerId,
                    'customer_name' =>
                        $shipment['customer_name']
                        ?? 'Customer #' . $customerId,
                    'credit_days'   => (int) ($shipment['credit_days'] ?? 0),
                    'shipments'     => [],
                    'total'         => 0,
                ];
            }

            $grouped[$customerId]['shipments'][] = $shipment;
            $grouped[$customerId]['total'] += $amount;
        }

        return view('finance/invoice_queue/index', [
            'title'  => 'Customer Invoice Queue',
            'groups' => $grouped,
        ]);
    }

    public function createInvoice()
    {
        $shipmentIds = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        (array) $this->request->getPost('shipment_ids')
                    )
                )
            )
        );

        if (empty($shipmentIds)) {
            return redirect()->back()->with(
                'error',
                'Pilih minimal satu shipment.'
            );
        }

        $invoiceDate = trim(
            (string) $this->request->getPost('invoice_date')
        );

        $dueDate = trim(
            (string) $this->request->getPost('due_date')
        );

        if (
            strtotime($invoiceDate) === false
            || strtotime($dueDate) === false
            || $invoiceDate === ''
            || $dueDate === ''
        ) {
            return redirect()->back()->with(
                'error',
                'Tanggal invoice atau jatuh tempo tidak valid.'
            );
        }

        if (strtotime($dueDate) < strtotime($invoiceDate)) {
            return redirect()->back()->with(
                'error',
                'Tanggal jatuh tempo tidak boleh sebelum tanggal invoice.'
            );
        }

        $db     = db_connect();
        $now    = date('Y-m-d H:i:s');
        $userId = (int) (auth()->id() ?? 0);

        $db->transBegin();

        try {
            $shipments = $db
                ->table('shipments')
                ->whereIn('id', $shipmentIds)
                ->where('owner_type', 'CUSTOMER')
                ->where('payment_type', 'INVOICE')
                ->where('pricing_status', 'FINAL')
                ->where('is_invoiced', 0)
                ->where('invoice_id IS NULL', null, false)
                ->where('customer_id IS NOT NULL', null, false)
                ->whereIn('shipment_status', $this->eligibleStatuses)
                ->get()
                ->getResultArray();

            if (count($shipments) !== count($shipmentIds)) {
                throw new \RuntimeException(
                    'Sebagian shipment tidak lagi memenuhi syarat invoice.'
                );
            }

            $customerIds = array_values(
                array_unique(
                    array_map(
                        static fn (array $shipment): int =>
                            (int) $shipment['customer_id'],
                        $shipments
                    )
                )
            );

            if (count($customerIds) !== 1) {
                throw new \RuntimeException(
                    'Satu invoice hanya boleh berisi shipment '
                    . 'dari satu customer.'
                );
            }

            $customerId = $customerIds[0];
            $total      = 0;

            foreach ($shipments as $shipment) {
                $amount = (float) (
                    $shipment['customer_total']
                    ?: $shipment['total_charge']
                    ?: $shipment['grand_total']
                    ?: 0
                );

                if ($amount <= 0) {
                    throw new \RuntimeException(
                        'Shipment '
                        . ($shipment['awb_no'] ?? '')
                        . ' belum memiliki total final.'
                    );
                }

                $total += $amount;
            }

            if ($total <= 0) {
                throw new \RuntimeException(
                    'Total invoice tidak valid.'
                );
            }

            $invoiceNo = 'INV' . date('ymdHis');

            $invoiceData = [
                'invoice_no'     => $invoiceNo,
                'customer_id'    => $customerId,
                'invoice_date'   => $invoiceDate,
                'date'           => $invoiceDate,
                'due_date'       => $dueDate,
                'due'            => $dueDate,
                'total_amount'   => $total,
                'total'          => $total,
                'grand_total'    => $total,
                'paid_amount'    => 0,
                'paid'           => 0,
                'balance_amount' => $total,
                'balance'        => $total,
                'invoice_status' => 'UNPAID',
                'status'         => 'UNPAID',
                'created_by'     => $userId,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];

            $invoiceData = $this->filterExistingColumns(
                'invoices',
                $invoiceData
            );

            $inserted = $db
                ->table('invoices')
                ->insert($invoiceData);

            if ($inserted === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal membuat invoice: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            $invoiceId = (int) $db->insertID();

            if ($invoiceId <= 0) {
                throw new \RuntimeException(
                    'Gagal mendapatkan ID invoice yang baru dibuat.'
                );
            }

            foreach ($shipments as $shipment) {
                $amount = (float) (
                    $shipment['customer_total']
                    ?: $shipment['total_charge']
                    ?: $shipment['grand_total']
                    ?: 0
                );

                $itemData = $this->filterExistingColumns('invoice_items', [
                    'invoice_id'  => $invoiceId,
                    'shipment_id' => (int) $shipment['id'],
                    'amount'      => $amount,
                    'created_at'  => $now,
                    'updated_at'  => $now,
                ]);

                if (count($itemData) >= 3) {
                    $itemInserted = $db
                        ->table('invoice_items')
                        ->insert($itemData);

                    if ($itemInserted === false) {
                        $error = $db->error();

                        throw new \RuntimeException(
                            'Gagal menyimpan invoice item: '
                            . ($error['message'] ?? 'Unknown database error')
                        );
                    }
                }
            }

            $updated = $db
                ->table('shipments')
                ->whereIn('id', $shipmentIds)
                ->where('is_invoiced', 0)
                ->where('invoice_id IS NULL', null, false)
                ->update([
                    'invoice_id'  => $invoiceId,
                    'is_invoiced' => 1,
                    'updated_at'  => $now,
                ]);

            if ($updated === false) {
                $error = $db->error();

                throw new \RuntimeException(
                    'Gagal menghubungkan shipment ke invoice: '
                    . ($error['message'] ?? 'Unknown database error')
                );
            }

            if ($db->affectedRows() !== count($shipmentIds)) {
                throw new \RuntimeException(
                    'Tidak semua shipment berhasil dimasukkan ke invoice.'
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi pembuatan invoice gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('invoices/show/' . $invoiceId))
                ->with(
                    'success',
                    'Invoice '
                    . $invoiceNo
                    . ' berhasil dibuat untuk '
                    . count($shipmentIds)
                    . ' shipment.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                '[INVOICE QUEUE CREATE FAILED] '
                . $e->getMessage()
            );

            return redirect()->back()->with(
                'error',
                'Gagal membuat invoice: ' . $e->getMessage()
            );
        }
    }

    private function filterExistingColumns(
        string $table,
        array $data
    ): array {
        $fields = array_flip(
            db_connect()->getFieldNames($table)
        );

        return array_intersect_key($data, $fields);
    }
}
