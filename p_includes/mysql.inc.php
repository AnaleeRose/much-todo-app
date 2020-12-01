<?php
// DEFINE('DB_HOST', 'localhost');
// DEFINE('DB_NAME', 'analeerose_mtd');
// DEFINE('DB_INFO', 'analeerose_mtd');
// DEFINE('DB_USER', '');
// DEFINE('DB_PASSWORD', 'muchtodo19');

// connect to the db using pdo, best for longer or user generated content
// $dbpdo = new PDO("mysql:host=localhost;dbname=analeerose_mtd", DB_USER, DB_PASSWORD);
// $dbpdo = new PDO("mysql:host=localhost;dbname=analeerose_mtd", DB_USER, DB_PASSWORD);

// if (!$dbpdo) {
// 	echo 'oh no!';
// } else {
// 	echo 'dbOK';
// }

Class mysql {
    private $user;
    private $host;
    private $pass ;
    private $db;

    protected function __construct()
    {
        $this->db = "analeerose_mtd";
        $this->host = "localhost";
        $this->user = "root";
        $this->pass = "";
    }

    protected function mysql_connect() {
        if (strpos($_SERVER['SERVER_NAME'],'com') == false) {
            $link = new PDO("mysql:host=localhost;dbname=analeerose_mtd" , $this->user, $this->pass);
        } else {
            $online_pwd = "muchtodo19";
            $online_user = "analeerose_mtd_user";
            $link = new PDO("mysql:host=localhost;dbname=analeerose_mtd", $online_user, $online_pwd);
        }



        return $link;
    }
}

