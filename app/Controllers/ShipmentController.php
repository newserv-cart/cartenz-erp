<?php

namespace App\Controllers;
// Taruh ini di bagian paling atas, sebelum class
use App\Services\RegularRateService;
use App\Controllers\BaseController;
use App\Libraries\AwbGenerator;
use App\Models\ShipmentPackageModel;
use App\Models\ShipmentPackageItemModel;
use App\Models\ShipmentTrackingModel;
use App\Models\ShipmentVendorTrackingEventModel;
use App\Models\TrackingStatusModel;
use App\Models\CustomerModel;
use App\Models\CustomerAddressModel;
use Dompdf\Dompdf;
use Dompdf\Options;
use CodeIgniter\Shield\Entities\User;
use App\Models\CountryModel;
use App\Models\ServiceModel;
use App\Models\PackageTypeModel;
use App\Models\CommodityTypeModel;
use App\Libraries\RegularRate;
use App\Models\CommodityChargeRuleModel;
use App\Models\ShipmentModel;
use App\Models\ShipmentCostDetailModel;
use App\Models\ShipmentHistoryModel;
use App\Services\ShipmentCalculationService;
use App\Services\ShipmentOwnerContextService;
use App\Services\CommodityRuleService;
use App\Services\PricingEngine;
use App\Services\ShipmentLifecycleService;
use App\Services\ShipmentVendorWorkflowService;
use App\Services\VendorTransitTrackingService;
use App\Models\InboundIntakeModel;
use App\Models\InboundIntakePackageModel;
use App\Models\AgentModel;
use App\Services\ShipmentAcceptanceService;
use App\Services\BranchOperationalContextService;

class ShipmentController extends BaseController
{
    public function index()
    {
        if (!$this->isOperationalUser()) {
            return redirect()->to(site_url('customer/dashboard'));
        }

        $shipmentModel = new ShipmentModel();
        $lifecycle     = new ShipmentLifecycleService();
        $access        = $this->shipmentAccessContext();

        if (!$access['is_head_office']) {
            $agentId = (int) ($access['agent_id'] ?? 0);

            if ($agentId <= 0) {
                return redirect()->to(site_url('agent/dashboard'))
                    ->with('error', 'User belum terhubung ke agent aktif.');
            }

            $shipmentModel->where('agent_id', $agentId);
        }

        $shipments = $shipmentModel
            ->orderBy('id', 'DESC')
            ->findAll();

        foreach ($shipments as &$shipment) {
            $shipment['_actions'] =
                $lifecycle->getActionState($shipment);
        }
        unset($shipment);

        return view('shipments/index', [
            'shipments' => $shipments,
        ]);
    }
public function testAwb()
{
    return AwbGenerator::generate();
}

public function create()
{
    if (!$this->isOperationalUser()) {
        return redirect()->to(
            site_url('customer/dashboard')
        );
    }

    $db = db_connect();
    $acceptanceService = new ShipmentAcceptanceService();

    $inboundIntake  = null;
    $intakePackages = [];
    $intakeId       = (int) $this->request->getGet('intake_id');

    if ($intakeId > 0) {
        $inboundIntake = (new InboundIntakeModel())
            ->find($intakeId);

        if (
            !$inboundIntake
            || strtoupper(
                (string) (
                    $inboundIntake['intake_status'] ?? ''
                )
            ) !== 'READY_TO_CONVERT'
            || !empty(
                $inboundIntake['converted_shipment_id']
            )
        ) {
            return redirect()
                ->to(site_url('inbound-intakes'))
                ->with(
                    'error',
                    'Inbound Intake tidak tersedia untuk dikonversi.'
                );
        }

        $intakePackages =
            (new InboundIntakePackageModel())
                ->where(
                    'inbound_intake_id',
                    $intakeId
                )
                ->orderBy('package_no', 'ASC')
                ->findAll();
    }

    // Kode create lama berikutnya tetap lanjut di sini.

        $senderAddressModel   = new CustomerAddressModel();
        $receiverAddressModel = new CustomerAddressModel();

        $addressSelect = implode(', ', [
            'customer_addresses.*',
            'countries.country_code',
            'countries.country_name',
            'express_zones.zone_code AS express_zone_code',
            'customers.customer_name AS owner_customer_name',
            'customers.customer_code AS owner_customer_code',
        ]);

        $senderOwnerCustomerId = 1;

        $senderSql = <<<'SQL'
SELECT
    ca.*,
    countries.country_code,
    countries.country_name,
    express_zones.zone_code AS express_zone_code,
    customers.customer_name AS owner_customer_name,
    customers.customer_code AS owner_customer_code

FROM customer_addresses ca

INNER JOIN (
    SELECT
        MAX(x.id) AS selected_id

    FROM customer_addresses x

    WHERE x.customer_id = ?
      AND x.address_type = 'SENDER'
      AND x.status = 1

      /* Nama wajib layak */
      AND CHAR_LENGTH(
            TRIM(COALESCE(x.contact_name, ''))
          ) >= 3

      AND x.contact_name REGEXP '[A-Za-z]'

      /* Telepon harus mengandung angka */
      AND x.phone REGEXP '[0-9]'

      AND CHAR_LENGTH(
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                TRIM(COALESCE(x.phone, '')),
                                ' ',
                                ''
                            ),
                            '-',
                            ''
                        ),
                        '+',
                        ''
                    ),
                    '(',
                    ''
                ),
                ')',
                ''
            )
          ) >= 7

      /* Alamat minimal layak */
      AND CHAR_LENGTH(
            TRIM(COALESCE(x.address, ''))
          ) >= 10

      /* Minimal memiliki kota atau kode pos */
      AND (
            TRIM(COALESCE(x.city, '')) <> ''
            OR
            TRIM(COALESCE(x.postal_code, '')) <> ''
          )

      /*
       * Jangan tampilkan nama pengirim apabila salah satu
       * data duplikatnya sudah masuk invoice.
       */
      AND NOT EXISTS (
          SELECT 1

          FROM customer_addresses used_address

          INNER JOIN shipments s
              ON s.sender_address_id = used_address.id

          WHERE used_address.customer_id = x.customer_id
            AND used_address.address_type = 'SENDER'

            AND UPPER(
                    TRIM(
                        COALESCE(
                            used_address.contact_name,
                            ''
                        )
                    )
                )
                =
                UPPER(
                    TRIM(
                        COALESCE(
                            x.contact_name,
                            ''
                        )
                    )
                )

            AND (
                s.invoice_id IS NOT NULL
                OR COALESCE(s.is_invoiced, 0) = 1
            )
      )

    /*
     * Satu nama pengirim hanya satu baris.
     * MAX(id) mengambil data terbaru.
     */
    GROUP BY
        x.customer_id,
        UPPER(
            TRIM(
                COALESCE(
                    x.contact_name,
                    ''
                )
            )
        )

) clean_sender
    ON clean_sender.selected_id = ca.id

LEFT JOIN countries
    ON countries.id = ca.country_id

LEFT JOIN express_zones
    ON express_zones.id = countries.express_zone_id

INNER JOIN customers
    ON customers.id = ca.customer_id

WHERE customers.status = 1

ORDER BY
    ca.contact_name ASC
SQL;

        $senderAddresses = $db
            ->query(
                $senderSql,
                [$senderOwnerCustomerId]
            )
            ->getResultArray();

        $receiverAddresses = $receiverAddressModel
            ->select($addressSelect)
            ->join(
                'countries',
                'countries.id = customer_addresses.country_id',
                'left'
            )
            ->join(
                'express_zones',
                'express_zones.id = countries.express_zone_id',
                'left'
            )
            ->join(
                'customers',
                'customers.id = customer_addresses.customer_id',
                'inner'
            )
            ->where('customer_addresses.address_type', 'RECEIVER')
            ->where('customer_addresses.status', 1)
            ->where('customers.status', 1)
            ->orderBy('customer_addresses.contact_name', 'ASC')
            ->findAll();

        $automaticReceiving = $acceptanceService
            ->automaticContext(
                (int) (auth()->id() ?? 0),
                $inboundIntake
            );

        return view('shipments/create', [
            'senderAddresses'   => $senderAddresses,
            'receiverAddresses' => $receiverAddresses,
            'countries'         => (new CountryModel())
                ->select(
                    'countries.*, ' .
                    'express_zones.zone_code AS express_zone_code'
                )
                ->join(
                    'express_zones',
                    'express_zones.id = countries.express_zone_id',
                    'left'
                )
                ->where('countries.status', 1)
                ->orderBy('country_name', 'ASC')
                ->findAll(),
            'services'          => (new ServiceModel())
                ->where('status', 1)
                ->orderBy('service_name', 'ASC')
                ->findAll(),
            'packageTypes'      => (new PackageTypeModel())
                ->where('status', 1)
                ->orderBy('package_type_name', 'ASC')
                ->findAll(),
            'commodityTypes'    => (new CommodityTypeModel())
                ->where('status', 1)
                ->orderBy('commodity_name', 'ASC')
                ->findAll(),
            'transportModes'    => $db->table('transport_modes')
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray(),
            'deliveryTypes'     => $db->table('delivery_types')
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray(),
            'branches'          => $acceptanceService->branchOptions(),
            'acceptanceMethods' => ShipmentAcceptanceService::methodLabels(),
            'automaticReceiving'=> $automaticReceiving,
            'inboundIntake'     => $inboundIntake,
            'intakePackages'    => $intakePackages,
        ]);
    }

    public function view($id)
    {
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();
        $trackingModel = new ShipmentTrackingModel();
        $statusModel   = new TrackingStatusModel();

        $shipment = $shipmentModel
            ->select('shipments.*, customers.customer_name, customers.company_name, customers.phone, customers.email')
            ->join('customers', 'customers.id = shipments.customer_id', 'left')
            ->where('shipments.id', $id)
            ->first();

        if (!$shipment) {
            $shipment = $shipmentModel->find($id);
        }

        if (!$shipment) {
            return redirect()->to(site_url('shipments'))->with('error', 'Shipment tidak ditemukan atau sudah dihapus');
        }

        $this->assertShipmentReadable($shipment);

        $packages = $packageModel->where('shipment_id', $id)->findAll();
        foreach ($packages as &$package) {
            $package['items'] = $itemModel->where('shipment_package_id', $package['id'])->findAll();
        }

        $tracking = $trackingModel->where('shipment_id', $id)->orderBy('event_time', 'DESC')->findAll();
        $statuses = $statusModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();

        return view('shipments/view', [
            'shipment' => $shipment,
            'packages' => $packages,
            'tracking' => $tracking,
            'statuses' => $statuses,
        ]);
    }

    public function edit($id)
    {
        $shipmentId = (int) $id;
        $db = db_connect();

        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();
        $costModel     = new ShipmentCostDetailModel();

        $shipment = $shipmentModel->find($shipmentId);

        if (!$shipment) {
            return redirect()
                ->to(site_url('shipments'))
                ->with(
                    'error',
                    'Shipment tidak ditemukan atau sudah dihapus.'
                );
        }

        try {
            $this->assertShipmentEditable(
                $shipment,
                $db
            );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . $shipmentId))
                ->with('error', $e->getMessage());
        }

        $addressSelect = implode(', ', [
            'customer_addresses.*',
            'countries.country_code',
            'countries.country_name',
            'express_zones.zone_code AS express_zone_code',
            'customers.customer_name AS owner_customer_name',
            'customers.customer_code AS owner_customer_code',
        ]);

        $senderOwnerCustomerId = 1;

        $senderSql = <<<'SQL'
SELECT
    ca.*,
    countries.country_code,
    countries.country_name,
    express_zones.zone_code AS express_zone_code,
    customers.customer_name AS owner_customer_name,
    customers.customer_code AS owner_customer_code

FROM customer_addresses ca

INNER JOIN (
    SELECT
        MAX(x.id) AS selected_id

    FROM customer_addresses x

    WHERE x.customer_id = ?
      AND x.address_type = 'SENDER'
      AND x.status = 1

      /* Nama wajib layak */
      AND CHAR_LENGTH(
            TRIM(COALESCE(x.contact_name, ''))
          ) >= 3

      AND x.contact_name REGEXP '[A-Za-z]'

      /* Telepon harus mengandung angka */
      AND x.phone REGEXP '[0-9]'

      AND CHAR_LENGTH(
            REPLACE(
                REPLACE(
                    REPLACE(
                        REPLACE(
                            REPLACE(
                                TRIM(COALESCE(x.phone, '')),
                                ' ',
                                ''
                            ),
                            '-',
                            ''
                        ),
                        '+',
                        ''
                    ),
                    '(',
                    ''
                ),
                ')',
                ''
            )
          ) >= 7

      /* Alamat minimal layak */
      AND CHAR_LENGTH(
            TRIM(COALESCE(x.address, ''))
          ) >= 10

      /* Minimal memiliki kota atau kode pos */
      AND (
            TRIM(COALESCE(x.city, '')) <> ''
            OR
            TRIM(COALESCE(x.postal_code, '')) <> ''
          )

      /*
       * Jangan tampilkan nama pengirim apabila salah satu
       * data duplikatnya sudah masuk invoice.
       */
      AND NOT EXISTS (
          SELECT 1

          FROM customer_addresses used_address

          INNER JOIN shipments s
              ON s.sender_address_id = used_address.id

          WHERE used_address.customer_id = x.customer_id
            AND used_address.address_type = 'SENDER'

            AND UPPER(
                    TRIM(
                        COALESCE(
                            used_address.contact_name,
                            ''
                        )
                    )
                )
                =
                UPPER(
                    TRIM(
                        COALESCE(
                            x.contact_name,
                            ''
                        )
                    )
                )

            AND (
                s.invoice_id IS NOT NULL
                OR COALESCE(s.is_invoiced, 0) = 1
            )
      )

    /*
     * Satu nama pengirim hanya satu baris.
     * MAX(id) mengambil data terbaru.
     */
    GROUP BY
        x.customer_id,
        UPPER(
            TRIM(
                COALESCE(
                    x.contact_name,
                    ''
                )
            )
        )

) clean_sender
    ON clean_sender.selected_id = ca.id

