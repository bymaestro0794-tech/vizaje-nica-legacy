<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Promocodes extends BackEndController
{
    protected $add;
    protected $index_title;
    protected $success_add;
    protected $success_edit;
    protected $success_delete;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->index_title =
            'Промокоды';

        $this->add =
            lang('Add');

        $this->success_add =
            'Промокод успешно добавлен.';

        $this->success_edit =
            'Промокод успешно обновлён.';

        $this->success_delete =
            'Промокод успешно удалён или отключён.';

        $this->data['title'] =
            $this->index_title;

        $this->data['add'] =
            $this->add;

        $this->load->model(
            'promocodes_model'
        );

        $this->load->model(
            'brands_model'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | List
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $this->data['objects'] =
            $this->promocodes_model
                ->getAdminItems();

        $this->data['brands'] =
            $this->getBrands();

        $this->data['inner_view'] =
            'dashboard/promocodes/index';

        $this->load->vars(
            $this->data
        );

        $this->load->view(
            $this->main_layout
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function put()
    {
        check_if_POST();

        try {
            $post =
                $this->collectPostData();

            $brands =
								$this->collectBrands();

						$excludedBrands =
								$this->collectExcludedBrands();

            $this->validatePromoData(
                $post
            );

            if (
                $this->promocodes_model
                    ->codeExists(
                        $post['code']
                    )
            ) {
                throw new Exception(
                    'Промокод с таким кодом уже существует.'
                );
            }

							if (
								$post['brand_scope'] === 'selected'
								&& empty($brands)
						) {
								throw new Exception(
										'Выберите хотя бы один бренд.'
								);
						}

						if (
								$post['brand_scope'] === 'selected'
						) {
								$excludedBrands = array();
						} else {
								$brands = array();
						}

            $promoId =
							$this->promocodes_model
									->createPromo(
											$post,
											$brands,
											$excludedBrands
									);

            if (!$promoId) {
                throw new Exception(
                    'Не удалось сохранить промокод.'
                );
            }

            $_SESSION['success'] =
                $this->success_add;
        } catch (Exception $e) {
            log_message(
                'error',
                'Promocode create error: '
                . $e->getMessage()
            );

            $_SESSION['error'] = [
                $e->getMessage(),
            ];
        }

        redirect(
            $this->path
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

public function item($id = 0)
{
    $id = (int) $id;

    if ($id <= 0) {
        throw_on_404();
    }

    $item =
        $this->promocodes_model
            ->getItem($id);

    if (empty($item)) {
        throw_on_404();
    }

    if (
        $_SERVER['REQUEST_METHOD']
        === 'POST'
    ) {
        try {
            $post =
                $this->collectPostData();

            $brands =
                $this->collectBrands();

            $excludedBrands =
                $this->collectExcludedBrands();

            /*
            |--------------------------------------------------------------------------
            | Validate promo
            |--------------------------------------------------------------------------
            */

            $this->validatePromoData(
                $post
            );

            /*
            |--------------------------------------------------------------------------
            | Validate brand scope
            |--------------------------------------------------------------------------
            */

            if (
                $post['brand_scope'] === 'selected'
                && empty($brands)
            ) {
                throw new Exception(
                    'Выберите хотя бы один бренд.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Normalize brand relations
            |--------------------------------------------------------------------------
            |
            | selected:
            | - используем promocode_brands
            | - очищаем exclusions
            |
            | all:
            | - очищаем promocode_brands
            | - используем exclusions
            |
            */

            if (
                $post['brand_scope'] === 'selected'
            ) {
                $excludedBrands =
                    array();
            } else {
                $brands =
                    array();
            }

            /*
            |--------------------------------------------------------------------------
            | Unique code
            |--------------------------------------------------------------------------
            */

            if (
                $this->promocodes_model
                    ->codeExists(
                        $post['code'],
                        $id
                    )
            ) {
                throw new Exception(
                    'Промокод с таким кодом уже существует.'
                );
            }

            $post['updated_at'] =
                date('Y-m-d H:i:s');

            /*
            |--------------------------------------------------------------------------
            | Update
            |--------------------------------------------------------------------------
            */

            if (
                !$this->promocodes_model
                    ->updatePromo(
                        $id,
                        $post,
                        $brands,
                        $excludedBrands
                    )
            ) {
                throw new Exception(
                    'Не удалось обновить промокод.'
                );
            }

            $_SESSION['success'] =
                $this->success_edit;
        } catch (Exception $e) {
            log_message(
                'error',
                'Promocode update error: '
                . $e->getMessage()
            );

            $_SESSION['error'] = [
                $e->getMessage(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Reload item after POST
        |--------------------------------------------------------------------------
        */

        $item =
            $this->promocodes_model
                ->getItem($id);
    }

    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    $this->data['inner_view'] =
        'dashboard/promocodes/item';

    $this->data['title'] =
        lang('Edit')
        . ' '
        . (
            !empty($item->name)
                ? $item->name
                : $item->code
        );

    $this->data['parent_url'] =
        $this->path;

    $this->data['parent_title'] =
        $this->index_title;

    $this->data['item'] =
        $item;

    $this->data['brands'] =
        $this->getBrands();

    $this->data['brand_ids'] =
        $this->promocodes_model
            ->getBrandIds($id);

    $this->data['excluded_brand_ids'] =
        $this->promocodes_model
            ->getExcludedBrandIds($id);

    $this->load->vars(
        $this->data
    );

    $this->load->view(
        $this->main_layout
    );
}

    /*
    |--------------------------------------------------------------------------
    | Delete / disable
    |--------------------------------------------------------------------------
    */

    public function delete($id = 0)
    {
        $id =
            (int) $id;

        if ($id <= 0) {
            throw_on_404();
        }

        $item =
            $this->promocodes_model
                ->getItem(
                    $id
                );

        if (empty($item)) {
            throw_on_404();
        }

        try {
            if (
                !$this->promocodes_model
                    ->deletePromo(
                        $id
                    )
            ) {
                throw new Exception(
                    'Не удалось удалить промокод.'
                );
            }

            $_SESSION['success'] =
                $this->success_delete;
        } catch (Exception $e) {
            log_message(
                'error',
                'Promocode delete error: '
                . $e->getMessage()
            );

            $_SESSION['error'] = [
                $e->getMessage(),
            ];
        }

        redirect(
            $this->path
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Input
    |--------------------------------------------------------------------------
    */

    private function collectPostData()
    {
        $type =
            trim(
                (string) $this->input->post(
                    'type',
                    true
                )
            );

        $value =
            $this->normalizeNullableFloat(
                $this->input->post(
                    'value',
                    true
                )
            );

        $minAmount =
            $this->normalizeNullableFloat(
                $this->input->post(
                    'min_amount',
                    true
                )
            );

        $maxDiscount =
            $this->normalizeNullableFloat(
                $this->input->post(
                    'max_discount',
                    true
                )
            );

        if ($type !== 'percentage') {
            $maxDiscount =
                null;
        }

        return [
					'name' =>
							trim(
									(string) $this->input->post(
											'name',
											true
									)
							),

					'code' =>
							strtoupper(
									trim(
											(string) $this->input->post(
													'code',
													true
											)
									)
							),

					'type' =>
							$type,

					'value' =>
							$value,

					'min_amount' =>
							$minAmount,

					'max_discount' =>
							$maxDiscount,

					'usage_limit' =>
							$this->normalizeNullableInt(
									$this->input->post(
											'usage_limit',
											true
									)
							),

					'per_user_limit' =>
							$this->normalizeNullableInt(
									$this->input->post(
											'per_user_limit',
											true
									)
							),

					'allow_discounted' =>
							$this->input->post(
									'allow_discounted'
							)
									? 1
									: 0,

					'starts_at' =>
							$this->normalizeStartDate(
									$this->input->post(
											'starts_at',
											true
									)
							),

					'ends_at' =>
							$this->normalizeEndDate(
									$this->input->post(
											'ends_at',
											true
									)
							),

					'status' =>
							trim(
									(string) $this->input->post(
											'status',
											true
									)
							),

					'brand_scope' =>
							trim(
									(string) $this->input->post(
											'brand_scope',
											true
									)
							),
				];
    }

    private function collectExcludedBrands()
    {
        $brandIds =
            $this->input->post(
                'excluded_brands'
            );

        if (
            empty($brandIds)
            || !is_array($brandIds)
        ) {
            return [];
        }

        $result = [];

        foreach ($brandIds as $brandId) {
            $brandId =
                (int) $brandId;

            if (
                $brandId <= 0
                || in_array(
                    $brandId,
                    $result,
                    true
                )
            ) {
                continue;
            }

            $result[] =
                $brandId;
        }

        return $result;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validatePromoData(
        array $data
    ) {
        if (
            empty($data['name'])
        ) {
            throw new Exception(
                'Укажите название промокода.'
            );
        }

        if (
            empty($data['code'])
        ) {
            throw new Exception(
                'Укажите код промокода.'
            );
        }

        if (
            !preg_match(
                '/^[A-Z0-9_-]+$/',
                $data['code']
            )
        ) {
            throw new Exception(
                'Код может содержать только латинские буквы, цифры, "-" и "_".'
            );
        }

        if (
            !in_array(
                $data['type'],
                [
                    'percentage',
                    'fixed',
                ],
                true
            )
        ) {
            throw new Exception(
                'Некорректный тип скидки.'
            );
        }

        if (
            $data['value'] === null
            || $data['value'] <= 0
        ) {
            throw new Exception(
                'Значение скидки должно быть больше 0.'
            );
        }

        if (
            $data['type']
            === 'percentage'
            && $data['value'] > 100
        ) {
            throw new Exception(
                'Процент скидки не может быть больше 100%.'
            );
        }

        if (
            $data['min_amount'] !== null
            && $data['min_amount'] < 0
        ) {
            throw new Exception(
                'Минимальная сумма не может быть отрицательной.'
            );
        }

        if (
            $data['max_discount'] !== null
            && $data['max_discount'] <= 0
        ) {
            throw new Exception(
                'Максимальная скидка должна быть больше 0.'
            );
        }

        if (
            $data['usage_limit'] !== null
            && $data['usage_limit'] <= 0
        ) {
            throw new Exception(
                'Общий лимит использований должен быть больше 0.'
            );
        }

        if (
            $data['per_user_limit'] !== null
            && $data['per_user_limit'] <= 0
        ) {
            throw new Exception(
                'Лимит на пользователя должен быть больше 0.'
            );
        }

        if (
            !in_array(
                $data['status'],
                [
                    'draft',
                    'active',
                    'disabled',
                ],
                true
            )
        ) {
            throw new Exception(
                'Некорректный статус промокода.'
            );
        }

        if (
            !empty($data['starts_at'])
            && !empty($data['ends_at'])
            && strtotime(
                $data['starts_at']
            ) >= strtotime(
                $data['ends_at']
            )
        ) {
            throw new Exception(
                'Дата окончания должна быть позже даты начала.'
            );
        }

				if (
						!in_array(
								$data['brand_scope'],
								array(
										'all',
										'selected',
								),
								true
						)
				) {
						throw new Exception(
								'Некорректный режим применения по брендам.'
						);
				}
    }

    /*
    |--------------------------------------------------------------------------
    | Brands
    |--------------------------------------------------------------------------
    */

    private function getBrands()
    {
        /*
        |--------------------------------------------------------------------------
        | Не завязываемся на неизвестный метод Brands_model.
        |--------------------------------------------------------------------------
        |
        | Для админки нам нужны только id + title.
        |
        */

        return $this->db
            ->select(
                'id, title'
            )
            ->order_by(
                'title ASC'
            )
            ->get(
                'brands'
            )
            ->result();
    }

    /*
    |--------------------------------------------------------------------------
    | Normalizers
    |--------------------------------------------------------------------------
    */

    private function normalizeNullableFloat(
        $value
    ) {
        if (
            $value === null
            || trim(
                (string) $value
            ) === ''
        ) {
            return null;
        }

        $value =
            str_replace(
                ',',
                '.',
                trim(
                    (string) $value
                )
            );

        if (!is_numeric($value)) {
            throw new Exception(
                'Некорректное числовое значение.'
            );
        }

        return (float) $value;
    }

    private function normalizeNullableInt(
        $value
    ) {
        if (
            $value === null
            || trim(
                (string) $value
            ) === ''
        ) {
            return null;
        }

        if (
            !ctype_digit(
                trim(
                    (string) $value
                )
            )
        ) {
            throw new Exception(
                'Лимит должен быть целым положительным числом.'
            );
        }

        return (int) $value;
    }

private function normalizeStartDate(
    $value
) {
    $value =
        trim(
            (string) $value
        );

    if ($value === '') {
        return null;
    }

    $timestamp =
        strtotime(
            $value
        );

    if ($timestamp === false) {
        throw new Exception(
            'Некорректная дата начала действия.'
        );
    }

    return date(
        'Y-m-d 00:00:00',
        $timestamp
    );
}

private function normalizeEndDate(
    $value
) {
    $value =
        trim(
            (string) $value
        );

    if ($value === '') {
        return null;
    }

    $timestamp =
        strtotime(
            $value
        );

    if ($timestamp === false) {
        throw new Exception(
            'Некорректная дата окончания действия.'
        );
    }

    return date(
        'Y-m-d 23:59:59',
        $timestamp
    );
}
private function collectBrands()
{
    $brandIds =
        $this->input->post(
            'brands'
        );

    if (
        empty($brandIds)
        || !is_array($brandIds)
    ) {
        return array();
    }

    $result = array();

    foreach ($brandIds as $brandId) {
        $brandId =
            (int) $brandId;

        if (
            $brandId <= 0
            || in_array(
                $brandId,
                $result,
                true
            )
        ) {
            continue;
        }

        $result[] =
            $brandId;
    }

    return $result;
}
}