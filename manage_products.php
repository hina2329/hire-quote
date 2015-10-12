<?php

// Products Class
class manage_products extends HireQuote {

    public function __construct() {
        parent::__construct();
    }

    // Iniating main method to display products
    public function init() {
        ?>

        <h1><?php echo get_admin_page_title(); ?> <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form'); ?>" class="page-title-action">Add New Product</a></h1>

        <?php $this->notify('Product'); ?>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="10%">Thumbnail</th>
                    <th width="25%">Name</th>
                    <th width="15%">Category</th>
                    <th width="10%">Rate</th>
                    <th width="10%">Length</th>
                    <th width="10%">Width</th>
                    <th width="10%">Height</th>
                    <th width="10%" class="actions">Actions</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting products & categories
                $results = $this->wpdb->get_results("SELECT prod.*, cat.* FROM $this->products_tbl AS prod INNER JOIN $this->categories_tbl AS cat ON prod.prod_cat = cat.cat_id");

                if ($results) {

                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td><img src="<?php echo $row->prod_img; ?>" width="100"></td>
                            <td class="column-title">
                                <strong><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->prod_id); ?>"><?php echo $row->prod_name; ?></a></strong>
                            </td>
                            <td><?php echo $row->cat_name; ?></td>
                            <td>$<?php echo $row->prod_rate; ?></td>
                            <td><?php echo $row->prod_length; ?></td>
                            <td><?php echo $row->prod_width; ?></td>
                            <td><?php echo $row->prod_height; ?></td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->prod_id); ?>" class="dashicons-before dashicons-edit" title="Edit"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->prod_id . '&clone=true'); ?>" class="dashicons-before dashicons-admin-page" title="Clone"></a>
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=del&id=' . $row->prod_id); ?>" class="dashicons-before dashicons-trash" title="Delete" onclick="return confirm('Are you sure you want to delete this?');"></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="8" style="text-align: center;"><strong>No Records Found</strong></td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>

        </table>

        <?php
    }

    // Add new or edit product form
    public function form() {

        // Getting product data if user requests to edit
        $id = filter_input(INPUT_GET, 'id');
        $clone = filter_input(INPUT_GET, 'clone');
        $row = $this->wpdb->get_row("SELECT prod.*, cat.* FROM $this->products_tbl AS prod INNER JOIN $this->categories_tbl AS cat ON prod.prod_cat = cat.cat_id WHERE prod.prod_id = $id");
        ?>
        <div class="col-left">
            <h1><?php echo isset($id) && !isset($clone) ? 'Edit Product' : 'Add New Product'; ?></h1>
            <form method="post" action="<?php echo admin_url('admin.php?page=' . $this->page . '&action=save'); ?>">
                <input type="hidden" name="prod_id" value="<?php echo!isset($clone) ? $id : ''; ?>">
                <div class="form-field">
                    <label for="prod_name">Product Name <span>*</span></label>
                    <input name="prod_name" id="prod_name" type="text" value="<?php echo $row->prod_name; ?>" required>
                </div>
                <div class="form-field">
                    <label for="prod_desc">Description <span>*</span></label>
                    <textarea name="prod_desc" id="prod_desc" rows="5" cols="40" required><?php echo $row->prod_desc; ?></textarea>
                </div>
                <div class="form-field">
                    <label for="prod_cat">Category <span>*</span></label><br>
                    <select name="prod_cat" id="prod_cat" required>
                        <option value="">Please select...</option>
                        <?php
                        // Getting categories list
                        $cats = $this->wpdb->get_results("SELECT * FROM $this->categories_tbl");

                        // Listing all categories
                        foreach ($cats as $cat) {
                            echo '<option value="' . $cat->cat_id . '" ';
                            if (!isset($clone)) {
                                selected($cat->cat_id, $row->cat_id);
                            }
                            echo '>' . $cat->cat_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-field">
                    <label for="prod_rate">Rate <span>*</span></label><br>
                    $ <input name="prod_rate" id="prod_rate" type="text" value="<?php echo $row->prod_rate; ?>" class="small-text" required>
                </div>
                <div class="form-field">
                    <label for="prod_img">Image <span>*</span></label><br>
                    <input name="prod_img" id="prod_img" type="text" size="20"  value="<?php echo $row->prod_img; ?>" required>
                    <input id="upload_image_button" type="button" value="Upload Image">
                </div>
                <div class="form-field">
                    <label for="prod_length">Length <span>*</span></label>
                    <input name="prod_length" id="prod_length" type="text" value="<?php echo $row->prod_length; ?>" required>
                </div>
                <div class="form-field">
                    <label for="prod_width">Width <span>*</span></label>
                    <input name="prod_width" id="prod_width" type="text" value="<?php echo $row->prod_width; ?>" required>
                </div>
                <div class="form-field">
                    <label for="prod_height">Height <span>*</span></label>
                    <input name="prod_height" id="prod_height" type="text" value="<?php echo $row->prod_height; ?>" required>
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($id) && !isset($clone) ? 'Update Product' : 'Add New Product'; ?>"></p>
            </form>
        </div>

        <?php
    }

    // Save product
    public function save() {

        // Getting submitted data
        $id = filter_input(INPUT_POST, 'prod_id');
        $prod_name = filter_input(INPUT_POST, 'prod_name', FILTER_SANITIZE_STRING);
        $prod_desc = filter_input(INPUT_POST, 'prod_desc', FILTER_SANITIZE_STRING);
        $prod_cat = filter_input(INPUT_POST, 'prod_cat');
        $prod_rate = filter_input(INPUT_POST, 'prod_rate', FILTER_SANITIZE_STRING);
        $prod_img = filter_input(INPUT_POST, 'prod_img', FILTER_SANITIZE_STRING);
        $prod_length = filter_input(INPUT_POST, 'prod_length', FILTER_SANITIZE_STRING);
        $prod_width = filter_input(INPUT_POST, 'prod_width', FILTER_SANITIZE_STRING);
        $prod_height = filter_input(INPUT_POST, 'prod_height', FILTER_SANITIZE_STRING);

        if (!empty($id)) {

            $this->wpdb->update($this->products_tbl, array('prod_name' => $prod_name, 'prod_desc' => $prod_desc, 'prod_cat' => $prod_cat, 'prod_rate' => $prod_rate, 'prod_img' => $prod_img, 'prod_length' => $prod_length, 'prod_width' => $prod_width, 'prod_height' => $prod_height), array('prod_id' => $id));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=updated'));

            exit;
        } else {

            $this->wpdb->insert($this->products_tbl, array('prod_name' => $prod_name, 'prod_desc' => $prod_desc, 'prod_cat' => $prod_cat, 'prod_rate' => $prod_rate, 'prod_img' => $prod_img, 'prod_length' => $prod_length, 'prod_width' => $prod_width, 'prod_height' => $prod_height));
            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=added'));

            exit;
        }
    }

    // Delete product
    public function del() {

        // Getting category ID
        $id = filter_input(INPUT_GET, 'id');

        $this->wpdb->delete($this->products_tbl, array('prod_id' => $id));

        wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=deleted'));

        exit;
    }

}
