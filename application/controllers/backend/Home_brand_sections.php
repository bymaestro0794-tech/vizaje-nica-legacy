<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_brand_sections extends BackEndController
{
	protected $index_title;
	protected $add;

	protected $success_add;
	protected $success_edit;
	protected $success_delete;
	protected $success_update_order;

	protected $placements = [
		'after_new' => 'После новинок',
		'after_sale' => 'После скидок',
	];

	public function __construct()
	{
		parent::__construct(__CLASS__);

		$this->index_title =
			'Брендовые секции главной';

		$this->add = lang('Add');

		$this->success_add =
			'Брендовая секция успешно добавлена.';

		$this->success_edit =
			'Брендовая секция успешно обновлена.';

		$this->success_delete =
			'Брендовая секция успешно удалена.';

		$this->success_update_order =
			'Порядок успешно обновлён.';

		$this->data['title'] =
			$this->index_title;

		$this->data['add'] =
			$this->add;

		$this->data['placements'] =
			$this->placements;

		$this->load->model(
			'home_brand_sections_model'
		);

		$this->load->model(
			'products_model'
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Sections list
	|--------------------------------------------------------------------------
	*/

	public function index()
{
	init_load_img($this->main_page);

	$this->data['objects'] =
		$this->home_brand_sections_model
			->get_admin_sections();

	$this->data['products_path'] =
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/products/';

	$this->data['inner_view'] =
		'dashboard/home_brand_sections/index';

	$this->load->vars($this->data);
	$this->load->view($this->main_layout);
}

	/*
	|--------------------------------------------------------------------------
	| Create section
	|--------------------------------------------------------------------------
	*/

	public function put()
	{
		check_if_POST();

		init_load_img($this->main_page);

		$uploadedImages = [];

		try {
			$post =
				$this->collectSectionPost();

			$this->validateSectionData(
				$post
			);

			$desktopImage =
				$this->uploadImage('img');

			if (empty($desktopImage)) {
				throw new Exception(
					'Загрузите desktop-баннер.'
				);
			}

			$uploadedImages[] =
				$desktopImage;

			$post['img'] =
				$desktopImage;

			$mobileImage =
				$this->uploadImage('imgMob');

			if (!empty($mobileImage)) {
				$uploadedImages[] =
					$mobileImage;

				$post['imgMob'] =
					$mobileImage;
			}

			$sectionId =
				$this->home_brand_sections_model
					->create_section($post);

			if (empty($sectionId)) {
				throw new Exception(
					'Не удалось создать брендовую секцию.'
				);
			}

			$_SESSION['success'] =
				$this->success_add;
		} catch (Exception $e) {
			foreach ($uploadedImages as $image) {
				unlink_files(
					$this->main_page,
					$image
				);
			}

			log_message(
				'error',
				'Home brand section create error: '
				. $e->getMessage()
			);

			$_SESSION['error'] = [
				$e->getMessage(),
			];
		}

		redirect($this->path);
	}

	/*
	|--------------------------------------------------------------------------
	| Sections sorting
	|--------------------------------------------------------------------------
	*/

	public function update_order()
	{
		check_if_POST();

		try {
			$orders =
				$this->input->post('so');

			if (
				empty($orders)
				|| !is_array($orders)
			) {
				throw new Exception(
					lang('Error in received data!')
				);
			}

			$result =
				$this->home_brand_sections_model
					->update_sections_order(
						$orders
					);

			if (!$result) {
				throw new Exception(
					'Не удалось обновить порядок секций.'
				);
			}

			$_SESSION['success'] =
				$this->success_update_order;
		} catch (Exception $e) {
			log_message(
				'error',
				'Home brand sections order error: '
				. $e->getMessage()
			);

			$_SESSION['error'] = [
				$e->getMessage(),
			];
		}

		redirect($this->path);
	}

	/*
	|--------------------------------------------------------------------------
	| Edit section
	|--------------------------------------------------------------------------
	*/

	public function item($id = 0)
	{
		$id = (int) $id;

		if ($id <= 0) {
			throw_on_404();
		}

		init_load_img($this->main_page);

		$item =
			$this->home_brand_sections_model
				->get_section($id);

		if (empty($item)) {
			throw_on_404();
		}

		if (
			$_SERVER['REQUEST_METHOD']
			=== 'POST'
		) {
			$newUploadedImages = [];

			try {
				$post =
					$this->collectSectionPost();

				$this->validateSectionData(
					$post
				);

				$newDesktopImage =
					$this->uploadImage('img');

				$newMobileImage =
					$this->uploadImage('imgMob');

				if (!empty($newDesktopImage)) {
					$newUploadedImages[] =
						$newDesktopImage;

					$post['img'] =
						$newDesktopImage;
				}

				if (!empty($newMobileImage)) {
					$newUploadedImages[] =
						$newMobileImage;

					$post['imgMob'] =
						$newMobileImage;
				}

				$result =
					$this->home_brand_sections_model
						->update_section(
							$id,
							$post
						);

				if (!$result) {
					throw new Exception(
						'Не удалось обновить брендовую секцию.'
					);
				}

				if (
					!empty($newDesktopImage)
					&& !empty($item->img)
					&& $item->img !==
						$newDesktopImage
				) {
					unlink_files(
						$this->main_page,
						$item->img
					);
				}

				if (
					!empty($newMobileImage)
					&& !empty($item->imgMob)
					&& $item->imgMob !==
						$newMobileImage
				) {
					unlink_files(
						$this->main_page,
						$item->imgMob
					);
				}

				$_SESSION['success'] =
					$this->success_edit;
			} catch (Exception $e) {
				foreach (
					$newUploadedImages
					as $image
				) {
					unlink_files(
						$this->main_page,
						$image
					);
				}

				log_message(
					'error',
					'Home brand section edit error: '
						. $e->getMessage()
				);

				$_SESSION['error'] = [
					$e->getMessage(),
				];
			}

			$item =
				$this->home_brand_sections_model
					->get_section($id);
		}
		
		$this->data['delete_desktop_image_path'] =
        	'/' . ADM_CONTROLLER
        	. '/home_brand_sections/delete_image/'
        	. $id
        	. '/img';
        
        $this->data['delete_mobile_image_path'] =
        	'/' . ADM_CONTROLLER
        	. '/home_brand_sections/delete_image/'
        	. $id
        	. '/imgMob';
        
        $this->data['products_page_path'] =
        	'/' . ADM_CONTROLLER
        	. '/home_brand_sections/products/'
        	. $id;

		$this->data['inner_view'] =
			'dashboard/home_brand_sections/item';

		$this->data['title'] =
			lang('Edit')
			. ' '
			. (
				$item->titleRU
				?? ''
			);

		$this->data['parent_url'] =
			$this->path;

		$this->data['parent_title'] =
			$this->index_title;

		$this->data['item'] =
			$item;

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	/*
	|--------------------------------------------------------------------------
	| Delete section
	|--------------------------------------------------------------------------
	*/

	public function delete($id = 0)
	{
		$id = (int) $id;

		if ($id <= 0) {
			throw_on_404();
		}

		$item =
			$this->home_brand_sections_model
				->get_section($id);

		if (empty($item)) {
			throw_on_404();
		}

		try {
			$result =
				$this->home_brand_sections_model
					->delete_section($id);

			if (!$result) {
				throw new Exception(
					'Не удалось удалить брендовую секцию.'
				);
			}

			if (!empty($item->img)) {
				unlink_files(
					$this->main_page,
					$item->img
				);
			}

			if (!empty($item->imgMob)) {
				unlink_files(
					$this->main_page,
					$item->imgMob
				);
			}

			$_SESSION['success'] =
				$this->success_delete;
		} catch (Exception $e) {
			log_message(
				'error',
				'Home brand section delete error: '
					. $e->getMessage()
			);

			$_SESSION['error'] = [
				$e->getMessage(),
			];
		}

		redirect($this->path);
	}

	/*
	|--------------------------------------------------------------------------
	| Products page
	|--------------------------------------------------------------------------
	*/

	public function products($sectionId = 0)
{
	$sectionId = (int) $sectionId;

	if ($sectionId <= 0) {
		throw_on_404();
	}

	$section =
		$this->home_brand_sections_model
			->get_section($sectionId);

	if (empty($section)) {
		throw_on_404();
	}

	$searchQuery = trim(
		(string) $this->input->get(
			'query',
			true
		)
	);

	$searchProducts = [];

	if ($searchQuery !== '') {
		$searchProducts =
			$this->products_model
				->search_get_products_admin(
					$searchQuery
				);
	}

	$selectedItems =
		$this->home_brand_sections_model
			->get_admin_products(
				$sectionId
			);

	$selectedProductIds = [];

	foreach ($selectedItems as $item) {
		$selectedProductIds[] =
			(int) $item->product_id;
	}

	$this->data['title'] =
		'Товары брендовой секции: '
		. $section->titleRU;

	$this->data['parent_url'] =
		'/' . ADM_CONTROLLER
		. '/home_brand_sections';

	$this->data['parent_title'] =
		$this->index_title;

	$this->data['section'] =
		$section;

	$this->data['section_id'] =
		$sectionId;

	$this->data['query'] =
		$searchQuery;

	$this->data['search_products'] =
		$searchProducts;

	$this->data['selected_items'] =
		$selectedItems;

	$this->data['selected_product_ids'] =
		$selectedProductIds;

	/*
	|--------------------------------------------------------------------------
	| Canonical production routes
	|--------------------------------------------------------------------------
	|
	| Все POST-маршруты без завершающего слеша.
	| Иначе production выполняет 301 и POST превращается в GET.
	|
	*/

	$this->data['products_page_path'] =
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/products/'
		. $sectionId;

	$this->data['add_product_path'] =
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/add_product/'
		. $sectionId;

	$this->data['update_products_order_path'] =
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/update_products_order/'
		. $sectionId;

	$this->data['delete_product_path'] =
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/delete_product/'
		. $sectionId
		. '/';

	$this->data['inner_view'] =
		'dashboard/home_brand_sections/products';

	$this->load->vars($this->data);
	$this->load->view($this->main_layout);
}

	/*
	|--------------------------------------------------------------------------
	| Add product
	|--------------------------------------------------------------------------
	*/

	public function add_product(
		$sectionId = 0
	) {
		check_if_POST();

		$sectionId =
			(int) $sectionId;

		$productId =
			(int) $this->input->post(
				'product_id',
				true
			);

		$result =
			$this->home_brand_sections_model
				->add_product(
					$sectionId,
					$productId
				);

		if (!empty($result['success'])) {
			$_SESSION['success'] =
				$result['message'];
		} else {
			$_SESSION['error'] = [
				$result['message'],
			];
		}

		$this->redirectToProducts(
			$sectionId
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Products sorting
	|--------------------------------------------------------------------------
	*/

	public function update_products_order(
		$sectionId = 0
	) {
		check_if_POST();

		$sectionId =
			(int) $sectionId;

		$orders =
			$this->input->post('so');

		if (
			$sectionId <= 0
			|| empty($orders)
			|| !is_array($orders)
		) {
			$_SESSION['error'] = [
				'Некорректные данные сортировки.',
			];

			$this->redirectToProducts(
				$sectionId
			);
		}

		$result =
			$this->home_brand_sections_model
				->update_products_order(
					$sectionId,
					$orders
				);

		if ($result) {
			$_SESSION['success'] =
				'Порядок товаров обновлён.';
		} else {
			$_SESSION['error'] = [
				'Не удалось обновить порядок товаров.',
			];
		}

		$this->redirectToProducts(
			$sectionId
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Delete product from section
	|--------------------------------------------------------------------------
	*/

	public function delete_product(
		$sectionId = 0,
		$id = 0
	) {
		$sectionId =
			(int) $sectionId;

		$id =
			(int) $id;

		if (
			$sectionId <= 0
			|| $id <= 0
		) {
			throw_on_404();
		}

		$result =
			$this->home_brand_sections_model
				->delete_product(
					$sectionId,
					$id
				);

		if ($result) {
			$_SESSION['success'] =
				'Товар удалён из брендовой секции.';
		} else {
			$_SESSION['error'] = [
				'Не удалось удалить товар.',
			];
		}

		$this->redirectToProducts(
			$sectionId
		);
	}
	
	/*
|--------------------------------------------------------------------------
| Delete section image
|--------------------------------------------------------------------------
*/

public function delete_image(
	$id = 0,
	$field = ''
) {
	$id = (int) $id;

	$allowedFields = [
		'img',
		'imgMob',
	];

	if (
		$id <= 0
		|| !in_array(
			$field,
			$allowedFields,
			true
		)
	) {
		throw_on_404();
	}

	$item =
		$this->home_brand_sections_model
			->get_section($id);

	if (empty($item)) {
		throw_on_404();
	}

	$fileName = !empty($item->{$field})
		? (string) $item->{$field}
		: '';

	try {
		$result =
			$this->home_brand_sections_model
				->update_section(
					$id,
					[
						$field => null,
					]
				);

		if (!$result) {
			throw new Exception(
				'Не удалось удалить изображение.'
			);
		}

		if ($fileName !== '') {
			unlink_files(
				$this->main_page,
				$fileName
			);
		}

		$_SESSION['success'] =
			$field === 'imgMob'
				? 'Мобильный баннер удалён.'
				: 'Desktop-баннер удалён.';
	} catch (Exception $e) {
		log_message(
			'error',
			'Home brand image delete error: '
			. $e->getMessage()
		);

		$_SESSION['error'] = [
			$e->getMessage(),
		];
	}

	redirect(
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/item/'
		. $id
	);
}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	private function collectSectionPost()
	{
		return [
			'titleRU' => trim(
				(string) $this->input->post(
					'titleRU',
					true
				)
			),

			'titleRO' => trim(
				(string) $this->input->post(
					'titleRO',
					true
				)
			),

			'urlRU' => trim(
				(string) $this->input->post(
					'urlRU',
					true
				)
			),

			'urlRO' => trim(
				(string) $this->input->post(
					'urlRO',
					true
				)
			),

			'placement' =>
				$this->normalizePlacement(
					$this->input->post(
						'placement',
						true
					)
				),

			'isShown' =>
				$this->input->post(
					'isShown'
				)
					? 1
					: 0,
		];
	}

	private function validateSectionData(
		array $data
	) {
		if (empty($data['titleRU'])) {
			throw new Exception(
				'Укажите название бренда на русском языке.'
			);
		}

		if (empty($data['titleRO'])) {
			throw new Exception(
				'Укажите название бренда на румынском языке.'
			);
		}

		if (empty($data['urlRU'])) {
			throw new Exception(
				'Укажите ссылку бренда на русском языке.'
			);
		}

		if (empty($data['urlRO'])) {
			throw new Exception(
				'Укажите ссылку бренда на румынском языке.'
			);
		}
	}

	private function uploadImage(
		$fieldName
	) {
		if (
			empty($_FILES[$fieldName])
			|| empty(
				$_FILES[$fieldName]['name']
			)
		) {
			return null;
		}

		if (
			!$this->upload->do_upload(
				$fieldName
			)
		) {
			throw new Exception(
				strip_tags(
					$this->upload
						->display_errors(
							'',
							''
						)
				)
			);
		}

		$fileData =
			$this->upload->data();

		if (
			empty($fileData['file_name'])
			|| !verify_img_extension(
				$fileData['file_ext']
			)
		) {
			throw new Exception(
				'Недопустимый формат изображения.'
			);
		}

		return $fileData['file_name'];
	}

	private function normalizePlacement(
		$placement
	) {
		$placement = strtolower(
			trim((string) $placement)
		);

		return isset(
			$this->placements[$placement]
		)
			? $placement
			: 'after_new';
	}

	private function redirectToProducts($sectionId)
{
	redirect(
		'/' . ADM_CONTROLLER
		. '/home_brand_sections/products/'
		. (int) $sectionId
	);
}
}