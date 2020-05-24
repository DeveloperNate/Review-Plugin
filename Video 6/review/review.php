<?php
/**
 * @package Nathan
 */
/*
Plugin Name: Review Plugin 
Plugin URI: none
Description:  This is plugin that uses AI to find the best comments in a review 
Version: 0.1
Author: Nathan
Author URI: 
License: GPLv2 
Text Domain: Nathan 
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.


*/

// Make sure we don't expose any info if called directly

class Review{

    //creating the variables that I think I will need
		
		private $tableAlert;
        private $arrayOfArray = array();
        private $threshold = 0.87;

    // New table information 
		private $tableName = 'reviewtable'; 
		private $columnPostive = 'positive'; 
		private $columnId = 'comment_ID'; 
        private $columnQuote = 'quote';


	// adding button 2 with functionality 
        private $getDataAction = "add_table_data";

    //WP Comment Table

	    private $secondTable = 'wp_comments';
        

    function __construct(){

        // call this function to see if the new table has been created 
            $this->checkTable();
            
            // if new table has been created, call this function 
            if ( $this->tableAlert === FALSE) {$this->getData();}
            
            // call all my actions
			$this->allActions();

    }


    function addTemplate(){

        ?>
			<h1>Adding the Review Table</h1>
			<p>This is for adding the Review Table to your Database</p>

        <?php
        // checks to see if our table has been added 
			if ( $this->tableAlert === FALSE) {
        ?>

                <div style="background-color: #7db68e; width:30%; height:30px; color:white; font-size:20px;">
  					<strong>Success!</strong> The table was added .
				</div>
        <?php
            }else{
                // if our table was not added, we added a button. This button needs to go to admin-post. We can then match up our value to a class method that will be called when it is clicked
        ?>
            <div style="background-color: #DC143C; width:30%; height:60px; color:white; font-size:20px;">
                  <strong>Error -There is no Table !</strong> 
            </div>
            <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
            <input type="hidden" name="action" value="<?php echo "create_table"; ?>">
            <?php submit_button( 'Add the Table' ); ?>
            </form>
        <?php
            }

        ?>
            <h1>Add Trial Data</h1>
			<p> Add 3 reviews to test the widget</p>


            <?php
				// If there is any results, it will be displayed 

				if (count($this->arrayOfArray) === 0){
				?>

				<div class="alert" style="background-color: #DC143C; width:30%; height:60px; color:white; font-size:20px;">
  					<strong>Error -There is no information !</strong> 
					  <br>
					  <?php if ( $this->tableAlert === TRUE) { echo 'Add the table'; } ?> <br>
					  <?php if (count($this->arrayOfArray) === 0) { echo 'Add the Trial Data to the New Table'; } ?> 
				</div>
				<form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
				<input type="hidden" name="action" value="<?php echo "trial_data"; ?>">
				<?php submit_button( 'Add Trial Data' ); ?>
				</form>

				<?php 
				}elseif((count($this->arrayOfArray) > 0))
				{
				?>

				<div class="alert" style="background-color: #7db68e; width:30%; height:30px; color:white; font-size:20px;">
  					<strong>Success!</strong> The Data was Added .
				</div>
				<?php 
				}
		

    }

    function allActions(){
        /// call all my actions
        add_action('admin_menu', array($this,'my_menu_pages')); //creates my main widget page
        add_action( "admin_post_create_table", array($this,'createTable' )); // create our own action on our main page
        add_action( "admin_post_trial_data", array($this,'trialData' )); // create our own action on our main page
   
    }

    function checkTable(){

        global $wpdb;// get the wpdb class to allow us to query the database with its methods 

        $this->tableName = $wpdb->base_prefix.$this->tableName;

        $query = "SHOW TABLES LIKE '$this->tableName'";

        if ( $wpdb->get_var($query) == $this->tableName) { // use the wpdb's get_var method to search for a table
            $this->tableAlert = FALSE; // if found, set to false 
			}else{
				$this->tableAlert =TRUE;  // not found, set to true 
			}
    }

    function createTable(){

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $this->tableName(
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			$this->columnId bigint(20)  NOT NULL,
			$this->columnPostive float(3,2) NOT NULL,
			$this->columnQuote text NOT NULL,
			PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        wp_safe_redirect(admin_url());
        exit;
            
    }

    

    function getData(){

        global $wpdb;
        $getDataQuery = $wpdb->prepare( "SELECT $this->columnId,$this->columnQuote,$this->columnPostive  FROM $this->tableName WHERE $this->columnPostive > %f ORDER BY $this->columnPostive DESC" ,$this->threshold ); // used prepare query because might want the user to edit the positive limit 
        
        $allrows = $wpdb->get_results($getDataQuery ); // use get_results because more than result will be returned 

        if ($allrows !== NULL){ // if any results are returned , print out the results
            $number = 0;

            foreach( $allrows as $row )  // cycles through the results - to get a field, write $row->columnname 
                        {
                            $Query = "SELECT * FROM wp_comments WHERE comment_ID = $row->comment_ID "; // get the full comment from the wp_table
                            $rowData= $wpdb->get_row($Query);
                            $allData = array();
                            $allData['number'] = $number;
                            $allData['author'] = $rowData->comment_author;
                            $allData['fullComment'] = $rowData->comment_content;
                            $allData['likes'] = $rowData->comment_approved;
                            $allData['quote'] = $row->quote;
                            $allData['positive'] = $row->positive;
                            $this->arrayOfArray[] = $allData; // by the end of the loop, we will have created an array of arrays to store 
                            $number++;
                        }
             }
						
    }

    function my_menu_pages(){
        add_menu_page('Review Plugin', 'Review Plugin', 'manage_options', 'my-menu', array($this,'addTemplate')); //adds my menu page to dashboard 
    }


    function trialData(){

        global $wpdb;
		
		$wpdb->query("INSERT INTO $this->tableName
            (comment_ID, positive, quote) 
            VALUES
            ('11', '0.88', 'Go The Extra Mile'),
            ('12', '0.91', 'Highly Recommend'),
            ('13', '0.90', 'Quality Work If You Have A Tight Budget')");
            
        $wpdb->query("INSERT INTO $this->secondTable
            (comment_ID, comment_author, comment_content)
            VALUES
            ('11', 'Cindy Shelton', 'Great Place ! These guys knows what they are doing and go the extra mile, in a friendly and relax way .'),
            ('12', 'Scott Pacult', 'All the products were brilliantly. Highly recommend this place if you are ever in the area.'),
            ('13', 'Jasmine Gershaw', 'I have come here twice. Both times I have received great customer services. I definitely recommend this pland if you are looking for quality work if you have a tight budget.')");

        wp_safe_redirect(admin_url());
        exit;
    }


}


$plugin = new Review();  //creates the object
        








        

        






