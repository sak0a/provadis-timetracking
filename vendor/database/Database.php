<?php

namespace vendor\database;

use mysqli;

include 'Filter.php';

class Database
{

    private string $host;
    private string $user;
    private string $password;
    private string $database;
    private mysqli|false $conn;

    /**
     * Database constructor.
     * @param string $host Database host
     * @param string $user Database user
     * @param string $password Database password
     * @param string $database Database name
     */
    function __construct($host, $user, $password, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;

        $this->conn = mysqli_connect($this->host, $this->user, $this->password, $this->database);
        if (!$this->conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        mysqli_set_charset($this->conn, "utf8");
    }

    /**
     * Database Destructor
     */
    function __destruct()
    {
        mysqli_close($this->conn);
    }

    /**
     * @return mysqli|false Database connection
     */
    function getConnection(): mysqli|false
    {
        return $this->conn;
    }

    /**
     * @return Database Default database connection
     */
    public static function initDefault(): Database
    {
        return new Database("korra.design", "provadis", "alexandros2406", "provadis_project");
    }

}