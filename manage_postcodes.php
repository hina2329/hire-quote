<?php

// Post Codes Class
class manage_postcodes extends HireQuote {

    public function __construct() {
        parent::__construct();
    }

    // Iniating main method to display postcodes
    public function init() {
        ?>

        <h1><?php echo get_admin_page_title(); ?> <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form'); ?>" class="page-title-action">Add New Post Code</a></h1>

        <?php $this->notify('Post Code'); ?>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="40%">Post Codes</th>
                    <th width="40%">Post Codes Names</th>
                    <th width="10%" class="actions">Actions</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting postcodes
                $results = $this->wpdb->get_results("SELECT * FROM $this->postcodes_tbl");

                if ($results) {

                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td class="column-title">
                                <strong><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->cat_id); ?>"><?php echo $row->pc_code; ?></a></strong>
                            </td>
                            <td><?php echo $row->pc_name; ?></td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->pc_id); ?>" class="dashicons-before dashicons-edit" title="Edit"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=del&id=' . $row->pc_id); ?>" class="dashicons-before dashicons-trash" title="Delete" onclick="return confirm('Are you sure you want to delete this?');"></a>
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

    // Add new or edit post code form
    public function form() {

        // Getting category data if user requests to edit
        $id = filter_input(INPUT_GET, 'id');
        $row = $this->wpdb->get_row("SELECT * FROM $this->postcodes_tbl WHERE pc_id = $id");
        ?>

        <h1><?php echo isset($id) ? 'Edit Post Code' : 'Add Post Code'; ?></h1>

        <div class="col-left">
            <form method="post" action="<?php echo admin_url('admin.php?page=' . $this->page . '&action=save'); ?>">
                <input type="hidden" name="pc_id" value="<?php echo $id; ?>">
                <div class="form-field">
                    <label for="post_code">Post Code <span>*</span></label>
                    <input name="post_code" id="post_code" type="text" value="<?php echo $row->pc_code; ?>" required>
                </div>
                <div class="form-field">
                    <label for="post_code_name">Post Code Name <span>*</span></label>
                    <textarea name="post_code_name" id="post_code_name" rows="5" cols="40" required><?php echo $row->pc_name; ?></textarea>
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($id) ? 'Update Post Code' : 'Add New Post Code'; ?>"></p>
            </form>
        </div>

        <?php
    }

    // Update or edit postcode
    public function save() {

        // Getting submitted data
        $id = filter_input(INPUT_POST, 'pc_id');
        $post_code = filter_input(INPUT_POST, 'post_code');
        $post_code_name = filter_input(INPUT_POST, 'post_code_name');

        if (!empty($id)) {

            $this->wpdb->update($this->postcodes_tbl, array('pc_code' => $post_code, 'pc_name' => $post_code_name), array('pc_id' => $id));
            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=updated'));

            exit;
        } else {

            $this->wpdb->insert($this->postcodes_tbl, array('pc_code' => $post_code, 'pc_name' => $post_code_name));
            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=added'));
            exit;
        }
    }

    // Delete postcode
    public function del() {

        // Getting category ID
        $id = filter_input(INPUT_GET, 'id');

        $this->wpdb->delete($this->postcodes_tbl, array('pc_id' => $id));

        wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=deleted'));
        exit;
    }

}
