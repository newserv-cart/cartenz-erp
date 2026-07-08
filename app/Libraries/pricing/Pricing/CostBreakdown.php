<?php

namespace App\Libraries\Pricing;

class CostBreakdown
{
    protected array $rows = [];

    /**
     * Tambah komponen biaya
     */
    public function add(
        string $party,
        string $code,
        string $name,
        float $amount,
        array $details = []
    ): void
    {
        $this->rows[] = [
            'party'          => strtoupper($party),
            'component_code' => strtoupper($code),
            'component_name' => $name,
            'amount'         => round($amount, 2),
            'details'        => $details,
            'created_at'     => date('H:i:s') // ✅ Upgrade 5: Tambah waktu
        ];
    }

    /**
     * Ambil semua daftar komponen biaya
     */
    public function items(): array
    {
        return $this->rows;
    }

    /**
     * Hitung total biaya untuk Customer
     */
    public function customer(): float
    {
        $total = 0;
        foreach ($this->rows as $row) {
            if ($row['party'] === 'CUSTOMER') {
                $total += $row['amount'];
            }
        }
        return round($total, 2);
    }

    /**
     * Hitung total biaya untuk Vendor
     */
    public function vendor(): float
    {
        $total = 0;
        foreach ($this->rows as $row) {
            if ($row['party'] === 'VENDOR') {
                $total += $row['amount'];
            }
        }
        return round($total, 2);
    }

    /**
     * Dapatkan ringkasan total biaya + Margin & Markup ✅ Upgrade 1
     */
    public function summary(): array
    {
        $customer = $this->customer();
        $vendor   = $this->vendor();
        $profit   = $customer - $vendor;
        $margin   = 0;
        $markup   = 0;

        if ($customer > 0) {
            $margin = ($profit / $customer) * 100;
        }
        if ($vendor > 0) {
            $markup = ($profit / $vendor) * 100;
        }

        return [
            'customer_total' => round($customer, 2),
            'vendor_total'   => round($vendor, 2),
            'profit'         => round($profit, 2),
            'margin'         => round($margin, 2),
            'markup'         => round($markup, 2)
        ];
    }

    /**
     * Cari komponen biaya berdasarkan kode
     */
    public function get(string $code): ?array
    {
        foreach ($this->rows as $row) {
            if ($row['component_code'] === strtoupper($code)) {
                return $row;
            }
        }
        return null;
    }

    /**
     * Ambil daftar biaya berdasarkan pihak ✅ Upgrade 2
     */
    public function partyItems(string $party): array
    {
        return array_values(
            array_filter(
                $this->rows,
                fn($row) => $row['party'] === strtoupper($party)
            )
        );
    }

    /**
     * Ambil total berdasarkan pihak ✅ Upgrade 3
     */
    public function total(string $party): float
    {
        return strtoupper($party) === 'CUSTOMER'
            ? $this->customer()
            : $this->vendor();
    }

    /**
     * Urutkan komponen biaya sesuai urutan yang ditentukan ✅ Upgrade 4
     */
    public function sort(): void
    {
        $order = [
            'FREIGHT',
            'COMMODITY',
            'REMOTE',
            'INSURANCE',
            'PACKING',
            'ADDITIONAL',
            'DISCOUNT',
            'FUEL',
            'GOGREEN',
            'VAT'
        ];

        usort($this->rows, function ($a, $b) use ($order) {
            $posA = array_search($a['component_code'], $order) !== false ? array_search($a['component_code'], $order) : 999;
            $posB = array_search($b['component_code'], $order) !== false ? array_search($b['component_code'], $order) : 999;
            return $posA <=> $posB;
        });
    }

    /**
     * Hapus semua data biaya
     */
    public function clear(): void
    {
        $this->rows = [];
    }
}