<?php
// Operator autoloading
$eZTemplateOperatorArray   = array();
$eZTemplateOperatorArray[] = array( 'script' => dirname(__FILE__) . '/show_variables.php',
                                    'class' => 'showVariables',
                                    'operator_names' => array('show_variables'));
?>