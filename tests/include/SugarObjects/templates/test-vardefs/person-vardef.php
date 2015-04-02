<?php
$vardefs['fields']['customField'] = array(
	'name' => 'customField',
	'type' => 'varchar',
	'len' => '100',
	'unified_search' => true,
	'duplicate_on_record_copy' => 'always',
	'full_text_search' => array('enabled' => true, 'boost' => 3),
	'comment' => 'customTestField',
	'merge_filter' => 'selected',
);
