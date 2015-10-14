<?php

// Settings Class
class settings extends HireQuote {

    public $setting;

    public function __construct() {
        parent::__construct();

        // Getting plugin settings
        $this->setting = (object) get_option('hq_settings');
    }

    // Iniating main method to display settings
    public function init() {
        ?>

        <h1><?php echo get_admin_page_title(); ?></h1>

        <?php $this->notify('Settnigs'); ?>

        <div class="col-left">
            <form method="post" action="options.php">
                <?php settings_fields('hq_settings'); ?>
                <div class="form-field">
                    <label for="add_day">Additional Day Price</label><br>
                    $ <input name="hq_settings[add_day]" id="add_day" type="text" value="<?php echo $this->setting->add_day; ?>" class="small-text">
                </div>
                <div class="form-field">
                    <label for="hq_email">Notify Email</label><br>
                    <input name="hq_settings[hq_email]" id="hq_email" type="text" value="<?php echo $this->setting->hq_email; ?>" >
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Settings"></p>
            </form>
        </div>
        <?php
    }

}
