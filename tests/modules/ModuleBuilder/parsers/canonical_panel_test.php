<?php
/**
 * User: rbacon
 * Date: 2012.04.03
 * Time: 16:51
 * just require this file into the unit test. and then use some iterator in the data provider.
 */
$canonicals = array(
// canonical panels
    array(array(
        'fields' => array(),
        'name' => 0,
        'label' => 0,
        'columns' => 2,
        'labelsOnTop' => 1,
        'placeholders' => 1,
    )),

    array(array(
        'fields' => array(
            array(
                'name' => 'name',
                'label' => 'LBL_NAME',
            ),
            array(
                'name' => 'status',
                'label' => 'LBL_STATUS',
                'comment' => 'Status of the lead'
            ),
            array(
                'name' => 'description',
                'label' => 'LBL_DESCRIPTION',
                'comment' => 'Full text of the note',
            ),
            array()
        ),
        'name' => 0,
        'label' => '0',
        'columns' => 2,
        'labelsOnTop' => 1,
        'placeholders' => 1,
    )),


    array(array(
        'fields' => array(
            array(
                'name' => 'name',
                'label' => 'LBL_NAME',
                'span' => 12
            ),
            array(
                'name' => 'status',
                'label' => 'LBL_STATUS',
                'comment' => 'Status of the lead'
            ),
            array(
                'name' => 'description',
                'label' => 'LBL_DESCRIPTION',
                'comment' => 'Full text of the note'
            ),

        ),
        'name' => 0,
        'label' => '0',
        'columns' => 2,
        'labelsOnTop' => 1,
        'placeholders' => 1,
    )),
    array(array(
        'fields' => array(
            'name',
            'status',
            array(
                'name' => 'description',
            ),
            array()
        ),
        'name' => 0,
        'label' => '0',
        'columns' => 2,
       'labelsOnTop' => 1,
        'placeholders' => 1,
    )),
    array(array(
        'fields' => array(
            array(),
            array()
        ),
        'name' => 0,
        'label' => '0',
        'columns' => 2,
        'labelsOnTop' => 1,
        'placeholders' => 1,
    )),
// end
);
