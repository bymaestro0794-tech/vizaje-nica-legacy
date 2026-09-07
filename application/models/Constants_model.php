<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Constants_model extends BaseModel
{
    protected $tblname = 'constants';

    public function __construct()
    {
        parent::__construct();
    }

	public function update_ru($id = false, $data)
	{
		if (empty($id)) {
			return false;
		}

		return $this->db->where('id', $id)->update($this->tblname, array('RU' => $data));
	}
	public function update_ro($id = false, $data)
	{
		if (empty($id)) {
			return false;
		}

		return $this->db->where('id', $id)->update($this->tblname, array('RO' => $data));
	}
    public function update_en($id = false, $data)
    {
        if (empty($id)) {
            return false;
        }

        return $this->db->where('id', $id)->update($this->tblname, array('EN' => $data));
    }
}