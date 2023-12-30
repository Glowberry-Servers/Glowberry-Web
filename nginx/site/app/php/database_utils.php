<?php
	require_once $_SERVER["DOCUMENT_ROOT"] . "/vendor/autoload.php";

	use LaminariaCore\MySQLDatabaseManager;
	use LaminariaCore\MySQLServerConnector;

    /**
    * Gets the connection to the MySQL server using the config file.
    * @return MySQLDatabaseManager The MySQLDatabaseManager object used to interact with the database.
    */
	function getManagerFromConfig() : MySQLDatabaseManager {

        $mysql_config_path = $_SERVER["DOCUMENT_ROOT"] . "/mysql_config.json";
        $sql_script_path = $_SERVER["DOCUMENT_ROOT"] . "/app/sql/glowberry_db.sql";

		$mysql_config = json_decode(file_get_contents($mysql_config_path), true);
		$server = $mysql_config['host'];
		$database = $mysql_config['database'];
		$username = $mysql_config['user'];
		$password = $mysql_config['password'];
		$connector = MySQLServerConnector::makeWithAuth($server, "", $username, $password);

		return getManagerAfterChecks($connector, $database, $sql_script_path);
	}

    /**
	 * Makes sure that the database exists by sending a dummy query to the server attempting
	 * to create it if it doesn't exist. If it does, it runs the MySQL script to create the
	 * tables.
	 * After this, it returns the MySQLDatabaseManager object used to interact with the database.
	 *
	 * @param $connector MySQLServerConnector The MySQLServerConnector object used to connect to the server.
	 * @param $database string The database's name.
	 * @param $mysql_script_path string The path to the MySQL script to run.
	 * @return MySQLDatabaseManager The MySQLDatabaseManager object used to interact with the database.
	 */
	function getManagerAfterChecks(MySQLServerConnector $connector, string $database,
								   string $mysql_script_path) : MySQLDatabaseManager {
		
		$dummy = "CREATE DATABASE IF NOT EXISTS ". $database;
		
		// Sends the dummy query to the server and checks if there are any warnings.
		// If there are warnings, the database exists.
		$database_exists = mysqli_query($connector->getConnection(), $dummy)
			&& mysqli_warning_count($connector->getConnection()) != 0;
		
		$manager = new MySQLDatabaseManager($connector);
		
		if ( !$database_exists ) $manager->runMySQLScript($mysql_script_path);

        $manager->useDatabase($database);
		return $manager;
	}
