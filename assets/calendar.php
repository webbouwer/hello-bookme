<?php
class calendarAvailabillity {
    public $icsurl;
    public $events;
    public $slots;

    public function __construct($icsurl){
        $this->icsurl = $icsurl;
    }

    public function importICS(){
        if (empty($this->icsurl)) {
            return 'No ICS URL provided.';
        }
        $ics_content = @file_get_contents($this->icsurl);
        if ($ics_content === false) {
            return 'Unable to fetch ICS file. Please check the URL and try again.';
        }
        $lines = explode("\n", $ics_content);
        $events = array();
        $event = array();
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === 'BEGIN:VEVENT') {
                $event = array();
            } elseif ($line === 'END:VEVENT') {
                $events[] = $event;
            } else {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $event[$key] = $value;
                }
            }
        }
        return $events;
    }

    public function hasAvailableSlot($date, $events) {
        foreach ($events as $event) {
            if (strpos($event['start'], $date) === 0) {
                return true;
            }
        }
        return false; 
    }

    public function arrangeTiming(){
        $this->events = $this->importICS();
        $events_array = array();
        if($this->events) {
            foreach ($this->events as $event) {
                $start = date('c', strtotime($event['DTSTART;TZID=Europe/Amsterdam']));
                $end = date('c', strtotime($event['DTEND;TZID=Europe/Amsterdam']));
                $events_array[] = array(
                    'title' => $event['SUMMARY'],
                    'start' => $start,
                    'end' => $end
                );
            }
        }
        // Initialize array for available slots
        $available_slots = array();
        // Set start date to 2 days from now
        $start_date = new DateTime();
        $start_date->modify('+2 days');
        // Set end date to 6 months from start
        $end_date = clone $start_date;
        $end_date->modify('+6 months');
        // Loop through each day
        while ($start_date <= $end_date) {
            // Skip Sundays (7 is Sunday)
            if ($start_date->format('N') != 7) {
                // Define the two time slots for this day
                $slot1_start = $start_date->format('Y-m-d') . ' 10:30:00';
                $slot1_end = $start_date->format('Y-m-d') . ' 12:30:00';
                $slot2_start = $start_date->format('Y-m-d') . ' 13:30:00';
                $slot2_end = $start_date->format('Y-m-d') . ' 15:30:00';
                // Check if slot 1 is available
                $slot1_available = true;
                foreach ($events_array as $event) {
                    if (strtotime($event['start']) < strtotime($slot1_end) && 
                        strtotime($event['end']) > strtotime($slot1_start)) {
                        $slot1_available = false;
                        break;
                    }
                }
                // Check if slot 2 is available
                $slot2_available = true;
                foreach ($events_array as $event) {
                    if (strtotime($event['start']) < strtotime($slot2_end) && 
                        strtotime($event['end']) > strtotime($slot2_start)) {
                        $slot2_available = false;
                        break;
                    }
                }
                // Add available slots to array
                if ($slot1_available) {
                    $available_slots[] = array(
                        'title' => Language::translate('morning'),
                        'description' => Language::translate('morning'),
                        'start' => date('c', strtotime($slot1_start)),
                        'className'=> 'available',
                        'end' => date('c', strtotime($slot1_end)),
                    );
                }
                if ($slot2_available) {
                    $available_slots[] = array(
                        'title' => Language::translate('afternoon'),
                        'description' => Language::translate('afternoon'),
                        'start' => date('c', strtotime($slot2_start)),
                        'className'=> 'available',
                        'end' => date('c', strtotime($slot2_end)),
                    );
                }
            } 
            $start_date->modify('+1 day');
        }
        
        $this->slots = $available_slots;
        
    }

    public function getSlots(){
        if(empty($this->slots)){
            $this->arrangeTiming();
        }
        echo json_encode($this->slots);
        return;
    }
}
?>