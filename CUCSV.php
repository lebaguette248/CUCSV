<?php
//TODO: Change Vars
/**
 * @package CUCSV
 */

/*
 * Plugin Name: CUCSV
 * Description: This plugin creates users from a CSV file. Uses format "EMAIL"
 * Version: 0.1
 * Autor: Nicky Lopez
 * License: GPLv2
 * Text Domain: cucsv
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    die('no access muehehehe😼');
}

/**
 * Cucsv Class containing all CUCSV functions
 */
class Cucsv
{
    /**
     * Contains the roles that can be assigned to users
     * Use $roles[4] for default or $roles[3] for special access
     * @var array|string[]
     */
    protected array $roles = ['administrator', 'editor', 'author', 'contributor', 'subscriber'];
    private array $exportData = [];

    /**
     * Constructor
     */
    function __construct()
    {
    }

    /**
     * Registers the plugin by adding admin page and enqueueing scripts
     */
    public function register(): void
    {
        add_action('admin_menu', [$this, 'addAdminpage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
    }

    /**
     * Function to be called on plugin activation
     */
    function onActivation(): void
    {
    }

    /**
     * Function to be called on plugin deactivation
     * @return void
     */
    function onDeactivation(): void
    {
    }

    /**
     * Enqueues the necessary styles for the plugin
     */
    function enqueue(): void
    {
        wp_enqueue_style('standartstyle', plugins_url('assets/mystyle.css', __FILE__));
    }


    /**
     * Adds the admin page for the plugin
     */
    public function addAdminpage(): void
    {
        add_menu_page('CUCSV', // Page title
            'CUCSV', // Menu title
            'manage_options', // Capability required to access the page
            'cucsv_plugin_admin', // Menu slug
            [$this, 'admin_index'], // Function to display the page content
            'dashicons-list-view', // Icon URL or dashicon class
            110 // Position in the menu order, higher numbers are lower
        );
    }

    /**
     * Handles the file upload process for the CSV file and displays the admin page
     * @throws Exception
     */
    public function admin_index(): void
    {
        require_once plugin_dir_path(__FILE__) . 'templates/admin.php';
        echo '<div style="background-color: #dadfe1; > ';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
            $uploaded_file = $_FILES['csv_file'];
            $temp_file = tempnam(sys_get_temp_dir(), 'csv_');

            if (move_uploaded_file($uploaded_file['tmp_name'], $temp_file)) {
                echo '<p>File uploaded successfully.</p>';
                // Call createUserfromCSV with the temporary file path
                $this->createUserfromCSV($temp_file);


                // Delete all used files
                unlink($temp_file);
                //unlink($uploaded_file['tmp_name']);
            } else {
                echo '<p>File upload failed.</p>';
                error_log('File upload failed.');
            }
        }
        echo '</div>';

    }

    /**
     * Creates users from the CSV file
     * @param string $file_path Path to the CSV file
     * @throws Exception
     */
    public function createUserfromCSV(string $file_path): void
    {
        $pattern = '/[^a-zA-Z0-9.,!?-]/';


        $userData = $this->getDatafromCSV($file_path);
        if (empty($userData)) {
            exit;
        }
        foreach ($userData as $user) {
            try {
                // Check if the login name is empty
                if (empty($user[1])) {
                    echo("The login name cannot be empty for user with email $user[0].");
                    continue;
                }
                if (empty($user[0])) {
                    echo("The email cannot be empty for user with login name $user[1].");
                    continue;
                }

                if (preg_match($pattern, $user[1])) {
                    echo("The login name $user[1] contains disallowed characters.");
                    continue;
                }
                
                // Assigns Userdata into OBJ so that it can be fed to wp_insert_user
                $userdata = [
                    'user_login' => $user[1],
                    'user_email' => $user[0],
                    'user_pass' => empty($user[2]) ? wp_generate_password(10) : $user[2],
                    'role' => $this->roles[$user[3]] ?? $this->roles[4]
                ];
                $user_id = wp_insert_user($userdata);

                if (is_wp_error($user_id)) {
                    throw new Exception($user_id->get_error_message() . " occurred. " . PHP_EOL . "User with Email $user[0] could not be generated" . PHP_EOL);
                } else {
                    echo "User $user[1] has been created <br>" . PHP_EOL;
                    $this->exportData[] = $userdata;
                }
            } catch (Exception $exception) {
                echo($exception->getMessage() . PHP_EOL . " User with Email$user[0] could not be generated" . PHP_EOL);
                error_log($exception->getMessage());
            }

        }
        echo "CSV Uploaded and possible Users Created" . PHP_EOL;
        echo "Export Data" . PHP_EOL;
        if (count($this->exportData) > 0) {
            $this->writeDatatoCSV($this->exportData);
        }

    }


    /**
     * Gets data from a CSV file. CSV must be constructed with email;name;password.
     * It is recommended to create CSV by exporting a Microsoft Excel file.
     * @param string $file_path Path to the CSV file
     * @return array CSV Data
     */
    protected function getDatafromCSV(string $file_path): array
    {

        $userData = [];

        if (file_exists($file_path)) {
            $temp_file = tempnam(sys_get_temp_dir(), 'csv_');
            copy($file_path, $temp_file);

            $file = fopen($temp_file, "r");
            while (($data = fgetcsv($file, 1000, ";")) !== FALSE) {
                $userData[] = $data;
            }
            fclose($file);
        } else {
            echo 'File not found.' . PHP_EOL;
        }
        return $userData;
    }

    /**
     * Writes data to a CSV file
     * @param array $data Data to write to the CSV file
     */
    protected function writeDatatoCSV($data): void
    {
        $csvFile = fopen(plugin_dir_path(__FILE__) . "assets/output.csv", "w");
        if ($csvFile !== FALSE) {
            // Write the header row
            fputcsv($csvFile, array_keys($data[0]), ";");

            // Loop through each row of data and write it to the CSV file
            foreach ($data as $row) {
                fputcsv($csvFile, $row);
            }

            // Close the file
            fclose($csvFile);

            echo "Data written to file successfully.";
        } else {
            echo "Failed to Create / Open File";
        }

        $this->exportData = [];
    }
}


// Check if the class exists and register the plugin
if (class_exists('Cucsv')) {
    $Cucsv = new Cucsv();
    $Cucsv->register();
}

// Register the activation hook
register_activation_hook(__FILE__, [$Cucsv, 'onActivation']);

// Register the deactivation hook
register_deactivation_hook(__FILE__, [$Cucsv, 'onDeactivation']);