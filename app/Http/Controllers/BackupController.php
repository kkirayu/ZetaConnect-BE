<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class BackupController extends Controller
{
    public function download()
    {
        $tables = DB::select('SHOW TABLES');
        $dbName = env('DB_DATABASE');
        $property = "Tables_in_" . $dbName;

        $sql = "-- Zeta Connect Database Backup\n";
        $sql .= "-- Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";

        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableRow) {
            $table = $tableRow->$property ?? (array_values((array)$tableRow)[0]);

            // Get Create Table Syntax
            $createTable = DB::select("SHOW CREATE TABLE `$table`");
            $createKey = 'Create Table';
            $sql .= "-- Table structure for table `$table`\n";
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $sql .= $createTable[0]->$createKey . ";\n\n";

            // Get Rows
            $rows = DB::select("SELECT * FROM `$table`");
            if (count($rows) > 0) {
                $sql .= "-- Dumping data for table `$table`\n";
                foreach ($rows as $row) {
                    $values = [];
                    foreach ((array)$row as $value) {
                        if (is_null($value)) {
                            $values[] = "NULL";
                        } else {
                            // Escape quotes and backslashes
                            $value = addslashes($value);
                            // Convert newlines
                            $value = str_replace("\n", "\\n", $value);
                            $value = str_replace("\r", "\\r", $value);
                            $values[] = "'" . $value . "'";
                        }
                    }
                    $sql .= "INSERT INTO `$table` VALUES (" . implode(", ", $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = "backup_zeta_connect_" . now()->format('Y_m_d_His') . ".sql";

        return Response::make($sql, 200, [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
