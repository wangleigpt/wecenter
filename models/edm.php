<?php
/*
+--------------------------------------------------------------------------
|   WeCenter [#RELEASE_VERSION#]
|   ========================================
|   by WeCenter Software
|   © 2011 - 2013 WeCenter. All Rights Reserved
|   http://www.wecenter.com
|   ========================================
|   Support: WeCenter@qq.com
|
+---------------------------------------------------------------------------
*/


if (!defined('IN_ANWSION'))
{
	die;
}

class edm_class extends AWS_MODEL
{
	public function fetch_groups($page = null, $limit = null)
	{
		return $this->fetch_page('edm_usergroup', null, 'id DESC', $page, $limit);
	}

	public function fetch_tasks($page, $limit)
	{
		return $this->fetch_page('edm_task', null, 'id DESC', $page, $limit);
	}

	public function add_task($title, $subject, $message, $from_name)
	{
		return $this->insert('edm_task', array(
			'title' => htmlspecialchars($title),
			'subject' => htmlspecialchars($subject),
			'message' => $message,
			'from_name' => htmlspecialchars($from_name),
			'time' => time()
		));
	}

	public function get_task_info($task_id)
	{
		return $this->fetch_row('edm_task', 'id = ' . intval($task_id));
	}

	public function calc_task_users($task_id)
	{
		return $this->count('edm_taskdata', 'taskid = ' . intval($task_id));
	}

	public function calc_task_views($task_id)
	{
		return $this->count('edm_taskdata', 'view_time > 0 AND taskid = ' . intval($task_id));
	}

	public function calc_task_sent($task_id)
	{
		return $this->count('edm_taskdata', 'sent_time > 0 AND taskid = ' . intval($task_id));
	}

	public function calc_group_users($group_id)
	{
		return $this->count('edm_userdata', 'usergroup = ' . intval($group_id));
	}

	public function remove_group($group_id)
	{
		$this->delete('edm_userdata', 'usergroup = ' . intval($group_id));
		$this->delete('edm_usergroup', 'id = ' . intval($group_id));

		return true;
	}

	public function remove_task($task_id)
	{
		$this->delete('edm_taskdata', 'taskid = ' . intval($task_id));
		$this->delete('edm_task', 'id = ' . intval($task_id));

		return true;
	}

	public function fetch_task_active_emails($task_id)
	{
		return $this->fetch_all('edm_taskdata', "view_time > 0 AND taskid = " . intval($task_id));
	}

	public function add_group($title)
	{
		return $this->insert('edm_usergroup', array(
			'title' => htmlspecialchars($title),
			'time' => time()
		));
	}

	public function set_task_view($task_id, $email)
	{
		return $this->update('edm_taskdata', array(
			'view_time' => time(),
		), "email = '" . $this->quote($email) . "'");
	}

	public function add_user_data($group_id, $email)
	{
		if (!H::valid_email($email))
		{
			return false;
		}

		if ($this->fetch_row('edm_userdata', 'usergroup = ' . intval($group_id) . " AND email = '" . $this->quote(strtolower($email)) . "'"))
		{
			return false;
		}

		return $this->insert('edm_userdata', array(
			'usergroup' => $group_id,
			'email' => strtolower($email)
		));
	}

	public function run_task()
	{
		if (!$user_list = $this->fetch_all('edm_taskdata', "`sent_time` = 0", "id ASC", 30))
		{
			return false;
		}

		foreach ($user_list AS $key => $item)
		{
			if (!$task_data[$item['taskid']]['id'])
			{
				$task_data[$item['taskid']] = $this->get_task_info($item['taskid']);
			}

			if ($task_data[$item['taskid']]['from_name'])
			{
				$from_name = $task_data[$item['taskid']]['from_name'];
			}

			$message = $task_data[$item['taskid']]['message'] . '<p><center>为确保我们的邮件不被当做垃圾邮件处理，请把 ' . get_setting('from_email') . ' 添加为你的联系人。</center></p><p><center>如果内容显示不正确, 请<a href="' . get_js_url('/account/edm/mail/' . $item['taskid']) . '">点此查看在线版</a>。<img src="' . get_js_url('/account/edm/ping/' . urlencode(base64_encode($item['email'])) . '|' . md5($item['email'] . G_SECUKEY)) . '|' . $item['taskid'] . '" alt="" width="1" height="1" /></center></p>';

			$this->update('edm_taskdata', array(
				'sent_time' => time()
			), 'id = ' . $item['id']);

			AWS_APP::mail()->send($item['email'], $task_data[$item['taskid']]['subject'], $message, $from_name, null, 'slave');
		}

		return true;
	}

