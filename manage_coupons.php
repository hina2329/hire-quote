<?php

// Coupons Class
class manage_coupons extends HireQuote {

    public function __construct() {
        parent::__construct();
    }

    // Iniating main method to display coupons
    public function init() {
        ?>

        <h1><?php echo get_admin_page_title(); ?> <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form'); ?>" class="page-title-action">Add New Category</a></h1>

        <?php $this->notify('Coupon'); ?>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="45%">Coupon Code</th>
                    <th width="45%">Coupon Discount</th>
                    <th width="10%" class="actions">Actions</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting coupon
                $results = $this->wpdb->get_results("SELECT * FROM $this->coupons_tbl");

                if ($results) {

                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td class="column-title">
                                <strong><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->copn_id); ?>"><?php echo $row->copn_code; ?></a></strong>
                            </td>
                            <td><?php echo $row->copn_discount; ?>%</td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->copn_id); ?>" class="dashicons-before dashicons-edit" title="Edit"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=del&id=' . $row->copn_id); ?>" class="dashicons-before dashicons-trash" title="Delete" onclick="return confirm('Are you sure you want to delete this?');"></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="3" style="text-align: center;"><strong>No Records Found</strong></td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>

        </table>
        <?php
    }

    // Add new or edit coupon form
    public function form() {

        // Getting coupon data if user requests to edit
        $id = filter_input(INPUT_GET, 'id');
        $row = $this->wpdb->get_row("SELECT * FROM $this->coupons_tbl WHERE copn_id = $id");
        ?>

        <h1><?php echo isset($id) ? 'Edit Category' : 'Add New Category'; ?></h1>

        <div class="col-left">
            <form method="post" action="<?php echo admin_url('admin.php?page=' . $this->page . '&action=save'); ?>">
                <input type="hidden" name="copn_id" value="<?php echo $id; ?>">
                <div class="form-field">
                    <label for="coupon_code">Coupon Code <span>*</span></label>
                    <input name="copn_code" id="copn_code" type="text" value="<?php echo $row->copn_code; ?>" required>
                </div>
                <div class="form-field">
                    <label for="copn_discount">Discount <span>*</span></label><br>
                    <input type="text" name="copn_discount" id="copn_discount" value="<?php echo $row->copn_discount; ?>" class="small-text" required> %
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($id) ? 'Update Category' : 'Add New Category'; ?>"></p>
            </form>
        </div>

        <?php
    }

    // Save coupon
    public function save() {

        // Getting submitted data
        $id = filter_input(INPUT_POST, 'copn_id');
        $copn_code = filter_input(INPUT_POST, 'copn_code', FILTER_SANITIZE_STRING);
        $copn_discount = filter_input(INPUT_POST, 'copn_discount', FILTER_SANITIZE_STRING);

        if (!empty($id)) {

            $this->wpdb->update($this->coupons_tbl, array('copn_code' => $copn_code, 'copn_discount' => $copn_discount), array('copn_id' => $id));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=updated'));

            exit;
        } else {

            $this->wpdb->insert($this->coupons_tbl, array('copn_code' => $copn_code, 'copn_discount' => $copn_discount));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=added'));

            exit;
        }
    }

    // Delete coupon
    public function del() {

        // Getting coupon ID
        $id = filter_input(INPUT_GET, 'id');

        $this->wpdb->delete($this->coupons_tbl, array('copn_id' => $id));

        wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=deleted'));

        exit;
    }

}
