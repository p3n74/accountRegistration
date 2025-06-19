<?php

class UsersController extends Controller {
    // Example: /users/search?q=john
    public function search() {
        header('Content-Type: application/json');
        $q = $_GET['q'] ?? '';
        $q = trim($q);
        if ($q === '') {
            echo json_encode([]);
            return;
        }

        $userModel = $this->model('User');
        $results = $userModel->searchUsers($q, 10);

        echo json_encode($results);
    }
} 