	public function import_system_email_by_reputation_group($group_id, $user_group_id)
	{
		return $this->query("INSERT INTO `" . get_table('edm_userdata') . "` (`usergroup`, `email`) SELECT '" . $group_id . "' ,  `email` FROM `" . get_table('users') . "` WHERE email != '' AND reputation_group = " . intval($user_group_id));
	}

	public function import_system_email_by_user_group($group_id, $user_group_id)
	{
		return $this->query("INSERT INTO `" . get_table('edm_userdata') . "` (`usergroup`, `email`) SELECT '" . intval($group_id) . "' ,  `email` FROM `" . get_table('users') . "` WHERE email != '' AND group_id = " . intval($user_group_id));
	}

	public function import_system_email_by_last_active($group_id, $last_active)
	{
		return $this->query("INSERT INTO `" . get_table('edm_userdata') . "` (`usergroup`, `email`) SELECT '" . intval($group_id) . "' ,  `email` FROM `" . get_table('users') . "` WHERE email != '' AND last_active > " . (time() - intval($last_active)));
	}

	public function import_system_email_by_last_login($group_id, $last_active)
	{
		return $this->query("INSERT INTO `" . get_table('edm_userdata') . "` (`usergroup`, `email`) SELECT '" . intval($group_id) . "' ,  `email` FROM `" . get_table('users') . "` WHERE email != '' AND last_login < " . (time() - intval($last_active)));
	}

	public function import_group_data_to_task($task_id, $user_group_id)
	{
		return $this->query("INSERT INTO `" . get_table('edm_taskdata') . "` (`taskid`, `email`) SELECT '" . intval($task_id) . "' ,  `email` FROM `" . get_table('edm_userdata') . "` WHERE usergroup = " . intval($user_group_id));
	}

	public function receive_email_crond()
	{
		$receiving_mail_config = get_setting('receiving_mail_config');

		if (empty($receiving_mail_config['server']) OR empty($receiving_mail_config['username']))
		{
			return false;
		}

		$mail_config = array(
							'host' => $receiving_mail_config['server'],
							'user' => $receiving_mail_config['username'],
							'password' => $receiving_mail_config['password']
						);

		if ($receiving_mail_config['ssl'] == 'Y')
		{
			$mail_config['ssl'] = 'SSL';
		}

		if ($receiving_mail_config['port'])
		{
			$mail_config['port'] = $receiving_mail_config['port'];
		}

		try
		{
			$mail = new Zend_Mail_Storage_Pop3($mail_config);
		}
		catch (Exception $e) {
			echo $e->getMessage() . "\n";

			return false;
		}

		foreach ($mail AS $num => $message)
		{
			$received_email['message_id'] = substr($message->messageID, 1, -1);

			$received_email['date'] = intval(strtotime($message->Date));
/*
			if ($this->fetch_row(`received_email`, 'message_id = "' . $this->quote($received_email['message_id']) . '" AND date = ' . $received_email['date']))
			{
				continue;
			}
*/
			if ($message->isMultipart())
			{
				for ($i=1; $i<=$message->countParts(); $i++)
				{
					$part = $message->getPart($i);

					if (substr($part->contentType, 0, 5) == 'text/')
					{
						$encoding = $part->contentTransferEncoding;

						$type = $part->contentType;

						$received_email['content'] = $part->getContent();

						break;
					}
					else
					{
						continue;
					}
				}
			}
			else
			{
				$encoding = $message->contentTransferEncoding;

				$type = $message->contentType;

				$received_email['content'] = $message->getContent();
			}

			if (empty($encoding) OR empty($type))
			{
				continue;
			}

			preg_match('/charset\s?=\s?"?([a-zA-Z0-9-]+)"?$/i', $type, $matches);

			$charset = strtolower($matches[1]);

			$received_email['subject'] = decode_eml($message->Subject);

			preg_match('/<?(.+@.+)>?$/i', $message->From, $matches);

			$received_email['from'] = strtolower($matches[1]);

			switch ($encoding)
			{
				case 'base64':
					$received_email['content'] = base64_decode($received_email['content']);

					break;

				case 'quoted-printable':
					$received_email['content'] = quoted_printable_decode($received_email['content']);

					break;
			}

			if ($charset AND $charset != 'utf-8')
			{
				$received_email['subject'] = mb_convert_encoding($received_email['subject'], 'utf-8', $charset);

				$received_email['content'] = mb_convert_encoding($received_email['content'], 'utf-8', $charset);
			}
var_dump($received_email);
			//$this->insert('received_email', $received_email);

			//$mail->removeMessage($num);
		}

	}
}