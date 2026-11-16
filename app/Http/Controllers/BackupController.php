<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use ZipArchive;
use Exception;

class BackupController extends Controller
{
    private $backupPath;
    private $mysqldumpPath;
    private $mysqlPath;

    public function __construct()
    {
        // Set time limit to 0 (unlimited) because zipping large images can take time
        set_time_limit(0);
        
        $this->backupPath = storage_path('app/backups');
        
        // Ensure the backup directory exists
        if (!File::exists($this->backupPath)) {
            File::makeDirectory($this->backupPath, 0755, true);
        }

        // Detect XAMPP paths on Windows. We will hardcode these for your local setup.
        // If you deploy later, you will update these to just 'mysqldump' and 'mysql'
        if (PHP_OS_FAMILY === 'Windows') {
            $this->mysqldumpPath = '"C:\xampp\mysql\bin\mysqldump.exe"';
            $this->mysqlPath = '"C:\xampp\mysql\bin\mysql.exe"';
        } else {
            $this->mysqldumpPath = 'mysqldump';
            $this->mysqlPath = 'mysql';
        }
    }

    public function index()
    {
        $files = File::files($this->backupPath);
        
        // Sort files by modified time (newest first)
        usort($files, function($a, $b) {
            return $b->getMTime() - $a->getMTime();
        });

        return view('admin.backup.index', compact('files'));
    }

    public function create()
    {
        try {
            $filename = 'backup-' . date('Y-m-d_H-i-s') . '.zip';
            $zipPath = $this->backupPath . '/' . $filename;
            $tempDbFile = $this->backupPath . '/temp_db.sql';

            // 1. Dump Database
            $this->dumpDatabase($tempDbFile);

            // 2. Create ZIP Archive
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                
                // Add Database dump
                $zip->addFile($tempDbFile, 'database.sql');

                // Add Public Storage Folder
                $publicPath = storage_path('app/public');
                if (File::exists($publicPath)) {
                    $this->addFolderToZip($publicPath, $zip, 'public');
                }

                $zip->close();
            } else {
                throw new Exception("Failed to create ZIP file.");
            }

            // Clean up temp DB file
            File::delete($tempDbFile);

            return back()->with('success', "Backup created successfully: {$filename}");

        } catch (Exception $e) {
            return back()->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    public function download($filename)
    {
        // SECURITY FIX: Sanitize the filename to prevent directory traversal
        $safeFilename = basename($filename);
        $filePath = $this->backupPath . '/' . $safeFilename;
        
        // Ensure the file exists AND it resolves to a path strictly inside the backup directory
        if (File::exists($filePath) && str_starts_with(realpath($filePath), realpath($this->backupPath))) {
            return response()->download($filePath);
        }

        return back()->with('error', 'Backup file not found or access denied.');
    }

    public function delete($filename)
    {
        // SECURITY FIX: Sanitize the filename to prevent directory traversal
        $safeFilename = basename($filename);
        $filePath = $this->backupPath . '/' . $safeFilename;
        
        if (File::exists($filePath) && str_starts_with(realpath($filePath), realpath($this->backupPath))) {
            File::delete($filePath);
            return back()->with('success', 'Backup deleted successfully.');
        }

        return back()->with('error', 'Backup file not found or access denied.');
    }

    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|string'
        ]);

        // SECURITY FIX: Sanitize the filename to prevent directory traversal
        $safeFilename = basename($request->backup_file);
        $zipPath = $this->backupPath . '/' . $safeFilename;

        if (!File::exists($zipPath) || !str_starts_with(realpath($zipPath), realpath($this->backupPath))) {
            return back()->with('error', 'Backup file not found or access denied.');
        }

        try {
            // 1. Safety First: Create a backup of the CURRENT state before restoring
            $this->create(); 
            
            // 2. Put application into maintenance mode
            Artisan::call('down');

            // 3. Extract ZIP to temporary folder
            $tempExtractPath = storage_path('app/temp_restore_' . time());
            $zip = new ZipArchive();
            if ($zip->open($zipPath) === TRUE) {
                $zip->extractTo($tempExtractPath);
                $zip->close();
            } else {
                throw new Exception("Failed to open ZIP file.");
            }

            // 4. Restore Database
            $sqlFile = $tempExtractPath . '/database.sql';
            if (File::exists($sqlFile)) {
                $this->restoreDatabase($sqlFile);
            } else {
                throw new Exception("Database file not found in backup archive.");
            }

            // 5. Restore Public Storage
            $extractedPublic = $tempExtractPath . '/public';
            if (File::exists($extractedPublic)) {
                $currentPublic = storage_path('app/public');
                
                // Clear current public folder (careful not to delete the folder itself)
                File::cleanDirectory($currentPublic);
                
                // Copy restored files back
                File::copyDirectory($extractedPublic, $currentPublic);
            }

            // 6. Clean up temp extract
            File::deleteDirectory($tempExtractPath);

            // 7. Turn off maintenance mode
            Artisan::call('up');

            return back()->with('success', 'System restored successfully from backup.');

        } catch (Exception $e) {
            // Make sure we turn the app back on if something failed
            Artisan::call('up');
            // Clean up temp extract just in case
            if (isset($tempExtractPath) && File::exists($tempExtractPath)) {
                File::deleteDirectory($tempExtractPath);
            }
            return back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    // --- Helper Functions ---

    private function dumpDatabase($outputFile)
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $db   = config('database.connections.mysql.database');

        $passwordString = $pass ? "--password={$pass}" : "";

        // Command to dump database
        $command = "{$this->mysqldumpPath} --user={$user} {$passwordString} --host={$host} --port={$port} --single-transaction --quick {$db} > \"{$outputFile}\"";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception("Database dump failed. Check your XAMPP MySQL path.");
        }
    }

    private function restoreDatabase($sqlFile)
    {
        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $db   = config('database.connections.mysql.database');

        $passwordString = $pass ? "--password={$pass}" : "";

        // Command to import database
        $command = "{$this->mysqlPath} --user={$user} {$passwordString} --host={$host} --port={$port} {$db} < \"{$sqlFile}\"";
        
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new Exception("Database restore failed. Check your XAMPP MySQL path.");
        }
    }

    private function addFolderToZip($folder, &$zipFile, $exclusiveLength)
    {
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before adding to ZIP
                $localPath = substr($filePath, strlen($exclusiveLength) + 1);
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory recursively
                    $zipFile->addEmptyDir($localPath);
                    $this->addFolderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }
}