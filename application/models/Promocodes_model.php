<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Promocodes_model extends BaseModel
{
    protected $tblname = 'promocodes';

    public function __construct()
    {
        parent::__construct();
    }

    /*
    |--------------------------------------------------------------------------
    | Admin list
    |--------------------------------------------------------------------------
    */

    public function getAdminItems()
    {
        $this->db->select('
            promocodes.*,
            (
                SELECT COUNT(*)
                FROM promocode_usages
                WHERE promocode_usages.promocode_id = promocodes.id
            ) AS used_count
        ');

        $this->db->order_by('promocodes.id DESC');

        return $this->db
            ->get($this->tblname)
            ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Single promo
    |--------------------------------------------------------------------------
    */

    public function getItem($id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return null;
        }

        return $this->db
            ->where('id', $id)
            ->get($this->tblname)
            ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Find by code
    |--------------------------------------------------------------------------
    */

    public function findByCode($code)
    {
        $code = strtoupper(
            trim(
                (string) $code
            )
        );

        if ($code === '') {
            return null;
        }

        return $this->db
            ->where('code', $code)
            ->get($this->tblname)
            ->row();
    }

    /*
    |--------------------------------------------------------------------------
    | Code exists
    |--------------------------------------------------------------------------
    */

    public function codeExists(
        $code,
        $excludeId = null
    ) {
        $code = strtoupper(
            trim(
                (string) $code
            )
        );

        if ($code === '') {
            return false;
        }

        $this->db->where(
            'code',
            $code
        );

        if ($excludeId !== null) {
            $this->db->where(
                'id !=',
                (int) $excludeId
            );
        }

        return $this->db
            ->count_all_results(
                $this->tblname
            ) > 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

		public function createPromo(
			array $data,
			array $brands = array(),
			array $excludedBrands = array()
		) {
				$this->db->trans_begin();

				/*
				|--------------------------------------------------------------------------
				| Create promocode
				|--------------------------------------------------------------------------
				*/

				$this->db->insert(
						$this->tblname,
						$data
				);

				$promoId =
						(int) $this->db->insert_id();

				if ($promoId <= 0) {
						$this->db->trans_rollback();

						return false;
				}

				/*
				|--------------------------------------------------------------------------
				| Selected brands
				|--------------------------------------------------------------------------
				*/

				if (
						!$this->replaceBrands(
								$promoId,
								$brands
						)
				) {
						$this->db->trans_rollback();

						return false;
				}

				/*
				|--------------------------------------------------------------------------
				| Excluded brands
				|--------------------------------------------------------------------------
				*/

				if (
						!$this->replaceExcludedBrands(
								$promoId,
								$excludedBrands
						)
				) {
						$this->db->trans_rollback();

						return false;
				}

				/*
				|--------------------------------------------------------------------------
				| Transaction
				|--------------------------------------------------------------------------
				*/

				if (
						$this->db->trans_status()
						=== false
				) {
						$this->db->trans_rollback();

						return false;
				}

				$this->db->trans_commit();

				return $promoId;
		}

    /*
    |------------------ --------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

		public function updatePromo(
				$id,
				array $data,
				array $brands = array(),
				array $excludedBrands = array()
		) {
				$id =
						(int) $id;

				if ($id <= 0) {
						return false;
				}

				$this->db->trans_begin();

				/*
				|--------------------------------------------------------------------------
				| Update promocode
				|--------------------------------------------------------------------------
				*/

				$this->db
						->where(
								'id',
								$id
						)
						->update(
								$this->tblname,
								$data
						);

				/*
				|--------------------------------------------------------------------------
				| Selected brands
				|--------------------------------------------------------------------------
				*/

				if (
						!$this->replaceBrands(
								$id,
								$brands
						)
				) {
						$this->db->trans_rollback();

						return false;
				}

				/*
				|--------------------------------------------------------------------------
				| Excluded brands
				|--------------------------------------------------------------------------
				*/

				if (
						!$this->replaceExcludedBrands(
								$id,
								$excludedBrands
						)
				) {
						$this->db->trans_rollback();

						return false;
				}

				/*
				|--------------------------------------------------------------------------
				| Transaction
				|--------------------------------------------------------------------------
				*/

				if (
						$this->db->trans_status()
						=== false
				) {
						$this->db->trans_rollback();

						return false;
				}

				$this->db->trans_commit();

				return true;
		}
    /*
    |--------------------------------------------------------------------------
    | Excluded brands
    |--------------------------------------------------------------------------
    */

    public function getExcludedBrandIds(
        $promoId
    ) {
        $promoId =
            (int) $promoId;

        if ($promoId <= 0) {
            return array();
        }

        $rows =
            $this->db
                ->select('brand_id')
                ->where(
                    'promocode_id',
                    $promoId
                )
                ->get(
                    'promocode_excluded_brands'
                )
                ->result();

        $ids = array();

        foreach ($rows as $row) {
            $ids[] =
                (int) $row->brand_id;
        }

        return $ids;
    }

    public function replaceExcludedBrands(
        $promoId,
        array $brandIds
    ) {
        $promoId =
            (int) $promoId;

        if ($promoId <= 0) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $normalized = array();

        foreach ($brandIds as $brandId) {
            $brandId =
                (int) $brandId;

            if (
                $brandId <= 0
                || in_array(
                    $brandId,
                    $normalized,
                    true
                )
            ) {
                continue;
            }

            $normalized[] =
                $brandId;
        }

        /*
        |--------------------------------------------------------------------------
        | Clear old relations
        |--------------------------------------------------------------------------
        */

        $this->db
            ->where(
                'promocode_id',
                $promoId
            )
            ->delete(
                'promocode_excluded_brands'
            );

        if (empty($normalized)) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Insert new relations
        |--------------------------------------------------------------------------
        */

        $rows = array();

        foreach ($normalized as $brandId) {
            $rows[] = array(
                'promocode_id' =>
                    $promoId,

                'brand_id' =>
                    $brandId,
            );
        }

        return $this->db
            ->insert_batch(
                'promocode_excluded_brands',
                $rows
            ) !== false;
    }

		public function getBrandIds(
    $promoId
) {
    $promoId = (int) $promoId;

    if ($promoId <= 0) {
        return array();
    }

    $rows = $this->db
        ->select('brand_id')
        ->where(
            'promocode_id',
            $promoId
        )
        ->get('promocode_brands')
        ->result();

    $ids = array();

    foreach ($rows as $row) {
        $ids[] = (int) $row->brand_id;
    }

    return $ids;
}

public function replaceBrands(
    $promoId,
    array $brandIds
) {
    $promoId = (int) $promoId;

    if ($promoId <= 0) {
        return false;
    }

    $normalized = array();

    foreach ($brandIds as $brandId) {
        $brandId = (int) $brandId;

        if (
            $brandId <= 0
            || in_array(
                $brandId,
                $normalized,
                true
            )
        ) {
            continue;
        }

        $normalized[] = $brandId;
    }

    $this->db
        ->where(
            'promocode_id',
            $promoId
        )
        ->delete('promocode_brands');

    if (empty($normalized)) {
        return true;
    }

    $rows = array();

    foreach ($normalized as $brandId) {
        $rows[] = array(
            'promocode_id' => $promoId,
            'brand_id' => $brandId,
        );
    }

    return $this->db
        ->insert_batch(
            'promocode_brands',
            $rows
        ) !== false;
}

    /*
    |--------------------------------------------------------------------------
    | Usage counters
    |--------------------------------------------------------------------------
    */

    public function countUsages(
        $promoId
    ) {
        return (int) $this->db
            ->where(
                'promocode_id',
                (int) $promoId
            )
            ->count_all_results(
                'promocode_usages'
            );
    }

    public function countClientUsages(
        $promoId,
        $clientId
    ) {
        return (int) $this->db
            ->where(
                'promocode_id',
                (int) $promoId
            )
            ->where(
                'client_id',
                (int) $clientId
            )
            ->count_all_results(
                'promocode_usages'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Register usage
    |--------------------------------------------------------------------------
    */

    public function registerUsage(
        $promoId,
        $clientId,
        $orderId
    ) {
        $promoId =
            (int) $promoId;

        $clientId =
            (int) $clientId;

        $orderId =
            (int) $orderId;

        if (
            $promoId <= 0
            || $clientId <= 0
            || $orderId <= 0
        ) {
            return false;
        }

        return $this->db->insert(
            'promocode_usages',
            array(
                'promocode_id' =>
                    $promoId,

                'client_id' =>
                    $clientId,

                'order_id' =>
                    $orderId,

                'created_at' =>
                    date('Y-m-d H:i:s'),
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Promo event audit
    |--------------------------------------------------------------------------
    */

    public function logEvent(
        $eventType,
        array $data = array()
    ) {
        $eventType =
            strtoupper(
                trim(
                    (string) $eventType
                )
            );

        if ($eventType === '') {
            return false;
        }

        $code =
            !empty($data['code'])
                ? strtoupper(
                    trim(
                        (string) $data['code']
                    )
                )
                : '';

        if ($code === '') {
            return false;
        }

        $row = array(
            'promocode_id' =>
                !empty($data['promocode_id'])
                    ? (int) $data['promocode_id']
                    : null,

            'client_id' =>
                !empty($data['client_id'])
                    ? (int) $data['client_id']
                    : null,

            'order_id' =>
                !empty($data['order_id'])
                    ? (int) $data['order_id']
                    : null,

            'code' =>
                $code,

            'event_type' =>
                $eventType,

            'cart_subtotal' =>
                isset($data['cart_subtotal'])
                    ? (float) $data['cart_subtotal']
                    : 0,

            'discount_amount' =>
                isset($data['discount_amount'])
                    ? (float) $data['discount_amount']
                    : 0,

            'final_total' =>
                isset($data['final_total'])
                    ? (float) $data['final_total']
                    : 0,

            'reason' =>
                !empty($data['reason'])
                    ? substr(
                        (string) $data['reason'],
                        0,
                        100
                    )
                    : null,

            'ip_address' =>
                !empty($data['ip_address'])
                    ? substr(
                        (string) $data['ip_address'],
                        0,
                        45
                    )
                    : null,

            'user_agent' =>
                !empty($data['user_agent'])
                    ? substr(
                        (string) $data['user_agent'],
                        0,
                        500
                    )
                    : null,

            'created_at' =>
                date('Y-m-d H:i:s'),
        );

        return $this->db
            ->insert(
                'promocode_events',
                $row
            );
    }

    public function getClientEvents($clientId)
    {
        $clientId =
            (int) $clientId;

        if ($clientId <= 0) {
            return array();
        }

        return $this->db
            ->where(
                'client_id',
                $clientId
            )
            ->order_by(
                'created_at',
                'DESC'
            )
            ->order_by(
                'id',
                'DESC'
            )
            ->get(
                'promocode_events'
            )
            ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    |
    | Used promos are not physically deleted.
    |
    */

    public function deletePromo($id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return false;
        }

        if (
            $this->countUsages($id) > 0
        ) {
            return $this->db
                ->where('id', $id)
                ->update(
                    $this->tblname,
                    array(
                        'status' =>
                            'disabled',

                        'updated_at' =>
                            date(
                                'Y-m-d H:i:s'
                            ),
                    )
                );
        }

        $this->db->trans_begin();

        $this->db
            ->where(
                'promocode_id',
                $id
            )
            ->delete(
                'promocode_excluded_brands'
            );

        $this->db
            ->where('id', $id)
            ->delete(
                $this->tblname
            );

        if (
            $this->db->trans_status()
            === false
        ) {
            $this->db->trans_rollback();

            return false;
        }

        $this->db->trans_commit();

        return true;
    }
}