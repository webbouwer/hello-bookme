document.addEventListener('DOMContentLoaded', function() {
    var selectedDate, selectedSlot, formData;

    // Save selected date to hidden form field
    function saveSelectedDate(date) {
        selectedDate = date;
        $('#selectedDate').val(selectedDate);
    }

    // Save selected slot to hidden form field
    function saveSelectedSlot(slot) {
        selectedSlot = slot;
        $('#selectedSlotTitle').val(selectedSlot.title);
        $('#selectedSlotStart').val(selectedSlot.start);
        $('#selectedSlotEnd').val(selectedSlot.end);
    }

    // Define the translate function
    function translate(key) {
        var translations = {
            "name": "Naam",
            "email": "E-mail",
            "telephone": "Telefoon",
            "date": "Datum",
            "time-slot": "Tijdslot",
            "visitors": "Bezoekers"
            // Add other translations as needed
        };
        return translations[key] || key;
    }

    // Define the updateStepNavigator function
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

    $('#submitBooking').click(function() {
        formData = $('#bookingForm').serializeArray();
        selectedDate = $('#selectedDate').val();
        selectedSlot = {
            title: $('#selectedSlotTitle').val(),
            start: $('#selectedSlotStart').val(),
            end: $('#selectedSlotEnd').val()
        };

        if (!selectedDate || !selectedSlot.title) {
            alert('Please select a date and time slot.');
            return;
        }

        // Validate form data
        var name = formData.find(field => field.name === 'name').value;
        var email = formData.find(field => field.name === 'email').value;
        var telephone = formData.find(field => field.name === 'telephone').value;
        var city = formData.find(field => field.name === 'city').value;
        var size = formData.find(field => field.name === 'size').value;
        var bezoekers = formData.find(field => field.name === 'bezoekers').value;
        var info = formData.find(field => field.name === 'info').value;

        if (!name || !email || !telephone || !city || !size || !bezoekers) {
            alert('Please fill in all required fields.');
            return;
        }

        $.post(helloBookMeAjax.ajaxurl, {
            action: 'send_booking_email',
            name: name,
            email: email,
            telephone: telephone,
            city: city,
            size: size,
            bezoekers: bezoekers,
            info: info,
            date: selectedDate,
            slot: {
                title: selectedSlot.title,
                start: selectedSlot.start,
                end: selectedSlot.end
            },
            lang: lang,
            selectedDate: selectedDate,
            selectedSlot: selectedSlot,
            formData: formData
        }, function(response) {
            $('#confirmation').html(`
                <p>${translate("name")}: ${name}</p>
                <p>${translate("email")}: ${email}</p>
                <p>${translate("telephone")}: ${telephone}</p>
                <p>${translate("date")}: ${selectedDate}</p>
                <p>${translate("time-slot")}: ${selectedSlot.title} (${selectedSlot.start.split('T')[1].substring(0, 5)} - ${selectedSlot.end.split('T')[1].substring(0, 5)})</p>
                <p>${translate("visitors")}: ${bezoekers}</p>
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

});
