<?php
$viewdefs['Products'] =
array (
  'base' =>
  array (
    'view' =>
    array (
      'record' =>
      array (
        'panels' =>
        array (
          0 =>
          array (
            'name' => 'panel_header',
            'header' => true,
            'fields' =>
            array (
              0 =>
              array (
                'name' => 'picture',
                'type' => 'avatar',
                'size' => 'large',
                'dismiss_label' => true,
                'readonly' => true,
              ),
              1 =>
              array (
                'name' => 'name',
                'link' => false,
                'label' => 'LBL_MODULE_NAME_SINGULAR',
              ),
              2 =>
              array (
                'name' => 'favorite',
                'label' => 'LBL_FAVORITE',
                'type' => 'favorite',
                'dismiss_label' => true,
              ),
              3 =>
              array (
                'name' => 'follow',
                'label' => 'LBL_FOLLOW',
                'type' => 'follow',
                'readonly' => true,
                'dismiss_label' => true,
              ),
            ),
          ),
          1 =>
          array (
            'name' => 'panel_body',
            'label' => 'LBL_RECORD_BODY',
            'columns' => 2,
            'labels' => true,
            'labelsOnTop' => true,
            'placeholders' => true,
            'newTab' => true,
            'panelDefault' => 'expanded',
            'fields' =>
            array (
              array (
                'name' => 'type_id',
                'label' => 'LBL_TYPE',
              ),
              array (
                'name' => 'category_id',
                'label' => 'LBL_CATEGORY_NAME',
              ),
            ),
          ),
        ),
        'templateMeta' =>
        array (
          'maxColumns' => '2',
          'useTabs' => true,
        ),
      ),
    ),
  ),
);
