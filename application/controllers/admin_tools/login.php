<?php if ( ! defined('BASEPATH')) exit('Tidak boleh mengakses script ini secara langsung');
	
define('TPL_LOGIN','admin/admin_login.php');
class Login extends AdminController 
{
	protected $user_data;
	protected $admin_username;
	protected $admin_id;
	protected $view_data;
	
	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
		$this->load->database();
		$this->user_data = $this->session->all_userdata();
		$this->view_data = array();
		
		$ip = $this->GetIp();
		$admin_ip = array();
		$admin_ip[] = '202.138.249.12';
		$admin_ip[] = '172.16.1.42';
		
		
	}
	
	public function index()
	{
		$session_check = $this->check_session();
		if ($session_check)
		{
			$now = time();
			if ($now < $this->user_data['timeout'])
			{
				$this->RedirectToHome();
			}
			
		}else
		{
			$this->show_login();
		}
	}
	
	private function show_login()
	{
		$data = array();
		
		
		if (!$this->input->post('login_token'))
		{
			$data['token'] = $this->SetToken();
			$this->load->view(TPL_LOGIN,$data);
		}
		else
		{
			$error_message = array(
				'token yg dikirimkan tidak valid',
				'Username/Password Salah'
			);
			$valid = false;
			$token = $this->input->post('login_token');
			if ($token == $this->user_data['token'])
			{
				$username = $this->input->post('uname');
				$password = md5($this->input->post('passwd'));
				$query_res = $this->db->get_where('admin_tbl',array('admin_name'=>$username,'admin_pswd'=>$password));
				if ($query_res->num_rows() > 0)
				{
					$row = $query_res->row_array();
					$admin_id = $row['idx'];
					
					$session_data = array(
						'admin_id'=>$admin_id,
						'admin_username'=>$username,
						'timeout'=>time()+3600,
					);
					$this->session->set_userdata($session_data);
					$this->RedirectToHome();
				}
				else
				{
					$data['token'] = $this->SetToken();
					$data['error_message'] = $error_message[1];
					$this->load->view(TPL_LOGIN,$data);
				}
			}
			else
			{
				$data['error_message'] = $error_message[0];
				$data['token'] = $this->SetToken();
				$this->load->view(TPL_LOGIN,$data);
			}
			
		}
	}
	
	private function RedirectToHome()
	{
		header('Location: '.WEB_ADDRESS.'admin_tools/home.php');
		exit();
	}
	
	protected function SetToken()
	{
		$value = rand(1,100000);
		$this->session->set_userdata('token',$value );
		return $value;	
	}
	
	
	protected function check_session()
	{
		if (!empty($this->user_data['admin_username']))
		{
			$now = time();
			if ($now > $this->user_data['timeout'])
			{
				$this->unset_userdata();
				return false;	
			}
			else
			{
				$this->admin_username = $this->user_data['admin_username'];
				$this->admin_game_id = $this->user_data['admin_game_id'];
				$this->admin_id = $this->user_data['admin_id'];
				return true;
			}
		}
		else
		{
			return false;
		}
	}
	
	protected function GetIp()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		
		return $ip;
	}
	
	protected function unset_userdata()
	{
		$session_data = array(
			'admin_id'=>'',
			'admin_username'=>'',
			'timeout'=>'',
		);
		$this->session->unset_userdata($session_data);
	}
	
}

?>