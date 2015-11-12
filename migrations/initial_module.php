<?php
/**
*
* @package phpBB Extension - selfdelete
* @copyright (c) 2015 rinsrans <karl.rinser@gmail.com>
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/


namespace rinsrans\selfdelete\migrations;
class initial_module extends \phpbb\db\migration\migration
{
	public function update_data()
	{
		return array(
			array('permission.add', array('u_self_delete_posts', true, 'a_board')),
		);
	}
}