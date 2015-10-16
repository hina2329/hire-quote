<?php

// Orders Class
class manage_orders extends HireQuote {

    public function __construct() {
        parent::__construct();
    }

    // Iniating main method to display orders
    public function init() {
        $sort = filter_input(INPUT_GET, 'sort');
        $search = filter_input(INPUT_POST, 's');
        ?>

        <h1>
            <form method="post" action="<?php echo admin_url('admin.php?page=' . $this->page . '&action=init&search=true'); ?>" class="search-box">
                <label class="screen-reader-text" for="search-input">Search Orders:</label>
                <input type="search" id="search-input" name="s" value="">
                <input type="submit" id="search-submit" class="button" value="Search Orders">
            </form>
            <?php echo get_admin_page_title(); ?>
        </h1>

        <?php $this->notify('Order'); ?>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="10%">Order No.</th>
                    <th width="20%">Product Name</th>
                    <th width="20%">Category Name</th>
                    <th width="20%">Customer Name</th>
                    <th width="10%"><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=init&sort=postcode'); ?>"><span>Post Code</span> <span class="dashicons-before dashicons-sort"></span></a></th>
                    <th width="10%">Order Status</th>
                    <th width="10%" class="actions">Actions</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting orders
                if (isset($sort)) {
                    $results = $this->wpdb->get_results("SELECT * FROM $this->orders_tbl ORDER BY odr_postcode ASC");
                } else if (isset($search)) {
                    $results = $this->wpdb->get_results("SELECT odr.*, prod.* FROM $this->orders_tbl AS odr "
                            . "INNER JOIN $this->products_tbl as prod ON odr.odr_prod_id = prod.prod_id "
                            . "INNER JOIN $this->categories_tbl as cat ON odr.odr_cat_id = cat.cat_id "
                            . "INNER JOIN $this->customers_tbl as cust ON odr.odr_cust_id = cust.cust_id "
                            . "WHERE prod.prod_name LIKE '%$search%' "
                            . "OR cat.cat_name LIKE '%$search%' "
                            . "OR cust.cust_name LIKE '%$search%' "
                            . "OR odr.odr_status LIKE '%$search%' "
                            . "OR odr.odr_postcode LIKE '%$search%' "
                            . "OR odr.odr_id LIKE '%$search%'");
                } else {
                    $results = $this->wpdb->get_results("SELECT * FROM $this->orders_tbl");
                }

                if ($results) {

                    foreach ($results as $row) {
                        $customer = $this->wpdb->get_row("SELECT * FROM $this->customers_tbl WHERE cust_id = $row->odr_cust_id");
                        $product = $this->wpdb->get_row("SELECT * FROM $this->products_tbl WHERE prod_id = $row->odr_prod_id");
                        $cat = $this->wpdb->get_row("SELECT * FROM $this->categories_tbl WHERE cat_id = $row->odr_cat_id");
                        ?>
                        <tr>
                            <td class="column-title">
                                <strong><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=view&id=' . $row->odr_id); ?>">#<?php echo $row->odr_id; ?></a></strong>
                            </td>
                            <td><?php echo $product->prod_name; ?></td>
                            <td><?php echo $cat->cat_name; ?></td>
                            <td><?php echo $customer->cust_name; ?></td>
                            <td><?php echo $row->odr_postcode; ?></td>
                            <td><?php echo $row->odr_status; ?></td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=view&id=' . $row->odr_id); ?>" class="dashicons-before dashicons-visibility" title="View"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=status&set=Processed&id=' . $row->odr_id); ?>" class="dashicons-before dashicons-thumbs-up" title="Process" onclick="return confirm('Are you sure you want to process this order?');"></a>
                <!--                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->odr_id); ?>" class="dashicons-before dashicons-edit" title="Edit"></a>-->
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=status&set=Approved&id=' . $row->odr_id); ?>" class="dashicons-before dashicons-yes" title="Approve" onclick="return confirm('Are you sure you want to approve this order?');"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=status&set=Canceled&id=' . $row->odr_id); ?>" class="dashicons-before dashicons-no" title="Cancel" onclick="return confirm('Are you sure you want to cancel this order?');"></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center;"><strong>No Records Found</strong></td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>

        </table>

        <?php
    }

    // View
    public function view() {
        $id = filter_input(INPUT_GET, 'id');
        $odr = $this->wpdb->get_row("SELECT * FROM $this->orders_tbl WHERE odr_id = $id");
        $customer = $this->wpdb->get_row("SELECT * FROM $this->customers_tbl WHERE cust_id = $odr->odr_cust_id");
        $product = $this->wpdb->get_row("SELECT * FROM $this->products_tbl WHERE prod_id = $odr->odr_prod_id");
        $cat = $this->wpdb->get_row("SELECT * FROM $this->categories_tbl WHERE cat_id = $odr->odr_cat_id");
        $opt = explode(';', $odr->odr_options);
        $opt_count = count($opt) - 1;
        ?>
        <h1>View Order</h1>

        <div class="odr-view">
            <table class="wp-list-table widefat fixed striped pages print-this">
                <thead>
                    <tr>
                        <th width="20%">Order Details</th>
                        <th class="actions"><a href="#" class="dashicons-before dashicons-format-aside" onclick="window.print();">&nbsp;&nbsp;Print this order</a></th>
                    </tr>
                </thead>

                <tbody id="the-list">
                    <tr><td colspan="2"><?php echo $odr->odr_full; ?></td></tr>
                </tbody>

            </table>
        </div>
        <?php
    }

    // Status Setter
    public function status() {

        $id = filter_input(INPUT_GET, 'id');
        $status = filter_input(INPUT_GET, 'set');

        $this->wpdb->update($this->orders_tbl, array('odr_status' => $status), array('odr_id' => $id));

        wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=updated'));

        exit;
    }

}
