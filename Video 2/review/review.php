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
        

    // New table information 
		private $tableName = 'reviewtable'; 
		private $columnPostive = 'positive'; 
		private $columnId = 'comment_ID'; 
        private $columnQuote = 'quote';  
        
    // adding button 1 with functionality 
		private $CreateTableAction = "create_table";

	// adding button 2 with functionality 
        private $getDataAction = "add_table_data";
        
    function __construct(){

        // call this function to see if the new table has been created 
			$this->checkTable();

    }

    function checkTable(){

        global $wpdb;// get the wpdb class to allow us to query the database with its methods 
        $this->tableName = $wpdb->base_prefix.$this->tableName;

        $query = $wpdb->prepare("SHOW TABLES LIKE %s",$this->tableName);

    }
}

