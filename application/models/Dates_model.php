<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dates_model extends BaseModel
{
    protected $tblname = 'dates';
    protected $nextDay = '';

    public function __construct()
    {
        parent::__construct();
    }
}
