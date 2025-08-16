<?php
// Define the directory to scan (current directory)
$dir = __DIR__;

// Function to fix files recursively
function fixFilesInDirectory($dir) {
    $files = scandir($dir);
    $count = 0;
    
    foreach ($files as $file) {
        // Skip . and .. directories
        if ($file === '.' || $file === '..') {
            continue;
        }
        
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        
        // If directory, recurse into it
        if (is_dir($path)) {
            $count += fixFilesInDirectory($path);
        } 
        // Process PHP files
        elseif (pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $content = file_get_contents($path);
            
            // Check for username prefix
            if (strpos($content, 'zukalutoka<?php') === 0) {
                // Replace the prefix
                $newContent = str_replace('zukalutoka<?php', '<?php', $content);
                
                // Write the fixed content back
                file_put_contents($path, $newContent);
                
                echo "Fixed: {$path}<br>";
                $count++;
            }
        }
    }
    
    return $count;
}

// Run the fix
$totalFixed = fixFilesInDirectory($dir);
echo "<p>Total {$totalFixed} files fixed.</p>";
echo "<p>✓ PREFIX REMOVAL COMPLETE!</p>";
?>