<?php
echo "<h2>Fixing Directory Permissions</h2>";

// Use absolute paths that should work with XAMPP
$project_root = '/Applications/XAMPP/xamppfiles/htdocs/findahotell/';
$temp_dir = $project_root . 'temp_uploads/';
$upload_dir = $project_root . 'uploads/hotels/';

echo "Project root: $project_root<br>";
echo "Project root exists: " . (is_dir($project_root) ? 'Yes' : 'No') . "<br>";
echo "Project root writable: " . (is_writable($project_root) ? 'Yes' : 'No') . "<br><br>";

// Try to create directories with different methods
echo "Attempting to create directories...<br>";

// Method 1: Using shell command if possible
$output1 = shell_exec("mkdir -p \"$temp_dir\" 2>&1");
$output2 = shell_exec("mkdir -p \"$upload_dir\" 2>&1");

echo "Shell command output for temp: " . ($output1 ?: 'No output') . "<br>";
echo "Shell command output for upload: " . ($output2 ?: 'No output') . "<br>";

// Method 2: Using PHP mkdir
if (!is_dir($temp_dir)) {
    if (@mkdir($temp_dir, 0755, true)) {
        echo "✅ PHP created temp directory<br>";
    } else {
        echo "❌ PHP failed to create temp directory<br>";
    }
}

if (!is_dir($upload_dir)) {
    if (@mkdir($upload_dir, 0755, true)) {
        echo "✅ PHP created upload directory<br>";
    } else {
        echo "❌ PHP failed to create upload directory<br>";
    }
}

// Check final status
echo "<br>Final Status:<br>";
echo "Temp directory exists: " . (is_dir($temp_dir) ? 'Yes' : 'No') . "<br>";
echo "Temp directory writable: " . (is_writable($temp_dir) ? 'Yes' : 'No') . "<br>";
echo "Upload directory exists: " . (is_dir($upload_dir) ? 'Yes' : 'No') . "<br>";
echo "Upload directory writable: " . (is_writable($upload_dir) ? 'Yes' : 'No') . "<br>";

// Test file creation
if (is_dir($upload_dir)) {
    $test_file = $upload_dir . 'test.txt';
    if (file_put_contents($test_file, 'test')) {
        echo "✅ Can write to upload directory<br>";
        unlink($test_file);
    }
}

// Show current user and permissions
echo "<br>Current user: " . shell_exec('whoami') . "<br>";
echo "Directory listing: " . shell_exec("ls -la \"$project_root\"") . "<br>";
?>