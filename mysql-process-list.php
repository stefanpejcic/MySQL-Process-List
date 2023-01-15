<?php
/*
 * Plugin Name:       MySQL Process List
 * Plugin URI:           https://plugins.club/wordpress/mysql-process-list-wordpress-plugin/
 * Description:         Show MySQL Process list under Tools > MySQL Process List
 * Version:               1.0
 * Author:                Stefan Pejcic
 * Author URI:         https://plugins.club/wordpress/mysql-process-list-wordpress-plugin/
 */
 
// Add a custom menu item to the Tools menu
add_action( 'admin_menu', 'pluginsclub_mpc_mysql_process_list_menu' );
function pluginsclub_mpc_mysql_process_list_menu() {
  add_submenu_page( 'tools.php', 'MySQL Process List', 'MySQL Process List', 'manage_options', 'mysql-process-list', 'pluginsclub_mpc_render_mysql_process_list_page' );
}

// Render the custom admin page
function pluginsclub_mpc_render_mysql_process_list_page() {
  if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'You do not have sufficient permissions to access this page.' );
  }
  ?>
  <div class="wrap">
    <h1>MySQL Process List</h1>
    <p>The MySQL process list indicates the operations currently being performed by the set of threads executing within the website.</p>
    <div id="refresh-controls" style="display:none">
      Refresh every:
      <select id="refresh-interval">
        <option value="5">5 seconds</option>
        <option value="10" selected>10 seconds</option>
        <option value="30">30 seconds</option>
        <option value="60">60 seconds</option>
        <option value="120">2 minutes</option>
        <option value="300">5 minutes</option>
        <option value="600">10 minutes</option>
      </select>
    </div>
    <table class="wp-list-table widefat fixed striped">
      <thead>
        <tr>
          <th>ID</th>
          <th>User</th>
          <th>Host</th>
          <th>DB</th>
          <th>Command</th>
          <th>Time</th>
          <th>State</th>
          <th>Info</th>
        </tr>
      </thead>
      <tbody id="process-list">
        <?php
		
        // Connect to the database
        $mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
        if ( $mysqli->connect_error ) {
          wp_die( $mysqli->connect_error );
        }
		
        // Execute the SHOW FULL PROCESSLIST command and display the results
        $result = $mysqli->query( 'SHOW FULL PROCESSLIST' );
        while ( $row = $result->fetch_assoc() ) {
        echo '<tr>';
		echo '<td>' . esc_html( $row['Id'] ) . '</td>';
        echo '<td>' . esc_html( $row['User'] ) . '</td>';
        echo '<td>' . esc_html( $row['Host'] ) . '</td>';
        echo '<td>' . esc_html( $row['db'] ) . '</td>';
        echo '<td>' . esc_html( $row['Command'] ) . '</td>';
        echo '<td>' . esc_html( $row['Time'] ) . '</td>';
        echo '<td>' . esc_html( $row['State'] ) . '</td>';
        echo '<td>' . esc_html( $row['Info'] ) . '</td>';
        echo '</tr>';
}

// Close the database connection
$mysqli->close();
?>
      </tbody>
    </table>
  </div>
  <script>
  
    // Auto-refresh the process list
    ( function() {
      'use strict';
      // Set the initial refresh interval (in seconds)
      var refreshInterval = document.getElementById( 'refresh-interval' ).value;
      // Refresh the process list every interval
      setInterval( function() {
        // Fetch the process list from the server
        jQuery.get( '<?php echo admin_url( 'admin-ajax.php' ); ?>', {
          action: 'refresh_process_list'
        }, function( data ) {
          // Update the process list table
          jQuery( '#process-list' ).html( data );
        } );
      }, refreshInterval * 1000 );
      // Update the refresh interval when the select box changes
      document.getElementById( 'refresh-interval' ).addEventListener( 'change', function() {
        refreshInterval = this.value;
      } );
    }() );
  </script>
  <?php
}

// Handle AJAX request to refresh the process list
add_action( 'wp_ajax_refresh_process_list', 'pluginsclub_mpc_refresh_process_list_callback' );
function pluginsclub_mpc_refresh_process_list_callback() {
	
  // Verify the nonce
  check_ajax_referer( 'mysql_process_list_refresh' );
  
  // Connect to the database
  $mysqli = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
  if ( $mysqli->connect_error ) {
    wp_die( $mysqli->connect_error );
  }
  
  // Execute the SHOW PROCESSLIST command and display the results
  $result = $mysqli->query( 'SHOW FULL PROCESSLIST' );
  echo '<tbody>';
  while ( $row = $result->fetch_assoc() ) {
    echo '<tr>';
    echo '<td>' . esc_html( $row['Id'] ) . '</td>';
    echo '<td>' . esc_html( $row['User'] ) . '</td>';
    echo '<td>' . esc_html( $row['Host'] ) . '</td>';
    echo '<td>' . esc_html( $row['db'] ) . '</td>';
    echo '<td>' . esc_html( $row['Command'] ) . '</td>';
    echo '<td>' . esc_html( $row['Time'] ) . '</td>';
    echo '<td>' . esc_html( $row['State'] ) . '</td>';
    echo '<td>' . esc_html( $row['Info'] ) . '</td>';
    echo '</tr>';
  }
  echo '</tbody>';
  
  // Close the database connection
  $mysqli->close();
  
  // Exit to prevent WordPress from appending the admin footer
  exit;
}
