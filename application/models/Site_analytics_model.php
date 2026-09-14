<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Site_analytics_model extends CI_Model
{
    public function insert_event(array $event)
    {
        return $this->db->insert('analytics_events', $event);
    }
}

