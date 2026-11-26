<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Orders extends BackEndController
{
    protected $add;
    protected $index_title;
    protected $title;
    protected $success_add;
    protected $success_update_order;
    protected $success_edit;
    protected $success_delete;

    public function __construct()
    {
        parent::__construct(__CLASS__);

        $this->index_title = lang('Orders');
        $this->add = lang('Add');
        $this->success_update_order = lang('You have successfully updated display order!');
        $this->success_add = lang('You have successfully added an object');
        $this->success_edit = lang('You have successfully updated the object');
        $this->success_delete = lang('You have successfully deleted the object');

        $this->data['title'] = $this->index_title;
        $this->data['add'] = $this->add;
        $this->load->model('orders_model');
        $this->load->model('stores_model');
    }

    public function index()
    {
        init_load_img($this->main_page);
        $filters = $this->input->get(null, true) ?: array();
        $search = trim((string) ($filters['query'] ?? ''));
        $view = $this->admin_role === 'operator'
            ? ((($filters['view'] ?? 'my') === 'all') ? 'all' : 'my')
            : 'all';
        $objects = $this->orders_model->listAdminOrders(
            $search,
            $filters,
            $this->admin_role,
            (int) ($_SESSION['admin_id'] ?? 0),
            $view
        );

        $this->data['inner_view'] = $this->index_view;
        $this->data['objects'] = $objects;
        $this->data['orders_view'] = $view;
        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function put()
    {
        check_if_POST();

        $post = array();

        init_load_img($this->main_page);

        try {
            foreach ($_POST as $index => $item) {
                $post[$index] = $this->input->post($index, TRUE);
            }

            if (!$this->orders_model->put($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_add;
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function update_order()
    {
        check_if_POST();

        try {
            $post = $this->input->post('so');

            if (empty($post) || !is_array($post)) {
                throw new Exception(lang('Error writing data to table'));
            }

            if (!$this->orders_model->update_sorder($post)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }

            $_SESSION['success'] = $this->success_update_order;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

    public function item($id = 0)
    {
        $id = (int)$id;

        init_load_img($this->main_page);

        $item = $this->orders_model->findAdminOrder($id);
        if (empty($item)) throw_on_404();
        $statusBeforeUpdate = (string) $item->status;
        $currentAdminId = (int) ($_SESSION['admin_id'] ?? 0);
        $isAssignedToOtherOperator = $this->admin_role === 'operator'
            && !empty($item->operator_admin_id)
            && (int) $item->operator_admin_id !== $currentAdminId;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            try {
                if ($isAssignedToOtherOperator) {
                    throw new Exception('Заказ уже назначен другому оператору.');
                }

                foreach ($_POST as $index => $post_data) {
                    $post[$index] = $this->input->post($index, TRUE);
                }
                unset($post['operator_admin_id']);

                if (isset($post['status'])) {
                    $post['status'] = strtolower(trim((string) $post['status']));
                    if (!in_array($post['status'], array(
                        'new',
                        'progress',
                        'courier',
                        'delivered',
                        'finished',
                        'canceled'
                    ), true)) {
                        throw new Exception('Invalid order status');
                    }
                }

                $post['updated'] = date('Y-m-d H:i:s', strtotime('+2 hours'));
                $products = array();
                $deleteProductIds = array();
                if (isset($post['products'])) {
                    foreach ($post['products'] as $key => $product) {
                        if ($product['price'] == 0)
                            $deleteProductIds[] = (int) $key;
                        else
                            $products[] = ['id' => $key, 'qty' => $product['qty'], 'total' => $product['qty'] * $product['price']];
                    }

                }

                unset($post['products']);

                $saveResult = $this->orders_model->saveAdminOrder(
                    $id,
                    $post,
                    $currentAdminId,
                    $this->admin_role === 'operator'
                );
                if ($saveResult === 'assigned_to_other') {
                    throw new Exception('Другой оператор уже взял этот заказ в обработку.');
                }
                if ($saveResult !== true) {
                    throw new Exception(lang('Error writing data to table') . $this->main_page);
                }

                if (!empty($products)) {
                    $this->db->update_batch('orders_products', $products, 'id');
                }
                if (!empty($deleteProductIds)) {
                    $this->db->where_in('id', $deleteProductIds)->delete('orders_products');
                }

                $_SESSION['success'] = $this->success_edit;
            } catch (Exception $e) {
                log_message('error', $e->getMessage());
                $errors[] = lang('Exception thrown') . $e->getMessage();
                $_SESSION['error'] = $errors;
            }

            $item = $this->orders_model->findAdminOrder($id);
            $isAssignedToOtherOperator = $this->admin_role === 'operator'
                && !empty($item->operator_admin_id)
                && (int) $item->operator_admin_id !== $currentAdminId;

            if (!empty($item) && (string) $item->status !== $statusBeforeUpdate) {
                try {
                    $this->load->model(
                        'Partners_referral_analytics_model',
                        'partners_referral_analytics_model'
                    );
                    $this->partners_referral_analytics_model->recordOrderStatusChange(
                        $id,
                        $statusBeforeUpdate,
                        (string) $item->status
                    );
                } catch (Throwable $analyticsException) {
                    // Admin order updates must not depend on the analytics service.
                }
            }

        }

        $this->data['inner_view'] = $this->item_view;
        $this->data['title'] = 'Order #' . $item->order_id;
        $this->data['parent_url'] = $this->path;
        $this->data['parent_title'] = $this->index_title;
        $this->data['item'] = $item;
        $this->data['is_assigned_to_other_operator'] = $isAssignedToOtherOperator;
        $this->data['creator_referral'] = $this->orders_model->creatorReferralForOrder($item->id);
        $this->data['stores'] = $this->stores_model->get_stores('RU');
        $this->data['products'] = $this->orders_model->orderProducts($item->id, 'RU');

        $this->load->vars($this->data);
        $this->load->view($this->main_layout);
    }

    public function delete($id = false)
    {
        $id = (int)$id;

        $item = $this->orders_model->find_first($id);

        if (empty($item)) throw_on_404();

        try {
            if (!$this->orders_model->delete($id)) {
                throw new Exception(lang('Error writing data to table') . $this->main_page);
            }
            $_SESSION['success'] = $this->success_delete;
        } catch (Exception $e) {
            $errors[] = lang('Exception thrown') . $e->getMessage();
            $_SESSION['error'] = $errors;
        }

        redirect($this->path);
    }

}
