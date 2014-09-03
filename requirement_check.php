<?php

    require_once('common.php');
    
    if(empty($argv[1])) // Get sql directory from CLI or default
    {
        $directory_path = dirname(__FILE__).'/../../../sql/active/';
    } else {
        $directory_path=$argv[1];
    }
    
    $patch_list = get_available_patch_list($directory_path); //Get list of active patches
    
    $trimmed_patch_list = array();
    foreach($patch_list as $patch)
    {
        $trimmed_patch_list[] = substr($patch, 0, -4); // remove last 4 chars - hopefully '.sql'
    }
    $patch_list = $trimmed_patch_list;
    
    $bad_patches = array();
    foreach($patch_list as $patch)
    {
        $patch_requirements = get_patches_requirements(array($directory_path.$patch.'.sql')); // Get requirements for this specific patch
        if(count($patch_requirements)<=1) $bad_patches[] = $patch;
    }
    
    if(count($bad_patches) >= 1)
    {
        echo sprintf("\033[31mThe following patches need a requirement section:\n%s\n", implode($bad_patches, ' '));
    } else {
        echo sprintf("\033[32mNo bad patches found\n");
    }