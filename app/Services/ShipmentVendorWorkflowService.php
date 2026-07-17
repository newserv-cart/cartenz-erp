<?php

namespace App\Services;

use App\Models\ShipmentCostDetailModel;
use App\Models\ShipmentHistoryModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentPackageModel;
use App\Models\ShipmentVendorRecommendationModel;
use CodeIgniter\Database\BaseConnection;

class ShipmentVendorWorkflowService
{
    private const TERMINAL_STATUSES = [
        'CANCELLED',
        'DELIVERED',
        'RETURN_TO_SENDER',
        'CLOSED',
    ];

    protected BaseConnection $db;
    protected ShipmentModel $shipmentModel;
    protected ShipmentPackageModel $packageModel;
    protected ShipmentCostDetailModel $costModel;
    protected ShipmentHistoryModel $historyModel;
    protected ShipmentVendorRecommendationModel $recommendationModel;
    protected VendorRecommendationService $recommendationService;
    protected VendorCostService $costService;

    public function __construct()
    {
        $this->db                  = db_connect();
        $this->shipmentModel       = new ShipmentModel();
        $this->packageModel        = new ShipmentPackageModel();
        $this->costModel           = new ShipmentCostDetailModel();
        $this->historyModel        = new ShipmentHistoryModel();
        $this->recommendationModel = new ShipmentVendorRecommendationModel();
        $this->recommendationService = new VendorRecommendationService();
        $this->costService           = new VendorCostService();
    }

