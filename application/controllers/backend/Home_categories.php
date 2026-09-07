<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home_categories extends BackEndController
{
	protected $add;
	protected $index_title;
	protected $success_add;
	protected $success_update_order;
	protected $success_edit;
	protected $success_delete;

	public function __construct()
	{
		parent::__construct(__CLASS__);

		$this->index_title = 'Категории на главной';
		$this->add = lang('Add');

		$this->success_add =
			'Категория успешно добавлена.';

		$this->success_update_order =
			'Порядок категорий успешно обновлён.';

		$this->success_edit =
			'Категория успешно обновлена.';

		$this->success_delete =
			'Категория успешно удалена.';

		$this->data['title'] =
			$this->index_title;

		$this->data['add'] =
			$this->add;

		$this->load->model(
			'home_categories_model'
		);
	}

	/*
	|--------------------------------------------------------------------------
	| Categories list
	|--------------------------------------------------------------------------
	*/

	public function index()
	{
		init_load_img($this->main_page);

		$this->data['objects'] =
			$this->home_categories_model
				->get_admin_items();

		$this->data['inner_view'] =
			'dashboard/home_categories/index';

		$this->load->vars($this->data);
		$this->load->view($this->main_layout);
	}

	/*
	|--------------------------------------------------------------------------
	| Add category
	|--------------------------------------------------------------------------
	*/

	public function put()
	{
		check_if_POST();

		init_load_img($this->main_page);

		try {
			$post = $this->collectPostData();

			if (empty($post['titleRU'])) {
				throw new Exception(
					'Укажите название категории на русском языке.'
				);
			}

			if (empty($post['titleRO'])) {
				throw new Exception(
					'Укажите название категории на румынском языке.'
				);
			}

			if (empty($post['urlRU'])) {
				throw new Exception(
					'Укажите ссылку категории на русском языке.'
				);
			}

			if (empty($post['urlRO'])) {
				throw new Exception(
					'Укажите ссылку категории на румынском языке.'
				);
			}

			$uploadedImage =
				$this->uploadImage('img');

			if (empty($uploadedImage)) {
				throw new Exception(
					'Не удалось загрузить изображение категории.'
				);
			}

			$post['img'] = $uploadedImage;

			if (
				!$this->home_categories_model
					->create($post)
			) {
				throw new Exception(
					lang('Error writing data to table')
					. $this->main_page
				);
			}

			$_SESSION['success'] =
				$this->success_add;
		} catch (Exception $e) {
			log_message(
				'error',
				'Home category create error: '
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
	| Update sorting
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

			if (
				!$this->home_categories_model
					->updateOrder($orders)
			) {
				throw new Exception(
					lang('Error writing data to table')
					. $this->main_page
				);
			}

			$_SESSION['success'] =
				$this->success_update_order;
		} catch (Exception $e) {
			log_message(
				'error',
				'Home category order error: '
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
	| Edit category
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
			$this->home_categories_model
				->get_item($id);

		if (empty($item)) {
			throw_on_404();
		}

		if (
			$_SERVER['REQUEST_METHOD']
			=== 'POST'
		) {
			try {
				$post = $this->collectPostData();

				if (empty($post['titleRU'])) {
					throw new Exception(
						'Укажите название категории на русском языке.'
					);
				}

				if (empty($post['titleRO'])) {
					throw new Exception(
						'Укажите название категории на румынском языке.'
					);
				}

				if (empty($post['urlRU'])) {
					throw new Exception(
						'Укажите ссылку категории на русском языке.'
					);
				}

				if (empty($post['urlRO'])) {
					throw new Exception(
						'Укажите ссылку категории на румынском языке.'
					);
				}

				$uploadedImage =
					$this->uploadImage('img');

				if (!empty($uploadedImage)) {
					$oldImage =
						(string) ($item->img ?? '');

					$post['img'] =
						$uploadedImage;
				}

				if (
					!$this->home_categories_model
						->updateItem(
							$id,
							$post
						)
				) {
					throw new Exception(
						lang('Error writing data to table')
						. $this->main_page
					);
				}

				/*
			 * Старое изображение удаляем только после
			 * успешного обновления записи в базе.
			 */
				if (
					!empty($uploadedImage)
					&& !empty($oldImage)
					&& $oldImage !== $uploadedImage
				) {
					unlink_files(
						$this->main_page,
						$oldImage
					);
				}

				$_SESSION['success'] =
					$this->success_edit;
			} catch (Exception $e) {
				log_message(
					'error',
					'Home category update error: '
					. $e->getMessage()
				);

				$_SESSION['error'] = [
					$e->getMessage(),
				];
			}

			$item =
				$this->home_categories_model
					->get_item($id);
		}

		$this->data['inner_view'] =
			'dashboard/home_categories/item';

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
	| Delete category
	|--------------------------------------------------------------------------
	*/

	public function delete($id = 0)
	{
		$id = (int) $id;

		if ($id <= 0) {
			throw_on_404();
		}

		$item =
			$this->home_categories_model
				->get_item($id);

		if (empty($item)) {
			throw_on_404();
		}

		try {
			if (
				!$this->home_categories_model
					->deleteItem($id)
			) {
				throw new Exception(
					lang('Error deleting data from table')
					. $this->main_page
				);
			}

			if (!empty($item->img)) {
				unlink_files(
					$this->main_page,
					$item->img
				);
			}

			$_SESSION['success'] =
				$this->success_delete;
		} catch (Exception $e) {
			log_message(
				'error',
				'Home category delete error: '
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
	| Helpers
	|--------------------------------------------------------------------------
	*/

	private function collectPostData()
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

			'isShown' => $this->input->post(
				'isShown'
			)
				? 1
				: 0,
		];
	}

	private function uploadImage($fieldName)
	{
		if (
			empty($_FILES[$fieldName])
			|| empty($_FILES[$fieldName]['name'])
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
}