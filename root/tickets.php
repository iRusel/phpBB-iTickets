<?php
/**
*
* @package iTickets
* @author iRusel www.irusel.com
* @version 0.0.2
* @copyright (c) 2014 iRusel www.irusel.com
*
*/

/**
 * @ignore
 */
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include_once($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/itickets');   

$action = request_var('action', '');
$start = request_var('start', 0);
$tid = request_var('tid', 0);
$sql_where = '';
$result = ''; 

switch ($action) 
{
  case '':
  {
        $pagination_url = append_sid("{$phpbb_root_path}tickets.{$phpEx}");

        if (!$user->data['is_registered'])
        {
            if ($user->data['is_bot'])
            {             
                redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
            }          
            login_box('', $user->lang['TICKETS_AUTH']);
        }

        if($user->data['group_id'] != 5)
        {
          $sql = 'SELECT ticket_id, ticket_name, ticket_small, ticket_text, answers, user_status, admin_status, bbcode_bitfield, bbcode_uid, time, last_answer FROM '.ITICKETS_TABLE.' WHERE author_id = '.$user->data['user_id'].' ORDER BY user_status ASC, last_answer DESC';        
        }
        else if($user->data['group_id'] == 5)
        {
          $sql = 'SELECT a.ticket_id, a.author_id, a.ticket_name, a.ticket_small, a.ticket_text, a.answers, a.user_status, a.admin_status, a.bbcode_bitfield, a.bbcode_uid, a.time, u.username, u.user_colour FROM ' . ITICKETS_TABLE . ' a LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = a.author_id ' .$sql_where. '
                  ORDER BY a.admin_status ASC, a.time ASC';        
        }

        $result = $db->sql_query_limit($sql, 15, $start);
        while ($row = $db->sql_fetchrow($result))
        {
            $row['ticket_text'] = trim_text($row['ticket_text'], $row['bbcode_uid'], $config['blog_max_chars'], $config['blog_max_par'], array(' ', "\n"), '...', $row['bbcode_bitfield'], true);
            $row['bbcode_options'] = 7;
            $row['ticket_text'] = generate_text_for_display($row['ticket_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);

            $template->assign_block_vars('tickets', array(
            'TID'             => $row['ticket_id'],
            'AUTHOR'          => ($user->data['group_id'] == 5) ? (get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'])):(0),  
            'TITLE'           => $row['ticket_name'],
            'SMALLT'          => $row['ticket_small'],
            'TEXT'            => $row['ticket_text'],
            'TIME'            => $user->format_date($row['time']),
            'ANSWERS'         => $row['answers'],
            'STATUS'          => ($user->data['group_id'] == 5) ? ($row['admin_status']):($row['user_status']),
            'U_MORE'          => append_sid("{$phpbb_root_path}tickets.{$phpEx}", 'action=view&amp;tid='.$row['ticket_id']),
          ));  
        }
        $db->sql_freeresult($result);

        $template->assign_vars(array(
          'S_USER_TICKETS'           => ($user->data['group_id'] == 5) ? (0):(1),          
        ));
        $page_title = $user->lang['TICKETS_TICKETS'];

        $template->assign_block_vars('navlinks', array(
          'FORUM_NAME'    => $user->lang['TICKETS_TICKETS'],
          'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}tickets.{$phpEx}"),
        ));

        if($user->data['group_id'] != 5)
        {
          $sql = 'SELECT COUNT(ticket_id) as total_tickets FROM ' . ITICKETS_TABLE . ' WHERE author_id = '.$user->data['user_id'];
        }
        else if($user->data['group_id'] == 5)
        {
          $sql = 'SELECT COUNT(a.ticket_id) as total_tickets FROM ' . ITICKETS_TABLE . ' a ' . $sql_where;
        }
        $db->sql_query($sql);
        $total_tickets = $db->sql_fetchfield('total_tickets');
        $db->sql_freeresult($result);

        $template->assign_vars(array(
          'PAGINATION'     => generate_pagination($pagination_url, $total_tickets, 15, $start),
          'PAGE_NUMBER'    => on_page($total_tickets, 15, $start),
          'TOTAL_TICKETS' => ($total_tickets == 1) ? $user->lang['TICKETS_LIST_ARTICLE'] : sprintf($user->lang['TICKETS_LIST_ARTICLES'], $total_tickets),
        ));
        break;
    }
    case 'view': 
    {
        if (!$tid)
        {
          trigger_error('TICKETS_NO_SELECTED');
        }

        if($user->data['group_id'] != 5)
        {
            $sql = 'SELECT author_id, ticket_name, ticket_small, ticket_text, answers, user_status, bbcode_bitfield, bbcode_uid, time, last_answer FROM ' . ITICKETS_TABLE . '                 
                WHERE author_id = '.$user->data['user_id'].' ORDER BY time DESC';
        }
        else if($user->data['group_id'] == 5)
        {
             $sql = 'SELECT a.author_id, a.ticket_name, a.ticket_small, a.ticket_text, a.answers, a.admin_status, a.bbcode_bitfield, a.bbcode_uid, a.time, a.last_answer, u.username, u.user_colour
              FROM ' . ITICKETS_TABLE . ' a
                LEFT JOIN ' . USERS_TABLE . ' u
                  ON u.user_id = a.author_id                
                WHERE a.ticket_id = ' . $tid;
        }

        $result = $db->sql_query($sql);
        $row = $db->sql_fetchrow($result);

        if($user->data['user_id'] != $row['author_id'] && ($user->data['group_id'] != 5))
        {
          trigger_error('TICKETS_NO_ACCESS');
        }       

        $submit = (isset($_POST['submit'])) ? true : false;
        if ($submit)
        {
          $text = utf8_normalize_nfc(request_var('t_comment', '', true));

          $error = array();  
          if(!$text)
          {
            $error[] = $user->lang['TICKETS_MISSING_ERROR'];
            $err = 1;
          }
          $template->assign_vars(array(                  
            'ERROR'               => (sizeof($error)) ? implode('<br />', $error) : '',            
          ));
          if($err != 1)
          {
            $sql_ary = (array(   
              'ticket_id'       => $tid,
              'author_id'       => $user->data['user_id'],
              'uip'             => $user->data['user_ip'],
              'ticket_author'   => ($row['author_id'] == $user->data['user_id']) ? (1):(0),
              'text'            => $text,
              'time'            => time(),
            ));
            $sql = 'INSERT INTO ' . ITICKETS_TABLE_COMMENTS . $db->sql_build_array('INSERT', $sql_ary);
            $db->sql_query($sql);

            if($user->data['group_id'] != 5)
            {
              $sql = 'UPDATE '. ITICKETS_TABLE .' SET answers = answers+1, user_status = 2, admin_status = 1, last_answer = '.time().' WHERE ticket_id = '.$tid;
            }
            else if($user->data['group_id'] == 5)
            {
              $sql = 'UPDATE '. ITICKETS_TABLE .' SET answers = answers+1, user_status = 1, admin_status = 2, last_answer = '.time().' WHERE ticket_id = '.$tid;
            }
            $db->sql_query($sql);

            //
            include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
            $messenger = new messenger(false);
            $messenger->template('itickets_nc', $user->data['user_lang']);
            $messenger->to($user->data['user_email'], $user->data['username']);
            $messenger->anti_abuse_headers($config, $user);

            $messenger->assign_vars(array(
              'USERNAME'      => htmlspecialchars_decode($user->data['username']),
              'T_ID'          => $tid,
              'T_LINK'        => generate_board_url() . "/tickets.$phpEx?action=view&tid=$tid",              
            ));
            $messenger->send(NOTIFY_EMAIL);
            //

            meta_refresh(3, append_sid("{$phpbb_root_path}tickets.$phpEx", 'action=view&amp;tid='.$tid));
            $message = $user->lang['TICKETS_SENDERS'] . '<br /><br />' . sprintf($user->lang['TICKETS_VIEW'], '<a href="' . $phpbb_root_path.'tickets.'.$phpEx.'?action=view&amp;tid='.$tid . '">', '</a>');
            trigger_error($message);
          }
        } 

        $row['ticket_text'] = trim_text($row['ticket_text'], $row['bbcode_uid'], $config['blog_max_chars'], $config['blog_max_par'], array(' ', "\n"), '...', $row['bbcode_bitfield'], true);
        $row['bbcode_options'] = 7;
        $row['ticket_text'] = generate_text_for_display($row['ticket_text'], $row['bbcode_uid'], $row['bbcode_bitfield'], $row['bbcode_options']);

        if($row['user_status'] == 3 || $row['user_status'] == 4 || $row['admin_status'] == 3 || $row['admin_status'] == 4) $close = 1;

        $template->assign_vars(array(
          'T_ID'                       => $tid,
          'S_USER_TICKETS'             => ($user->data['group_id'] == 5) ? (0):(1),
          'T_AUTHOR'                   => ($user->data['group_id'] == 5) ? (get_username_string('full', $row['author_id'], $row['username'], $row['user_colour'])):(0),
          'T_TIME'                     => $user->format_date($row['time']),
          'T_L_TIME'                   => $user->format_date($row['last_answer']),
          'T_ANSWERS'                  => $row['answers'],
          'T_STATUS'                   => ($user->data['group_id'] == 5) ? ($row['admin_status']):($row['user_status']),
          'T_TITLE'                    => $row['ticket_name'],
          'T_SMTEXT'                   => $row['ticket_small'],
          'T_FULLT'                    => $row['ticket_text'],
        ));
        $db->sql_freeresult($result);

        $sql = 'SELECT comment_id, ticket_id, a.author_id, ticket_author, text, time, uip, u.username, u.user_colour FROM '. ITICKETS_TABLE_COMMENTS .' a
          LEFT JOIN '. USERS_TABLE .' u ON u.user_id = a.author_id WHERE ticket_id = '.$tid.' ORDER BY time DESC';
        $resultes = $db->sql_query_limit($sql, 5, $start);

        while($rowes = $db->sql_fetchrow($resultes))
        {
            $template->assign_block_vars('comments', array(
            'CID'             => $rowes['comment_id'],
            'CI_AUTHOR'       => ($rowes['ticket_author'] == 1) ? (1):(0),
            'AUTHOR'          => get_username_string('full', $rowes['author_id'], $rowes['username'], $rowes['user_colour']),
            'TIME'            => $user->format_date($rowes['time']),
            'TEXT'            => $rowes['text'],
            'UIP'             => $rowes['uip'],
          ));  
        }        
        $db->sql_freeresult($result);

        $pagination_url = append_sid("{$phpbb_root_path}tickets.$phpEx", 'action=view&amp;tid='.$tid);

        $sql = 'SELECT COUNT(a.comment_id) as total_comments FROM ' . ITICKETS_TABLE_COMMENTS . ' a WHERE a.ticket_id = ' . $tid;
        $db->sql_query($sql);
        $total_comments = $db->sql_fetchfield('total_comments');
        $db->sql_freeresult($result);

        $template->assign_vars(array(
          'PAGINATION'        => generate_pagination($pagination_url, $total_comments, 5, $start),
          'PAGE_NUMBER'       => on_page($total_comments, 5, $start),
          'TOTAL_COMMENTS'    => ($total_comments == 1) ? $user->lang['TICKETS_LIST_COMMENT'] : sprintf($user->lang['TICKETS_LIST_COMMENTS'], $total_comments),
        ));

        $page_title = $user->lang['TICKETS_TICKETS_ID']. $tid;
        $template->assign_block_vars('navlinks', array(
          'FORUM_NAME'    => $user->lang['TICKETS_TICKETS'],
          'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}tickets.{$phpEx}"),
        ));

        $template->assign_block_vars('navlinks', array(
          'FORUM_NAME'    => $user->lang['TICKETS_TICKETS_ID'].$tid,
          'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}tickets.{$phpEx}", 'action=view&amp;tid='.$tid),
        ));
        break;
    }
    case 'create':
    {
        if ($user->data['user_id'] == ANONYMOUS)
        {
          login_box();
        }

        $user->add_lang('posting');
        include_once($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
        include_once($phpbb_root_path . 'includes/functions_display.' . $phpEx);        
        display_custom_bbcodes();        
        generate_smilies('inline', 0); 
        $uid = $bitfield = $options = '';       
        $submit = (isset($_POST['submit'])) ? true : false;

        if ($submit)
        {
          $title = utf8_normalize_nfc(request_var('ticket_name', '', true));
          $tsm = utf8_normalize_nfc(request_var('ticket_small', '', true));
          $text = utf8_normalize_nfc(request_var('ticket_message', '', true));

          $error = array();  
          if(!$title ||!$tsm || !$text)
          {
            $error[] = $user->lang['TICKETS_MISSING_ERROR'];
          }

          $template->assign_vars(array(                  
            'ERROR'               => (sizeof($error)) ? implode('<br />', $error) : '',
            'TICKET_NAME'         => $title,
            'TICKET_SMALL'        => $tsm,
            'TICKET_TEXT'         => $text,
          ));

          if(!sizeof($error))
          {
            generate_text_for_storage($text, $uid, $bitfield, $options, true, true, true);

            $sql_ary = (array(              
              'author_id'       => $user->data['user_id'],
              'ticket_name'     => $title,
              'ticket_small'    => $tsm,
              'ticket_text'     => $text,
              'user_status'     => 2,
              'admin_status'    => 1,
              'bbcode_bitfield' => $bitfield,
              'bbcode_uid'      => $uid,
              'time'            => time(),              
              'last_answer'     => time(),
            ));

            $sql = 'INSERT INTO ' . ITICKETS_TABLE . $db->sql_build_array('INSERT', $sql_ary);
            $db->sql_query($sql);

            $sql = 'SELECT ticket_id FROM ' . ITICKETS_TABLE . ' ORDER BY ticket_id DESC';
            $db->sql_query_limit($sql, 1);
            $tid = $db->sql_fetchfield('ticket_id'); 

            include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
            $messenger = new messenger(false);
            $messenger->template('itickets_nt', $user->data['user_lang']);
            $messenger->to($user->data['user_email'], $user->data['username']);
            $messenger->anti_abuse_headers($config, $user);

            $messenger->assign_vars(array(
              'USERNAME'      => htmlspecialchars_decode($user->data['username']),
              'T_ID'          => $tid,
              'T_LINK'        => generate_board_url() . "/tickets.$phpEx?action=view&tid=$tid",
              'T_CLOSE_LINK'  => generate_board_url() . "/tickets.$phpEx?action=close&tid=$tid",
            ));
            $messenger->send(NOTIFY_EMAIL);

            meta_refresh(3, append_sid("{$phpbb_root_path}tickets.$phpEx", 'action=view&amp;tid='.$tid));
            $message = $user->lang['TICKETS_CREATE_SUCCESS'] . '<br /><br />' . sprintf($user->lang['TICKETS_VIEW'], '<a href="' . $phpbb_root_path.'tickets.'.$phpEx.'?action=view&amp;tid='.$tid . '">', '</a>');
            trigger_error($message);
          }
        }

        $page_title = $user->lang['TICKETS_CREATE'];       
        $template->assign_block_vars('navlinks', array(
          'FORUM_NAME'    => $user->lang['TICKETS_TICKETS'],
          'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}tickets.{$phpEx}"),
        ));

        $template->assign_block_vars('navlinks', array(
          'FORUM_NAME'    => $user->lang['TICKETS_CREATE'],
          'U_VIEW_FORUM'  => append_sid("{$phpbb_root_path}tickets.{$phpEx}", 'action=create'),
        ));
        break;
    }
    case 'close':
    {
        $sql_ary = (array(              
          'user_status'     => ($user->data['group_id'] == 5) ? (4):(3),
          'admin_status'    => ($user->data['group_id'] == 5) ? (4):(3),
        ));
        $sql = 'UPDATE ' . ITICKETS_TABLE .' SET ' .$db->sql_build_array('UPDATE', $sql_ary).' WHERE ticket_id = ' . $tid;
        $db->sql_query($sql);
        if($user->data['group_id'] == 5)
        {
          include_once($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
          $messenger = new messenger(false);
          $messenger->template('itickets_close', $user->data['user_lang']);
          $messenger->to($user->data['user_email'], $user->data['username']);
          $messenger->anti_abuse_headers($config, $user);

          $messenger->assign_vars(array(
           'USERNAME'      => htmlspecialchars_decode($user->data['username']),
           'T_ID'          => $tid,
           'T_LINK'        => generate_board_url() . "/tickets.$phpEx",
          ));
          $messenger->send(NOTIFY_EMAIL);
        }
        meta_refresh(3, append_sid("{$phpbb_root_path}tickets.$phpEx"));      
        $message = $user->lang['TICKETS_CLOSED'] . '<br /><br />' . sprintf($user->lang['TICKETS_VIEW'], '<a href="' . $phpbb_root_path.'tickets.'.$phpEx.'?action=view&amp;tid='.$tid . '">', '</a>');
        trigger_error($message);
        break;
    }
    default: redirect(append_sid("{$phpbb_root_path}tickets.$phpEx"));
}

$template->assign_vars(array(
  'S_ACTION'           => $action,
  'S_CREATE_TICKET'    => append_sid("{$phpbb_root_path}tickets.{$phpEx}", 'action=create'),
  'S_CLOSE_TICKET'     => append_sid("{$phpbb_root_path}tickets.{$phpEx}", 'action=close&amp;tid='.$tid),
  'S_CLOSED_TICKET'    => ($close == 1) ? (1):(0),
));

page_header($page_title);

$template->set_filenames(array(
  'body' => 'tickets_body.html',
));

page_footer();

function trim_text($text, $uid, $max_length, $max_paragraphs = 0, $stops = array(' ', "\n"), $replacement = '…', $bitfield = '', $enable_bbcode = true)
{
  $orig_text = $text;

  if ($enable_bbcode)
  {
    static $custom_bbcodes = array();

    // Get all custom bbcodes
    if (empty($custom_bbcodes))
    {
      global $db;

      $sql = 'SELECT bbcode_id, bbcode_tag, second_pass_match
            FROM ' . BBCODES_TABLE;
      $result = $db->sql_query($sql, 3600);

      while ($row = $db->sql_fetchrow($result))
      {
        // There can be problems only with tags having an argument
        if (substr($row['bbcode_tag'], -1, 1) == '=')
        {
          $custom_bbcodes[$row['bbcode_id']] = array('[' . $row['bbcode_tag'], ':' . $uid . ']', str_replace('$uid', $uid, $row['second_pass_match']));
        }
      }
      $db->sql_freeresult($result);
    }
  }

  $trimmed = false;

  // Paragraph trimming
  if ($max_paragraphs && $max_paragraphs < preg_match_all('#\n\s*\n#m', $text, $matches))
  {
    $find = $matches[0][$max_paragraphs - 1];
    // Grab all the matches preceeding the paragraph to trim at, finds
    // those that match the trim marker, sum them to skip over them.
    $skip = sizeof(array_intersect(array_slice($matches[0], 0, $max_paragraphs - 1), array($find)));
    $pos = 0;
    do
    {
      $pos = utf8_strpos($text, $find, $pos + 1);
      $skip--;
    } while ($skip >= 0);

    $text = utf8_substr($text, 0, $pos);

    $trimmed = true;
  }

  // First truncate the text
  if ($max_length && utf8_strlen($text) > $max_length)
  {
    $pos = 0;
    $length = 0;

    if (!is_array($stops[0]))
    {
      $stops = array($stops);
    }

    foreach ($stops as $stop_group)
    {
      if (!is_array($stop_group))
      {
        continue;
      }

      foreach ($stop_group as $k => $v)
      {
        $find = (is_string($v)) ? $v : $k;
        $include = is_bool($v) && $v;

        if (($_pos = utf8_strpos(utf8_substr($text, $max_length), $find)) !== false)
        {
          if ($_pos < $pos || !$pos)
          {
            // This is a better find, it cuts the text shorter
            $pos = $_pos;
            $length = $include ? utf8_strlen($find) : 0;
          }
        }
      }

      if ($pos)
      {
        // Include the length of the search string if requested
        $max_length += $pos + $length;
        break;
      }
    }

    // Trim off spaces, this will miss UTF8 spacers :(
    $text = rtrim(utf8_substr($text, 0, $max_length));

    $trimmed = true;
  }

  // No BBCode or no trimming return
  if (!$enable_bbcode || !$trimmed)
  {
    return $text . ($trimmed ? $replacement : '');
  }

  // Some tags may contain spaces inside the tags themselves.
  // If there is any tag that had been started but not ended
  // cut the string off before it begins.
  $unsafe_tags = array(
    array('<', '>'),
    array('[quote=&quot;', "&quot;:$uid]"), // 3rd parameter true here too for now
    );

  // If bitfield is given only check for those tags that are surely existing in the text
  if (!empty($bitfield))
  {
    // Get all used tags
    $bitfield = new bitfield($bitfield);

    // isset() provides better performance
    $bbcodes_set = array_flip($bitfield->get_all_set());

    // Add custom BBCodes having a parameter and being used
    // to the array of potential tags that can be cut apart.
    foreach ($custom_bbcodes as $bbcode_id => $bbcode_tag)
    {
      if (isset($bbcodes_set[$bbcode_id]))
      {
        $unsafe_tags[] = $bbcode_tag;
      }
    }
  }
  // Else do the check for all possible tags
  else
  {
    $unsafe_tags = array_merge($unsafe_tags, $custom_bbcodes);
  }

  foreach ($unsafe_tags as $tag)
  {
    // Ooops, we are in the middle of an opening BBCode or HTML tag,
    // truncate the string before the opening tag
    if (($start_pos = strrpos($text, $tag[0])) > strrpos($text, $tag[1]))
    {
      // Wait, is this really an opening tag or does it just look like one?
      $match = array();
      if (isset($tag[2]) && preg_match($tag[2], substr($orig_text, $start_pos), $match, PREG_OFFSET_CAPTURE) != 0 && $match[0][1] === 0)
      {
        $text = rtrim(substr($text, 0, $start_pos));
      }
    }
  }

  $text = $text . $replacement;

  // Get all of the BBCodes the text contains.
  // If it does not contain any than just skip this step.
  // Preg expression is borrowed from strip_bbcode()
  if (preg_match_all("#\[(\/?)([a-z0-9_\*\+\-]+)(?:=(&quot;.*&quot;|[^\]]*))?(?::[a-z])?(?:\:$uid)\]#", $text, $matches, PREG_PATTERN_ORDER) != 0)
  {
    $open_tags = array();

    for ($i = 0, $size = sizeof($matches[0]); $i < $size; ++$i)
    {
      $bbcode_name =& $matches[2][$i];
      $opening = ($matches[1][$i] == '/') ? false : true;

      // If a new BBCode is opened add it to the array of open BBCodes
      if ($opening)
      {
        $open_tags[] = array(
          'name'   => $bbcode_name,
          'plus'   => ($opening && $bbcode_name == 'list' && !empty($matches[3][$i])) ? ':o' : '',
        );
      }
      // If a BBCode is closed remove it from the array of open BBCodes.
      // As always only the last opened open tag can be closed,
      // so we only need to remove the last element of the array.
      else
      {
        array_pop($open_tags);
      }
    }

    // Sort open BBCode tags so the most recently opened will be the first (because it has to be closed first)
    krsort($open_tags);

    // Close remaining open BBCode tags
    foreach ($open_tags as $tag)
    {
      $text .= '[/' . $tag['name'] . $tag['plus'] . ':' . $uid . ']';
    }
  }

  return $text;
}

?>