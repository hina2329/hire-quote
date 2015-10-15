<?php

// Shortcode Class
class shortcode extends HireQuote {

    public $step;
    public $postcode;
    public $cat_id;
    public $prod_id;
    public $prod_rate;
    public $d_date;
    public $c_date;
    public $prf_time;

    public function __construct() {
        parent::__construct();

        // Form data
        $this->step = filter_input(INPUT_POST, 'step');
        $this->postcode = filter_input(INPUT_POST, 'postcode');
        $this->cat_id = filter_input(INPUT_POST, 'cat_id');
        $this->prod_id = filter_input(INPUT_POST, 'prod_id');
        $this->prod_rate = filter_input(INPUT_POST, 'prod_rate');
        $this->d_date = filter_input(INPUT_POST, 'd_date');
        $this->c_date = filter_input(INPUT_POST, 'c_date');
        $this->prf_time = filter_input(INPUT_POST, 'prf_time');

        if (isset($this->step) && $this->step == 'display_categories') {
            $this->display_categories();
        } else if (isset($this->step) && $this->step == 'set_dates') {
            $this->set_dates();
        } else if (isset($this->step) && $this->step == 'display_products') {
            $this->display_products();
        } else if (isset($this->step) && $this->step == 'final_form') {
            $this->final_form();
        } else if (isset($this->step) && $this->step == 'submit_quote') {
            $this->submit_quote();
        } else {
            $this->postcode();
        }
    }

    // Post code checker
    public function postcode() {
        ?>
        <div id="hire-quote">
            <div class="postcode-wdgt">
                <h1><span>Compare and Book Online</span>
                    Today’s Best Deals From Leading<br> Suppliers</h1>
                <div class="postcode-fld">
                    <form method="post" action="<?php the_permalink(); ?>">
                        <input type="hidden" name="step" value="display_categories">
                        <input type="text" name="postcode" placeholder="Enter your postcode"><button>NEXT <i class="dashicons-before dashicons-controls-play"></i></button>
                    </form>
                </div>
                <?php
                $postcode_validation = filter_input(INPUT_GET, 'postcode');
                if (isset($postcode_validation)) {
                    ?>
                    <div class="msg">Sorry we do not service your area!</div>
                <?php } ?>
            </div>
        </div>
        <?php
    }

    // Display categories after post code validation
    public function display_categories() {

        $results = $this->wpdb->get_row("SELECT * FROM $this->postcodes_tbl WHERE pc_code = $this->postcode");
        $cats = $this->wpdb->get_results("SELECT * FROM $this->categories_tbl");
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        if ($results) {
            ?>
            <div class="hq-list-table">
                <h2>Select your Waste Type:</h2>
                <form method="post" action="<?php the_permalink(); ?>">
                    <input type="hidden" name="step" value="set_dates">
                    <input type="hidden" name="postcode" value="<?php echo $results->pc_code; ?>">

                    <table>
                        <thead>
                            <tr>
                                <th>Waste Type</th>
                                <th>Allowed</th>
                                <th>Not Allowed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cats as $cat) { ?>
                                <tr>
                                    <td>
                                        <label for="hq-cats-<?php echo $cat->cat_id; ?>">
                                            <input type="radio" name="cat_id" id="hq-cats-<?php echo $cat->cat_id; ?>" value="<?php echo $cat->cat_id; ?>" required> <?php echo $cat->cat_name; ?>
                                        </label>
                                    </td>
                                    <td><label for="hq-cats-<?php echo $cat->cat_id; ?>"><?php echo $cat->cat_allowed; ?></label></td>
                                    <td><label for="hq-cats-<?php echo $cat->cat_id; ?>"><?php echo $cat->cat_not_allowed; ?></label></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <div class="hq-options">
                        <p><strong>Additional Items</strong><br>
                            Are you disposing of any Mattresses or Tyres? If yes, please select the quantity from the boxes below:</p>
                        <?php foreach ($options as $option) { ?>
                            <p><label for="opt_<?php echo $option->opt_id; ?>"><?php echo $option->opt_name; ?>:</label> <input type="number" name="opt_<?php echo $option->opt_id; ?>" id="opt_<?php echo $option->opt_id; ?>" value="0"> X $<?php echo $option->opt_price; ?></p>
                        <?php } ?>
                    </div>
                    <button>NEXT <i class="dashicons-before dashicons-controls-play"></i></button>
                </form>
            </div>
            <?php
        } else {
            wp_redirect('?postcode=invalid');
        }
    }

