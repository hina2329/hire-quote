<?php

// Options Class
class manage_options extends HireQuote {

    // Iniating main method to display options
    public function init() {
        ?>
        <h1><?php echo get_admin_page_title(); ?> <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form'); ?>" class="page-title-action">Add New Options</a></h1>
        <?php $this->notify('Option'); ?>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="45%">Options</th>
                    <th width="45%">Options Price</th>
                    <th width="10%" class="actions">Actions</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting options
                $results = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");

                if ($results) {

                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td class="column-title">
                                <strong><a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->opt_id); ?>"><?php echo $row->opt_name; ?></a></strong>
                            </td>
                            <td>$<?php echo $row->opt_price; ?></td>
                            <td class="actions">
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=form&id=' . $row->opt_id); ?>" class="dashicons-before dashicons-edit" title="Edit"></a> 
                                <a href="<?php echo admin_url('admin.php?page=' . $this->page . '&action=del&id=' . $row->opt_id); ?>" class="dashicons-before dashicons-trash" title="Delete" onclick="return confirm('Are you sure you want to delete this?');"></a>
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

    // Add new or edit options form
    public function form() {

        // Getting options data if user requests to edit
        $id = filter_input(INPUT_GET, 'id');
        $row = $this->wpdb->get_row("SELECT * FROM $this->options_tbl WHERE opt_id = $id");
        ?>

        <h1><?php echo isset($id) ? 'Edit Option' : 'Add New Option'; ?></h1>

        <div class="col-left">
            <form method="post" action="<?php echo admin_url('admin.php?page=' . $this->page . '&action=save'); ?>">
                <input type="hidden" name="opt_id" value="<?php echo $id; ?>">
                <div class="form-field">
                    <label for="opt_name">Option Name <span>*</span></label>
                    <input name="opt_name" id="opt_name" type="text" value="<?php echo $row->opt_name; ?>" required>
                </div>
                <div class="form-field">
                    <label for="opt_price">Option Price <span>*</span></label>
                    <input name="opt_price" id="opt_price" type="text" value="<?php echo $row->opt_price; ?>" required>
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo isset($id) ? 'Edit Option' : 'Add New Option'; ?>"></p>
            </form>
        </div>

        <?php
    }

    // Save option
    public function save() {

        // Getting submitted data
        $id = filter_input(INPUT_POST, 'opt_id');
        $opt_name = filter_input(INPUT_POST, 'opt_name', FILTER_SANITIZE_STRING);
		$opt_price = filter_input(INPUT_POST, 'opt_price', FILTER_SANITIZE_STRING);

        if (!empty($id)) {

            $this->wpdb->update($this->options_tbl, array('opt_name' => $opt_name, 'opt_price' => $opt_price), array('opt_id' => $id));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=updated'));

            exit;
        } else {

            $this->wpdb->insert($this->options_tbl, array('opt_name' => $opt_name, 'opt_price' => $opt_price));

            wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=added'));

            exit;
        }
    }

    // Delete option
    public function del() {

        // Getting option ID
        $id = filter_input(INPUT_GET, 'id');

        $this->wpdb->delete($this->options_tbl, array('opt_id' => $id));

        wp_redirect(admin_url('admin.php?page=' . $this->page . '&update=deleted'));

        exit;
    }

}
