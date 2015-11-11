<?php
/**
*
* @package phpBB Extension - selfdelete
* @copyright (c) 2015 rinsrans <karl.rinser@gmail.com>
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rinsrans\selfdelete\event;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

		protected $error;

	/**
	* Constructor
	*
	* @param \phpbb\auth\auth					$auth			Auth object
	* @param \phpbb\config\config               $config         Config object
	* @param \phpbb\controller\helper           $helper         Controller helper object
	* @param \phpbb\db\driver\driver			$db				Database object
	* @param \phpbb\request\request				$request		Request object
	* @param \phpbb\template\template           $template       Template object
	* @param \phpbb\user                        $user           User object
	*/
	public function __construct(
			\phpbb\auth\auth $auth,
			\phpbb\config\config $config,
			\phpbb\controller\helper $helper,
			\phpbb\db\driver\driver_interface $db,
			\phpbb\request\request $request,
			\phpbb\template\template $template,
			\phpbb\user $user)

	{
		$this->auth = $auth;
		$this->config = $config;
		$this->helper = $helper;
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->error = array();

	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @return array
	 * @static
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.ucp_profile_reg_details_data'					=> 'ucp_profile_reg_details_data',
			'core.ucp_profile_reg_details_validate'				=> 'ucp_profile_reg_details_validate',
		);
	}
	
	
	public function ucp_profile_reg_details_data($event)
	{
		$this->user->add_lang(array('acp/common', 'acp/users'));
		$delete_type = request_var('delete_type', '');
		
		if ($event['submit'] && $delete_type)
		{
			if ($this->user->data['user_type'] == USER_FOUNDER)
			{
				$this->error[] = 'CANNOT_REMOVE_FOUNDER';
			}
			if(!sizeof($event['error']))
			{
				if (confirm_box(true))
				{
					user_delete($delete_type, $this->user->data['user_id'], $this->user->data['username']);
					add_log('admin', 'LOG_USER_DELETED', $this->user->data['username']);
					trigger_error($this->user->lang['USER_DELETED'] . '<br /><br />' . sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . generate_board_url() . '">', '</a>'));
				}
				else
				{
					confirm_box(false, $this->user->lang['CONFIRM_OPERATION'], build_hidden_fields(array(
						'delete'			=> 1,
						'form_token'		=> $this->request->variable('form_token', ''),
						'submit'			=> true,
						'cur_password'		=> $this->request->variable('cur_password', '', true),
						'delete_type'		=> $delete_type))
					);
				}
			}
		}
	}

	public function ucp_profile_reg_details_validate($event)
	{
		$this->error = array_merge($this->error, $event['error']);

		$event['error'] = $this->error;
	}
}