<?php
/**
 * Import scanned_barcodes from PostgreSQL dump into MySQL
 *
 * Usage:
 *   php import_barcodes.php                          # Import all records
 *   php import_barcodes.php 2026-03-18               # Import up to a specific date
 *   php import_barcodes.php 2026-01-01 2026-03-18    # Import a date range
 *
 * Place dump.sql in the same folder as this script.
 * (Extract from dump.sql.gz first using: gunzip -k dump.sql.gz)
 *
 * What this script does:
 *   1. Reads the 'users' table from the PG dump to map UUID user_id -> employee_id
 *   2. Backs up existing scanned_barcodes to a timestamped backup table
 *   3. Deletes all existing scanned_barcodes
 *   4. Imports scanned_barcodes from the dump, mapping user_id via employee_id to staff.id
 */

$dumpFile = __DIR__ . '/dump.sql';

// Parse date arguments
$dateFrom = isset($argv[1]) && isset($argv[2]) ? $argv[1] : null;
$dateTo = isset($argv[2]) ? $argv[2] : (isset($argv[1]) ? $argv[1] : null);

if ($dateTo) {
    $dateTo .= ' 23:59:59';
}

echo "=== Scanned Barcodes Importer ===\n";
if ($dateFrom && $dateTo) {
    echo "Date range: $dateFrom to $dateTo\n";
} elseif ($dateTo) {
    echo "Importing up to: $dateTo\n";
} else {
    echo "Importing ALL records (no date filter)\n";
}

// Verify dump file exists
if (!file_exists($dumpFile)) {
    echo "ERROR: Dump file not found at $dumpFile\n";
    echo "Please extract dump.sql.gz to that location first.\n";
    exit(1);
}

$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=setuprintersDB', 'root', 'Akash243@#$');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Step 1: Build UUID -> employee_id mapping from dump
echo "\n[1/4] Building user mapping from dump...\n";
$file = fopen($dumpFile, 'r');
$inUsers = false;
$userMap = [];

while (($line = fgets($file)) !== false) {
    if (strpos($line, 'COPY setu_printers.users') === 0) {
        $inUsers = true;
        continue;
    }
    if ($inUsers) {
        if (trim($line) === '\\.') { $inUsers = false; continue; }
        $parts = explode("\t", trim($line));
        $userMap[$parts[0]] = (int)$parts[1];
    }
}
fclose($file);
echo "  Mapped " . count($userMap) . " users\n";

// Step 2: Backup existing scanned_barcodes
echo "\n[2/4] Backing up existing data...\n";
$backupTable = 'scanned_barcodes_backup_' . date('Ymd_His');
$pdo->exec("DROP TABLE IF EXISTS `$backupTable`");
$pdo->exec("CREATE TABLE `$backupTable` AS SELECT * FROM scanned_barcodes");
$backupCnt = $pdo->query("SELECT COUNT(*) as c FROM `$backupTable`")->fetch(PDO::FETCH_ASSOC);
echo "  Backup table: $backupTable ({$backupCnt['c']} records)\n";

// Step 3: Delete all scanned_barcodes
echo "\n[3/4] Clearing scanned_barcodes...\n";
$pdo->exec("DELETE FROM scanned_barcodes");
$pdo->exec("ALTER TABLE scanned_barcodes AUTO_INCREMENT = 1");
echo "  Table cleared\n";

// Step 4: Import from dump
echo "\n[4/4] Importing from dump...\n";
$file = fopen($dumpFile, 'r');
$inBarcodes = false;
$imported = 0;
$skipped = 0;
$errors = 0;

$stmt = $pdo->prepare("INSERT INTO scanned_barcodes (user_id, barcode, selfie, is_deleted, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)");

$pdo->beginTransaction();

while (($line = fgets($file)) !== false) {
    if (strpos($line, 'COPY setu_printers.scanned_barcodes') === 0) {
        $inBarcodes = true;
        continue;
    }
    if ($inBarcodes) {
        if (trim($line) === '\\.') break;
        $parts = explode("\t", $line);
        if (count($parts) < 7) { $skipped++; continue; }

        $dumpUserId = trim($parts[1]);
        $barcode = trim($parts[2]);
        $selfie = trim($parts[3]);
        $isDeleted = trim($parts[4]) === 't' ? 1 : 0;
        $createdAt = trim($parts[5]);
        $updatedAt = trim($parts[6]);

        // Apply date filters
        if ($dateFrom && $createdAt < $dateFrom) continue;
        if ($dateTo && $createdAt > $dateTo) continue;

        // Map UUID to employee_id (= staff.id)
        if (!isset($userMap[$dumpUserId])) { $skipped++; continue; }
        $staffId = $userMap[$dumpUserId];

        // Fix barcode newline (PG stores \n as literal)
        $barcode = str_replace('\n', "\n", $barcode);

        // Fix selfie path separator
        $selfie = str_replace('\\', '/', $selfie);

        // Trim microseconds for MySQL
        $createdAt = substr($createdAt, 0, 19);
        $updatedAt = substr($updatedAt, 0, 19);

        try {
            $stmt->execute([$staffId, $barcode, $selfie, $isDeleted, $createdAt, $updatedAt]);
            $imported++;
        } catch (Exception $e) {
            $errors++;
            if ($errors <= 5) echo "  Error: " . $e->getMessage() . "\n";
        }

        if ($imported % 5000 === 0 && $imported > 0) echo "  Imported $imported...\n";
    }
}
fclose($file);

$pdo->commit();

// Results
echo "\n=== IMPORT COMPLETE ===\n";
echo "Imported: $imported\n";
echo "Skipped:  $skipped\n";
echo "Errors:   $errors\n";

// Verification
$total = $pdo->query("SELECT COUNT(*) as c FROM scanned_barcodes")->fetch(PDO::FETCH_ASSOC);
$range = $pdo->query("SELECT MIN(created_at) as mn, MAX(created_at) as mx FROM scanned_barcodes")->fetch(PDO::FETCH_ASSOC);
echo "\n=== VERIFICATION ===\n";
echo "Total records: {$total['c']}\n";
echo "Date range:    {$range['mn']} to {$range['mx']}\n";
echo "Backup table:  $backupTable (drop when no longer needed)\n";

// Sample recent records
echo "\nRecent records:\n";
$samples = $pdo->query("SELECT sb.id, sb.user_id, s.full_name, sb.barcode, sb.created_at FROM scanned_barcodes sb LEFT JOIN staff s ON sb.user_id = s.id ORDER BY sb.created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($samples as $r) {
    $barcode = trim($r['barcode']);
    echo "  #{$r['id']} | staff:{$r['user_id']} ({$r['full_name']}) | $barcode | {$r['created_at']}\n";
}
