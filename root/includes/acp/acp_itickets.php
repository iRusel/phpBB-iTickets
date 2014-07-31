<?php

class acp_itickets
{
   var $u_action;
   
   function main($id, $mode)
   {
      global $db, $user, $auth, $template;
      global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

      add_form_key('itickets_form');
      $submit = (isset($_POST['submit'])) ? true : false;

      switch($mode)
      {
         case 'index':
         {
            $this->page_title = 'ACP_ITICKETS';
            $this->tpl_name = 'acp_itickets';

            if ($submit)
            {
               if (!check_form_key('itickets_form'))
               {
                  trigger_error('FORM_INVALID', E_USER_WARNING);
               }

               set_config('itickets_enable', request_var('it_enable', 0));
               set_config('itickets_num_tp', request_var('it_numtp', 15));
               set_config('itickets_num_cp', request_var('it_numcp', 5));

               trigger_error($user->lang['ACP_ITICKETS_SETTINGS_UPDATE'] . adm_back_link($this->u_action));
            }

            $template->assign_vars(array(
               'IT_ENABLE'          => $config['itickets_enable'],
               'IT_NUM_TP'          => $config['itickets_num_tp'],
               'IT_NUM_CP'          => $config['itickets_num_cp'],
               'U_ACTION'           => $this->u_action,
            ));
            break;
         }
      }
   }
}
?>