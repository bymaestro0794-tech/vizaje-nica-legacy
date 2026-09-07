<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Brand_certificates_model extends BaseModel
{
    protected $tblname = 'brand_certificates';

    public function __construct()
    {
        parent::__construct();
    }

    public function get_by_brand($brandId)
    {
        return $this->db
            ->where('brand_id', (int) $brandId)
            ->order_by('sorder ASC, id DESC')
            ->get($this->tblname)
            ->result();
    }

    public function find_by_brand_and_id($brandId, $certificateId)
    {
        return $this->db
            ->where('id', (int) $certificateId)
            ->where('brand_id', (int) $brandId)
            ->get($this->tblname)
            ->row();
    }

    public function update_certificate(
        $certificateId,
        $brandId,
        array $data
    ) {
        return $this->db
            ->where('id', (int) $certificateId)
            ->where('brand_id', (int) $brandId)
            ->update($this->tblname, $data);
    }

    public function delete_certificate(
        $certificateId,
        $brandId
    ) {
        return $this->db
            ->where('id', (int) $certificateId)
            ->where('brand_id', (int) $brandId)
            ->delete($this->tblname);
    }

    public function get_visible_grouped_by_brand($lang = 'RU')
    {
        $lang = strtoupper($lang);

        if (!in_array($lang, ['RU', 'RO'], true)) {
            $lang = 'RU';
        }

        $titleColumn = 'title' . $lang;

        $rows = $this->db
            ->select(
                '
                brand_certificates.id,
                brand_certificates.brand_id,
                brand_certificates.image,
                brand_certificates.preview_image,
                brand_certificates.sorder,
                brand_certificates.' . $titleColumn . ' AS certificate_title,
                brands.title AS brand_title,
                brands.uri AS brand_uri,
                brands.img AS brand_image
                '
            )
            ->from($this->tblname)
            ->join(
                'brands',
                'brands.id = brand_certificates.brand_id',
                'inner'
            )
            ->where('brand_certificates.isShown', 1)
            ->where('brands.isShown', 1)
            ->order_by('brands.sorder ASC, brands.title ASC')
            ->order_by('brand_certificates.sorder ASC')
            ->order_by('brand_certificates.id DESC')
            ->get()
            ->result();

        if (empty($rows)) {
            return [];
        }

        $grouped = [];

        foreach ($rows as $row) {
            $brandId = (int) $row->brand_id;

            if (!isset($grouped[$brandId])) {
                $grouped[$brandId] = [
                    'id' => $brandId,
                    'title' => $row->brand_title,
                    'uri' => $row->brand_uri,
                    'image' => $row->brand_image,
                    'certificates' => [],
                ];
            }

            $grouped[$brandId]['certificates'][] = [
                'id' => (int) $row->id,
                'title' => !empty($row->certificate_title)
                    ? $row->certificate_title
                    : $row->brand_title,
                'image' => $row->image,
                'preview_image' => $row->preview_image,
                'sorder' => (int) $row->sorder,
            ];
        }

        return array_values($grouped);
    }
    public function get_public_grouped_by_brand()
{
    $rows = $this->db
        ->select([
            'brand_certificates.id',
            'brand_certificates.brand_id',
            'brand_certificates.titleRU',
            'brand_certificates.titleRO',
            'brand_certificates.image',
            'brand_certificates.preview_image',
            'brand_certificates.sorder',
            'brands.title AS brand_title',
            'brands.uri AS brand_uri',
        ])
        ->from($this->tblname)
        ->join(
            'brands',
            'brands.id = brand_certificates.brand_id',
            'inner'
        )
        ->where('brand_certificates.isShown', 1)
        ->where('brands.isShown', 1)
        ->order_by('brands.title', 'ASC')
        ->order_by('brand_certificates.sorder', 'ASC')
        ->order_by('brand_certificates.id', 'DESC')
        ->get()
        ->result();

    $grouped = [];

    foreach ($rows as $row) {
        $brandId = (int) $row->brand_id;

        if (!isset($grouped[$brandId])) {
            $grouped[$brandId] = [
                'id' => $brandId,
                'title' => $row->brand_title,
                'uri' => $row->brand_uri,
                'certificates' => [],
            ];
        }

        $grouped[$brandId]['certificates'][] = $row;
    }

    return array_values($grouped);
}
}