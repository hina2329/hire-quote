<?php

// Categories Class
class manage_categories extends HireQuote {

    public function __construct() {
        parent::__construct();
    }

    // Iniating main method to display categories
    public function init() {
        ?>

        <h1><?php echo get_admin_page_title(); ?> <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form'); ?>" class="page-title-action">Add New Category</a></h1>

        <?php $this->notify('Category'); ?>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="20%">Category Name</th>
                    <th width="30%">Allowed</th>
                    <th width="40%">Not Allowed</th>
                    <th width="10%" class="actions">Actions</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting categories
                $results = $this->wpdb->get_results("SELECT * FROM $this->categories_tbl");

                if ($results) {

                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td class="column-title">
                                <strong><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->cat_id); ?>"><?php echo $row->cat_name; ?></a></strong>
                            </td>
                            <td><?php echo $row->cat_allowed; ?></td>
                            <td><?php echo $row->cat_not_allowed; ?></td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->cat_id); ?>" class="dashicons-before dashicons-edit" title="Edit"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=del&id=' . $row->cat_id); ?>" class="dashicons-before dashicons-trash" title="Delete" onclick="return confirm('Are you sure you want to delete this category, doing so will delete all the products belongs to this category as well?');"></a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="4" style="text-align: center;"><strong>No Records Found</strong></td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>

        </table>

        <p><em><strong>Note:</strong> If you delete any category it will delete all the products belongs to that category as well.</em></p>

        <?php
    }

    // Add new or edit category form
    public function form() {

        // Getting category data if user requests to edit
        $id = filter_input(INPUT_GET, 'id');
        $row = $this->wpdb->get_row("SELECT * FROM $this->categories_tbl WHERE cat_id = $id");
        ?>

        <h1><?php echo isset($id) ? 'Edit Category' : 'Add New Category'; ?></h1>

        <div class="col-left">
            <form method="post" action="<?php echo admin_url('admin.php?page=' . $this->page . '&action=save'); ?>">
                <input type="hidden" name="cat_id" value="<?php echo $id; ?>">
                <div class="form-field">
                    <label for="cat_name">Category Name <span>*</span></label>
                    <input name="cat_name" id="cat_name" type="text" value="<?php echo $row->cat_name; ?>" required>
                </div>
                <div class="form-field">
                    <label for="cat_allowed">Allowed</label>
                    <textarea name="cat_allowed" id="cat_allowed" rows="5" cols="40"><?php echo $row->cat_allowed; ?></textarea>
                </div>
                <div class="form-field">
                    <label for="cat_not_allowed">Not Allowed</label>
                    <textarea name="cat_not_allowed" id="cat_not_allowed" rows="5" cols="40"><?php echo $row->cat_not_allowed; ?></textarea>
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($id) ? 'Update Category' : 'Add New Category'; ?>"></p>
            </form>
        </div>

        <?php
    }

    // Save category
    public function save() {

        // Getting submitted data
        $id = filter_input(INPUT_POST, 'cat_id');
        $cat_name = filter_input(INPUT_POST, 'cat_name', FILTER_SANITIZE_STRING);
        $cat_allowed = filter_input(INPUT_POST, 'cat_allowed', FILTER_SANITIZE_STRING);
        $cat_not_allowed = filter_input(INPUT_POST, 'cat_not_allowed', FILTER_SANITIZE_STRING);

        if (!empty($id)) {

            $this->wpdb->update($this->categories_tbl, array('cat_name' => $cat_name, 'cat_allowed' => $cat_allowed, 'cat_not_allowed' => $cat_not_allowed), array('cat_id' => $id));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=updated'));

            exit;
        } else {

            $this->wpdb->insert($this->categories_tbl, array('cat_name' => $cat_name, 'cat_allowed' => $cat_allowed, 'cat_not_allowed' => $cat_not_allowed));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=added'));

            exit;
        }
    }

    // Delete category
    public function del() {

        // Getting category ID
        $id = filter_input(INPUT_GET, 'id');

        $this->wpdb->delete($this->categories_tbl, array('cat_id' => $id));

        wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=deleted'));

        exit;
    }

}
