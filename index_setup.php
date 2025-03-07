<?php
/* 
 * This script is a simplyfied booking form that allows users to select a date and time slot for an appointment.
 * It checks the availability of time slots based on events from an ICS file and displays them in a calendar view.
 * Users can select a date and time slot, enter their details, review the booking, and submit the form.
 * The form data is sent via AJAX to a PHP script that sends an email with the booking details.
 */
require 'config/config.php';
require 'calendar.php';
require 'language.php';
loadLang(isset($_GET['lang']) ? $_GET['lang'] : 'nl_NL'); 
$bookme = new calendarAvailabillity($icsurl);
?>
<!DOCTYPE html>
<html lang="<?php echo isset($_GET['lang']) && $_GET['lang'] == 'en_EN' ? 'en_EN' : 'nl_NL'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afspraken Aanvragen</title>

    <script src="https://code.jquery.com/jquery-1.12.4.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js" integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU=" crossorigin="anonymous"></script>

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">

    <link rel="stylesheet" href="booking.css">

    <style>
        #calendar {
            max-width: 900px;
            margin: 0 auto;
        }
        /* Responsive changes */
        @media (max-width: 768px) {
            #calendar {
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <div id="bookmebox">

        <div id="language-selector">
            <form method="GET" action="">
                <label for="language"><?php echo Language::translate("language-name"); ?>:</label>
                <select name="lang" id="language" onchange="this.form.submit()">
                    <option class="nl_NL" value="nl_NL" <?php echo $_GET['lang'] == 'nl_NL' ? 'selected' : ''; ?>>Nederlands</option>
                    <option class="en_EN" value="en_EN" <?php echo $_GET['lang'] == 'en_EN' ? 'selected' : ''; ?>>English</option> 
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
        var translations = <?php echo json_encode(Language::$instance->trans); ?>;
        var lang = '<?php echo isset($_GET['lang']) ? $_GET['lang'] : 'nl_NL'; ?>';
    </script>
    <script src="assets/hello-bookme.js"></script>

    <script>
        jQuery(document).ready(function($) {
            var calendarEl = document.getElementById('bookingcalendar');
            var selectedDate, selectedSlot, formData;
            var events = <?php $bookme->getSlots(); //echo $events_json; ?>;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: 'UTC',
                initialView: 'dayGridMonth',
                locale: '<?php echo $_GET['lang'] == 'en_EN' ? 'en' : 'nl'; ?>',
                height: 'auto',
                allDaySlot: false,
                displayEventTime: true,
                displayEventEnd: true,
                firstDay: 1,
                events: events,
                headerToolbar: {
                    left: 'title',
                    center: '',
                    right: 'today'
                },
                footerToolbar: {
                    left: 'dayGridMonth,listWeek',
                    center: '',
                    right: 'prev,next'
                },
                dateClick: function(info) {
                    if (hasAvailableSlot(info.dateStr)) {
                        selectedDate = info.dateStr;
                        selectedSlot = false;
                        $('#step1').removeClass('active');
                        $('#step2').addClass('active');
                        updateStepNavigator(2);
                        loadTimeSlots(selectedDate);
                    }
                },
                eventClick: function(info) {
                    selectedDate = info.event.startStr.split('T')[0];
                    selectedSlot = {
                        title: info.event.title,
                        start: info.event.startStr,
                        end: info.event.endStr
                    };
                    $('#step1').removeClass('active');
                    $('#step2').addClass('active');
                    updateStepNavigator(2);
                    loadTimeSlots(selectedDate);
                },
                dayRender: function(info) {
                    if (!hasAvailableSlot(info.dateStr)) {
                        $(info.el).addClass('fc-disabled');
                    }
                },
                windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('listWeek');
                    } else {
                        calendar.changeView('dayGridMonth');
                    }
                }
            });
            calendar.render();

            // Initial view check for responsive display
            if (window.innerWidth < 768) {
                calendar.changeView('listWeek');
            }
            // Check if there are clickable days or timeslots in the current view
            function hasClickableDaysOrSlots() {
                var view = calendar.view;
                var hasClickable = false;
                var currentDate = view.activeStart;
                while (currentDate < view.activeEnd) {
                    if (hasAvailableSlot(currentDate.toISOString().split('T')[0])) {
                        hasClickable = true;
                        break;
                    }
                    currentDate.setDate(currentDate.getDate() + 1);
                }
                return hasClickable;
            }
            function checkAndMoveToNext() {
                if (!hasClickableDaysOrSlots()) {
                    calendar.next();
                    setTimeout(checkAndMoveToNext, 500); // Check again after moving to the next period
                }
            }
            checkAndMoveToNext(); // Initial check and move if necessary
            // Check and move to next if no clickable events on window resize
            $(window).resize(function() {
                checkAndMoveToNext();
            });
            function loadTimeSlots(date) {
                $('#timeslots').empty();
                var locale = '<?php echo $_GET['lang'] == 'en_EN' ? 'en-GB' : 'nl-NL'; ?>';
                var formattedDate = new Date(date).toLocaleDateString(locale, {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                $('#timeslots').append('<h3>' + formattedDate + '</h3><div class="timeslotbar"></div>');
                events.forEach(function(slot) {
                    if (slot.start.startsWith(date)) {
                        var button = $('<button>')
                            .text(slot.title + ' (' + slot.start.split('T')[1].substring(0, 5) + ' - ' + slot.end.split('T')[1].substring(0, 5) + ')')
                            .data('slot', slot)
                            .click(function() {
                                selectedSlot = $(this).data('slot');

                                $('#bookingForm .slotinfo').remove();
                                $('#bookingForm').prepend('<div class="slotinfo">' + formattedDate + ' - ' + selectedSlot.title + ' (' + selectedSlot.start.split('T')[1].substring(0, 5) + ' - ' + selectedSlot.end.split('T')[1].substring(0, 5) + ')</div>');
                                $('#timeslots button').removeClass('selected');
                                $(this).addClass('selected');
                                $('#step2').removeClass('active');
                                $('#step3').addClass('active');
                                updateStepNavigator(3);
                            });

                        if (selectedSlot && (selectedSlot.start.split('T')[1].substring(0, 5) === slot.start.split('T')[1].substring(0, 5))) {
                            button.addClass('selected');
                        }
                        $('#timeslots').find('.timeslotbar').append(button);
                    }
                });
            }

            function hasAvailableSlot(date) {
                return events.some(function(event) {
                    return event.start.startsWith(date);
                });
            }

            function updateStepNavigator(step) {
                $('.stepNav').removeClass('done active');
                $('.stepNav').each(function(index) {
                    if (index + 1 < step) {
                        $(this).addClass('done');
                    } else if (index + 1 === step) {
                        $(this).addClass('active');
                    }
                });
            }

            $('.stepNav').click(function() {
                var step = $(this).data('step');
                if ($(this).hasClass('done') || $(this).hasClass('active')) {
                    $('.step').removeClass('active');
                    $('#step' + step).addClass('active');
                    updateStepNavigator(step);
                }
            });

            $('#backToStep1Step2').click(function() {
                $('#step2').removeClass('active');
                $('#step1').addClass('active');
                updateStepNavigator(1);
            });

            $('#backToStep2').click(function() {
                $('#step3').removeClass('active');
                $('#step2').addClass('active');
                updateStepNavigator(2);
            });

            $('#toStep4').click(function() {
                var valid = true;
                $('#bookingForm input[required], #bookingForm textarea[required]').each(function() {
                    if (!this.value) {
                        $(this).addClass('error');
                        valid = false;
                    } else {
                        $(this).removeClass('error');
                    }
                });
                // Additional validation for specific fields
                var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                var telephonePattern = /^[0-9\-\+\s\(\)]+$/;
                if (!emailPattern.test($('#email').val())) {
                    $('#email').addClass('error');
                    valid = false;
                } else {
                    $('#email').removeClass('error');
                }
                if (!telephonePattern.test($('#telephone').val())) {
                    $('#telephone').addClass('error');
                    valid = false;
                } else {
                    $('#telephone').removeClass('error');
                }
                if (valid) {
                    formData = $('#bookingForm').serializeArray();
                    let infotext = formData.find(field => field.name === 'info').value;
                    if( infotext == '' ){
                         infotext = 'n.v.t.';
                    }
                    $('#review').html(`
                        <p><?php echo Language::translate("date"); ?>: ${selectedDate}</p>
                        <p><?php echo Language::translate("time-slot"); ?>: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                        <p><?php echo Language::translate("name"); ?>: ${formData.find(field => field.name === 'name').value}</p>
                        <p><?php echo Language::translate("email"); ?>: ${formData.find(field => field.name === 'email').value}</p>
                        <p><?php echo Language::translate("telephone"); ?>: ${formData.find(field => field.name === 'telephone').value}</p>
                        <p><?php echo Language::translate("city"); ?>: ${formData.find(field => field.name === 'city').value}</p>
                        <p><?php echo Language::translate("size"); ?>: ${formData.find(field => field.name === 'size').value}</p>
                        <p><?php echo Language::translate("visitors"); ?>: ${formData.find(field => field.name === 'bezoekers').value}</p>
                        <p><?php echo Language::translate("additional-info"); ?>: ${infotext}</p>
                    `);
                    $('#step3').removeClass('active');
                    $('#step4').addClass('active');
                    updateStepNavigator(4);
                }
            });

            $('#backToStep3').click(function() {
                $('#step4').removeClass('active');
                $('#step3').addClass('active');
                updateStepNavigator(3);
            });

            $('#submitBooking').click(function() {
                $.post('send_email.php', {
                    name: formData.find(field => field.name === 'name').value,
                    email: formData.find(field => field.name === 'email').value,
                    telephone: formData.find(field => field.name === 'telephone').value,
                    city: formData.find(field => field.name === 'city').value,
                    size: formData.find(field => field.name === 'size').value,
                    bezoekers: formData.find(field => field.name === 'bezoekers').value,
                    info: formData.find(field => field.name === 'info').value,
                    date: selectedDate,
                    slot: {
                        title: selectedSlot.title,
                        start: selectedSlot.start,
                        end: selectedSlot.end
                    },
                    lang: '<?php echo isset($_GET['lang']) ? $_GET['lang'] : 'nl_NL'; ?>'
                }, function(response) {
                    $('#confirmation').html(`
                        <p><?php echo Language::translate("name"); ?>: ${formData.find(field => field.name === 'name').value}</p>
                        <p><?php echo Language::translate("email"); ?>: ${formData.find(field => field.name === 'email').value}</p>
                        <p><?php echo Language::translate("telephone"); ?>: ${formData.find(field => field.name === 'telephone').value}</p>
                        <p><?php echo Language::translate("date"); ?>: ${selectedDate}</p>
                        <p><?php echo Language::translate("time-slot"); ?>: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                        <p><?php echo Language::translate("visitors"); ?>: ${formData.find(field => field.name === 'bezoekers').value}</p>
                    `);
                    $('#step4').removeClass('active');
                    $('#step5').addClass('active');
                    updateStepNavigator(5);
                    // Clear form data and reset steps
                    $('#bookingForm')[0].reset();
                    $('#review').empty();
                    selectedDate = null;
                    selectedSlot = null;
                    formData = null;
                });
            });

            $('#backToStep1').click(function() {
                $('#step5').removeClass('active');
                $('#step1').addClass('active');
                updateStepNavigator(1); 
            });
        });
    </script>
</body>

</html>