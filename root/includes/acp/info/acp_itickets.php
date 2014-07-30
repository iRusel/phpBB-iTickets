<?php

/*
   File: acp_itickets.php
   Location: includes/acp/info/acp_itickets.php
   Last edit: 27.07.2014
*/

class acp_itickets_info
{
    function module()
    {
        return array(
            'filename'      => 'acp_itickets',
            'title'         => 'ACP_ITICKETS',
            'version'       => '0.0.3',
            'modes'         => array(
            'index'         => array('title' => 'ACP_ITICKETS_INDEX_TITLE', 'auth' => 'acl_a_board', 'cat' => array('')),
            ),
        );
    }
}

?>