LEFT JOIN countries
    ON countries.id = ca.country_id

LEFT JOIN express_zones
    ON express_zones.id = countries.express_zone_id

INNER JOIN customers
    ON customers.id = ca.customer_id

WHERE customers.status = 1

ORDER BY
    ca.contact_name ASC
SQL;

        $senderAddresses = $db
            ->query(
                $senderSql,
                [$senderOwnerCustomerId]
            )
            ->getResultArray();

        /*
         * Pastikan alamat sender yang tersimpan di shipment tetap
         * tersedia di dropdown saat edit, meskipun bukan MAX(id)
         * untuk contact name-nya.
         */
        $selectedSenderId = (int) (
            $shipment['sender_address_id'] ?? 0
        );

        if ($selectedSenderId > 0) {
            $currentSender = (new CustomerAddressModel())
                ->select($addressSelect)
                ->join(
                    'countries',
                    'countries.id = customer_addresses.country_id',
                    'left'
                )
                ->join(
                    'express_zones',
                    'express_zones.id = countries.express_zone_id',
                    'left'
                )
                ->join(
                    'customers',
                    'customers.id = customer_addresses.customer_id',
                    'inner'
                )
                ->where('customer_addresses.id', $selectedSenderId)
                ->where('customer_addresses.address_type', 'SENDER')
                ->where('customer_addresses.status', 1)
                ->where('customers.status', 1)
                ->first();

            if ($currentSender) {
                $replaced = false;
                $currentContact = trim(
                    (string) ($currentSender['contact_name'] ?? '')
                );
                $currentCustomerId = (int) (
                    $currentSender['customer_id'] ?? 0
                );

                foreach ($senderAddresses as $key => $sender) {
                    $senderContact = trim(
                        (string) ($sender['contact_name'] ?? '')
                    );

                    if (
                        (int) ($sender['customer_id'] ?? 0)
                            === $currentCustomerId
                        && strcasecmp(
                            $senderContact,
                            $currentContact
                        ) === 0
                    ) {
                        $senderAddresses[$key] = $currentSender;
                        $replaced = true;
                        break;
                    }
                }

                if (!$replaced) {
                    $senderAddresses[] = $currentSender;
                }
            }
        }

        $receiverAddresses = (new CustomerAddressModel())
            ->select($addressSelect)
            ->join(
                'countries',
                'countries.id = customer_addresses.country_id',
                'left'
            )
            ->join(
                'express_zones',
                'express_zones.id = countries.express_zone_id',
                'left'
            )
            ->join(
                'customers',
                'customers.id = customer_addresses.customer_id',
                'inner'
            )
            ->where(
                'customer_addresses.address_type',
                'RECEIVER'
            )
            ->where('customer_addresses.status', 1)
            ->where('customers.status', 1)
            ->orderBy(
                'customer_addresses.contact_name',
                'ASC'
            )
            ->findAll();

        $packages = $packageModel
            ->where('shipment_id', $shipmentId)
            ->orderBy('package_no', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();

        foreach ($packages as &$package) {
            $package['items'] = $itemModel
                ->select(
                    'shipment_package_items.*, ' .
                    'currencies.currency_code'
                )
                ->join(
                    'currencies',
                    'currencies.id = ' .
                    'shipment_package_items.currency_id',
                    'left'
                )
                ->where(
                    'shipment_package_id',
                    $package['id']
                )
                ->orderBy(
                    'shipment_package_items.id',
                    'ASC'
                )
                ->findAll();
        }
        unset($package);

        if ($packages === []) {
            $packages = [[
                'package_no'       => 1,
                'actual_weight'    => 0,
                'length_cm'        => 0,
                'width_cm'         => 0,
                'height_cm'        => 0,
                'volume_weight'    => 0,
                'chargeable_weight'=> 0,
                'items'            => [[
                    'item_description' => '',
                    'quantity'         => 1,
                    'currency_code'    => 'USD',
                    'unit_value'       => 0,
                    'total_value'      => 0,
                ]],
            ]];
        }

        $costDetails = $costModel
            ->where('shipment_id', $shipmentId)
            ->findAll();

        $editCharges = [
            'insurance_customer' => $this->findCostAmount(
                $costDetails,
                'CUSTOMER',
                'INSURANCE',
                (float) ($shipment['insurance_amount'] ?? 0)
            ),
            'packing_customer' => $this->findCostAmount(
                $costDetails,
                'CUSTOMER',
                'PACKING',
                (float) ($shipment['packing_charge'] ?? 0)
            ),
            'additional_customer' => $this->findCostAmount(
                $costDetails,
                'CUSTOMER',
                'ADDITIONAL',
                (float) ($shipment['additional_charge'] ?? 0)
            ),
            'discount_amount' => abs(
                $this->findCostAmount(
                    $costDetails,
                    'CUSTOMER',
                    'DISCOUNT',
                    (float) ($shipment['discount_amount'] ?? 0)
                )
            ),
            'insurance_vendor' => $this->findCostAmount(
                $costDetails,
                'VENDOR',
                'INSURANCE',
                0
            ),
            'packing_vendor' => $this->findCostAmount(
                $costDetails,
                'VENDOR',
                'PACKING',
                0
            ),
            'additional_vendor' => $this->findCostAmount(
                $costDetails,
                'VENDOR',
                'ADDITIONAL',
                0
            ),
        ];

        return view('shipments/edit', [
            'shipment'          => $shipment,
            'packages'          => $packages,
            'senderAddresses'   => $senderAddresses,
            'receiverAddresses' => $receiverAddresses,
            'countries'         => (new CountryModel())
                ->select(
                    'countries.*, ' .
                    'express_zones.zone_code AS express_zone_code'
                )
                ->join(
                    'express_zones',
                    'express_zones.id = countries.express_zone_id',
                    'left'
                )
                ->where('countries.status', 1)
                ->orderBy('country_name', 'ASC')
                ->findAll(),
            'services'          => (new ServiceModel())
                ->where('status', 1)
                ->orderBy('service_name', 'ASC')
                ->findAll(),
            'packageTypes'      => (new PackageTypeModel())
                ->where('status', 1)
                ->orderBy('package_type_name', 'ASC')
                ->findAll(),
            'commodityTypes'    => (new CommodityTypeModel())
                ->where('status', 1)
                ->orderBy('commodity_name', 'ASC')
                ->findAll(),
            'transportModes'    => $db->table('transport_modes')
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray(),
            'deliveryTypes'     => $db->table('delivery_types')
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray(),
            'branches'          => (new ShipmentAcceptanceService())->branchOptions(),
            'acceptanceMethods' => ShipmentAcceptanceService::methodLabels(),
            'operationalProfile'=> (new BranchOperationalContextService())
                ->profileForUser((int) (auth()->id() ?? 0)),
            'editCharges'       => $editCharges,
        ]);
    }

    public function update($id)
    {
        $shipmentId = (int) $id;
        $db = db_connect();

        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();
        $costModel     = new ShipmentCostDetailModel();

        $db->transBegin();

        try {
            $shipment = $shipmentModel->find($shipmentId);

            if (!$shipment) {
                throw new \RuntimeException(
                    'Shipment tidak ditemukan.'
                );
            }

            $this->assertShipmentEditable(
                $shipment,
                $db
            );

            $post = $this->request->getPost();
            $actorId = (int) (auth()->id() ?? 0);

            if ($actorId <= 0) {
                throw new \RuntimeException(
                    'Sesi user tidak ditemukan.'
                );
            }

            $rules = [
                'customer_id'       =>
                    'required|is_natural_no_zero',
                'sender_address_id' =>
                    'required|is_natural_no_zero',
                'receiver_id'       =>
                    'required|is_natural_no_zero',
                'service_id'        =>
                    'required|is_natural_no_zero',
                'package_type_id'   =>
                    'required|is_natural_no_zero',
                'commodity_type_id' =>
                    'required|is_natural_no_zero',
                'payment_type'      =>
                    'required|in_list[CASH,DEPOSIT,INVOICE]',
                'shipment_date'     =>
                    'required|valid_date[Y-m-d]',
                'transport_mode_id' =>
                    'required|is_natural_no_zero',
                'delivery_type_id' =>
                    'required|is_natural_no_zero',
            ];

            if (!$this->validate($rules)) {
                throw new \RuntimeException(
                    'Validasi gagal: ' .
                    implode(
                        ', ',
                        $this->validator->getErrors()
                    )
                );
            }

            if (
                empty($post['packages'])
                || !is_array($post['packages'])
            ) {
                throw new \RuntimeException(
                    'Data paket belum diinput.'
                );
            }

            $existingOwnerType = strtoupper(
                trim(
                    (string) (
                        $shipment['owner_type']
                        ?? (
                            !empty($shipment['customer_id'])
                                ? 'CUSTOMER'
                                : 'AGENT'
                        )
                    )
                )
            );

            $existingOwnerId = (int) (
                $shipment['owner_id']
                ?? $shipment['customer_id']
                ?? $shipment['agent_id']
                ?? 0
            );

            try {
                $ownerContext = (new ShipmentOwnerContextService())
                    ->resolve(
                        $actorId,
                        $this->isOperationalUser(),
                        $existingOwnerType,
                        $existingOwnerId
                    );
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    $e->getMessage()
                );
            }

            $customerId = (int) (
                $ownerContext['customer_id'] ?? 0
            );

            $sourceAgentId = (int) (
                $ownerContext['source_agent_id'] ?? 0
            );

            $senderAddressId =
                (int) $post['sender_address_id'];

            $receiverId =
                (int) $post['receiver_id'];

            $serviceId =
                (int) $post['service_id'];

            $packageTypeId =
                (int) $post['package_type_id'];

            $commodityId =
                (int) $post['commodity_type_id'];

            $transportModeId = max(
                1,
                (int) (
                    $post['transport_mode_id']
                    ?? $shipment['transport_mode_id']
                    ?? 1
                )
            );

            $deliveryTypeId = (int) ($post['delivery_type_id'] ?? 0);

            // Acceptance is an operational audit snapshot.
            // Editing commercial shipment data must not silently
            // move the shipment to another branch.
            $acceptanceMethod = (string) (
                $shipment['acceptance_method']
                ?? ShipmentAcceptanceService::DATA_ENTRY_ONLY
            );

            $receivingBranchId = !empty(
                $shipment['receiving_branch_id']
            )
                ? (int) $shipment['receiving_branch_id']
                : null;

            $receivingLocation = trim(
                (string) (
                    $shipment['receiving_location']
                    ?? 'Cartenz System'
                )
            );

            $customer = $customerId > 0
                ? (new CustomerModel())
                    ->where('id', $customerId)
                    ->where('status', 1)
                    ->first()
                : null;

            if (!$customer && $ownerContext['owner_type'] === 'CUSTOMER') {
                throw new \RuntimeException(
                    'Customer tidak ditemukan atau tidak aktif.'
                );
            }

            $sender = (new CustomerAddressModel())
                ->where('id', $senderAddressId)
                ->where('owner_type', $ownerContext['owner_type'])
                ->where('owner_id', $ownerContext['owner_id'])
                ->where('address_type', 'SENDER')
                ->where('status', 1)
                ->first();

            if (!$sender) {
                throw new \RuntimeException(
                    'Alamat sender tidak ditemukan atau bukan ' .
                    'milik owner terpilih.'
                );
            }

            $receiver = (new CustomerAddressModel())
                ->where('id', $receiverId)
                ->where('owner_type', $ownerContext['owner_type'])
                ->where('owner_id', $ownerContext['owner_id'])
                ->where('address_type', 'RECEIVER')
                ->where('status', 1)
                ->first();

            if (!$receiver) {
                throw new \RuntimeException(
                    'Alamat receiver tidak ditemukan atau bukan ' .
                    'milik owner terpilih.'
                );
            }

            $countryId =
                (int) ($receiver['country_id'] ?? 0);

            if ($countryId <= 0) {
                throw new \RuntimeException(
                    'Negara tujuan receiver belum lengkap.'
                );
            }

            if (
                !empty($post['country_id'])
                && (int) $post['country_id'] !== $countryId
            ) {
                throw new \RuntimeException(
                    'Negara tujuan tidak sama dengan alamat receiver.'
                );
            }

            $country = (new CountryModel())
                ->where('id', $countryId)
                ->where('status', 1)
                ->first();

            $service = (new ServiceModel())
                ->where('id', $serviceId)
                ->where('status', 1)
                ->first();

            $packageType = (new PackageTypeModel())
                ->where('id', $packageTypeId)
                ->where('status', 1)
                ->first();

            $commodity = (new CommodityTypeModel())
                ->where('id', $commodityId)
                ->where('status', 1)
                ->first();

            if (
                !$country
                || !$service
                || !$packageType
                || !$commodity
            ) {
                throw new \RuntimeException(
                    'Master Country, Service, Package Type, ' .
                    'atau Commodity tidak valid.'
                );
            }

            $transportMode = $db
                ->table('transport_modes')
                ->where('id', $transportModeId)
                ->where('status', 1)
                ->get()
                ->getRowArray();

            if (!$transportMode) {
                throw new \RuntimeException(
                    'Transport mode tidak valid.'
                );
            }

            $deliveryType = $db
                ->table('delivery_types')
                ->where('id', $deliveryTypeId)
                ->where('status', 1)
                ->get()
                ->getRowArray();

            if (!$deliveryType) {
                throw new \RuntimeException('Delivery type tidak valid.');
            }

            $packageSummary = $this->normalizePackages(
                $post['packages'],
                $db
            );

            $customerInsurance = $this->money(
                $post['insurance_customer'] ?? 0
            );

            $customerPacking = $this->money(
                $post['packing_customer'] ?? 0
            );

            $customerAdditional = $this->money(
                $post['additional_customer'] ?? 0
            );

            $discountAmount = $this->money(
                $post['discount_amount'] ?? 0
            );

            $vendorInsurance = $this->money(
                $post['insurance_vendor'] ?? 0
            );

            $vendorPacking = $this->money(
                $post['packing_vendor'] ?? 0
            );

            $vendorAdditional = $this->money(
                $post['additional_vendor'] ?? 0
            );

            $shipmentType = strtoupper(
                (string) (
                    $packageType['package_type_code']
                    ?? 'PACKAGE'
                )
            );

            if (
                !in_array(
                    $shipmentType,
                    ['DOCUMENT', 'PACKAGE'],
                    true
                )
            ) {
                $shipmentType = str_contains(
                    $shipmentType,
                    'DOC'
                )
                    ? 'DOCUMENT'
                    : 'PACKAGE';
            }

            $zone = $this->resolveExpressZone(
                $db,
                (int) (
                    $country['express_zone_id']
                    ?? 0
                )
            );

            $pricingInput = [
                'customer_id'       => $customerId,
                'shipment_date'     => (string) $post['shipment_date'],
                'country_id'        => $countryId,
                'service_id'        => $serviceId,
                'commodity_type_id' => $commodityId,
                'transport_mode_id' => $transportModeId,
                'package_type_id'   => $packageTypeId,
                'vendor_id'         =>
                    (int) ($service['vendor_id'] ?? 0),
                'shipment_type'     => $shipmentType,
                'zone'              => $zone,
                'actual_weight'     =>
                    $packageSummary['actual_weight'],
                'volume_weight'     =>
                    $packageSummary['chargeable_weight'],
                'chargeable_weight' =>
                    $packageSummary['chargeable_weight'],
                'total_colly'       =>
                    $packageSummary['total_colly'],
                'total_items'       =>
                    $packageSummary['total_items'],
                'insurance_amount'  => $customerInsurance,
                'packing_charge'    => $customerPacking,
                'additional_charge' => $customerAdditional,
                'discount_amount'   => $discountAmount,
                'discount'          => $discountAmount,
                'receiver_city'     =>
                    (string) ($receiver['city'] ?? ''),
                'receiver_postcode' =>
                    (string) (
                        $receiver['postal_code']
                        ?? ''
                    ),
            ];

            $pricing =
                (new ShipmentCalculationService())
                    ->calculate($pricingInput);

            $freight = $this->money(
                $pricing['freight'] ?? 0
            );

            $customerTotal = $this->money(
                $pricing['customer_total'] ?? 0
            );

            $vendorBaseTotal = $this->money(
                $pricing['vendor_total'] ?? 0
            );

            if (
                $freight <= 0
                || $customerTotal <= 0
            ) {
                log_message('error', 'CUSTOMER RATE DEBUG: ' . json_encode([
                    'customer_id'       => $this->request->getPost('customer_id'),
                    'sender_address_id' => $this->request->getPost('sender_address_id'),
                    'country_id'        => $this->request->getPost('country_id'),
                    'service_id'        => $this->request->getPost('service_id'),
                    'package_type_id'   => $this->request->getPost('package_type_id'),
                    'commodity_type_id' => $this->request->getPost('commodity_type_id'),
                    'transport_mode_id' => $this->request->getPost('transport_mode_id'),
                    'delivery_type_id'  => $this->request->getPost('delivery_type_id'),
                    'actual_weight'     => $this->request->getPost('actual_weight'),
                    'chargeable_weight' => $this->request->getPost('chargeable_weight'),
                    'payment_type'      => $this->request->getPost('payment_type'),
                ], JSON_UNESCAPED_SLASHES));

                throw new \RuntimeException(
                    'Tarif customer tidak ditemukan. ' .
                    'Periksa master tariff.'
                );
            }

            $vendorTotal = round(
                $vendorBaseTotal
                + $vendorInsurance
                + $vendorPacking
                + $vendorAdditional,
                2
            );

            $profit = round(
                $customerTotal - $vendorTotal,
                2
            );

            $margin = $customerTotal > 0
                ? round(
                    ($profit / $customerTotal) * 100,
                    2
                )
                : 0;

            $markup = $vendorTotal > 0
                ? round(
                    ($profit / $vendorTotal) * 100,
                    2
                )
                : 0;

            $shipmentData = [
                'company_id'           =>
                    $customer['company_id'] ?? null,
                'customer_reference_no'=>
                    $this->clean(
                        $post['customer_reference_no']
                        ?? ''
                    ),
                'shipment_date'        =>
                    $post['shipment_date'],
                'customer_id'          => $ownerContext['customer_id'],
                'owner_type'           => $ownerContext['owner_type'],
                'owner_id'             => $ownerContext['owner_id'],
                'created_by_user_id'   => $ownerContext['created_by_user_id'],
                'entry_channel'        => $ownerContext['entry_channel'],
                'sender_address_id'    => $senderAddressId,
                'sender_country_id'    =>
                    $sender['country_id'] ?? null,
                'receiver_address_id'  => $receiverId,
                'agent_id'             =>
                    $sourceAgentId > 0
                        ? $sourceAgentId
                        : ($ownerContext['agent_id'] ?? $customer['agent_id'] ?? null),
                'vendor_id'            =>
                    $service['vendor_id'] ?? null,
                'country_id'           => $countryId,
                'service_id'           => $serviceId,
                'transport_mode_id'    => $transportModeId,
                'delivery_type_id'     => $deliveryTypeId,
                'acceptance_method'   => $acceptanceMethod,
                'receiving_branch_id' => $receivingBranchId,
                'receiving_location'  => $receivingLocation,
                'package_type_id'      => $packageTypeId,
                'payment_type'         => strtoupper(
                    (string) $post['payment_type']
                ),
                'commodity_type_id'    => $commodityId,
                'total_colly'          =>
                    $packageSummary['total_colly'],
                'actual_weight'        =>
                    $packageSummary['actual_weight'],
                'volume_weight'        =>
                    $packageSummary['volume_weight'],
                'chargeable_weight'    =>
                    $packageSummary['chargeable_weight'],
                'invoice_weight'       => (float) (
                    $pricing['invoice_weight']
                    ?? $pricing['billing_weight']
                    ?? $packageSummary['chargeable_weight']
                    ?? 0
                ),
                'declared_value'       =>
                    $packageSummary['declared_value'],
                'currency_id'          =>
                    $packageSummary['currency_id'],
                'insurance_amount'     => $customerInsurance,
                'packing_charge'       => $customerPacking,
                'additional_charge'    => $customerAdditional,
                'discount_amount'      => $discountAmount,
                'freight_charge'       => $freight,
                'remote_charge'        => $this->money(
                    $pricing['remote'] ?? 0
                ),
                'selling_amount'       => $freight,
                'vendor_cost'          => $vendorBaseTotal,
                'vendor_freight'       => $vendorBaseTotal,
                'fuel_charge'          => $this->money(
                    $pricing['fuel'] ?? 0
                ),
                'vendor_remote_charge' => $this->money(
                    $pricing['vendor_breakdown']['remote']
                    ?? 0
                ),
                'go_green_charge'      => $this->money(
                    $pricing['gogreen']
                    ?? $pricing['go_green']
                    ?? 0
                ),
                'handling_charge'      => $this->money(
                    $pricing['vendor_breakdown']['handling']
                    ?? 0
                ),
                'vat_amount'           => $this->money(
                    $pricing['tax']
                    ?? $pricing['vendor_breakdown']['vat']
                    ?? 0
                ),
                'vendor_total'         => $vendorTotal,
                'total_charge'         => $customerTotal,
                'profit_amount'        => $profit,
                'margin_percent'       => $margin,
                'markup_percent'       => $markup,
                'remarks'              => $this->clean(
                    $post['remarks'] ?? ''
                ),

                'sender_name'          => $this->clean(
                    $sender['contact_name'] ?? ''
                ),
                'sender_company'       => $this->clean(
                    $sender['company_name'] ?? ''
                ),
                'sender_phone'         => $this->clean(
                    $sender['phone'] ?? ''
                ),
                'sender_email'         => $this->clean(
                    $sender['email'] ?? ''
                ),
                'sender_identity_number' => $this->clean(
                    $sender['identity_number'] ?? ''
                ),
                'sender_address'       => $this->clean(
                    $sender['address'] ?? ''
                ),
                'sender_city'          => $this->clean(
                    $sender['city'] ?? ''
                ),
                'sender_state'         => $this->clean(
                    $sender['state'] ?? ''
                ),
                'sender_postal_code'   => $this->clean(
                    $sender['postal_code'] ?? ''
                ),

                'receiver_name'        => $this->clean(
                    $receiver['contact_name'] ?? ''
                ),
                'receiver_company'     => $this->clean(
                    $receiver['company_name'] ?? ''
                ),
                'receiver_phone'       => $this->clean(
                    $receiver['phone'] ?? ''
                ),
                'receiver_email'       => $this->clean(
                    $receiver['email'] ?? ''
                ),
                'receiver_id_number'   => $this->clean(
                    $receiver['identity_number'] ?? ''
                ),
                'receiver_address'     => $this->clean(
                    $receiver['address'] ?? ''
                ),
                'receiver_city'        => $this->clean(
                    $receiver['city'] ?? ''
                ),
                'receiver_state'       => $this->clean(
                    $receiver['state'] ?? ''
                ),
                'receiver_postal_code' => $this->clean(
                    $receiver['postal_code'] ?? ''
                ),
            ];

            if (
                $shipmentModel->update(
                    $shipmentId,
                    $shipmentData
                ) === false
            ) {
                throw new \RuntimeException(
                    'Gagal memperbarui shipment: ' .
                    implode(
                        ', ',
                        $shipmentModel->errors()
                    )
                );
            }

            $existingPackages = $packageModel
                ->where('shipment_id', $shipmentId)
                ->findAll();

            foreach ($existingPackages as $existingPackage) {
                $itemModel
                    ->where(
                        'shipment_package_id',
                        $existingPackage['id']
                    )
                    ->delete();
            }

            $packageModel
                ->where('shipment_id', $shipmentId)
                ->delete();

            $this->savePackages(
                $shipmentId,
                $packageSummary['packages'],
                $packageTypeId
            );

            $costModel
                ->where('shipment_id', $shipmentId)
                ->delete();

            $this->saveCostBreakdown(
                $shipmentId,
                'CUSTOMER',
                [
                    [
                        'code'   => 'FREIGHT',
                        'name'   => 'Freight Customer',
                        'amount' => $freight,
                    ],
                    [
                        'code'   => 'INSURANCE',
                        'name'   => 'Customer Insurance',
                        'amount' => $customerInsurance,
                    ],
                    [
                        'code'   => 'PACKING',
                        'name'   => 'Customer Packing',
                        'amount' => $customerPacking,
                    ],
                    [
                        'code'   => 'ADDITIONAL',
                        'name'   => 'Customer Additional',
                        'amount' => $customerAdditional,
                    ],
                    [
                        'code'   => 'DISCOUNT',
                        'name'   => 'Discount',
                        'amount' => -$discountAmount,
                    ],
                ]
            );

            $this->saveCostBreakdown(
                $shipmentId,
                'VENDOR',
                [
                    [
                        'code'   => 'BASE_VENDOR_COST',
                        'name'   => 'Base Vendor Cost',
                        'amount' => $vendorBaseTotal,
                    ],
                    [
                        'code'   => 'INSURANCE',
                        'name'   => 'Vendor Insurance',
                        'amount' => $vendorInsurance,
                    ],
                    [
                        'code'   => 'PACKING',
                        'name'   => 'Vendor Packing',
                        'amount' => $vendorPacking,
                    ],
                    [
                        'code'   => 'ADDITIONAL',
                        'name'   => 'Vendor Additional',
                        'amount' => $vendorAdditional,
                    ],
                ]
            );

            $this->recordShipmentHistory(
                $shipmentId,
                $shipment,
                $shipmentData,
                $packageSummary
            );

            if ($db->transStatus() === false) {
                throw new \RuntimeException(
                    'Transaksi database gagal.'
                );
            }

            $db->transCommit();

            return redirect()
                ->to(
                    site_url(
                        'shipments/detail/' . $shipmentId
                    )
                )
                ->with(
                    'success',
                    'Shipment berhasil diperbarui dan dihitung ulang.'
                );
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                'Shipment update failed: {message}',
                ['message' => $e->getMessage()]
            );

            return redirect()
                ->back()
                ->withInput()
                ->with(
                    'error',
                    'Gagal memperbarui shipment: ' .
                    $e->getMessage()
                );
        }
    }


    private function assertShipmentEditable(
        array $shipment,
        $db
    ): void {
        $status = strtoupper(
            (string) ($shipment['shipment_status'] ?? '')
        );

        $terminalStatuses = [
            'DELIVERED',
            'RETURN_TO_SENDER',
            'CLOSED',
            'CANCELLED',
        ];

        if (in_array($status, $terminalStatuses, true)) {
            throw new \RuntimeException(
                'Shipment dengan status ' .
                $status .
                ' tidak dapat diedit.'
            );
        }

        if ((int) ($shipment['is_invoiced'] ?? 0) === 1) {
            throw new \RuntimeException(
                'Shipment yang sudah masuk invoice tidak dapat diedit.'
            );
        }

        $submittedPackage = $db
            ->table('shipment_packages')
            ->select('id')
            ->where(
                'shipment_id',
                (int) $shipment['id']
            )
            ->groupStart()
                ->where('is_submitted_to_vendor', 1)
                ->orWhere('vendor_awb_no IS NOT NULL', null, false)
            ->groupEnd()
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($submittedPackage) {
            throw new \RuntimeException(
                'Shipment yang sudah dikirim ke vendor tidak dapat diedit.'
            );
        }

        if (
            !empty($shipment['vendor_cost_locked_at'])
            || in_array(
                strtoupper((string) ($shipment['vendor_assignment_status'] ?? '')),
                ['ASSIGNED', 'LOCKED'],
                true
            )
        ) {
            throw new \RuntimeException(
                'Shipment sudah memiliki vendor assignment dan vendor cost terkunci. Ubah vendor melalui tab Vendor, bukan Edit Shipment.'
            );
        }

        $lockedManifest = $db
            ->table('manifest_details md')
            ->select([
                'm.manifest_no',
                'm.status',
            ])
            ->join(
                'manifests m',
                'm.id = md.manifest_id',
                'inner'
            )
            ->where(
                'md.shipment_id',
                (int) $shipment['id']
            )
            ->whereIn(
                'm.status',
                ['DISPATCHED', 'RECEIVED']
            )
            ->limit(1)
            ->get()
            ->getRowArray();

        if ($lockedManifest) {
            throw new \RuntimeException(
                'Shipment berada dalam manifest ' .
                $lockedManifest['manifest_no'] .
                ' dengan status ' .
                $lockedManifest['status'] .
                ' dan tidak dapat diedit.'
            );
        }
    }

    private function findCostAmount(
        array $costDetails,
        string $party,
        string $componentCode,
        float $fallback = 0
    ): float {
        $party = strtoupper($party);
        $componentCode = strtoupper($componentCode);

        foreach ($costDetails as $cost) {
            if (
                strtoupper((string) ($cost['party'] ?? ''))
                    === $party
                && strtoupper(
                    (string) (
                        $cost['component_code']
                        ?? ''
                    )
                ) === $componentCode
            ) {
                return round(
                    (float) ($cost['amount'] ?? 0),
                    2
                );
            }
        }

        return round($fallback, 2);
    }

    private function recordShipmentHistory(
        int $shipmentId,
        array $oldShipment,
        array $newShipment,
        array $packageSummary
    ): void {
        $trackedFields = [
            'customer_id',
            'sender_address_id',
            'receiver_address_id',
            'country_id',
            'service_id',
            'package_type_id',
            'commodity_type_id',
            'payment_type',
            'total_colly',
            'actual_weight',
            'volume_weight',
            'chargeable_weight',
            'declared_value',
            'total_charge',
            'vendor_total',
            'profit_amount',
            'remarks',
        ];

        $changes = [];

        foreach ($trackedFields as $field) {
            $oldValue = $oldShipment[$field] ?? null;
            $newValue = $newShipment[$field] ?? null;

            if ((string) $oldValue === (string) $newValue) {
                continue;
            }

            $changes[$field] = [
                'from' => $oldValue,
                'to'   => $newValue,
            ];
        }

        $description = sprintf(
            'Shipment diperbarui. Colly %d, chargeable %.2f Kg, ' .
            'customer total Rp %s, vendor total Rp %s.',
            (int) $packageSummary['total_colly'],
            (float) $packageSummary['chargeable_weight'],
            number_format(
                (float) ($newShipment['total_charge'] ?? 0),
                0,
                ',',
                '.'
            ),
            number_format(
                (float) ($newShipment['vendor_total'] ?? 0),
                0,
                ',',
                '.'
            )
        );

        $historyModel = new ShipmentHistoryModel();

        if ($historyModel->insert([
            'shipment_id'   => $shipmentId,
            'event_type'    => 'UPDATED',
            'event_title'   => 'Shipment Updated',
            'description'   => $description,
            'changed_fields'=> json_encode(
                $changes,
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ),
            'created_by'    => auth()->id() ?? null,
            'created_at'    => date('Y-m-d H:i:s'),
        ]) === false) {
            throw new \RuntimeException(
                'Gagal menyimpan shipment history: ' .
                implode(
                    ', ',
                    $historyModel->errors()
                )
            );
        }
    }

    public function delete($id)
    {
        $reason = (string) $this->request->getPost(
            'delete_reason'
        );

        $confirmAwb = (string) $this->request->getPost(
            'confirm_awb'
        );

        try {
            $result = (new ShipmentLifecycleService())
                ->deletePermanently(
                    (int) $id,
                    $reason,
                    $confirmAwb,
                    $this->requireLifecycleActorId()
                );

            return redirect()
                ->to(site_url('shipments'))
                ->with(
                    'success',
                    'Shipment ' .
                    $result['awb_no'] .
                    ' berhasil dihapus permanen. ' .
                    'Audit penghapusan tetap tersimpan.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $reason = (string) $this->request->getPost(
            'cancel_reason'
        );

        try {
            $result = (new ShipmentLifecycleService())
                ->cancel(
                    (int) $id,
                    $reason,
                    $this->requireLifecycleActorId()
                );

            return redirect()
                ->to(
                    site_url(
                        'shipments/detail/' .
                        (int) $id
                    )
                )
                ->with(
                    'success',
                    'Shipment ' .
                    $result['awb_no'] .
                    ' dibatalkan dari status ' .
                    $result['previous_status'] .
                    '.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function printAwb($id)
    {
        $shipmentModel = new ShipmentModel();
        $packageModel  = new ShipmentPackageModel();
        $itemModel     = new ShipmentPackageItemModel();

        $shipment = $shipmentModel->find($id);
        if (!$shipment) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $packages = $packageModel->where('shipment_id', $id)->findAll();
        foreach ($packages as &$package) {
            $package['items'] = $itemModel->where('shipment_package_id', $package['id'])->findAll();
        }

        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode(base_url('track/' . $shipment['awb_no']));

        $html = view('shipments/awb_pdf', ['shipment' => $shipment, 'packages' => $packages, 'qrUrl' => $qrUrl]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream('AWB-' . $shipment['awb_no'] . '.pdf', ['Attachment' => false]);
    }

    public function testPdf()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml('<h1>PDF TEST CARTENZ</h1>');
        $dompdf->setPaper('A4');
        $dompdf->render();
        $dompdf->stream('test.pdf', ['Attachment' => false]);
    }

    public function publicTrackingIndex()
    {
        return view('shipments/tracking', [
            'shipment'           => null,
            'publicEvents'        => [],
            'currentPublicStatus' => null,
            'progressStatus'      => null,
            'notFound'            => false,
            'invalidFormat'       => false,
        ]);
    }

    public function publicTrackingSearch()
    {
        $rawAwb = (string) $this->request->getPost(
            'awb_no'
        );

        if ($rawAwb === '') {
            return redirect()
                ->to(site_url('track'))
                ->withInput()
                ->with(
                    'tracking_error',
                    'Masukkan nomor AWB yang ingin dilacak.'
                );
        }

        if (!AwbGenerator::isValidPublicAwb($rawAwb)) {
            return redirect()
                ->to(site_url('track'))
                ->withInput()
                ->with(
                    'tracking_error',
                    'Masukkan nomor AWB saja dengan format CTZ + 9 angka, contoh: ' .
                    AwbGenerator::PUBLIC_EXAMPLE .
                    '.'
                );
        }

        $awb = AwbGenerator::normalize($rawAwb);

        return redirect()->to(
            site_url('track/' . rawurlencode($awb))
        );
    }

    public function track($awb)
    {
        $rawAwb = (string) $awb;

        if (!AwbGenerator::isValidPublicAwb($rawAwb)) {
            return $this->publicTrackingInvalidFormat(
                AwbGenerator::normalize($rawAwb)
            );
        }

        $awb = AwbGenerator::normalize($rawAwb);

        $shipmentModel = new ShipmentModel();
        $trackingModel = new ShipmentTrackingModel();

        /*
         * Select only customer-safe shipment fields.
         * Internal status, pricing, addresses, vendor cost, and profit
         * are deliberately not passed to the public page.
         */
        $shipment = $shipmentModel
            ->select([
                'shipments.id',
                'shipments.awb_no',
                'shipments.shipment_date',
                'shipments.public_tracking_status',
                'shipments.total_colly',
                'countries.country_name',
                'services.service_name',
            ])
            ->join(
                'countries',
                'countries.id = shipments.country_id',
                'left'
            )
            ->join(
                'services',
                'services.id = shipments.service_id',
                'left'
            )
            ->where('shipments.awb_no', $awb)
            ->first();

        if (!$shipment) {
            return $this->publicTrackingNotFound($awb);
        }

        $trackingRows = $trackingModel->getPublicTrackingHistory(
            (int) $shipment['id']
        );

        $publicEvents = [];

        foreach ($trackingRows as $row) {
            $statusCode = strtoupper(
                trim((string) ($row['public_status'] ?? ''))
            );

            if (!$this->isAllowedPublicStatus($statusCode)) {
                continue;
            }

            $isPublicVendorEvent = (int) ($row['is_public'] ?? 0) === 1
                && (
                    strtoupper((string) ($row['event_type'] ?? ''))
                        === 'VENDOR_TRACKING'
                    || !empty($row['vendor_event_id'])
                );

            $customMessage = $isPublicVendorEvent
                ? trim((string) ($row['tracking_notes'] ?? ''))
                : '';

            $publicEvents[] = [
                'status_code' => $statusCode,
                'status_name' => $this->publicStatusName($statusCode),
                'message'     => $customMessage !== ''
                    ? $customMessage
                    : $this->publicStatusMessage($statusCode),
                'location'    => $isPublicVendorEvent
                    ? trim((string) ($row['tracking_location'] ?? ''))
                    : '',
                'event_time'  => $row['event_time']
                    ?? $row['created_at']
                    ?? null,
            ];
        }

        $currentPublicStatus = strtoupper(
            trim(
                (string) (
                    $shipment['public_tracking_status']
                    ?? ''
                )
            )
        );

        if (
            !$this->isAllowedPublicStatus(
                $currentPublicStatus
            )
        ) {
            $latestPublicEvent = $publicEvents !== []
                ? $publicEvents[array_key_last($publicEvents)]
                : null;

            $currentPublicStatus = $latestPublicEvent['status_code']
                ?? 'CREATED';
        }

        $progressStatus = $currentPublicStatus;

        if (
            in_array(
                $currentPublicStatus,
                ['ON_HOLD', 'RETURN_TO_SENDER', 'CANCELLED'],
                true
            )
        ) {
            for (
                $index = count($publicEvents) - 1;
                $index >= 0;
                $index--
            ) {
                $candidate =
                    $publicEvents[$index]['status_code'];

                if (
                    !in_array(
                        $candidate,
                        ['ON_HOLD', 'RETURN_TO_SENDER', 'CANCELLED'],
                        true
                    )
                ) {
                    $progressStatus = $candidate;
                    break;
                }
            }
        }

        return view('shipments/tracking', [
            'shipment'           => $shipment,
            'publicEvents'        => $publicEvents,
            'currentPublicStatus' => $currentPublicStatus,
            'progressStatus'      => $progressStatus,
            'notFound'            => false,
            'invalidFormat'       => false,
        ]);
    }

    private function publicTrackingNotFound(string $awb)
    {
        $html = view('shipments/tracking', [
            'shipment'           => null,
            'publicEvents'        => [],
            'currentPublicStatus' => null,
            'progressStatus'      => null,
            'notFound'            => true,
            'invalidFormat'       => false,
            'searchedAwb'         => $awb,
        ]);

        return $this->response
            ->setStatusCode(404)
            ->setBody($html);
    }

    private function publicTrackingInvalidFormat(string $awb)
    {
        $html = view('shipments/tracking', [
            'shipment'           => null,
            'publicEvents'        => [],
            'currentPublicStatus' => null,
            'progressStatus'      => null,
            'notFound'            => false,
            'invalidFormat'       => true,
            'searchedAwb'         => $awb,
        ]);

        return $this->response
            ->setStatusCode(422)
            ->setBody($html);
    }

    private function isAllowedPublicStatus(string $status): bool
    {
        return in_array(
            $status,
            [
                'CREATED',
                'PICKED_UP',
                'IN_TRANSIT',
                'OUT_FOR_DELIVERY',
                'DELIVERED',
                'ON_HOLD',
                'RETURN_TO_SENDER',
                'CANCELLED',
            ],
            true
        );
    }

    private function publicStatusName(string $status): string
    {
        return match ($status) {
            'CREATED'          => 'Shipment Created',
            'PICKED_UP'        => 'Picked Up',
            'IN_TRANSIT'       => 'In Transit',
            'OUT_FOR_DELIVERY' => 'Out for Delivery',
            'DELIVERED'        => 'Delivered',
            'ON_HOLD'          => 'On Hold',
            'RETURN_TO_SENDER' => 'Return to Sender',
            'CANCELLED'        => 'Cancelled',
            default            => 'Shipment Update',
        };
    }

    private function publicStatusMessage(string $status): string
    {
        return match ($status) {
            'CREATED' =>
                'Informasi pengiriman telah diterima.',
            'PICKED_UP' =>
                'Kiriman telah dijemput dari pengirim.',
            'IN_TRANSIT' =>
                'Kiriman sedang dalam perjalanan menuju tujuan.',
            'OUT_FOR_DELIVERY' =>
                'Kurir sedang mengantarkan kiriman ke penerima.',
            'DELIVERED' =>
                'Kiriman telah diterima di tujuan.',
            'ON_HOLD' =>
                'Perjalanan kiriman tertunda sementara dan sedang ditangani.',
            'RETURN_TO_SENDER' =>
                'Kiriman sedang dikembalikan kepada pengirim.',
            'CANCELLED' =>
                'Pengiriman telah dibatalkan.',
            default =>
                'Status pengiriman telah diperbarui.',
        };
    }

    public function store()
    {
        $db            = db_connect();
        $shipmentModel = new ShipmentModel();

        $db->transBegin();

        try {
            $post = $this->request->getPost();
            $actorId = (int) (auth()->id() ?? 0);

            if ($actorId <= 0) {
                throw new \RuntimeException(
                    'Sesi user tidak ditemukan.'
                );
            }

            $rules = [
                'customer_id'       => 'required|is_natural_no_zero',
                'sender_address_id' => 'required|is_natural_no_zero',
                'receiver_id'       => 'required|is_natural_no_zero',
                'service_id'        => 'required|is_natural_no_zero',
                'package_type_id'   => 'required|is_natural_no_zero',
                'commodity_type_id' => 'required|is_natural_no_zero',
                'payment_type'      => 'required|in_list[CASH,DEPOSIT,INVOICE]',
                'shipment_date'     => 'required|valid_date[Y-m-d]',
                'transport_mode_id' => 'required|is_natural_no_zero',
                'delivery_type_id'  => 'required|is_natural_no_zero',
            ];

            if (!$this->validate($rules)) {
                throw new \RuntimeException(
                    'Validasi gagal: ' .
                    implode(', ', $this->validator->getErrors())
                );
            }

            if (empty($post['packages']) || !is_array($post['packages'])) {
                throw new \RuntimeException('Data paket belum diinput.');
            }

            $ownerType = strtoupper(
                trim(
                    (string) (
                        $this->request->getPost('owner_type')
                        ?? ''
                    )
                )
            );

            $ownerId = (int) $this->request
                ->getPost('owner_id');

            if ($ownerType === '') {
                if (
                    (int) $this->request
                        ->getPost('customer_id') > 0
                ) {
                    $ownerType = 'CUSTOMER';
                    $ownerId = (int) $this->request
                        ->getPost('customer_id');
                } elseif (
                    (int) $this->request
                        ->getPost('agent_id') > 0
                ) {
                    $ownerType = 'AGENT';
                    $ownerId = (int) $this->request
                        ->getPost('agent_id');
                }
            }

            try {
                $ownerContext = (new ShipmentOwnerContextService())
                    ->resolve(
                        $actorId,
                        $this->isOperationalUser(),
                        $ownerType,
                        $ownerId
                    );
            } catch (\Throwable $e) {
                throw new \RuntimeException(
                    $e->getMessage()
                );
            }

            $customerId = (int) (
                $ownerContext['customer_id'] ?? 0
            );

            $sourceAgentId = (int) (
                $ownerContext['source_agent_id'] ?? 0
            );

            $senderAddressId = (int) $post['sender_address_id'];
            $receiverId     = (int) $post['receiver_id'];
            $serviceId      = (int) $post['service_id'];
            $packageTypeId  = (int) $post['package_type_id'];
            $commodityId    = (int) $post['commodity_type_id'];
            $transportModeId = max(1, (int) ($post['transport_mode_id'] ?? 1));
            $deliveryTypeId = (int) ($post['delivery_type_id'] ?? 0);
            $acceptanceService = new ShipmentAcceptanceService();
            $acceptanceMethod = null;
            $receivingBranchId = null;
            $receivingLocation = '';
            $initialState = null;
            $automaticReceiving = null;
            $inboundIntakeId = (int) ($post['inbound_intake_id'] ?? 0);

            $lockedIntake = null;

            if ($inboundIntakeId > 0) {
                $lockedIntake = $db->query(
                    'SELECT * FROM inbound_intakes WHERE id = ? FOR UPDATE',
                    [$inboundIntakeId]
                )->getRowArray();

                if (
                    !$lockedIntake
                    || strtoupper((string) ($lockedIntake['intake_status'] ?? '')) !== 'READY_TO_CONVERT'
                    || !empty($lockedIntake['converted_shipment_id'])
                ) {
                    throw new \RuntimeException('Inbound Intake sudah tidak dapat dikonversi.');
                }

                $sourceAgentId = (int) ($lockedIntake['agent_id'] ?? 0);
            }

            $automaticReceiving = $acceptanceService
                ->automaticContext(
                    $actorId,
                    $lockedIntake
                );

            if ($automaticReceiving) {
                $acceptanceMethod =
                    $automaticReceiving['acceptance_method'];

                if (
                    !$lockedIntake
                    && $sourceAgentId <= 0
                    && !empty(
                        $automaticReceiving[
                            'profile'
                        ]['agent_id']
                    )
                ) {
                    $sourceAgentId = (int) (
                        $automaticReceiving[
                            'profile'
                        ]['agent_id']
                    );
                }

                $receivingBranchId = (int) (
                    $automaticReceiving[
                        'receiving_branch_id'
                    ]
                    ?? 0
                );

                $receivingLocation = (string) (
                    $automaticReceiving[
                        'receiving_location'
                    ]
                    ?? ''
                );

                $initialState =
                    $automaticReceiving['initial_state'];
            } else {
                $acceptanceMethod =
                    $acceptanceService->normalizeMethod(
                        (string) (
                            $post['acceptance_method']
                            ?? ShipmentAcceptanceService::
                                DATA_ENTRY_ONLY
                        )
                    );

                $receivingBranchId =
                    !empty($post['receiving_branch_id'])
                        ? (int) $post['receiving_branch_id']
                        : null;

                $receivingLocation = trim(
                    (string) (
                        $post['receiving_location']
                        ?? ''
                    )
                );

                if ($receivingLocation === '') {
                    throw new \RuntimeException(
                        'Receiving Location wajib diisi.'
                    );
                }

                if (
                    $acceptanceMethod ===
                        ShipmentAcceptanceService::
                            AGENT_HANDOVER_NO_AWB
                    && !$lockedIntake
                ) {
                    throw new \RuntimeException(
                        'Agent Handover Without AWB wajib dibuat melalui Inbound Intake.'
                    );
                }

                $initialState =
                    $acceptanceService->initialState(
                        $acceptanceMethod
                    );
            }

            $customer = $customerId > 0
                ? (new CustomerModel())
                    ->where('id', $customerId)
                    ->where('status', 1)
                    ->first()
                : null;

            if (!$customer && $ownerContext['owner_type'] === 'CUSTOMER') {
                throw new \RuntimeException('Customer tidak ditemukan atau tidak aktif.');
            }

            $addressModel = new CustomerAddressModel();

            $sender = $addressModel
                ->where('id', $senderAddressId)
                ->where('owner_type', $ownerContext['owner_type'])
                ->where('owner_id', $ownerContext['owner_id'])
                ->where('address_type', 'SENDER')
                ->where('status', 1)
                ->first();

            if (!$sender) {
                throw new \RuntimeException(
                    'Alamat sender tidak ditemukan atau bukan milik owner terpilih.'
                );
            }

            $receiver = (new CustomerAddressModel())
                ->where('id', $receiverId)
                ->where('owner_type', $ownerContext['owner_type'])
                ->where('owner_id', $ownerContext['owner_id'])
                ->where('address_type', 'RECEIVER')
                ->where('status', 1)
                ->first();

            if (!$receiver) {
                throw new \RuntimeException(
                    'Alamat receiver tidak ditemukan atau bukan milik owner terpilih.'
                );
            }

            $countryId = (int) ($receiver['country_id'] ?? 0);

            if ($countryId <= 0) {
                throw new \RuntimeException('Negara tujuan receiver belum lengkap.');
            }

            if (
                !empty($post['country_id']) &&
                (int) $post['country_id'] !== $countryId
            ) {
                throw new \RuntimeException(
                    'Negara tujuan tidak sama dengan alamat receiver.'
                );
            }

            $country = (new CountryModel())
                ->where('id', $countryId)
                ->where('status', 1)
                ->first();

            $service = (new ServiceModel())
                ->where('id', $serviceId)
                ->where('status', 1)
                ->first();

            $packageType = (new PackageTypeModel())
                ->where('id', $packageTypeId)
                ->where('status', 1)
                ->first();

            $commodity = (new CommodityTypeModel())
                ->where('id', $commodityId)
                ->where('status', 1)
                ->first();

            if (!$country || !$service || !$packageType || !$commodity) {
                throw new \RuntimeException(
                    'Master Country, Service, Package Type, atau Commodity tidak valid.'
                );
            }

            $transportMode = $db->table('transport_modes')
                ->where('id', $transportModeId)
                ->where('status', 1)
                ->get()
                ->getRowArray();

            if (!$transportMode) {
                throw new \RuntimeException('Transport mode tidak valid.');
            }

            $deliveryType = $db->table('delivery_types')
                ->where('id', $deliveryTypeId)
                ->where('status', 1)
                ->get()
                ->getRowArray();

            if (!$deliveryType) {
                throw new \RuntimeException('Delivery type tidak valid.');
            }

            if ($sourceAgentId > 0) {
                $sourceAgent = (new AgentModel())
                    ->where('id', $sourceAgentId)
                    ->where('status', 1)
                    ->first();

                if (!$sourceAgent) {
                    throw new \RuntimeException('Source Agent tidak ditemukan atau tidak aktif.');
                }
            }

            $packageSummary = $this->normalizePackages(
                $post['packages'],
                $db
            );

            $customerInsurance = $this->money($post['insurance_customer'] ?? 0);
            $customerPacking   = $this->money($post['packing_customer'] ?? 0);
            $customerAdditional = $this->money($post['additional_customer'] ?? 0);
            $discountAmount    = $this->money($post['discount_amount'] ?? 0);

            $vendorInsurance = $this->money($post['insurance_vendor'] ?? 0);
            $vendorPacking   = $this->money($post['packing_vendor'] ?? 0);
            $vendorAdditional = $this->money($post['additional_vendor'] ?? 0);

            $shipmentType = strtoupper(
                (string) ($packageType['package_type_code'] ?? 'PACKAGE')
            );

            if (!in_array($shipmentType, ['DOCUMENT', 'PACKAGE'], true)) {
                $shipmentType = str_contains($shipmentType, 'DOC')
                    ? 'DOCUMENT'
                    : 'PACKAGE';
            }

            $zone = $this->resolveExpressZone(
                $db,
                (int) ($country['express_zone_id'] ?? 0)
            );

            $pricingInput = [
                'customer_id'        => $customerId,
                'shipment_date'      => (string) $post['shipment_date'],
                'country_id'         => $countryId,
                'service_id'         => $serviceId,
                'commodity_type_id'  => $commodityId,
                'transport_mode_id'  => $transportModeId,
                'package_type_id'    => $packageTypeId,
                'vendor_id'          => (int) ($service['vendor_id'] ?? 0),
                'shipment_type'      => $shipmentType,
                'zone'               => $zone,
                'actual_weight'      => $packageSummary['actual_weight'],

                // Service lama memakai max(actual_weight, volume_weight).
                // Nilai ini sengaja memakai total chargeable multi-colly.
                'volume_weight'      => $packageSummary['chargeable_weight'],
                'chargeable_weight'  => $packageSummary['chargeable_weight'],
                'total_colly'        => $packageSummary['total_colly'],
                'total_items'        => $packageSummary['total_items'],
                'insurance_amount'   => $customerInsurance,
                'packing_charge'     => $customerPacking,
                'additional_charge'  => $customerAdditional,
                'discount_amount'    => $discountAmount,
                'discount'           => $discountAmount,
                'receiver_city'      => (string) ($receiver['city'] ?? ''),
                'receiver_postcode'  => (string) ($receiver['postal_code'] ?? ''),
            ];

            $pricing = (new ShipmentCalculationService())
                ->calculate($pricingInput);

            $freight = $this->money($pricing['freight'] ?? 0);
            $customerTotal = $this->money(
                $pricing['customer_total'] ?? 0
            );
            $vendorBaseTotal = $this->money(
                $pricing['vendor_total'] ?? 0
            );

            if ($freight <= 0 || $customerTotal <= 0) {
                log_message('error', 'CUSTOMER RATE DEBUG: ' . json_encode([
                    'customer_id'       => $this->request->getPost('customer_id'),
                    'sender_address_id' => $this->request->getPost('sender_address_id'),
                    'country_id'        => $this->request->getPost('country_id'),
                    'service_id'        => $this->request->getPost('service_id'),
                    'package_type_id'   => $this->request->getPost('package_type_id'),
                    'commodity_type_id' => $this->request->getPost('commodity_type_id'),
                    'transport_mode_id' => $this->request->getPost('transport_mode_id'),
                    'delivery_type_id'  => $this->request->getPost('delivery_type_id'),
                    'actual_weight'     => $this->request->getPost('actual_weight'),
                    'chargeable_weight' => $this->request->getPost('chargeable_weight'),
                    'payment_type'      => $this->request->getPost('payment_type'),
                ], JSON_UNESCAPED_SLASHES));

                throw new \RuntimeException(
                    'Tarif customer tidak ditemukan. Periksa master tariff.'
                );
            }

            $vendorTotal = round(
                $vendorBaseTotal +
                $vendorInsurance +
                $vendorPacking +
                $vendorAdditional,
                2
            );

            $profit = round($customerTotal - $vendorTotal, 2);
            $margin = $customerTotal > 0
                ? round(($profit / $customerTotal) * 100, 2)
                : 0;
            $markup = $vendorTotal > 0
                ? round(($profit / $vendorTotal) * 100, 2)
                : 0;

            $awbNo = AwbGenerator::generate();

            $shipmentData = [
                'company_id'           => $customer['company_id'] ?? null,
                'awb_no'               => $awbNo,
                'shipment_mode'        => 'MULTI_COLLY',
                'customer_reference_no'=> $this->clean($post['customer_reference_no'] ?? ''),
                'shipment_date'        => $post['shipment_date'],
                'customer_id'          => $ownerContext['customer_id'],
                'owner_type'           => $ownerContext['owner_type'],
                'owner_id'             => $ownerContext['owner_id'],
                'created_by_user_id'   => $ownerContext['created_by_user_id'],
                'entry_channel'        => $ownerContext['entry_channel'],
                'sender_address_id'    => $senderAddressId,
                'sender_country_id'    => $sender['country_id'] ?? null,
                'receiver_address_id'  => $receiverId,
                'agent_id'             => $sourceAgentId > 0
                    ? $sourceAgentId
                    : ($ownerContext['agent_id'] ?? $customer['agent_id'] ?? null),
                'inbound_intake_id'    => $inboundIntakeId > 0
                    ? $inboundIntakeId
                    : null,
                'acceptance_method'    => $acceptanceMethod,
                'acceptance_branch_id' => $receivingBranchId,
                'current_branch_id'    => in_array(
                    $initialState['internal_status'],
                    [
                        'RECEIVED_AT_ORIGIN_BRANCH',
                        'RECEIVED_AT_MAIN_WAREHOUSE',
                    ],
                    true
                )
                    ? $receivingBranchId
                    : null,
                'receiving_branch_id'  => $receivingBranchId,
                'receiving_location'   => $receivingLocation,
                'received_at'          => str_starts_with(
                    $initialState['internal_status'],
                    'RECEIVED_AT_'
                )
                    ? date('Y-m-d H:i:s')
                    : null,
                'received_by'          => str_starts_with(
                    $initialState['internal_status'],
                    'RECEIVED_AT_'
                )
                    ? $actorId
                    : null,
                'vendor_id'            => $service['vendor_id'] ?? null,
                'country_id'           => $countryId,
                'service_id'           => $serviceId,
                'transport_mode_id'    => $transportModeId,
                'delivery_type_id'     => $deliveryTypeId,
                'package_type_id'      => $packageTypeId,
                'payment_type'         => strtoupper((string) $post['payment_type']),
                'commodity_type_id'    => $commodityId,
                'total_colly'          => $packageSummary['total_colly'],
                'actual_weight'        => $packageSummary['actual_weight'],
                'volume_weight'        => $packageSummary['volume_weight'],
                'chargeable_weight'    => $packageSummary['chargeable_weight'],
                'invoice_weight'       => (float) (
                    $pricing['invoice_weight']
                    ?? $pricing['billing_weight']
                    ?? $packageSummary['chargeable_weight']
                    ?? 0
                ),
                'declared_value'       => $packageSummary['declared_value'],
                'currency_id'          => $packageSummary['currency_id'],
                'insurance_amount'     => $customerInsurance,
                'packing_charge'       => $customerPacking,
                'additional_charge'    => $customerAdditional,
                'discount_amount'      => $discountAmount,
                'freight_charge'       => $freight,
                'remote_charge'        => $this->money($pricing['remote'] ?? 0),
                'selling_amount'       => $freight,
                'vendor_cost'          => $vendorBaseTotal,
                'vendor_freight'       => $vendorBaseTotal,
                'fuel_charge'          => $this->money($pricing['fuel'] ?? 0),
                'vendor_remote_charge' => $this->money(
                    $pricing['vendor_breakdown']['remote'] ?? 0
                ),
                'go_green_charge'      => $this->money(
                    $pricing['gogreen'] ?? $pricing['go_green'] ?? 0
                ),
                'handling_charge'      => $this->money(
                    $pricing['vendor_breakdown']['handling'] ?? 0
                ),
                'vat_amount'           => $this->money(
                    $pricing['tax'] ??
                    $pricing['vendor_breakdown']['vat'] ??
                    0
                ),
                'vendor_total'         => $vendorTotal,
                'total_charge'         => $customerTotal,
                'profit_amount'        => $profit,
                'margin_percent'       => $margin,
                'markup_percent'       => $markup,
                'shipment_status'      => $initialState['internal_status'],
                'public_tracking_status'=> $initialState['public_status'],
                'remarks'              => $this->clean($post['remarks'] ?? ''),

                // Snapshot sender agar histori tidak berubah saat address book diedit.
                'sender_name'          => $this->clean($sender['contact_name'] ?? ''),
                'sender_company'       => $this->clean($sender['company_name'] ?? ''),
                'sender_phone'         => $this->clean($sender['phone'] ?? ''),
                'sender_email'         => $this->clean($sender['email'] ?? ''),
                'sender_identity_number' => $this->clean(
                    $sender['identity_number'] ?? ''
                ),
                'sender_address'       => $this->clean($sender['address'] ?? ''),
                'sender_city'          => $this->clean($sender['city'] ?? ''),
                'sender_state'         => $this->clean($sender['state'] ?? ''),
                'sender_postal_code'   => $this->clean($sender['postal_code'] ?? ''),

                // Snapshot receiver.
                'receiver_name'        => $this->clean($receiver['contact_name'] ?? ''),
                'receiver_company'     => $this->clean($receiver['company_name'] ?? ''),
                'receiver_phone'       => $this->clean($receiver['phone'] ?? ''),
                'receiver_email'       => $this->clean($receiver['email'] ?? ''),
                'receiver_id_number'   => $this->clean(
                    $receiver['identity_number'] ?? ''
                ),
                'receiver_address'     => $this->clean($receiver['address'] ?? ''),
                'receiver_city'        => $this->clean($receiver['city'] ?? ''),
                'receiver_state'       => $this->clean($receiver['state'] ?? ''),
                'receiver_postal_code' => $this->clean($receiver['postal_code'] ?? ''),
            ];

            $shipmentId = $shipmentModel->insert($shipmentData, true);

            if ($shipmentId === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan shipment: ' .
                    implode(', ', $shipmentModel->errors())
                );
            }

            $this->savePackages(
                (int) $shipmentId,
                $packageSummary['packages'],
                $packageTypeId
            );

            $this->saveCostBreakdown(
                (int) $shipmentId,
                'CUSTOMER',
                [
                    ['code' => 'FREIGHT', 'name' => 'Freight Customer', 'amount' => $freight],
                    ['code' => 'INSURANCE', 'name' => 'Customer Insurance', 'amount' => $customerInsurance],
                    ['code' => 'PACKING', 'name' => 'Customer Packing', 'amount' => $customerPacking],
                    ['code' => 'ADDITIONAL', 'name' => 'Customer Additional', 'amount' => $customerAdditional],
                    ['code' => 'DISCOUNT', 'name' => 'Discount', 'amount' => -$discountAmount],
                ]
            );

            $this->saveCostBreakdown(
                (int) $shipmentId,
                'VENDOR',
                [
                    ['code' => 'BASE_VENDOR_COST', 'name' => 'Base Vendor Cost', 'amount' => $vendorBaseTotal],
                    ['code' => 'INSURANCE', 'name' => 'Vendor Insurance', 'amount' => $vendorInsurance],
                    ['code' => 'PACKING', 'name' => 'Vendor Packing', 'amount' => $vendorPacking],
                    ['code' => 'ADDITIONAL', 'name' => 'Vendor Additional', 'amount' => $vendorAdditional],
                ]
            );

            $acceptanceService->recordInitialEvents(
                (int) $shipmentId,
                $awbNo,
                $acceptanceMethod,
                $receivingLocation,
                $actorId,
                $receivingBranchId,
                $initialState
            );

            if ($lockedIntake) {
                $updated = $db->table('inbound_intakes')
                    ->where('id', $inboundIntakeId)
                    ->where('intake_status', 'READY_TO_CONVERT')
                    ->where('converted_shipment_id', null)
                    ->update([
                        'intake_status'         => 'CONVERTED_TO_SHIPMENT',
                        'converted_shipment_id' => (int) $shipmentId,
                        'updated_at'            => date('Y-m-d H:i:s'),
                    ]);

                if (!$updated) {
                    throw new \RuntimeException('Inbound Intake gagal ditautkan ke shipment.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi database gagal.');
            }

            $db->transCommit();

            return redirect()
                ->to(site_url('shipments'))
                ->with('success', 'Shipment berhasil dibuat: ' . $awbNo);
        } catch (\Throwable $e) {
            $db->transRollback();

            log_message(
                'error',
                'Shipment store failed: {message}',
                ['message' => $e->getMessage()]
            );

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat shipment: ' . $e->getMessage());
        }
    }
    public function directReceive($id)
    {
        try {
            (new ShipmentAcceptanceService())->receiveDirect(
                (int) $id,
                (string) $this->request->getPost('receiving_location'),
                (string) $this->request->getPost('receive_method'),
                trim((string) $this->request->getPost('reference_no')),
                (string) $this->request->getPost('notes'),
                $this->requireLifecycleActorId()
            );

            return redirect()
                ->back()
                ->with('success', 'Shipment berhasil diterima langsung di main warehouse.');
        } catch (\Throwable $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }

    private function requireLifecycleActorId(): int
    {
        $userId = auth()->id();

        if (!$userId) {
            throw new \RuntimeException(
                'Sesi login admin tidak ditemukan. ' .
                'Silakan login ulang sebelum Cancel atau Delete shipment.'
            );
        }

        return (int) $userId;
    }

    private function normalizePackages(array $rawPackages, $db): array
    {
        $currencyRows = $db->table('currencies')
            ->select('id, currency_code')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $currencyMap = [];

        foreach ($currencyRows as $currency) {
            $currencyMap[strtoupper((string) $currency['currency_code'])] =
                (int) $currency['id'];
        }

        $packages = [];
        $totalActual = 0.0;
        $totalVolume = 0.0;
        $totalChargeable = 0.0;
        $declaredValue = 0.0;
        $totalItems = 0;
        $shipmentCurrencyCode = null;
        $shipmentCurrencyId = null;

        foreach (array_values($rawPackages) as $packageIndex => $rawPackage) {
            if (!is_array($rawPackage)) {
                continue;
            }

            $actual = max(0, $this->money($rawPackage['actual_weight'] ?? 0));
            $length = max(0, $this->money($rawPackage['length_cm'] ?? 0));
            $width  = max(0, $this->money($rawPackage['width_cm'] ?? 0));
            $height = max(0, $this->money($rawPackage['height_cm'] ?? 0));

            $volume = round(($length * $width * $height) / 5000, 2);
            $chargeable = round(max($actual, $volume), 2);

            if ($chargeable <= 0) {
                throw new \RuntimeException(
                    'Berat Colly #' . ($packageIndex + 1) . ' harus lebih dari 0.'
                );
            }

            $normalizedItems = [];
            $rawItems = is_array($rawPackage['items'] ?? null)
                ? $rawPackage['items']
                : [];

            foreach (array_values($rawItems) as $itemIndex => $rawItem) {
                if (!is_array($rawItem)) {
                    continue;
                }

                $description = $this->clean(
                    $rawItem['item_description'] ?? ''
                );

                if ($description === '') {
                    throw new \RuntimeException(
                        'Item description pada Colly #' .
                        ($packageIndex + 1) .
                        ' baris #' .
                        ($itemIndex + 1) .
                        ' wajib diisi.'
                    );
                }

                $quantity = (int) ($rawItem['quantity'] ?? 0);

                if ($quantity <= 0) {
                    throw new \RuntimeException(
                        'Quantity item harus lebih dari 0.'
                    );
                }

                $unitValue = max(0, $this->money($rawItem['unit_value'] ?? 0));
                $currencyCode = strtoupper(
                    $this->clean($rawItem['currency'] ?? 'USD')
                );

                if (!isset($currencyMap[$currencyCode])) {
                    throw new \RuntimeException(
                        'Currency ' . $currencyCode . ' tidak ditemukan.'
                    );
                }

                if ($shipmentCurrencyCode === null) {
                    $shipmentCurrencyCode = $currencyCode;
                    $shipmentCurrencyId = $currencyMap[$currencyCode];
                } elseif ($shipmentCurrencyCode !== $currencyCode) {
                    throw new \RuntimeException(
                        'Satu shipment sementara hanya boleh memakai satu currency.'
                    );
                }

                $totalValue = round($quantity * $unitValue, 2);
                $declaredValue += $totalValue;
                $totalItems++;

                $normalizedItems[] = [
                    'item_description' => $description,
                    'quantity'         => $quantity,
                    'unit_type'        => 'PCS',
                    'unit_value'       => $unitValue,
                    'currency_id'      => $currencyMap[$currencyCode],
                    'total_value'      => $totalValue,
                ];
            }

            if ($normalizedItems === []) {
                throw new \RuntimeException(
                    'Colly #' . ($packageIndex + 1) . ' belum memiliki item.'
                );
            }

            $packages[] = [
                'package_no'       => $packageIndex + 1,
                'actual_weight'    => round($actual, 2),
                'length_cm'        => round($length, 2),
                'width_cm'         => round($width, 2),
                'height_cm'        => round($height, 2),
                'volume_weight'    => $volume,
                'chargeable_weight'=> $chargeable,
                'items'            => $normalizedItems,
            ];

            $totalActual += $actual;
            $totalVolume += $volume;
            $totalChargeable += $chargeable;
        }

        if ($packages === []) {
            throw new \RuntimeException('Tidak ada paket yang valid.');
        }

        return [
            'packages'          => $packages,
            'total_colly'       => count($packages),
            'total_items'       => $totalItems,
            'actual_weight'     => round($totalActual, 2),
            'volume_weight'     => round($totalVolume, 2),
            'chargeable_weight' => round($totalChargeable, 2),
            'declared_value'    => round($declaredValue, 2),
            'currency_id'       => $shipmentCurrencyId,
        ];
    }

    private function savePackages(
        int $shipmentId,
        array $packages,
        int $packageTypeId
    ): void {
        $packageModel = new ShipmentPackageModel();
        $itemModel    = new ShipmentPackageItemModel();

        foreach ($packages as $package) {
            $items = $package['items'];
            unset($package['items']);

            $package['shipment_id'] = $shipmentId;
            $package['package_type_id'] = $packageTypeId;

            $packageId = $packageModel->insert($package, true);

            if ($packageId === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan package: ' .
                    implode(', ', $packageModel->errors())
                );
            }

            foreach ($items as $item) {
                $item['shipment_package_id'] = (int) $packageId;

                if ($itemModel->insert($item) === false) {
                    throw new \RuntimeException(
                        'Gagal menyimpan package item: ' .
                        implode(', ', $itemModel->errors())
                    );
                }
            }
        }
    }

    private function saveCostBreakdown(
        int $shipmentId,
        string $party,
        array $components
    ): void {
        $model = new \App\Models\ShipmentCostDetailModel();

        foreach ($components as $component) {
            $rawAmount = $component['amount'] ?? 0;
            $amount = is_numeric($rawAmount)
                ? round((float) $rawAmount, 2)
                : 0.0;

            if (abs($amount) < 0.00001) {
                continue;
            }

            if ($model->insert([
                'shipment_id'   => $shipmentId,
                'party'         => strtoupper($party),
                'component_code'=> strtoupper((string) $component['code']),
                'component_name'=> (string) $component['name'],
                'amount'        => $amount,
                'remarks'       => $component['remarks'] ?? null,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan cost breakdown: ' .
                    implode(', ', $model->errors())
                );
            }
        }
    }

    private function createTracking(int $shipmentId, string $awbNo): void
    {
        $trackingModel = new ShipmentTrackingModel();

        if ($trackingModel->insert([
            'shipment_id'       => $shipmentId,
            'tracking_code'     => $awbNo,
            'tracking_status'   => 'DRAFT',
            'public_status'     => 'CREATED',
            'tracking_location' => 'Cartenz System',
            'tracking_notes'    => 'Shipment berhasil dibuat',
            'event_time'        => date('Y-m-d H:i:s'),
            'created_by'        => auth()->id() ?? null,
            'created_at'        => date('Y-m-d H:i:s'),
        ]) === false) {
            throw new \RuntimeException(
                'Gagal membuat tracking awal: ' .
                implode(', ', $trackingModel->errors())
            );
        }
    }

    private function resolveExpressZone($db, int $zoneId): int
    {
        if ($zoneId <= 0) {
            return 1;
        }

        $zone = $db->table('express_zones')
            ->where('id', $zoneId)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        if (!$zone) {
            return $zoneId;
        }

        $digits = preg_replace('/\D+/', '', (string) ($zone['zone_code'] ?? ''));

        return $digits !== '' ? (int) $digits : $zoneId;
    }

    private function money($value): float
    {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return round(max(0, $number), 2);
    }

    private function clean($value): string
    {
        return trim(strip_tags((string) $value));
    }
    public function addTracking($shipmentId)
    {
        $status   = (string) $this->request->getPost('tracking_status');
        $location = (string) $this->request->getPost('tracking_location');
        $notes    = (string) $this->request->getPost('tracking_notes');

        try {
            $result = $this->applyTrackingTransition(
                (int) $shipmentId,
                $status,
                $location,
                $notes
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId))
                ->with(
                    'success',
                    'Status internal diperbarui ke ' .
                    $result['internal_status'] .
                    ' dan status publik menjadi ' .
                    $result['public_status'] . '.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId))
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function quickStatus($shipmentId, $status)
    {
        try {
            $result = $this->applyTrackingTransition(
                (int) $shipmentId,
                (string) $status,
                'Cartenz System',
                'Quick status update'
            );

            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId))
                ->with(
                    'success',
                    'Status diperbarui ke ' .
                    $result['internal_status'] . '.'
                );
        } catch (\Throwable $e) {
            return redirect()
                ->to(site_url('shipments/detail/' . (int) $shipmentId))
                ->with('error', $e->getMessage());
        }
    }

    private function applyTrackingTransition(
        int $shipmentId,
        string $targetStatus,
        string $location,
        string $notes
    ): array {
        $db = db_connect();

        $shipmentModel = new ShipmentModel();
        $trackingModel = new ShipmentTrackingModel();
        $statusModel   = new TrackingStatusModel();

        $targetStatus = strtoupper(trim($targetStatus));
        $location     = trim(strip_tags($location));
        $notes        = trim(strip_tags($notes));

        $db->transBegin();

        try {
            $shipment = $shipmentModel->find($shipmentId);

            if (!$shipment) {
                throw new \RuntimeException('Shipment tidak ditemukan.');
            }

            $targetMaster = $statusModel->getByCode($targetStatus);

            if (!$targetMaster) {
                throw new \RuntimeException(
                    'Status ' . $targetStatus . ' tidak aktif atau tidak terdaftar.'
                );
            }

            $currentStatus = strtoupper(
                trim((string) ($shipment['shipment_status'] ?? 'DRAFT'))
            );

            if ($currentStatus === $targetStatus) {
                throw new \RuntimeException(
                    'Status shipment sudah ' . $targetStatus . '.'
                );
            }

            if ($targetStatus === 'CANCELLED') {
                throw new \RuntimeException(
                    'Gunakan tombol Cancel Shipment agar alasan dan audit pembatalan tercatat.'
                );
            }

            if (
                (int) ($targetMaster['requires_location'] ?? 0) === 1
                && $location === ''
            ) {
                throw new \RuntimeException(
                    'Lokasi wajib diisi untuk status ' . $targetStatus . '.'
                );
            }

            if (
                (int) ($targetMaster['requires_notes'] ?? 0) === 1
                && $notes === ''
            ) {
                throw new \RuntimeException(
                    'Catatan wajib diisi untuk status ' . $targetStatus . '.'
                );
            }

            $this->assertTrackingTransition(
                $shipmentId,
                $currentStatus,
                $targetStatus,
                $trackingModel
            );

            $publicStatus = strtoupper(
                trim((string) (
                    $targetMaster['public_status_code']
                    ?? $targetStatus
                ))
            );

            $now = date('Y-m-d H:i:s');

            if ($trackingModel->insert([
                'shipment_id'       => $shipmentId,
                'tracking_code'     => (string) $shipment['awb_no'],
                'tracking_status'   => $targetStatus,
                'public_status'     => $publicStatus,
                'tracking_location' => $location !== ''
                    ? $location
                    : 'Cartenz System',
                'tracking_notes'    => $notes,
                'event_time'        => $now,
                'created_by'        => auth()->id() ?? null,
                'created_at'        => $now,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal menyimpan tracking: ' .
                    implode(', ', $trackingModel->errors())
                );
            }

            if ($shipmentModel->update($shipmentId, [
                'shipment_status'       => $targetStatus,
                'public_tracking_status'=> $publicStatus,
            ]) === false) {
                throw new \RuntimeException(
                    'Gagal memperbarui status shipment: ' .
                    implode(', ', $shipmentModel->errors())
                );
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaksi tracking gagal.');
            }

            $db->transCommit();

            return [
                'internal_status' => $targetStatus,
                'public_status'   => $publicStatus,
            ];
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }

    private function assertTrackingTransition(
        int $shipmentId,
        string $currentStatus,
        string $targetStatus,
        ShipmentTrackingModel $trackingModel
    ): void {
        $normalFlow = [
            'DRAFT',
            'CREATED',
            'MANIFESTED',
            'PICKED_UP',
            'RECEIVED_AT_ORIGIN_BRANCH',
            'SENT_TO_MAIN_WAREHOUSE',
            'RECEIVED_AT_MAIN_WAREHOUSE',
            'ARRIVED_HUB',
            'IN_TRANSIT_HUB',
            'IN_TRANSIT',
            'OUT_FOR_DELIVERY',
            'DELIVERED',
        ];

        $terminalStatuses = [
            'DELIVERED',
            'RETURN_TO_SENDER',
        ];

        if (in_array($currentStatus, $terminalStatuses, true)) {
            throw new \RuntimeException(
                'Status ' . $currentStatus . ' bersifat terminal dan tidak dapat diubah.'
            );
        }

        if ($targetStatus === 'ON_HOLD') {
            return;
        }

        if ($targetStatus === 'RETURN_TO_SENDER') {
            if (in_array($currentStatus, ['DRAFT', 'CREATED'], true)) {
                throw new \RuntimeException(
                    'RETURN_TO_SENDER belum dapat digunakan sebelum pickup.'
                );
            }

            return;
        }

        $currentForValidation = $currentStatus;

        if ($currentStatus === 'ON_HOLD') {
            $lastNormal = $trackingModel->getLastNonExceptionTracking(
                $shipmentId
            );

            $currentForValidation = strtoupper(
                trim((string) (
                    $lastNormal['tracking_status']
                    ?? 'DRAFT'
                ))
            );
        }

        $currentIndex = array_search(
            $currentForValidation,
            $normalFlow,
            true
        );

        $targetIndex = array_search(
            $targetStatus,
            $normalFlow,
            true
        );

        if ($targetIndex === false) {
            throw new \RuntimeException(
                'Status tujuan tidak termasuk workflow utama.'
            );
        }

        if ($currentIndex === false) {
            throw new \RuntimeException(
                'Status aktif ' . $currentForValidation .
                ' tidak dikenali oleh workflow.'
            );
        }

        if ($targetIndex <= $currentIndex) {
            throw new \RuntimeException(
                'Status tidak boleh mundur dari ' .
                $currentForValidation .
                ' ke ' .
                $targetStatus . '.'
            );
        }
    }

    public function printLabel($id)
    {
        $shipment = (new \App\Models\ShipmentModel())->find($id);
        if (!$shipment) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('shipments/label', ['shipment' => $shipment]);
    }

    public function label($id)
    {
        $shipment = (new \App\Models\ShipmentModel())->find($id);
        return view('shipments/label', ['shipment' => $shipment]);
    }

    public function testRate()
    {
        return $this->response->setJSON(['success' => true, 'price_per_kg' => 55000, 'total' => 660000]);
    }




public function getCommodityRule()
{
    $commodityId = (int)$this->request->getGet('commodity_id');

    // Panggil Service saja, nggak perlu tulis query lagi di sini
    $service = new CommodityRuleService();
    $rule = $service->getRule($commodityId);

    if (!$rule) {
        return $this->response->setJSON([
            'success' => false
        ]);
    }

    return $this->response->setJSON([
        'success' => true,
        'rule' => $rule
    ]);
}







public function detail($id)
{
    $shipmentId = (int) $id;

    $shipmentModel = new ShipmentModel();
    $packageModel  = new ShipmentPackageModel();
    $itemModel     = new ShipmentPackageItemModel();
    $trackingModel = new ShipmentTrackingModel();
    $vendorTrackingEventModel = new ShipmentVendorTrackingEventModel();
    $costModel     = new ShipmentCostDetailModel();
    $statusModel   = new TrackingStatusModel();
    $historyModel  = new ShipmentHistoryModel();
    $lifecycle     = new ShipmentLifecycleService();

    $shipment = $shipmentModel
        ->select([
            'shipments.*',
            'customers.customer_name',
            'customers.company_name AS customer_company',
            'customers.phone AS customer_phone',
            'customers.email AS customer_email',
            'countries.country_name',
            'services.service_name',
            'vendors.vendor_name',
            'package_types.package_type_name',
            'commodity_types.commodity_name',
            'currencies.currency_code',
        ])
        ->join(
            'customers',
            'customers.id = shipments.customer_id',
            'left'
        )
        ->join(
            'countries',
            'countries.id = shipments.country_id',
            'left'
        )
        ->join(
            'services',
            'services.id = shipments.service_id',
            'left'
        )
        ->join(
            'vendors',
            'vendors.id = shipments.vendor_id',
            'left'
        )
        ->join(
            'package_types',
            'package_types.id = shipments.package_type_id',
            'left'
        )
        ->join(
            'commodity_types',
            'commodity_types.id = shipments.commodity_type_id',
            'left'
        )
        ->join(
            'currencies',
            'currencies.id = shipments.currency_id',
            'left'
        )
        ->where('shipments.id', $shipmentId)
        ->first();

    if (!$shipment) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
            'Shipment tidak ditemukan.'
        );
    }

    $this->assertShipmentReadable($shipment);
    $detailAccess = $this->shipmentAccessContext();
    $canViewInternalCommercials = (bool) ($detailAccess['is_head_office'] ?? false);

    $packages = $packageModel
        ->where('shipment_id', $shipmentId)
        ->orderBy('package_no', 'ASC')
        ->orderBy('id', 'ASC')
        ->findAll();

    foreach ($packages as &$package) {
        $package['items'] = $itemModel
            ->where('shipment_package_id', $package['id'])
            ->orderBy('id', 'ASC')
            ->findAll();
    }
    unset($package);

    $tracking = $trackingModel
        ->where('shipment_id', $shipmentId)
        ->orderBy('event_time', 'DESC')
        ->orderBy('id', 'DESC')
        ->findAll();

$vendorTrackingEvents = $vendorTrackingEventModel
    ->where('shipment_id', $shipmentId)
    ->orderBy('event_sequence', 'DESC')
    ->orderBy('event_time', 'DESC')
    ->orderBy('id', 'DESC')
    ->findAll();

    $vendorTrackingStatusOptions =
        VendorTransitTrackingService::manualStatusOptions();

    $statuses = $statusModel
        ->where('is_active', 1)
        ->orderBy('sort_order', 'ASC')
        ->findAll();

    $costDetails = $costModel
        ->where('shipment_id', $shipmentId)
        ->orderBy('party', 'ASC')
        ->orderBy('id', 'ASC')
        ->findAll();

    $shipmentHistories = $historyModel
        ->where('shipment_id', $shipmentId)
        ->orderBy('created_at', 'DESC')
        ->orderBy('id', 'DESC')
        ->findAll();

    $vendorWorkflow = new ShipmentVendorWorkflowService();
    $vendorRecommendations = $vendorWorkflow
        ->getRecommendations($shipmentId);
    $vendorActionState = $vendorWorkflow
        ->getActionState($shipment, $vendorRecommendations);

    $customerCosts = [];
    $vendorCosts   = [];
    $customerCostTotal = 0.0;
    $vendorCostTotal   = 0.0;

    foreach ($costDetails as $cost) {
        $amount = (float) ($cost['amount'] ?? 0);
        $party  = strtoupper((string) ($cost['party'] ?? ''));

        if ($party === 'CUSTOMER') {
            $customerCosts[] = $cost;
            $customerCostTotal += $amount;
            continue;
        }

        if ($party === 'VENDOR') {
            $vendorCosts[] = $cost;
            $vendorCostTotal += $amount;
        }
    }

    $customerTotal = (float) (
        $shipment['total_charge']
        ?? $shipment['selling_amount']
        ?? $customerCostTotal
    );

    $vendorTotal = (float) (
        $shipment['vendor_total']
        ?? $shipment['vendor_cost']
        ?? $vendorCostTotal
    );

    $profit = (float) (
        $shipment['profit_amount']
        ?? ($customerTotal - $vendorTotal)
    );

    $margin = isset($shipment['margin_percent'])
        ? (float) $shipment['margin_percent']
        : ($customerTotal > 0
            ? round(($profit / $customerTotal) * 100, 2)
            : 0.0);

    $actionState = $lifecycle->getActionState($shipment);

    if (!$canViewInternalCommercials) {
        $shipment['vendor_name'] = '-';
        $shipment['vendor_cost'] = 0;
        $shipment['vendor_freight'] = 0;
        $shipment['vendor_total'] = 0;
        $shipment['profit_amount'] = 0;
        $shipment['margin_percent'] = 0;
        $vendorCosts = [];
        $vendorCostTotal = 0.0;
        $vendorTotal = 0.0;
        $profit = 0.0;
        $margin = 0.0;
        $vendorRecommendations = [];
        $vendorTrackingEvents = [];
    }

    return view('shipments/detail', [
        'shipment'          => $shipment,
        'packages'          => $packages,
        'tracking'          => $tracking,
        'statuses'          => $statuses,
        'shipmentHistories' => $shipmentHistories,
        'costDetails'       => $costDetails,
        'customerCosts'     => $customerCosts,
        'vendorCosts'       => $vendorCosts,
        'customerCostTotal' => round($customerCostTotal, 2),
        'vendorCostTotal'   => round($vendorCostTotal, 2),
        'customerTotal'     => round($customerTotal, 2),
        'vendorTotal'       => round($vendorTotal, 2),
        'profit'            => round($profit, 2),
        'margin'            => round($margin, 2),
        'actionState'          => $actionState,
        'vendorRecommendations'       => $vendorRecommendations,
        'vendorActionState'           => $vendorActionState,
        'vendorTrackingEvents'        => $vendorTrackingEvents,
        'vendorTrackingStatusOptions' => $vendorTrackingStatusOptions,
    ]);
}
// Taruh ini di dalam isi class-nya
public function calculateRate()
{
    $service = new RegularRateService();

    $rate = $service->calculate(
        (int)$this->request->getPost('country_id'),
        (int)$this->request->getPost('service_id'),
        (int)$this->request->getPost('transport_mode_id'),
        (float)$this->request->getPost('chargeable_weight'),
        $this->request->getPost('agent_type') ?? 'A'
    );

    if (!$rate) {
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Tarif tidak ditemukan.'
        ]);
    }

    return $this->response->setJSON([
        'success'       => true,
        'price_per_kg'  => $rate['price_per_kg'],
        'vendor_per_kg' => $rate['vendor_per_kg'],
        'vendor_total'  => $rate['vendor_total'],
        'total'         => $rate['total']
    ]);
}
    // ✅ DIPERBARUI SESUAI STRUKTUR SERVICE
    public function calculate()
    {
        try {
            $shipment = [
                'service_id'         => (int)$this->request->getPost('service_id'),
                'country_id'         => (int)$this->request->getPost('country_id'),
                'transport_mode_id'  => (int)$this->request->getPost('transport_mode_id'),
                'chargeable_weight'  => (float)$this->request->getPost('chargeable_weight'),
                'agent_type'         => $this->request->getPost('agent_type') ?? 'A',
                'commodity_type_id'  => (int)$this->request->getPost('commodity_type_id'),
                'insurance_amount'   => (float)$this->request->getPost('insurance_amount') ?? 0,
                'packing_charge'     => (float)$this->request->getPost('packing_charge') ?? 0,
                'additional_charge'  => (float)$this->request->getPost('additional_charge') ?? 0,
                'discount'           => (float)$this->request->getPost('discount') ?? 0,
                'receiver_city'      => $this->request->getPost('receiver_city') ?? '',
                'receiver_postcode'  => $this->request->getPost('receiver_postcode') ?? ''
            ];

            // ✅ SEKARANG PANGGIL SERVICE SAJA
            $result = (new ShipmentCalculationService())->calculate($shipment);

            return $this->response->setJSON([
                'success' => true,
                ...$result
            ]);

        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function calculatePricing()
{
    $shipment = [
        'customer_id'        => $this->request->getPost('customer_id'),
        'country_id'         => $this->request->getPost('country_id'),
        'service_id'         => $this->request->getPost('service_id'),
        'commodity_type_id'  => $this->request->getPost('commodity_type_id'),

        'actual_weight'      => $this->request->getPost('actual_weight'),
        'volume_weight'      => $this->request->getPost('volume_weight'),
        'chargeable_weight'  => $this->request->getPost('chargeable_weight'),

        'total_colly'        => $this->request->getPost('total_colly'),

        'insurance_amount'   => $this->request->getPost('insurance_amount'),
        'packing_charge'     => $this->request->getPost('packing_charge'),
        'additional_charge'  => $this->request->getPost('additional_charge'),
        'discount_amount'    => $this->request->getPost('discount_amount'),

        'service_engine'     => 'REGULAR'
    ];

    $pricing = new PricingEngine();

    return $this->response->setJSON(
        $pricing->calculate($shipment)
    );
}

private function shipmentAccessContext(): array
{
    $actorId = (int) (auth()->id() ?? 0);
    $user    = auth()->user();

    if ($actorId <= 0 || $user === null) {
        throw new \RuntimeException('Sesi user tidak ditemukan.');
    }

    if ($user->inGroup('superadmin')) {
        return [
            'is_head_office' => true,
            'agent_id'       => null,
            'profile'        => null,
        ];
    }

    if (!$user->inGroup('admin')) {
        return [
            'is_head_office' => false,
            'agent_id'       => 0,
            'profile'        => null,
        ];
    }

    $context = new BranchOperationalContextService();
    $profile = $context->requireProfile($actorId);

    return [
        'is_head_office' => $context->profileIsMain($profile),
        'agent_id'       => (int) ($profile['agent_id'] ?? 0),
        'profile'        => $profile,
    ];
}

private function assertShipmentReadable(array $shipment): void
{
    $access = $this->shipmentAccessContext();

    if ($access['is_head_office']) {
        return;
    }

    $agentId = (int) ($access['agent_id'] ?? 0);

    if ($agentId <= 0 || (int) ($shipment['agent_id'] ?? 0) !== $agentId) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
            'Shipment tidak ditemukan untuk agent yang sedang login.'
        );
    }
}

private function isOperationalUser(): bool
{
    $user = auth()->user();

    if ($user === null) {
        return false;
    }

    return $user->inGroup('superadmin')
        || $user->inGroup('admin');
}

}
