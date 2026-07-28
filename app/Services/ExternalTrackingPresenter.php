<?php

namespace App\Services;

class ExternalTrackingPresenter
{
    public function present(array $event): array
    {
        $code = strtoupper(
            trim(
                (string) (
                    $event['event_code']
                    ?? $event['tracking_status']
                    ?? $event['status']
                    ?? ''
                )
            )
        );

        $event['display_location'] = $this->externalLocation(
            $code,
            (string) (
                $event['tracking_location']
                ?? $event['location']
                ?? ''
            )
        );

        $event['display_description'] = $this->externalDescription(
            $code
        );

        unset(
            $event['vendor_id'],
            $event['vendor_name'],
            $event['vendor_awb'],
            $event['manifest_no'],
            $event['booking_reference'],
            $event['transport_reference'],
            $event['internal_notes']
        );

        return $event;
    }

    public function presentMany(array $events): array
    {
        return array_map(
            fn (array $event): array => $this->present($event),
            $events
        );
    }

    private function externalDescription(string $status): string
    {
        return match ($status) {
            'CREATED' =>
                'Shipment dibuat oleh customer melalui portal self-service. '
                . 'Barang belum diterima secara fisik oleh CARTENZ.',

            'ASSIGNED_TO_VENDOR' =>
                'Shipment sedang dipersiapkan untuk pengiriman internasional.',

            'HANDED_OVER_TO_VENDOR' =>
                'Shipment telah diserahkan kepada mitra pengiriman internasional.',

            'BOOKED_WITH_VENDOR' =>
                'Jadwal pengiriman internasional telah dikonfirmasi.',

            'IN_TRANSIT_INTERNATIONAL' =>
                'Shipment sedang dalam perjalanan internasional.',

            'ARRIVED_HUB' =>
                'Shipment telah tiba di hub tujuan.',

            'IN_TRANSIT_HUB' =>
                'Shipment sedang diproses melalui jaringan hub.',

            'CUSTOMS_EXCEPTION' =>
                'Shipment sedang menjalani pemeriksaan kepabeanan atau memerlukan dokumen tambahan.',

            'HANDED_TO_LAST_MILE' =>
                'Shipment telah diserahkan kepada mitra pengantaran lokal.',

            'DELIVERED' =>
                'Shipment berhasil diserahkan kepada penerima.',

            'POD_RECORDED' =>
                'Bukti penerimaan shipment telah tersedia.',

            'PICKUP_REQUESTED' =>
                'Permintaan pickup telah diterima oleh CARTENZ. '
                . 'Tim operasional akan menghubungi PIC pickup.',

            'PICKUP_SCHEDULED' =>
                'Jadwal penjemputan shipment telah ditentukan.',

            'COURIER_ASSIGNED' =>
                'Kurir telah ditugaskan untuk menjemput shipment.',

            'RECEIVED_AT_ORIGIN_BRANCH' =>
                'Shipment telah diterima di origin branch. '
                . 'Berat dan colly sedang diverifikasi.',

            default =>
                'Status shipment telah diperbarui.',
        };
    }

    private function externalLocation(
        string $status,
        string $originalLocation
    ): string {
        return match ($status) {
            'ASSIGNED_TO_VENDOR',
            'HANDED_OVER_TO_VENDOR',
            'BOOKED_WITH_VENDOR' =>
                'International Gateway',

            default =>
                $originalLocation !== ''
                    ? $originalLocation
                    : 'Shipment Facility',
        };
    }
}
