document.addEventListener('DOMContentLoaded', function() {
    var selectedDate, selectedSlot, formData;

    function translate(key) {
        return translations[key] || key;
    }

    function loadTimeSlots(date) {
        $('#timeslots').empty();
        var locale = lang === 'en_EN' ? 'en-GB' : 'nl-NL';
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
                <p>${translate("date")}: ${selectedDate}</p>
                <p>${translate("time-slot")}: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                <p>${translate("name")}: ${formData.find(field => field.name === 'name').value}</p>
                <p>${translate("email")}: ${formData.find(field => field.name === 'email').value}</p>
                <p>${translate("telephone")}: ${formData.find(field => field.name === 'telephone').value}</p>
                <p>${translate("city")}: ${formData.find(field => field.name === 'city').value}</p>
                <p>${translate("size")}: ${formData.find(field => field.name === 'size').value}</p>
                <p>${translate("visitors")}: ${formData.find(field => field.name === 'bezoekers').value}</p>
                <p>${translate("additional-info")}: ${infotext}</p>
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
        $.post(helloBookMeAjax.ajaxurl, {
            action: 'send_booking_email',
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
            lang: lang
        }, function(response) {
            $('#confirmation').html(`
                <p>${translate("name")}: ${formData.find(field => field.name === 'name').value}</p>
                <p>${translate("email")}: ${formData.find(field => field.name === 'email').value}</p>
                <p>${translate("telephone")}: ${formData.find(field => field.name === 'telephone').value}</p>
                <p>${translate("date")}: ${selectedDate}</p>
                <p>${translate("time-slot")}: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                <p>${translate("visitors")}: ${formData.find(field => field.name === 'bezoekers').value}</p>
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

    // Initialize FullCalendar
    var calendarEl = document.getElementById('bookingcalendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        initialView: 'dayGridMonth',
        selectable: true,
        events: events,
        select: function(info) {
            selectedDate = info.startStr;
            $('#step1').removeClass('active');
            $('#step2').addClass('active');
            updateStepNavigator(2);
            loadTimeSlots(selectedDate);
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
        }
    });
    calendar.render();
});
