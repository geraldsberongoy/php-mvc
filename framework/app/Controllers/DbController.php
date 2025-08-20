<?php
namespace App\Controllers;

use Gerald\Framework\Controllers\AbstractController;
use Gerald\Framework\Database\Connection;
use Gerald\Framework\Http\Response;

class DbController extends AbstractController
{
    public function check(): Response
    {
        try {
            $conn = Connection::create();
            $pdo  = $conn->pdo;

            // simple lightweight query
            $stmt   = $pdo->query('SELECT 1');
            $result = $stmt->fetchColumn();
            $ok     = ($result === '1' || $result === 1);

            return $this->render('dbcheck.html.twig', ['connected' => $ok]);
        } catch (\Throwable $e) {
            return $this->render('dbcheck.html.twig', ['connected' => false, 'error' => $e->getMessage()]);
        }
    }
}
