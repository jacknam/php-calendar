<?php 
/*
 * Copyright 2009 Sean Proctor
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

if ( !defined('IN_PHPC') ) {
       die("Hacking attempt");
}

function cadmin()
{
	global $phpcid;

        if(!can_admin_calendar($phpcid)) {
                soft_error(_('You must be logged in as an admin.'));
        }

	return array(config_form(), user_list());
}

function config_form()
{
	global $phpcid, $phpc_script, $phpcdb, $vars;

        $tbody = tag('tbody');
	$config = $phpcdb->get_calendar_config($phpcid);

        foreach(get_config_options() as $element) {
                $name = $element[0];
                $text = $element[1];
                $type = $element[2];

                switch($type) {
                        case PHPC_CHECK:
                                $input = create_checkbox($name, '1',
                                                $config[$name]);
                                break;
                        case PHPC_TEXT:
                                $input = create_text($name, $config[$name]);
                                break;
                        case PHPC_DROPDOWN:
                                $sequence = create_sequence(0,
                                                count($element[3]) - 1);
                                $input = create_select($name, $element[3],
                                                $config[$name], $sequence);
                                break;
                        default:
                                soft_error(_('Unsupported config type')
                                                . ": $type");
                }

                $tbody->add(tag('tr',
                                tag('th', $text),
                                tag('td', $input)));
        }

	$hidden_div = tag('div',
			create_hidden('action', 'options_submit'));
	if(isset($vars['phpcid']))
		$hidden_div->add(create_hidden('phpcid', $vars['phpcid']));
		
        return tag('form', attributes("action=\"$phpc_script\"",
                                'method="post"'),
			$hidden_div,
			tag('table', attributes("class=\"phpc-container\""),
				tag('caption', _('Options')),
				tag('tfoot',
                                        tag('tr',
                                                tag('td', attributes('colspan="2"'),
							create_submit(_('Submit'))))),
				$tbody));

}

function user_list()
{
	global $phpc_script, $phpcid, $phpcdb, $vars;

	$users = $phpcdb->get_users_with_permissions($phpcid);

	$tbody = tag('tbody');

	foreach ($users as $user) {
		$tbody->add(tag('tr',
					tag('th', $user['username'],
						create_hidden('uid[]',
							$user['uid'])),
					tag('td', create_checkbox("read{$user['uid']}", "1", !empty($user['read']))),
					tag('td', create_checkbox("write{$user['uid']}", "1", !empty($user['write']))),
					tag('td', create_checkbox("readonly{$user['uid']}", "1", !empty($user['readonly']))),
					tag('td', create_checkbox("modify{$user['uid']}", "1", !empty($user['modify']))),
					tag('td', create_checkbox("admin{$user['uid']}", "1", !empty($user['admin'])))
				   ));
	}

	$hidden_div = tag('div',
			create_hidden('action', 'user_permissions_submit'));
	if(isset($vars['phpcid']))
		$hidden_div->add(create_hidden('phpcid', $vars['phpcid']));
		
	return tag('form', attributes("action=\"$phpc_script\"",
                                'method="post"'),
			$hidden_div,
			tag('table', attributes("class=\"phpc-container\""),
				tag('caption', _('User Permissions')),
				tag('tfoot',
					tag('tr',
						tag('td', attributes('colspan="6"'),
							create_submit(_('Submit'))))),
				tag('thead',
					tag('tr',
						tag('th', _('User Name')),
						tag('th', _('Read')),
						tag('th', _('Write')),
						tag('th', _('Can Create Read-Only')),
						tag('th', _('Modify')),
						tag('th', _('Admin')))),
				$tbody));
}

function tag_list()
{
	global $phpc_script, $phpcid, $phpcdb, $vars;

	$tags = $phpcdb->get_tags($phpcid);

	$tbody = tag('tbody');

	foreach ($tags as $tag) {
		$name = empty($tag['name']) ? _('No Name') : $tag['name'];
		$tid = $tag['tid'];
		$tbody->add(tag('tr',
					tag('th',
						create_action_link($name,
							'tag_form',
							array('tid' => $tid)),
						create_action_link(_('Delete'),
							'tag_delete',
							array('tid' => $tid))
					   ),
					tag('td', $tag['text_color']),
					tag('td', $tag['bg_color'])
				   ));
	}

	return tag('table', attributes("class=\"phpc-container\""),
			tag('caption', _('Calendar Tags')),
			tag('thead',
				tag('tr',
					tag('th', _('Name')),
					tag('th', _('Text Color')),
					tag('th', _('Background Color'))
				   )),
			$tbody);
}

?>