    public function generateRecommendations(
        int $shipmentId,
        int $actorId
    ): int {
        $this->db->transBegin();

        try {
            $shipment = $this->loadShipmentForRate($shipmentId, true);
            $this->assertNonTerminal($shipment);
            $this->assertNotSubmitted($shipmentId, $shipment);

            $rows = $this->recommendationService->compare($shipment);

            if ($rows === []) {
                throw new \RuntimeException(
                    'Tidak ada vendor aktif dengan tarif yang cocok untuk shipment ini.'
                );
            }

            $this->recommendationModel
                ->where('shipment_id', $shipmentId)
                ->delete();

            foreach ($rows as $row) {
                $cost = $row['cost_result'];

                if (!$this->recommendationModel->insert([
                    'shipment_id'        => $shipmentId,
                    'vendor_id'          => (int) $row['vendor']['id'],
                    'vendor_cost'        => $row['vendor_cost'],
                    'customer_charge'    => $row['customer_charge'],
                    'profit'             => $row['profit'],
                    'score'              => $row['score'],
                    'recommended'        => $row['recommended'],
                    'rank_no'            => $row['rank_no'],
                    'rate_source'        => $cost['rate_source'],
                    'rate_reference_id'  => $cost['rate_reference_id'],
                    'currency_id'        => $cost['currency_id'],
                    'chargeable_weight'  => $cost['chargeable_weight'],
                    'rate_weight'        => $cost['rate_weight'],
                    'rate_effective_date'=> $cost['rate_effective_date'],
                    'calculation_json'   => json_encode(
                        $cost['breakdown'],
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    'generated_by'       => $actorId,
                ])) {
                    throw new \RuntimeException(
                        'Rekomendasi vendor gagal disimpan.'
                    );
                }
            }

            $this->recordHistory(
                $shipmentId,
                'VENDOR_RECOMMENDATIONS_GENERATED',
                'Vendor Recommendations Generated',
                count($rows) . ' rekomendasi vendor berhasil dihitung.',
                [
                    'total_recommendations' => count($rows),
                    'best_vendor_id' => (int) $rows[0]['vendor']['id'],
                    'best_vendor_cost' => $rows[0]['vendor_cost'],
                ],
                $actorId
            );

            $this->commitOrFail();

            return count($rows);
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function assignVendor(
        int $shipmentId,
        int $recommendationId,
        ?string $overrideReason,
        int $actorId
    ): void {
        $this->db->transBegin();

        try {
            $shipment = $this->loadShipmentForRate($shipmentId, true);
            $this->assertNonTerminal($shipment);
            $this->assertNotSubmitted($shipmentId, $shipment);

            $recommendation = $this->db
                ->query(
                    'SELECT r.*, v.vendor_name, v.status AS vendor_status ' .
                    'FROM shipment_vendor_recommendations r ' .
                    'INNER JOIN vendors v ON v.id = r.vendor_id ' .
                    'WHERE r.id = ? AND r.shipment_id = ? FOR UPDATE',
                    [$recommendationId, $shipmentId]
                )
                ->getRowArray();

            if (!$recommendation) {
                throw new \RuntimeException(
                    'Rekomendasi vendor tidak ditemukan atau bukan milik shipment ini.'
                );
            }

            if ((int) ($recommendation['vendor_status'] ?? 0) !== 1) {
                throw new \RuntimeException(
                    'Vendor yang dipilih sedang tidak aktif.'
                );
            }

            $currentCost = $this->costService->calculateForVendor(
                $shipment,
                (int) $recommendation['vendor_id']
            );

            if (
                $currentCost === null
                || $currentCost['rate_source'] !== $recommendation['rate_source']
                || (int) $currentCost['rate_reference_id']
                    !== (int) $recommendation['rate_reference_id']
                || abs(
                    (float) $currentCost['chargeable_weight']
                    - (float) $recommendation['chargeable_weight']
                ) > 0.001
                || abs(
                    (float) $currentCost['vendor_cost']
                    - (float) $recommendation['vendor_cost']
                ) > 0.01
            ) {
                throw new \RuntimeException(
                    'Rekomendasi sudah tidak sesuai dengan data/rate terbaru. Generate Recommendations ulang.'
                );
            }

            $overrideReason = trim((string) $overrideReason);

            if (
                (int) ($recommendation['recommended'] ?? 0) !== 1
                && mb_strlen($overrideReason) < 10
            ) {
                throw new \RuntimeException(
                    'Override reason minimal 10 karakter karena vendor yang dipilih bukan rekomendasi terbaik.'
                );
            }

            $vendorCost = round(
                max(0, (float) $recommendation['vendor_cost']),
                2
            );

            $this->upsertVendorFreightCost(
                $shipmentId,
                $vendorCost,
                'Locked from recommendation #' . $recommendationId
            );

            $vendorTotal   = $this->sumVendorCosts($shipmentId);
            $customerTotal = round(
                (float) (
                    $shipment['total_charge']
                    ?? $shipment['selling_amount']
                    ?? 0
                ),
                2
            );
            $profit = round($customerTotal - $vendorTotal, 2);
            $margin = $customerTotal > 0
                ? round(($profit / $customerTotal) * 100, 2)
                : 0.0;
            $markup = $vendorTotal > 0
                ? round(($profit / $vendorTotal) * 100, 2)
                : 0.0;
            $now = date('Y-m-d H:i:s');

            if (!$this->shipmentModel->update($shipmentId, [
                'vendor_id'                 => (int) $recommendation['vendor_id'],
                'vendor_cost'               => $vendorCost,
                'vendor_freight'            => $vendorCost,
                'vendor_total'              => $vendorTotal,
                'profit_amount'             => $profit,
                'margin_percent'            => $margin,
                'markup_percent'            => $markup,
                'vendor_assignment_status'  => 'ASSIGNED',
                'vendor_assignment_source'  => (int) $recommendation['recommended'] === 1
                    ? 'RECOMMENDED'
                    : 'OVERRIDE',
                'vendor_recommendation_id'  => $recommendationId,
                'vendor_rate_source'        => $recommendation['rate_source'],
                'vendor_rate_reference_id'  => $recommendation['rate_reference_id'],
                'vendor_rate_currency_id'   => $recommendation['currency_id'],
                'vendor_rate_weight'        => $recommendation['rate_weight'],
                'vendor_assigned_at'        => $now,
                'vendor_assigned_by'        => $actorId,
                'vendor_override_reason'    => $overrideReason !== ''
                    ? $overrideReason
                    : null,
                'vendor_cost_locked_at'     => $now,
                'vendor_cost_locked_by'     => $actorId,
                'vendor_submission_status'  => 'NOT_SUBMITTED',
            ])) {
                throw new \RuntimeException(
                    'Vendor assignment gagal disimpan.'
                );
            }

            $this->packageModel
                ->where('shipment_id', $shipmentId)
                ->set(['vendor_id' => (int) $recommendation['vendor_id']])
                ->update();

            $this->recordHistory(
                $shipmentId,
                'VENDOR_ASSIGNED',
                'Vendor Assigned',
                'Vendor ' . $recommendation['vendor_name'] .
                ' dipilih dan vendor cost dikunci.',
                [
                    'vendor_id'        => (int) $recommendation['vendor_id'],
                    'recommendation_id'=> $recommendationId,
                    'assignment_source'=> (int) $recommendation['recommended'] === 1
                        ? 'RECOMMENDED'
                        : 'OVERRIDE',
                    'override_reason'  => $overrideReason !== ''
                        ? $overrideReason
                        : null,
                    'vendor_cost'      => $vendorCost,
                    'vendor_total'     => $vendorTotal,
                    'profit'           => $profit,
                ],
                $actorId
            );

            $this->commitOrFail();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function submitToVendor(
        int $shipmentId,
        string $vendorAwbNo,
        ?string $masterAwbNo,
        ?string $trackingNo,
        int $actorId
    ): void {
        $this->db->transBegin();

        try {
            $shipment = $this->loadShipmentForRate($shipmentId, true);
            $this->assertNonTerminal($shipment);
            $this->assertNotSubmitted($shipmentId, $shipment);

            if (
                strtoupper((string) ($shipment['shipment_status'] ?? ''))
                !== 'RECEIVED_AT_MAIN_WAREHOUSE'
            ) {
                throw new \RuntimeException(
                    'Submit ke vendor hanya boleh setelah shipment berstatus RECEIVED_AT_MAIN_WAREHOUSE.'
                );
            }

            if ((int) ($shipment['vendor_id'] ?? 0) <= 0) {
                throw new \RuntimeException(
                    'Assign vendor terlebih dahulu.'
                );
            }

            if (empty($shipment['vendor_cost_locked_at'])) {
                throw new \RuntimeException(
                    'Vendor cost belum dikunci.'
                );
            }

            $vendorAwbNo = strtoupper(trim($vendorAwbNo));

            if (
                mb_strlen($vendorAwbNo) < 3
                || mb_strlen($vendorAwbNo) > 100
                || !preg_match('/^[A-Z0-9._\-\/]+$/', $vendorAwbNo)
            ) {
                throw new \RuntimeException(
                    'Vendor AWB wajib 3–100 karakter dan hanya boleh berisi huruf, angka, titik, garis miring, underscore, atau tanda hubung.'
                );
            }

            $packages = $this->db
                ->query(
                    'SELECT * FROM shipment_packages ' .
                    'WHERE shipment_id = ? ORDER BY id ASC FOR UPDATE',
                    [$shipmentId]
                )
                ->getResultArray();

            if ($packages === []) {
                throw new \RuntimeException(
                    'Shipment belum mempunyai package.'
                );
            }

            $now = date('Y-m-d H:i:s');

            if (!$this->shipmentModel->update($shipmentId, [
                'vendor_awb_no'            => $vendorAwbNo,
                'master_awb_no'            => $this->nullableText($masterAwbNo),
                'tracking_no_vendor'       => $this->nullableText($trackingNo)
                    ?? $vendorAwbNo,
                'vendor_assignment_status' => 'LOCKED',
                'vendor_submission_status' => 'SUBMITTED',
                'vendor_submitted_at'      => $now,
                'vendor_submitted_by'      => $actorId,
            ])) {
                throw new \RuntimeException(
                    'Shipment gagal disubmit ke vendor.'
                );
            }

            foreach ($packages as $package) {
                if (!$this->packageModel->update((int) $package['id'], [
                    'vendor_id'              => (int) $shipment['vendor_id'],
                    'vendor_awb_no'          => $vendorAwbNo,
                    'vendor_status'          => 'SUBMITTED',
                    'is_submitted_to_vendor' => 1,
                ])) {
                    throw new \RuntimeException(
                        'Package gagal ditandai submitted ke vendor.'
                    );
                }
            }

            $this->recordHistory(
                $shipmentId,
                'SUBMITTED_TO_VENDOR',
                'Submitted to Vendor',
                'Shipment disubmit ke vendor dengan AWB ' . $vendorAwbNo . '.',
                [
                    'vendor_id'       => (int) $shipment['vendor_id'],
                    'vendor_awb_no'   => $vendorAwbNo,
                    'master_awb_no'   => $this->nullableText($masterAwbNo),
                    'package_count'   => count($packages),
                    'vendor_total'    => (float) ($shipment['vendor_total'] ?? 0),
                ],
                $actorId
            );

            $this->commitOrFail();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    public function getRecommendations(int $shipmentId): array
    {
        return $this->db
            ->table('shipment_vendor_recommendations r')
            ->select([
                'r.*',
                'v.vendor_code',
                'v.vendor_name',
                'v.vendor_priority',
                'c.currency_code',
            ])
            ->join('vendors v', 'v.id = r.vendor_id', 'inner')
            ->join('currencies c', 'c.id = r.currency_id', 'left')
            ->where('r.shipment_id', $shipmentId)
            ->orderBy('r.rank_no', 'ASC')
            ->orderBy('r.vendor_cost', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function getActionState(
        array $shipment,
        array $recommendations = []
    ): array {
        $status = strtoupper((string) ($shipment['shipment_status'] ?? ''));
        $terminal = in_array($status, self::TERMINAL_STATUSES, true);
        $submitted = strtoupper(
            (string) ($shipment['vendor_submission_status'] ?? '')
        ) === 'SUBMITTED'
            || !empty($shipment['vendor_submitted_at'])
            || !empty($shipment['vendor_awb_no']);
        $assigned = (int) ($shipment['vendor_id'] ?? 0) > 0
            && !empty($shipment['vendor_cost_locked_at']);

        return [
            'terminal'        => $terminal,
            'submitted'       => $submitted,
            'assigned'        => $assigned,
            'can_generate'    => !$terminal && !$submitted,
            'can_assign'      => !$terminal
                && !$submitted
                && $recommendations !== [],
            'can_submit'      => !$terminal
                && !$submitted
                && $assigned
                && $status === 'RECEIVED_AT_MAIN_WAREHOUSE',
            'submit_block_reason' => $assigned
                && $status !== 'RECEIVED_AT_MAIN_WAREHOUSE'
                    ? 'Menunggu status RECEIVED_AT_MAIN_WAREHOUSE.'
                    : null,
        ];
    }

    private function loadShipmentForRate(
        int $shipmentId,
        bool $lock
    ): array {
        $sql = 'SELECT s.*, sv.service_code, sv.pricing_engine, ' .
            'c.express_zone_id, pt.package_type_name ' .
            'FROM shipments s ' .
            'LEFT JOIN services sv ON sv.id = s.service_id ' .
            'LEFT JOIN countries c ON c.id = s.country_id ' .
            'LEFT JOIN package_types pt ON pt.id = s.package_type_id ' .
            'WHERE s.id = ?' . ($lock ? ' FOR UPDATE' : '');

        $shipment = $this->db
            ->query($sql, [$shipmentId])
            ->getRowArray();

        if (!$shipment) {
            throw new \RuntimeException('Shipment tidak ditemukan.');
        }

        return $shipment;
    }

    private function assertNonTerminal(array $shipment): void
    {
        $status = strtoupper((string) ($shipment['shipment_status'] ?? ''));

        if (in_array($status, self::TERMINAL_STATUSES, true)) {
            throw new \RuntimeException(
                'Shipment berstatus ' . $status .
                ' dan tidak dapat diproses ke vendor.'
            );
        }

        if ((float) ($shipment['chargeable_weight'] ?? 0) <= 0) {
            throw new \RuntimeException(
                'Chargeable Weight shipment belum valid.'
            );
        }
    }

    private function assertNotSubmitted(
        int $shipmentId,
        array $shipment
    ): void {
        $submittedPackage = $this->db
            ->table('shipment_packages')
            ->select('id')
            ->where('shipment_id', $shipmentId)
            ->groupStart()
                ->where('is_submitted_to_vendor', 1)
                ->orWhere('vendor_awb_no IS NOT NULL', null, false)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray();

        if (
            $submittedPackage
            || !empty($shipment['vendor_submitted_at'])
            || !empty($shipment['vendor_awb_no'])
        ) {
            throw new \RuntimeException(
                'Shipment sudah disubmit ke vendor dan seluruh assignment terkunci.'
            );
        }
    }

    private function upsertVendorFreightCost(
        int $shipmentId,
        float $amount,
        string $remarks
    ): void {
        $existing = $this->costModel
            ->where('shipment_id', $shipmentId)
            ->where('party', 'VENDOR')
            ->where('component_code', 'BASE_VENDOR_COST')
            ->first();

        $data = [
            'shipment_id'   => $shipmentId,
            'party'         => 'VENDOR',
            'component_code'=> 'BASE_VENDOR_COST',
            'component_name'=> 'Base Vendor Cost',
            'amount'        => $amount,
            'remarks'       => $remarks,
        ];

        $ok = $existing
            ? $this->costModel->update((int) $existing['id'], $data)
            : $this->costModel->insert($data);

        if (!$ok) {
            throw new \RuntimeException(
                'Vendor cost breakdown gagal diperbarui.'
            );
        }
    }

    private function sumVendorCosts(int $shipmentId): float
    {
        $row = $this->db
            ->table('shipment_cost_details')
            ->selectSum('amount', 'total')
            ->where('shipment_id', $shipmentId)
            ->where('party', 'VENDOR')
            ->get()
            ->getRowArray();

        return round(max(0, (float) ($row['total'] ?? 0)), 2);
    }

    private function recordHistory(
        int $shipmentId,
        string $type,
        string $title,
        string $description,
        array $changedFields,
        int $actorId
    ): void {
        if (!$this->historyModel->insert([
            'shipment_id'    => $shipmentId,
            'event_type'     => $type,
            'event_title'    => $title,
            'description'    => $description,
            'changed_fields' => json_encode(
                $changedFields,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'created_by'     => $actorId,
            'created_at'     => date('Y-m-d H:i:s'),
        ])) {
            throw new \RuntimeException(
                'Shipment history gagal dicatat.'
            );
        }
    }

    private function nullableText($value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function commitOrFail(): void
    {
        if ($this->db->transStatus() === false) {
            throw new \RuntimeException(
                'Transaksi vendor workflow gagal.'
            );
        }

        $this->db->transCommit();
    }
}
