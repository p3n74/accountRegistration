<?php

class Database {
    private $host = '127.0.0.1';
    private $user = 's21102134_palisade';
    private $pass = 'webwebwebweb';
    private $dbname = 's21102134_palisade';
    private $conn;
    private $maxRetries = 3;
    private $retryDelay = 1; // seconds

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $retries = 0;
        while ($retries < $this->maxRetries) {
            try {
                $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
                
                if ($this->conn->connect_error) {
                    throw new Exception("Connection failed: " . $this->conn->connect_error);
                }
                
                // Set charset to utf8mb4
                $this->conn->set_charset("utf8mb4");
                
                // Set connection timeout
                $this->conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
                
                return;
            } catch (Exception $e) {
                $retries++;
                if ($retries >= $this->maxRetries) {
                    throw new Exception("Database connection failed after {$this->maxRetries} attempts: " . $e->getMessage());
                }
                sleep($this->retryDelay);
            }
        }
    }

    public function query($sql) {
        try {
            if (!$this->conn) {
                $this->connect();
            }
            return $this->conn->query($sql);
        } catch (Exception $e) {
            // Try to reconnect once
            $this->connect();
            return $this->conn->query($sql);
        }
    }

    public function escape($value) {
        if (!$this->conn) {
            $this->connect();
        }
        return $this->conn->real_escape_string($value);
    }

    public function getLastError() {
        return $this->conn->error;
    }

    public function getLastId() {
        return $this->conn->insert_id;
    }

    public function beginTransaction() {
        $this->conn->begin_transaction();
    }

    public function commit() {
        $this->conn->commit();
    }

    public function rollback() {
        $this->conn->rollback();
    }

    public function prepare($sql) {
        if (!$this->conn) {
            $this->connect();
        }
        return $this->conn->prepare($sql);
    }

    public function __destruct() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
} 