    // Set dates 
    public function set_dates() {
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        ?>
        <div class="hq-dates">
            <h2>Select hire period:</h2>
            <form method="post" action="<?php the_permalink(); ?>">
                <input type="hidden" name="step" value="display_products">
                <input type="hidden" name="cat_id" value="<?php echo $this->cat_id; ?>">
                <input type="hidden" name="postcode" value="<?php echo $this->postcode; ?>">
                <?php
                foreach ($options as $option) {
                    $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                    $opt_cost = $opt_val * $option->opt_price;
                    ?>
                    <input type="hidden" name="opt_<?php echo $option->opt_id; ?>" value="<?php echo $option->opt_name . ': ' . $opt_val . ' - $' . $opt_cost; ?>">
                <?php } ?>
                <p><label>Delivery Date:</label> <input type="text" name="d_date" id="hq-d-date"></p>
                <p><label>Collection Date:</label> <input type="text" name="c_date" id="hq-c-date"></p>
                <p style="line-height: 48px;"><label>Preferred Time:</label>
                    <input type="radio" name="prf_time" value="AM" required> AM &nbsp;&nbsp;<input type="radio" name="prf_time" value="PM" required> PM
                </p>
                <button>NEXT <i class="dashicons-before dashicons-controls-play"></i></button>
            </form>
        </div>
        <?php
    }

