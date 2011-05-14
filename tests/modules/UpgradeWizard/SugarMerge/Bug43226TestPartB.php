<?php
require_once 'include/dir_inc.php';

class Bug43226TestPartB extends Sugar_PHPUnit_Framework_TestCase  {
	
var $merge;

function setUp() {
    SugarTestMergeUtilities::setupFiles(array('Documents'), array('detailviewdefs'), 'tests/modules/UpgradeWizard/SugarMerge/metadata_files');
}


function tearDown() {
   SugarTestMergeUtilities::teardownFiles();
}
function test_uploadfile_convert_merge_610() {
   require_once 'modules/UpgradeWizard/SugarMerge/DetailViewMerge.php';
   $this->merge = new DetailViewMerge();
   $this->merge->merge('Documents', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/610/modules/Documents/metadata/detailviewdefs.php','modules/Documents/metadata/detailviewdefs.php','custom/modules/Documents/metadata/detailviewdefs.php');

   require('custom/modules/Documents/metadata/detailviewdefs.php');

   $foundUploadFile = 0;
   $foundFilename = 0;

   foreach ( $viewdefs['Documents']['DetailView']['panels'] as $panel ) {
       foreach ( $panel as $row ) {
           foreach ( $row as $col ) {
               if ( is_array($col) ) {
                   $fieldName = $col['name'];
               } else {
                   $fieldName = $col;
               }
               
               if ( $fieldName == 'filename' ) {
                   $foundFilename++;
               } else if ( $fieldName == 'uploadfile' ) {
                   $foundUploadFile++;
               }
           }
       }
   }
   
   $this->assertTrue($foundUploadFile==0,'Uploadfile field still exists, should be filename');
   $this->assertTrue($foundFilename>0,'Filename field doesn\'t exit, it should');

   if ( file_exists('custom/modules/Documents/metadata/detailviewdefs-testback.php') ) {
       copy('custom/modules/Documents/metadata/detailviewdefs-testback.php','custom/modules/Documents/metadata/detailviewdefs.php');
       unlink('custom/modules/Documents/metadata/detailviewdefs-testback.php');
   }
}

function test_uploadfile_convert_merge_600() {
   require_once 'modules/UpgradeWizard/SugarMerge/DetailViewMerge.php';
   $this->merge = new DetailViewMerge();
   $this->merge->merge('Documents', 'tests/modules/UpgradeWizard/SugarMerge/metadata_files/600/modules/Documents/metadata/detailviewdefs.php','modules/Documents/metadata/detailviewdefs.php','custom/modules/Documents/metadata/detailviewdefs.php');

   require('custom/modules/Documents/metadata/detailviewdefs.php');

   $foundUploadFile = 0;
   $foundFilename = 0;

   foreach ( $viewdefs['Documents']['DetailView']['panels'] as $panel ) {
       foreach ( $panel as $row ) {
           foreach ( $row as $col ) {
               if ( is_array($col) ) {
                   $fieldName = $col['name'];
               } else {
                   $fieldName = $col;
               }
               
               if ( $fieldName == 'filename' ) {
                   $foundFilename++;
               } else if ( $fieldName == 'uploadfile' ) {
                   $foundUploadFile++;
               }
           }
       }
   }
   
   $this->assertTrue($foundUploadFile==0,'Uploadfile field still exists, should be filename');
   $this->assertTrue($foundFilename>0,'Filename field doesn\'t exit, it should');

   if ( file_exists('custom/modules/Documents/metadata/detailviewdefs-testback.php') ) {
       copy('custom/modules/Documents/metadata/detailviewdefs-testback.php','custom/modules/Documents/metadata/detailviewdefs.php');
       unlink('custom/modules/Documents/metadata/detailviewdefs-testback.php');
   }
}

}