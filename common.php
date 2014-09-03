<?php

function get_available_patch_list($directory_path)
{
    // Get list of patches in patch directory
    $directory = opendir($directory_path); //TODO - handle bad directory given?
    $available_migrations = array();
    while($entry = readdir($directory)) {
        if(in_array($entry, array(".", ".."))) continue;
        
        if(!is_file($directory_path . "/" . $entry)) continue;
        
        $matches = array();
        if(!preg_match("/^([0-9]{4}-.*)\\.sql$/", $entry, $matches)) continue;
        
        $available_migrations[] = $entry;
    }
    closedir($directory);
    sort($available_migrations, SORT_STRING);
    
    return $available_migrations;
    
}

function get_patches_requirements($patches)
{
    $script_pipes = array();
    $script = proc_open(__DIR__ . "/tools/list-dependencies-from-patches.sh " . implode(' ', $patches), array(array("pipe", "r"), array("pipe", "w"), STDOUT), $script_pipes);
    $tsort_pipes = array();
    $tsort = proc_open("tsort", array($script_pipes[1], array("pipe", "w"), STDOUT), $tsort_pipes);
    fclose($script_pipes[0]);
    $sorted_migrations = array_reverse(explode("\n", stream_get_contents($tsort_pipes[1])));
    fclose($script_pipes[1]);
    fclose($tsort_pipes[1]);
    proc_close($script);
    proc_close($tsort);
    return $sorted_migrations;   
}