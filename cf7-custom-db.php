<?php
/*
Plugin Name: CF7 Custom Database Integration
Description: Integrates Contact Form 7 with a custom database table.
Version: 1.0
Author: Andrzej Sawczuk
*/

add_action('wpcf7_before_send_mail', 'custom_cf7_to_db');

function custom_cf7_to_db($contact_form) {
    $submission = WPCF7_Submission::get_instance();
    if ($submission) {
        $posted_data = $submission->get_posted_data();

        // Create a connection to the external database
        $mydb = new wpdb('DBNAME', 'PASSWORD', 'DBUSER', 'localhost');

        // Check the database connection
        if ($mydb->last_error) {
            custom_debug_log('Database connection error: ' . $mydb->last_error);
            return;
        }

        // Process fields to handle arrays and sanitize inputs
        $adults_count = isset($posted_data['adults']) ? (is_array($posted_data['adults']) ? implode(', ', $posted_data['adults']) : sanitize_text_field($posted_data['adults'])) : '';
        $children_count = isset($posted_data['children']) ? (is_array($posted_data['children']) ? implode(', ', $posted_data['children']) : sanitize_text_field($posted_data['children'])) : '';
        $cars_count = isset($posted_data['cars']) ? (is_array($posted_data['cars']) ? implode(', ', $posted_data['cars']) : sanitize_text_field($posted_data['cars'])) : '';

        // Log individual field values for debugging
        custom_debug_log('Adults count: ' . $adults_count);
        custom_debug_log('Children count: ' . $children_count);
        custom_debug_log('Cars count: ' . $cars_count);

        // Prepare data for insertion with sanitization
        $data = array(
            'name' => sanitize_text_field($posted_data['your-name']), // Removes unwanted HTML tags and special characters
            'email' => sanitize_email($posted_data['your-email']), // Removes invalid characters and checks email format
            'phone' => sanitize_text_field($posted_data['your-phone']), // Cleans text of unwanted characters
            'adults_count' => $adults_count, // Stores as string, removes unwanted characters
            'children_count' => $children_count, // Stores as string, removes unwanted characters
            'cars_count' => $cars_count, // Stores as string, removes unwanted characters
            'car_registration_number' => sanitize_text_field($posted_data['your-car']), // Cleans text of unwanted characters
            'arrival_date' => sanitize_text_field($posted_data['date-arrival']), // Cleans text of unwanted characters
            'arrival_time' => sanitize_text_field($posted_data['time-arrival']), // Cleans text of unwanted characters
            'departure_date' => sanitize_text_field($posted_data['date-departure']), // Cleans text of unwanted characters
            'departure_time' => sanitize_text_field($posted_data['time-departure']) // Cleans text of unwanted characters
        );

        // Log the data to be inserted for debugging
        custom_debug_log('Data to be inserted: ' . print_r($data, true));

        // Insert data into the table
        $result = $mydb->insert('reservations', $data);

        // Check if the insertion was successful and log the result
        if (false === $result) {
            custom_debug_log('Insert error: ' . $mydb->last_error);
        } else {
            custom_debug_log('Data inserted successfully.');
        }
    }
}

// Function for custom logging
function custom_debug_log($message) {
    $log_file = WP_CONTENT_DIR . '/custom_debug.log';
    if (!file_exists($log_file)) {
        touch($log_file);
        chmod($log_file, 0666);
    }
    error_log($message . "\n", 3, $log_file);
}


