<?php
namespace App\models;

abstract class BaseModel
{
    protected $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    protected function executePreparedQuery($query, $params = [], $types = '') {
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new \RuntimeException("Query preparation failed: " . $this->mysqli->error);
        }

        if (!empty($params) && !empty($types)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    protected function executeQuery($query) {
        $result = $this->mysqli->query($query);
        if (!$result) {
            throw new \RuntimeException("Query execution failed: " . $this->mysqli->error);
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    protected function getConnection() {
        return $this->mysqli;
    }
}