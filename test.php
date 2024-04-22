<?php
include "dbConnect.php";
try {
    $qry = "INSERT INTO task(name, taskListID, deadline, priority) VALUES (:name, :taskListID, :deadline, :priority);";
    $stmt = $conn->prepare($qry);

    $stmt->bindValue(':name', 'test2');
    $stmt->bindValue(':taskListID', 29);
    $stmt->bindValue(':deadline', NULL, PDO::PARAM_NULL);
    $stmt->bindValue(':priority', 2);

    $stmt->execute();
} catch(PDOException $e) {
    echo "Error : ".$e->getMessage();
}