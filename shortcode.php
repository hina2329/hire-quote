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
    public $add_days_price;
    public $diff;
    public $cust_name;
    public $cust_address;
    public $cust_postcode;
    public $cust_phone;
    public $cust_email;
    public $p_method;
    public $cust_city;

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
        $this->cust_name = filter_input(INPUT_POST, 'cust_name');
        $this->cust_address = filter_input(INPUT_POST, 'cust_address', FILTER_SANITIZE_STRING);
        $this->cust_postcode = filter_input(INPUT_POST, 'cust_postcode');
        $this->cust_phone = filter_input(INPUT_POST, 'cust_phone', FILTER_SANITIZE_NUMBER_INT);
        $this->cust_email = filter_input(INPUT_POST, 'cust_email');
        $this->p_method = filter_input(INPUT_POST, 'p_method');
        $this->cust_city = filter_input(INPUT_POST, 'cust_city');

        // Number of days
        $date1 = date_create($this->d_date);
        $date2 = date_create($this->c_date);
        $this->diff = date_diff($date1, $date2);
        $this->add_days_price = ($this->diff->format('%a') - 1) * $this->setting->add_day;

        if (isset($this->step) && $this->step == 'display_categories') {
            $this->display_categories();
        } else if (isset($this->step) && $this->step == 'set_dates') {
            $this->set_dates();
        } else if (isset($this->step) && $this->step == 'display_products') {
            $this->display_products();
        } else if (isset($this->step) && $this->step == 'customer_form') {
            $this->customer_form();
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
            <h1>Enter your address</h1>
            <div class="hq-wrap">
                <div class="postcode-wdgt">
                    <?php
                    $postcode_validation = filter_input(INPUT_GET, 'postcode');
                    if (isset($postcode_validation)) {
                        ?>
                        <div class="msg">Sorry we do not service your area!</div>
                    <?php } ?>
                    <form method="post" action="<?php the_permalink(); ?>">
                        <input type="hidden" name="step" value="display_categories">
                        <input type="text" name="postcode" placeholder="Enter your postcode"><button>NEXT <i class="dashicons-before dashicons-controls-play"></i></button>
                    </form>
                </div>
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
            <div id="hire-quote">
                <h1>Select your Waste Type:</h1>
                <div class="hq-wrap">
                    <div class="hq-list-table">
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
                                    <p><label for="opt_<?php echo $option->opt_id; ?>"><?php echo $option->opt_name; ?>:</label> <input type="number" name="opt_<?php echo $option->opt_id; ?>" id="opt_<?php echo $option->opt_id; ?>" value="0"></p>
                                <?php } ?>
                            </div>
                            <button>NEXT <i class="dashicons-before dashicons-controls-play"></i></button>
                        </form>
                    </div>
                </div>
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
        <div id="hire-quote">
            <h1>Select hire period:</h1>
            <div class="hq-wrap">
                <form method="post" action="<?php the_permalink(); ?>">
                    <input type="hidden" name="step" value="display_products">
                    <input type="hidden" name="cat_id" value="<?php echo $this->cat_id; ?>">
                    <input type="hidden" name="postcode" value="<?php echo $this->postcode; ?>">
                    <?php
                    foreach ($options as $option) {
                        $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                        ?>
                        <input type="hidden" name="opt_<?php echo $option->opt_id; ?>" value="<?php echo $opt_val; ?>">
                    <?php } ?>
                    <p><label>Delivery Date:</label> <input type="text" name="d_date" id="hq-d-date"></p>
                    <p><label>Collection Date:</label> <input type="text" name="c_date" id="hq-c-date"></p>
                    <p style="line-height: 48px;"><label>Preferred Time:</label>
                        <input type="radio" name="prf_time" value="AM" required> AM &nbsp;&nbsp;<input type="radio" name="prf_time" value="PM" required> PM
                    </p>
                    <button>NEXT <i class="dashicons-before dashicons-controls-play"></i></button>
                </form>
            </div>
        </div>
        <?php
    }

    // Display products after category selection
    public function display_products() {
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        $products = $this->wpdb->get_results("SELECT * FROM $this->products_tbl WHERE prod_cat = $this->cat_id");
        ?>
        <div id="hire-quote">
            <h1>Select a bin:</h1>
            <div class="hq-wrap">
                <div class="hq-list-table">
                    <table>
                        <tbody>
                            <?php foreach ($products as $product) { ?>
                                <tr>
                                    <td>
                                        <form method="post" action="<?php the_permalink(); ?>">
                                            <input type="hidden" name="step" value="customer_form">
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
            </div>
        </div>
        <?php
    }

    // Cutomer form
    public function customer_form() {
        $product = $this->wpdb->get_row("SELECT * FROM $this->products_tbl WHERE prod_id = $this->prod_id");
        $postcode = $this->wpdb->get_row("SELECT * FROM $this->postcodes_tbl WHERE pc_code = $this->postcode");
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        ?>
        <div id="hire-quote">
            <h1>Customer Details:</h1>
            <div class="hq-wrap">
                <div class="hq-final">
                    <div class="customer-detail-col">
                        <form method="post" action="<?php the_permalink(); ?>">
                            <input type="hidden" name="step" value="final_form">
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
                            <p>
                                <label>Your Name: *</label>
                                <input type="text" name="cust_name" required>
                            </p>
                            <p>
                                <label>Address: *</label>
                                <textarea name="cust_address" required></textarea>
                            </p>
                            <p>
                                <label>City/Suburb:</label>
                                <input type="text" name="cust_city" required>
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
                                <button>Next</button>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    // Final form
    public function final_form() {
        $product = $this->wpdb->get_row("SELECT * FROM $this->products_tbl WHERE prod_id = $this->prod_id");
        $cat = $this->wpdb->get_row("SELECT * FROM $this->categories_tbl WHERE cat_id = $this->cat_id");
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        $lastOrder = $this->wpdb->get_row("SELECT * FROM $this->orders_tbl ORDER BY odr_id DESC LIMIT 1");
        ?>
        <div id="hire-quote">
            <h1>Order Details:</h1>
            <div class="hq-wrap">
                <div class="hq-final">
                    <form method="post" action="<?php the_permalink(); ?>">
                        <input type="hidden" name="step" value="submit_quote">
                        <input type="hidden" name="cust_postcode" value="<?php echo $this->postcode; ?>">
                        <input type="hidden" name="cat_id" value="<?php echo $this->cat_id; ?>">
                        <input type="hidden" name="d_date" value="<?php echo $this->d_date; ?>">
                        <input type="hidden" name="c_date" value="<?php echo $this->c_date; ?>">
                        <input type="hidden" name="prf_time" value="<?php echo $this->prf_time; ?>">
                        <input type="hidden" name="prod_id" value="<?php echo $product->prod_id; ?>">
                        <input type="hidden" name="cust_name" value="<?php echo $this->cust_name; ?>">
                        <input type="hidden" name="cust_email" value="<?php echo $this->cust_email; ?>">
                        <input type="hidden" name="cust_address" value="<?php echo $this->cust_address; ?>">
                        <input type="hidden" name="cust_phone" value="<?php echo $this->cust_phone; ?>">
                        <input type="hidden" name="cust_postcode" value="<?php echo $this->cust_postcode; ?>">
                        <input type="hidden" name="cust_city" value="<?php echo $this->cust_city; ?>">
                        <?php
                        foreach ($options as $option) {
                            $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                            ?>
                            <input type="hidden" name="opt_<?php echo $option->opt_id; ?>" value="<?php echo $opt_val; ?>">
                        <?php } ?>
                            <table cellpadding="0" cellspacing="0" style="border: none; width: 100%">
                            <tr>
                                <td width="40%" style="border: none;">
                                    <img src="<?php echo plugins_url('hire-quote/images/Rentobin-Logo.png'); ?>" alt="" width="220">
                                </td>
                                <td colspan="2" style="border: none; text-align: right; vertical-align: top;">
                                    Ph: 9721 3576<br>
                                    Unit 1 79-91 Betts Rd, Smithfield NSW 2164<br>
                                    <strong>Invoice No.</strong> <?php echo ($lastOrder->odr_id + 1); ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <strong>Invoice Date:</strong> <?php echo date('d-m-Y'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                    <br>
                                    <strong>Customer Details:</strong><br>
                                    <?php echo $this->cust_name; ?><br>
                                    <?php echo $this->cust_address; ?><br>
                                    <?php echo $this->cust_city; ?><br>
                                    <?php echo $this->cust_postcode; ?><br>
                                    <br>
                                    <?php echo $this->cust_phone; ?><br>
                                    <br>
                                    <?php echo $this->cust_email; ?>
                                </td>
                                <td style="border: none;"></td>
                                <td style="border: none;">
                                    <br>
                                    <strong>Hire Date From:</strong><br>
                                    <?php echo $this->d_date; ?><br>
                                    <br>
                                    <strong>Hire Date To:</strong><br>
                                    <?php echo $this->c_date; ?>
                                </td>
                            </tr>
                        </table>

                        <hr>

                        <div class="odr-detail-col">
                            <strong>Order Description</strong>
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td><strong>Bin Size:</strong></td>
                                    <td><?php echo $product->prod_name; ?></td>
                                    <td class="hq-price">$<?php echo $product->prod_rate; ?></td>
                                </tr>
                                <tr>
                                    <td><strong>No. of Days:</strong></td>
                                    <td><?php echo $this->diff->format('%a') - 1; ?></td>
                                    <td class="hq-price">$<?php echo number_format($this->add_days_price, 2); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Add Ons:</strong></td>
                                    <td>
                                        <?php
                                        foreach ($options as $option) {
                                            $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                                            echo $option->opt_name . ': ' . $opt_val . '<br>';
                                        }
                                        ?>
                                    </td>
                                    <td class="hq-price">
                                        <?php
                                        $opt_t_cost = '';
                                        foreach ($options as $option) {
                                            $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
                                            echo '$' . $opt_val * $option->opt_price . '<br>';
                                            $opt_t_cost += $opt_val * $option->opt_price;
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr class="hq-total">
                                    <td></td>
                                    <td>
                                        TOTAL<br>
                                        GST: (10%)<br>
                                        Total Payable
                                    </td>
                                    <td class="hq-price">
                                        <?php
                                        $total = $opt_t_cost + $product->prod_rate + $this->add_days_price;
                                        $gst = ($total * 10) / 100;
                                        ?>
                                        $<?php echo number_format($total, 2); ?><br>
                                        $<?php echo number_format($gst, 2); ?><br>
                                        $<?php echo number_format(($total + $gst), 2); ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <h6>Special Instructions:</h6> 
                        <p>NOTE: You have selected ‘<?php echo $cat->cat_name; ?>’ as your waste type. Please note that only following <?php echo $cat->cat_name; ?> is allowed. You may be liable to pay additional charges if the waste is found to be different from your selection.</p>
                        <p><strong>Allowed:</strong> <?php echo $cat->cat_allowed; ?></p>
                        <p><strong>Not Allowed:</strong> <?php echo $cat->cat_not_allowed; ?></p>
                        <p>Please see our ‘Terms and Conditions’ of hire.</p>

                        <h6>Payment Method:</h6> 
                        <p>
                            <label><input type="radio" name="p_method" value="PayPal" checked> PayPal</label><br>
                            <label><input type="radio" name="p_method" value="Bank Transfer (EFT)"> Bank Transfer (EFT)</label>
                        </p>

                        <p>
                            <button>Pay Now</button>
                        </p>

                    </form>

                </div>
            </div>
        </div>
        <?php
    }

    // Get Quote
    public function submit_quote() {
        $product = $this->wpdb->get_row("SELECT * FROM $this->products_tbl WHERE prod_id = $this->prod_id");
        $options = $this->wpdb->get_results("SELECT * FROM $this->options_tbl");
        $cat = $this->wpdb->get_row("SELECT * FROM $this->categories_tbl WHERE cat_id = $this->cat_id");
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

        // Order details
        $odr_detail = '<table cellpadding="0" cellspacing="0" style="border: none; width: 100%">
                            <tr>
                                <td width="40%" style="border: none;">
                                    <img src="' . plugins_url('hire-quote/images/Rentobin-Logo.png') . '" alt="" width="220">
                                </td>
                                <td colspan="2" style="border: none; text-align: right; vertical-align: top;">
                                    Ph: 9721 3576<br>
                                    Unit 1 79-91 Betts Rd, Smithfield NSW 2164<br>
                                    <strong>Invoice No.</strong> ' . ($lastOrder->odr_id + 1) . '
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                    <strong>Invoice Date:</strong> ' . date('d-m-Y') . '
                                </td>
                            </tr>
                            <tr>
                                <td style="border: none;">
                                <br>
                                    <strong>Customer Details:</strong><br>
                                    ' . $this->cust_name . '<br>
                                    ' . $this->cust_address . '<br>
                                    ' . $this->cust_city . '<br>
                                    ' . $this->cust_postcode . '<br>
                                    <br>
                                    ' . $this->cust_phone . '<br>
                                    <br>
                                    ' . $this->cust_email . '
                                </td>
                                <td style="border: none;"></td>
                                <td style="border: none;">
                                    <br>
                                    <strong>Hire Date From:</strong><br>
                                    ' . $this->d_date . '<br>
                                    <br>
                                    <strong>Hire Date To:</strong><br>
                                    ' . $this->c_date . '
                                </td>
                            </tr>
                        </table>';
        
                        $odr_detail .= '<hr>
                        <div class="odr-detail-col">
                            <strong>Order Description</strong>
                            <table cellpadding="0" cellspacing="0" width="100%">
                                <tr>
                                    <td><strong>Bin Size:</strong></td>
                                    <td>' . $product->prod_name . '</td>
                                    <td class="hq-price" style="text-align: right;">$' . $product->prod_rate . '</td>
                                </tr>
                                <tr>
                                    <td><strong>No. of Days:</strong></td>
                                    <td>' . ($this->diff->format('%a') - 1) . '</td>
                                    <td class="hq-price" style="text-align: right;">$' . number_format($this->add_days_price, 2) . '</td>
                                </tr>
                                <tr>
                                    <td><strong>Add Ons:</strong></td>
                                    <td>';

        foreach ($options as $option) {
            $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
            $odr_detail .= $option->opt_name . ': ' . $opt_val . '<br>';
        }

        $odr_detail .= '</td>
                                    <td class="hq-price" style="text-align: right;">';

        $opt_t_cost = '';
        foreach ($options as $option) {
            $opt_val = filter_input(INPUT_POST, 'opt_' . $option->opt_id);
            $odr_detail .= '$' . $opt_val * $option->opt_price . '<br>';
            $opt_t_cost += $opt_val * $option->opt_price;
        }

        $odr_detail .= '</td>
                                </tr>
                                <tr style="background: #000; font-weight: bold;">
                                    <td></td>
                                    <td style="color: #fff;">
                                        TOTAL<br>
                                        GST: (10%)<br>
                                        Total Payable
                                    </td>
                                    <td class="hq-price" style="text-align: right; color: #fff;">';

        $total = $opt_t_cost + $product->prod_rate + $this->add_days_price;
        $gst = ($total * 10) / 100;

        $odr_detail .= '$' . number_format($total, 2) . '<br>
                                        $' . number_format($gst, 2) . '<br>
                                        $' . number_format(($total + $gst), 2) . '
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <h6 style="font-size: 20px;">Special Instructions:</h6> 
                        <p>NOTE: You have selected &acute;' . $cat->cat_name . '&acute; as your waste type. Please note that only following &acute;' . $cat->cat_name . '&acute; is allowed. You may be liable to pay additional charges if the waste is found to be different from your selection.</p>
                        <p><strong>Allowed:</strong> ' . $cat->cat_allowed . '</p>
                        <p><strong>Not Allowed:</strong> ' . $cat->cat_not_allowed . '</p>
                        <p>Please see our ‘Terms and Conditions’ of hire.</p>

                        <h6 style="font-size: 20px;">Payment Method: ' . $this->p_method . '</h6>';



        $this->wpdb->insert($this->customers_tbl, array('cust_name' => $cust_name, 'cust_address' => $cust_address, 'cust_postcode' => $cust_postcode, 'cust_phone' => $cust_phone, 'cust_email' => $cust_email, 'cust_suburb' => $this->cust_city));

        $last_id = $this->wpdb->insert_id;

        $this->wpdb->insert($this->orders_tbl, array('odr_cust_id' => $last_id, 'odr_prod_id' => $this->prod_id, 'odr_cat_id' => $this->cat_id, 'odr_options' => $opt, 'odr_d_date' => $this->d_date, 'odr_c_date' => $this->c_date, 'odr_pfr_time' => $this->prf_time, 'odr_postcode' => $cust_postcode, 'odr_status' => 'Unapproved', 'odr_full' => $odr_detail));

        // User header
        $user_headers = "MIME-Version: 1.0" . "\r\n";
        $user_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $user_headers .= 'From: <' . $cust_email . '>' . "\r\n";

        // Admin header
        $admin_headers = "MIME-Version: 1.0" . "\r\n";
        $admin_headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $admin_headers .= 'From: <' . $this->setting->hq_email . '>' . "\r\n";

        // Mail
        wp_mail($this->setting->hq_email, 'New order has been placed!', $odr_detail, $user_headers);
        wp_mail($cust_email, 'Your order details at rentobin.com.au!', $odr_detail, $admin_headers);


        echo '<div class="order-ok">Thanks For Requesting A Quote!</div>';

        if ($this->p_method == 'PayPal') {
            wp_redirect('https://www.paypal.com/au/webapps/mpp/home');
        }
    }

}
