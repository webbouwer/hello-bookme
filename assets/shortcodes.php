<?php

function hello_bookme_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'ics_url' => '',
            'lang' => 'nl_NL'
        ), $atts, 'hello_bookme'
    );

    if (empty($atts['ics_url'])) { 
        return 'No ICS URL provided.';
    }

    loadLang(isset($_GET['lang']) ? $_GET['lang'] : $atts['lang']);//loadLang($atts['lang']);
    $bookme = new calendarAvailabillity($atts['ics_url']);
    ob_start();
    ?>
    <div id="bookmebox">
        <div id="language-selector">
            <form method="GET" action="">
                <label for="language"><?php echo Language::translate("language-name"); ?>:</label>
                <select name="lang" id="language" onchange="this.form.submit()">
                    <option class="nl_NL" value="nl_NL" <?php echo isset($_GET['lang']) && $_GET['lang'] == 'nl_NL' ? 'selected' : ''; ?>>Nederlands</option>
                    <option class="en_EN" value="en_EN" <?php echo isset($_GET['lang']) && $_GET['lang'] == 'en_EN' ? 'selected' : ''; ?>>English</option> 
                </select>
            </form>
        </div> 
        <h1><?php echo Language::translate("booking-request"); ?></h1>
        <div id="formcontainer">
            <div id="stepNavigator">
                <span class="stepNav active" data-step="1"><?php echo Language::translate("step1"); ?></span>
                <span class="stepNav" data-step="2"><?php echo Language::translate("step2"); ?></span>
                <span class="stepNav" data-step="3"><?php echo Language::translate("step3"); ?></span>
                <span class="stepNav" data-step="4"><?php echo Language::translate("step4"); ?></span>
                <span class="stepNav" data-step="5"><?php echo Language::translate("step5"); ?></span>
            </div>
            <div id="step1" class="step active">
                <div id="bookingcalendar"></div>
            </div>
            <div id="step2" class="step">
                <h2><?php echo Language::translate("time-slot"); ?></h2>
                <div id="timeslots"></div>
                <button id="backToStep1Step2"><?php echo Language::translate("other-date"); ?></button>
            </div>
            <div id="step3" class="step">
                <h2><?php echo Language::translate("additional-info"); ?></h2>
                <p style="font-size:0.8em;"><?php echo Language::translate("info-note"); ?></p>
                <form id="bookingForm">
                    <div class="datafield">
                        <label for="name"><?php echo Language::translate("name"); ?>:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="datafield">
                        <label for="email"><?php echo Language::translate("email"); ?>:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="datafield">
                        <label for="telephone"><?php echo Language::translate("telephone"); ?>:</label>
                        <input type="tel" id="telephone" name="telephone" required>
                    </div>
                    <div class="datafield">
                        <label for="city"><?php echo Language::translate("city"); ?>:</label>
                        <input type="text" id="city" name="city" required>
                    </div>
                    <div class="datafield">
                        <label for="size"><?php echo Language::translate("size"); ?>:</label>
                        <select id="size" name="size" required>
                            <option value="32">32</option>
                            <option value="34">34</option>
                            <option value="36">36</option>
                            <option value="38" selected>38</option>
                            <option value="40">40</option>
                            <option value="42">42</option>
                            <option value="plussize"><?php echo Language::translate("plussize"); ?></option>
                        </select>
                    </div>
                    <div class="datafield">
                        <label for="bezoekers"><?php echo Language::translate("visitors"); ?></label> 
                        <select id="bezoekers" name="bezoekers" required>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="datafield">
                        <label for="info"><?php echo Language::translate("additional-info"); ?>:</label>
                        <textarea id="info" name="info" placeholder="<?php echo Language::translate("info-placeholder"); ?>"></textarea>
                    </div>
                    <div class="datafield">
                        <button type="button" id="backToStep2"><?php echo Language::translate("other-time"); ?></button>
                        <button type="button" id="toStep4"><?php echo Language::translate("review-request"); ?></button> 
                    </div>
                </form>
            </div>
            <div id="step4" class="step">
                <h2><?php echo Language::translate("step4"); ?></h2>
                <div id="review"></div>
                <button id="backToStep3"><?php echo Language::translate("edit"); ?></button>
                <button id="submitBooking"><?php echo Language::translate("submit"); ?></button>
            </div>
            <div id="step5" class="step">
                <h2><?php echo Language::translate("thank-you"); ?></h2>
                <p id="confirmation"><?php echo Language::translate("confirmation"); ?></p>
                <button id="backToStep1"><?php echo Language::translate("start-over"); ?></button>
            </div>
        </div>
    </div>
    <script>
        var events = <?php $bookme->getSlots(); ?>;
        var lang = '<?php echo $atts['lang']; ?>';
    </script>
    <?php
    return ob_get_clean();
}
?>

