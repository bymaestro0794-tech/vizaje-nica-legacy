<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Subscriptions_model extends BaseModel
	{
		protected $tblname = 'subscriptions';

		public function __construct()
		{
			parent::__construct();
		}

        public function get_front_subscriptions(){
            $this->db->select("
            id as id,
            email as email,
            mailing as mailing,
        ");
            $this->db->order_by('sorder ASC, id DESC');
            return $this->db->get($this->tblname)->result();
        }
        public function get_front_subscriptions_email($email){
            $this->db->select("*");
            $this->db->where('email', $email);
            return $this->db->get($this->tblname)->row();
        }
        public function get_subscriptions_for_xls(){
            $this->db->select("*");
            $this->db->where('mailing', 1);
            return $this->db->get($this->tblname)->result();
        }
	}