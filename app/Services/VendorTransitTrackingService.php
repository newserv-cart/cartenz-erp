<?php

namespace App\Services;

use App\Models\ShipmentModel;
use App\Models\ShipmentPackageModel;
use App\Models\ShipmentTrackingModel;
use App\Models\ShipmentVendorTrackingEventModel;
use App\Models\VendorStatusMappingModel;
use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;
use DateTimeZone;

class VendorTransitTrackingService
{
    private const SOURCE_TYPES = [
        'MANUAL',
        'VENDOR_API',
        'LAST_MILE_API',
        'SYSTEM',
    ];

    private const TERMINAL_SHIPMENT_STATUSES = [
        'CANCELLED',
        'RETURN_TO_SENDER',
        'DELIVERED',
    ];

    private const STATUS_META = [
        'SUBMITTED_TO_VENDOR' => [
            'label'           => 'Submitted to Vendor',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 10,
            'manual'          => false,
            'requires_country'=> false,
            'requires_hub'    => false,
        ],
        'VENDOR_PICKED_UP' => [
            'label'           => 'Vendor Picked Up',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 20,
            'manual'          => true,
            'requires_country'=> false,
            'requires_hub'    => false,
        ],
        'DEPARTED_ORIGIN' => [
            'label'           => 'Departed Origin Country',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 30,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'ARRIVED_AT_TRANSIT_HUB' => [
            'label'           => 'Arrived at Transit Hub',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 40,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => true,
        ],
        'DEPARTED_TRANSIT_HUB' => [
            'label'           => 'Departed Transit Hub',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 40,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => true,
        ],
        'ARRIVED_DESTINATION_COUNTRY' => [
            'label'           => 'Arrived Destination Country',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 50,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'CUSTOMS_CLEARANCE_STARTED' => [
            'label'           => 'Customs Clearance Started',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 60,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'CUSTOMS_CLEARANCE_COMPLETED' => [
            'label'           => 'Customs Clearance Completed',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 60,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'CUSTOMS_EXCEPTION' => [
            'label'           => 'Customs Exception',
            'public_status'   => 'ON_HOLD',
            'stage'           => 60,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'HANDED_TO_LAST_MILE' => [
            'label'           => 'Handed to Last Mile',
            'public_status'   => 'IN_TRANSIT',
            'stage'           => 70,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'OUT_FOR_DELIVERY' => [
            'label'           => 'Out for Delivery',
            'public_status'   => 'OUT_FOR_DELIVERY',
            'stage'           => 80,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
        'DELIVERED' => [
            'label'           => 'Delivered',
            'public_status'   => 'DELIVERED',
            'stage'           => 90,
            'manual'          => true,
            'requires_country'=> true,
            'requires_hub'    => false,
        ],
    ];

    protected BaseConnection $db;
    protected ShipmentModel $shipmentModel;
    protected ShipmentPackageModel $packageModel;
    protected ShipmentTrackingModel $trackingModel;
    protected ShipmentVendorTrackingEventModel $eventModel;
    protected VendorStatusMappingModel $mappingModel;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db            = $db ?? db_connect();
        $this->shipmentModel = new ShipmentModel();
        $this->packageModel  = new ShipmentPackageModel();
        $this->trackingModel = new ShipmentTrackingModel();
        $this->eventModel    = new ShipmentVendorTrackingEventModel();
        $this->mappingModel  = new VendorStatusMappingModel();
    }

    public static function manualStatusOptions(): array
    {
        $result = [];

        foreach (self::STATUS_META as $code => $meta) {
            if (!empty($meta['manual'])) {
                $result[$code] = $meta;
            }
        }

        return $result;
    }

    public function getEvents(int $shipmentId): array
    {
        return $this->eventModel->getEvents($shipmentId);
    }

    public function recordManualEvent(
        int $shipmentId,
        array $input,
        int $actorId
    ): array {
        $input['event_source'] = 'MANUAL';

        return $this->recordEvent(
            $shipmentId,
            $input,
            $actorId,
            true,
            true
        );
    }

    public function recordSystemSubmission(
        int $shipmentId,
        string $vendorAwbNo,
        int $actorId,
        bool $manageTransaction = true
    ): array {
        return $this->recordEvent(
            $shipmentId,
            [
                'internal_status'   => 'SUBMITTED_TO_VENDOR',
                'event_source'      => 'SYSTEM',
                'vendor_awb_no'     => $vendorAwbNo,
                'vendor_status_code'=> 'SUBMITTED',
                'event_time'        => date('Y-m-d H:i:s'),
                'location_name'     => 'Vendor Submission',
                'tracking_notes'    => 'Shipment telah disubmit ke vendor dengan AWB ' .
                    strtoupper(trim($vendorAwbNo)) . '.',
                'is_public'         => 1,
            ],
            $actorId,
            $manageTransaction,
            false
        );
    }

    public function recordMappedVendorEvent(
        int $shipmentId,
        array $payload,
        int $actorId = 0
    ): array {
        $shipment = $this->loadShipment($shipmentId, false);
        $vendorId = (int) ($shipment['vendor_id'] ?? 0);
        $vendorCode = strtoupper(
            trim((string) ($payload['vendor_status_code'] ?? ''))
        );

        if ($vendorId <= 0 || $vendorCode === '') {
            throw new \RuntimeException(
                'Vendor dan vendor status code wajib tersedia.'
            );
        }

        $mapping = $this->mappingModel->findActiveMapping(
            $vendorId,
            $vendorCode
        );

        if (!$mapping) {
            throw new \RuntimeException(
                'Vendor status code ' . $vendorCode .
                ' belum mempunyai mapping aktif.'
            );
        }

        $payload['internal_status'] = $mapping['internal_status'];
        $payload['public_status']   = $mapping['public_status'];
        $payload['event_source']    = $payload['event_source']
            ?? 'VENDOR_API';

        return $this->recordEvent(
            $shipmentId,
            $payload,
            $actorId,
            true,
            false
        );
    }

    private function recordEvent(
        int $shipmentId,
        array $input,
        int $actorId,
        bool $manageTransaction,
        bool $manualOnly
    ): array {
        if ($manageTransaction) {
            $this->db->transBegin();
        }

        try {
            $shipment = $this->loadShipment($shipmentId, true);
            $status   = strtoupper(
                trim((string) ($input['internal_status'] ?? ''))
            );

            if (!isset(self::STATUS_META[$status])) {
                throw new \RuntimeException(
                    'Status vendor tracking tidak valid.'
                );
            }

            $meta = self::STATUS_META[$status];

            if ($manualOnly && empty($meta['manual'])) {
                throw new \RuntimeException(
                    'Status ' . $status .
                    ' hanya boleh dibuat otomatis oleh sistem.'
                );
            }

            $this->assertShipmentEligible($shipment, $status);

            $source = strtoupper(
                trim((string) ($input['event_source'] ?? 'MANUAL'))
            );

            if (!in_array($source, self::SOURCE_TYPES, true)) {
                throw new \RuntimeException(
                    'Source vendor tracking tidak valid.'
                );
            }

            $eventTime = $this->normalizeDateTime(
                (string) ($input['event_time'] ?? '')
            );

            $timezone = trim(
                (string) ($input['timezone_name'] ?? '')
            );

            if ($timezone !== '') {
                try {
                    new DateTimeZone($timezone);
                } catch (\Throwable $e) {
                    throw new \RuntimeException(
                        'Time zone tidak valid. Gunakan format seperti Asia/Jakarta.'
                    );
                }
            }

            $countryCode = strtoupper(
                trim((string) ($input['country_code'] ?? ''))
            );

            if (
                $countryCode !== ''
                && !preg_match('/^[A-Z]{2,3}$/', $countryCode)
            ) {
                throw new \RuntimeException(
                    'Country Code harus 2–3 huruf, contoh ID, SG, HKG, TW.'
                );
            }

            $countryName = $this->cleanText(
                $input['country_name'] ?? null,
                100
            );
            $city = $this->cleanText(
                $input['city'] ?? null,
                100
            );
            $hubCode = strtoupper(
                (string) $this->cleanText(
                    $input['hub_code'] ?? null,
                    20
                )
            );
            $locationName = $this->cleanText(
                $input['location_name'] ?? null,
                200
            );

            if (
                !empty($meta['requires_country'])
                && $countryCode === ''
                && $countryName === ''
            ) {
                throw new \RuntimeException(
                    'Negara wajib diisi untuk status ' . $status . '.'
                );
            }

            if (
                !empty($meta['requires_hub'])
                && $hubCode === ''
                && $locationName === ''
            ) {
                throw new \RuntimeException(
                    'Hub/Airport Code atau Location wajib diisi untuk status ' .
                    $status . '.'
                );
            }

            if ($locationName === '') {
                $locationName = $this->composeLocation(
                    $hubCode,
                    $city,
                    $countryName,
                    $countryCode
                );
            }

            if ($locationName === '') {
                $locationName = 'Vendor Network';
            }

            $vendorAwbNo = strtoupper(
                trim((string) (
                    $input['vendor_awb_no']
                    ?? $shipment['vendor_awb_no']
                    ?? ''
                ))
            );

            if ($vendorAwbNo === '') {
                throw new \RuntimeException(
                    'Vendor AWB belum tersedia.'
                );
            }

            $sourceEventId = $this->cleanText(
                $input['source_event_id'] ?? null,
                150
            );
            $vendorStatusCode = strtoupper(
                (string) $this->cleanText(
                    $input['vendor_status_code'] ?? null,
                    100
                )
            );
            $notes = trim(
                strip_tags((string) ($input['tracking_notes'] ?? ''))
            );

            if ($notes === '') {
                $notes = $this->defaultNotes(
                    $status,
                    $locationName
                );
            }

            $isPublic = (int) ($input['is_public'] ?? 1) === 1
                ? 1
                : 0;

            $publicStatus = strtoupper(
                trim((string) (
                    $input['public_status']
                    ?? $meta['public_status']
                ))
            );

            $latestEvent = $this->eventModel
                ->where('shipment_id', $shipmentId)
                ->orderBy('event_time', 'DESC')
                ->orderBy('event_sequence', 'DESC')
                ->orderBy('id', 'DESC')
                ->first();

            $maximumSequenceRow = $this->eventModel
                ->selectMax('event_sequence', 'maximum_sequence')
                ->where('shipment_id', $shipmentId)
                ->first();

            $eventSequence = (int) (
                $maximumSequenceRow['maximum_sequence']
                ?? 0
            ) + 1;

            $fingerprint = hash(
                'sha256',
                implode('|', [
                    $shipmentId,
                    (int) ($shipment['vendor_id'] ?? 0),
                    $status,
                    $eventTime,
                    $source,
                    $sourceEventId,
                    $vendorStatusCode,
                    $countryCode,
                    $countryName,
                    $city,
                    $hubCode,
                    $locationName,
                ])
            );

            $duplicate = null;

            if ($sourceEventId !== '') {
                $duplicate = $this->eventModel
                    ->where('shipment_id', $shipmentId)
                    ->where('event_source', $source)
                    ->where('source_event_id', $sourceEventId)
                    ->first();
            }

            if (!$duplicate) {
                $duplicate = $this->eventModel
                    ->where('event_fingerprint', $fingerprint)
                    ->first();
            }

            if ($duplicate) {
                if ($manageTransaction) {
                    $this->commitOrFail();
                }

                return [
                    'event'      => $duplicate,
                    'duplicate'  => true,
                    'promoted'   => false,
                ];
            }

            $rawPayload = $input['raw_payload'] ?? null;

            if (is_array($rawPayload) || is_object($rawPayload)) {
                $rawPayload = json_encode(
                    $rawPayload,
                    JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_INVALID_UTF8_SUBSTITUTE
                );
            }

            $eventData = [
                'shipment_id'        => $shipmentId,
                'vendor_id'          => (int) ($shipment['vendor_id'] ?? 0)
                    ?: null,
                'shipment_package_id'=> $this->nullablePositiveInt(
                    $input['shipment_package_id'] ?? null
                ),
                'vendor_awb_no'      => $vendorAwbNo,
                'internal_status'    => $status,
                'public_status'      => $publicStatus,
                'event_source'       => $source,
                'source_event_id'    => $sourceEventId ?: null,
                'vendor_status_code' => $vendorStatusCode ?: null,
                'country_code'       => $countryCode ?: null,
                'country_name'       => $countryName ?: null,
                'city'               => $city ?: null,
                'hub_code'           => $hubCode ?: null,
                'location_name'      => $locationName,
                'timezone_name'      => $timezone ?: null,
                'event_time'         => $eventTime,
                'event_sequence'     => $eventSequence,
                'tracking_notes'     => $notes,
                'event_fingerprint'  => $fingerprint,
                'is_public'          => $isPublic,
                'raw_payload'        => $rawPayload ?: null,
                'created_by'         => $actorId > 0
                    ? $actorId
                    : null,
            ];

            if ($this->eventModel->insert($eventData) === false) {
                throw new \RuntimeException(
                    'Vendor tracking event gagal disimpan: ' .
                    implode(', ', $this->eventModel->errors())
                );
            }

            $eventId = (int) $this->eventModel->getInsertID();

            if ($this->trackingModel->insert([
                'shipment_id'        => $shipmentId,
                'vendor_event_id'    => $eventId,
                'branch_id'          => $this->nullablePositiveInt(
                    $shipment['current_branch_id'] ?? null
                ),
                'tracking_code'      => (string) $shipment['awb_no'],
                'tracking_status'    => $status,
                'public_status'      => $publicStatus,
                'event_type'         => 'VENDOR_TRACKING',
                'scan_type'          => null,
                'source_reference'   => 'VENDOR_EVENT:' . $eventId,
                'source_system'      => $source,
                'source_event_id'    => $sourceEventId ?: null,
                'vendor_status_code' => $vendorStatusCode ?: null,
                'tracking_location'  => $locationName,
                'country_code'       => $countryCode ?: null,
                'country_name'       => $countryName ?: null,
                'city'               => $city ?: null,
                'hub_code'           => $hubCode ?: null,
                'timezone_name'      => $timezone ?: null,
                'tracking_notes'     => $notes,
                'event_time'         => $eventTime,
                'event_sequence'     => $eventSequence,
                'is_public'          => $isPublic,
                'created_by'         => $actorId > 0
                    ? $actorId
                    : null,
                'created_at'         => date('Y-m-d H:i:s'),
            ]) === false) {
                throw new \RuntimeException(
                    'Tracking timeline gagal disimpan: ' .
                    implode(', ', $this->trackingModel->errors())
                );
            }

            $promoted = $this->shouldPromote(
                $shipment,
                $latestEvent,
                $status,
                $eventTime
            );

            if ($promoted) {
                if ($this->shipmentModel->update($shipmentId, [
                    'shipment_status'        => $status,
                    'public_tracking_status' => $publicStatus,
                ]) === false) {
                    throw new \RuntimeException(
                        'Status shipment gagal diperbarui: ' .
                        implode(', ', $this->shipmentModel->errors())
                    );
                }

                $this->packageModel
                    ->where('shipment_id', $shipmentId)
                    ->set([
                        'vendor_status' => $status,
                    ])
                    ->update();
            }

            if ($manageTransaction) {
                $this->commitOrFail();
            }

            $eventData['id'] = $eventId;

            return [
                'event'     => $eventData,
                'duplicate' => false,
                'promoted'  => $promoted,
            ];
        } catch (\Throwable $e) {
            if ($manageTransaction) {
                $this->db->transRollback();
            }

            throw $e;
        }
    }

    private function assertShipmentEligible(
        array $shipment,
        string $targetStatus
    ): void {
        $currentStatus = strtoupper(
            trim((string) ($shipment['shipment_status'] ?? ''))
        );

        if (
            in_array($currentStatus, self::TERMINAL_SHIPMENT_STATUSES, true)
            && !(
                $currentStatus === 'DELIVERED'
                && $targetStatus === 'DELIVERED'
            )
        ) {
            throw new \RuntimeException(
                'Shipment berstatus ' . $currentStatus .
                ' dan tidak dapat menerima vendor tracking baru.'
            );
        }

        if ((int) ($shipment['vendor_id'] ?? 0) <= 0) {
            throw new \RuntimeException(
                'Vendor belum di-assign.'
            );
        }

        $submitted = strtoupper(
            (string) ($shipment['vendor_submission_status'] ?? '')
        ) === 'SUBMITTED'
            || !empty($shipment['vendor_submitted_at'])
            || !empty($shipment['vendor_awb_no']);

        if (!$submitted) {
            throw new \RuntimeException(
                'Shipment belum disubmit ke vendor.'
            );
        }

        if (
            $targetStatus !== 'SUBMITTED_TO_VENDOR'
            && empty($shipment['vendor_awb_no'])
        ) {
            throw new \RuntimeException(
                'Vendor AWB belum tersimpan.'
            );
        }
    }

    private function shouldPromote(
        array $shipment,
        ?array $latestEvent,
        string $newStatus,
        string $newEventTime
    ): bool {
        $currentShipmentStatus = strtoupper(
            trim((string) ($shipment['shipment_status'] ?? ''))
        );

        if (
            $currentShipmentStatus === 'DELIVERED'
            && $newStatus !== 'DELIVERED'
        ) {
            return false;
        }

        if (!$latestEvent) {
            return true;
        }

        $latestTime = (string) ($latestEvent['event_time'] ?? '');

        if (
            $latestTime !== ''
            && strtotime($newEventTime) < strtotime($latestTime)
        ) {
            return false;
        }

        $currentVendorStatus = strtoupper(
            (string) ($latestEvent['internal_status'] ?? '')
        );

        $currentStage = (int) (
            self::STATUS_META[$currentVendorStatus]['stage']
            ?? 0
        );
        $newStage = (int) (
            self::STATUS_META[$newStatus]['stage']
            ?? 0
        );

        return $newStage >= $currentStage;
    }

    private function loadShipment(int $shipmentId, bool $lock): array
    {
        $sql = 'SELECT s.*, v.vendor_name ' .
            'FROM shipments s ' .
            'LEFT JOIN vendors v ON v.id = s.vendor_id ' .
            'WHERE s.id = ?' .
            ($lock ? ' FOR UPDATE' : '');

        $shipment = $this->db
            ->query($sql, [$shipmentId])
            ->getRowArray();

        if (!$shipment) {
            throw new \RuntimeException(
                'Shipment tidak ditemukan.'
            );
        }

        return $shipment;
    }

    private function normalizeDateTime(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return date('Y-m-d H:i:s');
        }

        try {
            return (new DateTimeImmutable($value))
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'Event Time tidak valid.'
            );
        }
    }

    private function cleanText($value, int $maxLength): string
    {
        $value = trim(strip_tags((string) $value));

        if (mb_strlen($value) > $maxLength) {
            $value = mb_substr($value, 0, $maxLength);
        }

        return $value;
    }

    private function composeLocation(
        string $hubCode,
        string $city,
        string $countryName,
        string $countryCode
    ): string {
        $parts = [];

        if ($hubCode !== '') {
            $parts[] = $hubCode;
        }

        if ($city !== '') {
            $parts[] = $city;
        }

        if ($countryName !== '') {
            $parts[] = $countryName;
        } elseif ($countryCode !== '') {
            $parts[] = $countryCode;
        }

        return implode(' · ', array_unique($parts));
    }

    private function defaultNotes(
        string $status,
        string $location
    ): string {
        $label = self::STATUS_META[$status]['label']
            ?? $status;

        return $label . ' at ' . $location . '.';
    }

    private function nullablePositiveInt($value): ?int
    {
        $value = (int) $value;

        return $value > 0 ? $value : null;
    }

    private function commitOrFail(): void
    {
        if ($this->db->transStatus() === false) {
            throw new \RuntimeException(
                'Transaksi vendor tracking gagal.'
            );
        }

        $this->db->transCommit();
    }
}
