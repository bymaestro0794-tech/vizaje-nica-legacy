<div class="main-catalog__body">
    <?php foreach ($products as $product) { ?>
        <?php $this->load->view("layouts/pages/product_main", array('item' => $product)); ?>
    <?php } ?>
</div>
<div class="main-catalog__paggination paggination-main-catalog">
    <? $this->load->view('layouts/pages/paginator'); ?>
</div>