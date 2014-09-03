<?php

    /*
     * Looks through all sql patches and makes sure we dont have multiple leaves on teh tree
     * This is to help prevent ambiguous include orders
     * If there are multiple leaves, it will output a suggested apply script that caps any dangling leaves, requiring they all run before any following this cap
     */

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
    
    $required_patches = array();
    foreach($patch_list as $patch)
    {
        $patch_requirements = get_patches_requirements(array($directory_path.$patch.'.sql')); // Get requirements for this specific patch
        $patch_requirements = array_diff($patch_requirements, array($patch)); // remove current patch from that list (shouldn't be in it to begin with)
        $required_patches = array_unique(array_merge($patch_requirements, $required_patches));
    }
    
    
    $leaves = array_values(array_diff($patch_list, $required_patches));
    
    if(count($leaves) > 1)
    {
        $new_patch = explode('-', $leaves[0])[0].date('-Y-m-d-').'cap-leaves.sql';
        $suggestion = sprintf("SELECT _v.register_patch('%s', ARRAY['%s'], NULL);", $new_patch, implode($leaves, "','"));
        
        echo sprintf("\033[31mMultiple leaves detected!\nSuggested patch: %s\n", $suggestion);
    } else {
        echo sprintf("\033[32mOnly 1 leaf detected\n");
    }