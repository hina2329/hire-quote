<?php

class customers_list extends HireQuote {

    public function __construct() {
        parent::__construct();
    }

    // Iniating main method to display customers
    public function init() {
        ?>

        <h1><?php echo get_admin_page_title(); ?></h1>

        <table class="wp-list-table widefat fixed striped pages">
            <thead>
                <tr>
                    <th width="25%">Customer Name</th>
                    <th width="25%">Address</th>
                    <th width="10%">Suburb</th>
                    <th width="10%">Postcode</th>
                    <th width="15%">Phone</th>
                    <th width="15%" class="actions">Email</th>
                </tr>
            </thead>

            <tbody id="the-list">

                <?php
                // Getting customers
                $results = $this->wpdb->get_results("SELECT * FROM $this->customers_tbl GROUP BY (cust_email)");

                if ($results) {

                    foreach ($results as $row) {
                        ?>
                        <tr>
                            <td class="column-title">
                                <strong><?php echo $row->cust_name; ?></strong>
                            </td>
                            <td><?php echo $row->cust_address; ?></td>
                            <td><?php echo $row->cust_suburb; ?></td>
                            <td><?php echo $row->cust_postcode; ?></td>
                            <td><?php echo $row->cust_phone; ?></td>
                            <td class="actions"><?php echo $row->cust_email; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6" style="text-align: center;"><strong>No Records Found</strong></td>
                    </tr>
                    <?php
                }
                ?>

            </tbody>

        </table>

        <?php
    }

}
