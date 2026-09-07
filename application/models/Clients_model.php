<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clients_model extends BaseModel
{
    protected $tblname = 'clients';

    public function __construct()
    {
        parent::__construct();
    }

    public function pagination($limit, $current = 1, $xml_success)
    {

        $this->db->where('code', 1);
        if (!empty($_GET['sorder'])){
            $this->db->order_by($_GET['sorder'].', id DESC');
        } else {
            $this->db->order_by('sorder ASC, id DESC');
        }

        $offset = ($current * $limit) - $limit;
        if (!empty($xml_success)) {
            $query = $this->db->order_by('sorder ASC, id DESC')->from($this->tblname);
        } else {
            $query = $this->db->order_by('sorder ASC, id DESC')->from($this->tblname);
        }

        $total = clone $query;
        $total = $total->count_all_results();


        if (!empty($_GET['sorder'])){
            $this->db->order_by($_GET['sorder'].', id DESC');
        } else {
            $this->db->order_by('sorder ASC, id DESC');
        }
        $items = $query->limit($limit)->offset($offset)->get()->result();

        return ['data' => $items, 'count' => $total];
    }


    public function get_clients_active($search = false){
        $this->db->select("*");
        $this->db->where('code', 1);
        if (!empty($search)) {
            $this->db->group_start();
        $this->db->or_like('name', $search);
        $this->db->or_like('surname', $search);
        $this->db->or_like('email',$search);

        $this->db->or_like('phone',$search);

        $this->db->group_end();
        }
        $this->db->order_by("id DESC");
        return $this->db->get($this->tblname)->result();
    }
    public function find_active(){
        $this->db->select("*");
        $this->db->where('code', 1);
        $this->db->order_by("id DESC");
        return $this->db->get($this->tblname)->result();
    }
    public function get_address($id){
        $this->db->select("*");
        $this->db->where("client_id",$id);
        return  $this->db->get('clients_addresses')->row();
    }
    public function login($phone, $password){
        $this->db->select("*");
        $this->db->where('phone', $phone);
        $this->db->where('password', $password);
        $this->db->where('code', 1);
        return $this->db->get($this->tblname)->row();
    }
    public function login_code($phone, $login_code){
        $this->db->select("*");
        $this->db->where('phone', $phone);
        $this->db->where('code', $login_code);
        return $this->db->get($this->tblname)->row();
    }
    public function registration_email($email){
        $this->db->select("id,active,email");
        $this->db->where('email', $email);
        $this->db->where('code', 1);
        $this->db->order_by('id', 'DESC');
        return $this->db->get($this->tblname)->row();
    }

    public function registration_phone($phone){
        $this->db->select("id");
        $this->db->where('phone', $phone);
        $this->db->where('code', 1);
        return $this->db->get($this->tblname)->row();
    }
    public function check_email($email){
        $this->db->select(" id, email, password");
        $this->db->where('email', $email);
        $this->db->where('code', 1);
        return $this->db->get($this->tblname)->row();
    }
    public function check_number($phone){
        $this->db->select(" id, email, password");
        $this->db->where('phone', $phone);
        $this->db->where('code', 1);
        return $this->db->get($this->tblname)->row();
    }

    public function recovery($email){
        $this->db->select(" id ");
        $this->db->where('email', $email);
        $this->db->where('code', 1);
        return $this->db->get($this->tblname)->row();
    }

    public function get_client($id = null)
    {
        if ($id == null) return false;
        $this->db->where('id', $id);
        $this->db->where('active', 1);
        return $this->db->get($this->tblname)->row();
    }

    public function get_client_login($id)
    {
        $this->db->select("
            id as id,
            name as name,
            surname as surname,
            email as email,
            phone as phone, 
            Discount as discount, 
            Bonus as Bonus, 
            address as address,  
        ");
        $this->db->where('id', $id);
        return $this->db->get($this->tblname)->row();
    }


}