    // Display products after category selection
    public function display_products() {
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        $products = $this->wpdb->get_results("SELECT * FROM $this->products_tbl WHERE prod_cat = $this->cat_id");
        ?>
        <div class="hq-list-table">
            <table>
                <tbody>
                    <?php foreach ($products as $product) { ?>
                        <tr>
                            <td>
                                <form method="post" action="<?php the_permalink(); ?>">
                                    <input type="hidden" name="step" value="final_form">
                                    <input type="hidden" name="postcode" value="<?php echo $this->postcode; ?>">
                                    <input type="hidden" name="cat_id" value="<?php echo $this->cat_id; ?>">
                                    <input type="hidden" name="d_date" value="<?php echo $this->d_date; ?>">
                                    <input type="hidden" name="c_date" value="<?php echo $this->c_date; ?>">
                                    <input type="hidden" name="prf_time" value="<?php echo $this->prf_time; ?>">
                                    <input type="hidden" name="prod_id" value="<?php echo $product->prod_id; ?>">
                                    <?php
                                    foreach ($options as $option) {
                                        $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                                        ?>
                                        <input type="hidden" name="opt_<?php echo $option->opt_id; ?>" value="<?php echo $opt_val; ?>">
                                    <?php } ?>
                                    <table>
                                        <tr>
                                            <td>
                                                <?php echo $product->prod_name; ?>
                                                <div class="prod_info">
                                                    <strong>Length:</strong> <?php echo $product->prod_length; ?><br>
                                                    <strong>Width:</strong> <?php echo $product->prod_width; ?><br>
                                                    <strong>Height:</strong> <?php echo $product->prod_height; ?>
                                                </div>
                                            </td>
                                            <td style="vertical-align: middle;"><img src="<?php echo $product->prod_img; ?>" width="150"></td>
                                            <td class="prod_desc">
                                                <?php echo $product->prod_desc; ?>
                                                <div class="prod_price">
                                                    $<?php echo $product->prod_rate; ?>
                                                    <button class="btn-book-now">Book Now</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </form>
                            </td>
                        </tr>

                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    // Final form
    public function final_form() {
        $product = $this->wpdb->get_row("SELECT * FROM $this->products_tbl WHERE prod_id = $this->prod_id");
        $cat = $this->wpdb->get_row("SELECT * FROM $this->categories_tbl WHERE cat_id = $this->cat_id");
        $postcode = $this->wpdb->get_row("SELECT * FROM $this->postcodes_tbl WHERE pc_code = $this->postcode");
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");

        // Number of days
        $date1 = date_create($this->d_date);
        $date2 = date_create($this->c_date);
        $diff = date_diff($date1, $date2);
        ?>
        <div class="hq-final">
            <h2>Order Details & Customer Details</h2>

            <div class="customer-detail-col">
                <form method="post" action="<?php the_permalink(); ?>">
                    <input type="hidden" name="step" value="submit_quote">
                    <input type="hidden" name="cust_postcode" value="<?php echo $this->postcode; ?>">
                    <input type="hidden" name="cat_id" value="<?php echo $this->cat_id; ?>">
                    <input type="hidden" name="d_date" value="<?php echo $this->d_date; ?>">
                    <input type="hidden" name="c_date" value="<?php echo $this->c_date; ?>">
                    <input type="hidden" name="prf_time" value="<?php echo $this->prf_time; ?>">
                    <input type="hidden" name="prod_id" value="<?php echo $product->prod_id; ?>">
                    <?php
                    foreach ($options as $option) {
                        $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                        ?>
                        <input type="hidden" name="opt_<?php echo $option->opt_id; ?>" value="<?php echo $opt_val; ?>">
                    <?php } ?>
                    <strong>Enter Your Delivery Details</strong>
                    <p>
                        <label>Your Name: *</label>
                        <input type="text" name="cust_name" required>
                    </p>
                    <p>
                        <label>Address: *</label>
                        <textarea name="cust_address" required></textarea>
                    </p>
                    <p>
                        <label>Post Code:</label>
                        <input type="text" name="cust_postcode" value="<?php echo $postcode->pc_code . ' - ' . $postcode->pc_suburb . ' - ' . $postcode->pc_state; ?>" readonly>
                    </p>
                    <p>
                        <label>Phone: *</label>
                        <input type="text" name="cust_phone" required>
                    </p>
                    <p>
                        <label>Email: *</label>
                        <input type="email" name="cust_email" required>
                    </p>
                    <p>
                        <button>GET A QUOTE</button>
                    </p>
                </form>
            </div>

            <div class="odr-detail-col">
                <strong>Order Description</strong>
                <ul>
                    <li>
                        <span class="term">Bin Size: <?php echo $product->prod_name; ?></span>
                        <span class="def">$<?php echo $product->prod_rate; ?></span>
                    </li>
                    <li>
                        <span class="term">No of Days: <?php echo $diff->format('%a'); ?></span>
                        <span class="def">$<?php echo number_format($diff->format('%a') * $this->setting->add_day); ?></span>
                    </li>
                    <li>
                        <span class="term">Waste Type</span>
                        <span class="def"><?php echo $cat->cat_name; ?></span>
                    </li>
                    <li><span class="term">Additional Items</span>
                        <span class="def">
                            <?php
                            foreach ($options as $option) {
                                $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                                ?>
                                <?php echo $opt_val; ?><br>
                            <?php } ?>
                        </span>
                    </li>
                    <li>
                        <span class="term">Delivery Date</span>
                        <span class="def"><?php echo $this->d_date; ?></span>
                    </li>
                    <li>
                        <span class="term">Collection Date</span>
                        <span class="def"><?php echo $this->c_date ?></span>
                    </li>
                    <li>
                        <span class="term">Preferred Time</span>
                        <span class="def"><?php echo $this->prf_time; ?></span>
                    </li>
                    <li>
                        <span class="term">Delivery Zone</span>
                        <span class="def"><?php echo $postcode->pc_code . '<br>' . $postcode->pc_suburb . '<br>' . $postcode->pc_state; ?></span>
                    </li>
                </ul>
            </div>
        </div>
        <?php
    }

    // Get Quote
    public function submit_quote() {
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        $cust_name = filter_input(INPUT_POST, 'cust_name');
        $cust_address = filter_input(INPUT_POST, 'cust_address', FILTER_SANITIZE_STRING);
        $cust_postcode = filter_input(INPUT_POST, 'cust_postcode');
        $cust_phone = filter_input(INPUT_POST, 'cust_phone', FILTER_SANITIZE_NUMBER_INT);
        $cust_email = filter_input(INPUT_POST, 'cust_email');
        $opt = '';

        foreach ($options as $option) {
            $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
            $opt .= $opt_val . ';';
        }

        $this->wpdb->insert($this->customers_tbl, array('cust_name' => $cust_name, 'cust_address' => $cust_address, 'cust_postcode' => $cust_postcode, 'cust_phone' => $cust_phone, 'cust_email' => $cust_email));

        $last_id = $this->wpdb->insert_id;

        $this->wpdb->insert($this->orders_tbl, array('odr_cust_id' => $last_id, 'odr_prod_id' => $this->prod_id, 'odr_cat_id' => $this->cat_id, 'odr_options' => $opt, 'odr_d_date' => $this->d_date, 'odr_c_date' => $this->c_date, 'odr_pfr_time' => $this->prf_time, 'odr_postcode' => $cust_postcode, 'odr_status' => 'Unapproved'));

        echo '<div class="order-ok">Thanks For Requesting A Quote!</div>';
    }

}
