<?php


class db 
{
    protected $conn;
    protected $last_statement;
    
    public function __construct( $database_info )
    {
        try
        { 
            $pdo_dsn = $this->pdo_dsn( $database_info['host'], $database_info['name'] );
            $this->conn = new PDO( $pdo_dsn , $database_info['user'], $database_info['pass'] );
        }
        catch(Exception $e)
        {
            /*
             * ERROR CODES:
             * 1049 - supplied database not found
             * 1045 - invalid credentials supplied
             * 2003 - supplied host not found
             */
            throw new Exception( "Could not construct database connection: ".$e->getMessage(), $e->getCode() );
        }
    }
    
    /**
     * Format the connection dsn string for a PDO connection
     * 
     * @param string $host server hosting the database
     * @param string $name name of the database
     * @return string 
     */
    protected function pdo_dsn( $host, $name )
    {
        if( ! $name )throw new Exception ( "A database name was not properly defined - could not build pdo_dsn." );
        if( ! $host )throw new Exception ( "A database host was not properly defined - could not build pdo_dsn." );
        return "mysql:host=".$host.";dbname=".$name;
    }
    
    /**
     * make a parameterized query to the database
     * 
     * @param string $q_string "SELECT * FROM table WHERE var = :var"
     * @param array $param_array array(":var" => "value")
     */
    public function query($q_string, $param_array = null)
    {
        $this->last_statement = $this->conn->prepare($q_string);
        $this->last_statement->execute($param_array);
        
        if( $this->last_statement->errorCode() != "00000" )
        {
            $error = $this->last_statement->errorInfo();
            error_log( $error[2] );
            throw new Exception( $error[2] );
        }
        
        return $this->last_statement;
    }
    
    /**
     * get the number of rows from the query
     * 
     * returns the humberof rows of the PDOstatement passed via param,
     * or if no statement is passed, the last statement is used.
     * 
     * @param PDOstatement $PDOstatement the return value from a query
     * @return PDOstatement 
     */
    public function num_rows( $PDOstatement = null )
    {
        if($PDOstatement) return $PDOstatement->rowCount();
        else return $this->last_statement->rowCount();
    }
    
    /**
     * fetch associative array from the query
     * 
     * returns an associative array off the PDOstatement passed via param,
     * or if no statement is passed, the last statement is used.
     * 
     * @param PDOstatement $PDOstatement
     * @return PDOstatement 
     */
    public function fetch_assoc( $PDOstatement = null )
    {
        if($PDOstatement) return $PDOstatement->fetch(PDO::FETCH_ASSOC);
        else return $this->last_statement->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * fetch all associative arrays from the query
     * 
     * returns an array of associative arrays off the PDOstatement passed via param,
     * or if no statement is passed, the last statement is used.
     * 
     * @param PDOstatement $PDOstatement
     * @return PDOstatement 
     */
    public function fetch_all_assoc( $PDOstatement = null )
    {
	    if($PDOstatement) return $PDOstatement->fetchAll(PDO::FETCH_ASSOC);
        else return $this->last_statement->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * converts a php timestamp to mysql timestamp, or makes a mysqltimestamp
     * of the current time if no timestamp is passed
     * 
     * @param time() $php_timestamp
     * @return datetime 
     */
    public static function to_mysql_timestamp($php_timestamp = null)
    {
        if($php_timestamp) return date( 'Y-m-d H:i:s', $php_timestamp );
        else return date( 'Y-m-d H:i:s', time() );
    }
    
    /**
     * Converts a SQL datetime to a php timestamp
     * 
     * @param datetime $sql_datetime
     * @return time() 
     */
    public static function to_php_timestamp($sql_datetime)
    {
        return strtotime($sql_datetime);
    }
    
    public function transaction_begin() { $this->conn->beginTransaction(); }
    
    public function in_transaction() { return $this->conn->inTransaction(); }
    
    public function transaction_commit() { $this->conn->commit(); }
}

/*
class db_table
{
    public $code_version;
    public $database_version;
    
    protected $database;
    protected $creation_code;
    
    protected $revisions = Array();
    
    public function __construct( $table_name, $version, $db, $creation_code ) 
    {
        ;
    }
    
    public function create()
    {
        
    }
    
    public function add_revision()
    {
        
    }
    
    public function update()
    {
        
    }
}

class db_tables
{
    protected static $all = Array();
    
    
}
*/