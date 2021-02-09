<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth Controller
 *
 * @package				Facial POC
 * @subpackage			Authentication
 * @category			Controller
 * @author				Mubasher Iqbal
 * @company				Simplicity Technologies Private Limited.
 * @description			This will be used to authenticate one hardcoded user.
 */
class Auth extends CI_Controller {


    protected   $mResponse          = array();
    protected   $mJsonResponseArray;
    private     $mStatus         = null;
    private     $mMessage_type   = null;
    private     $mMessage        = null;

	/**
	 * Auth constructor.
	 * Initializing the controller.
	 * Responsible to load models/libraries/helpers
	 */
    public function __construct(){
        parent::__construct();
		$this->load->model('ip_devices_model');
		$this->load->model('lookups_model');
        $this->load->model('users_model');
		$this->mJsonResponseArray = get_json_response();

		
    }
	
	/**
	 * Default function: Show the form to login
	 * 
	 * @return void
	*/
	public function index(){
		include_once APPPATH . "libraries/vendor/autoload.php";
		$google_client = new Google_Client();
		$google_client->setClientId($this->config->item('GOOGLE_ID')); //Define your ClientID
		$google_client->setClientSecret($this->config->item('GOOGLE_SECRET')); //Define your Client Secret Key
		$google_client->setRedirectUri($this->config->item('GOOGLE_CALLBACK_URL')); //Define your Redirect Uri
		$google_client->addScope('email');
		$google_client->addScope('profile');
		$login_button =	'<a href="'.$google_client->createAuthUrl().'" class="google btn1"><i class="fa fa-google fa-fw"></i> Login with Google+</a>';
		//$login_button = '<a href="'.$google_client->createAuthUrl().'"><img src="assets/dist/img/google-sign.png" width="250" height="50" /></a>';

		$meta['page_title'] = "Login";
		$data['login_button'] = $login_button;
		$this->load->view('includes/header-auth', $meta);
		$this->load->view('users/login', $data);
		$this->load->view('includes/footer-auth');	
	}

	/**
	 * will check if user login credentials are valid
	 *
	 * @param string $password
	 * @param string $username
	 * @return boolean
	 */
	public function check_valid($password,$username){
		if(!$this->users_model->isValid($username,sha1($password))){
			$this->form_validation->set_message('check_valid', 'login_err|Sorry, but you have provided invalid username or password.');
			return FALSE;
		}
		return TRUE;
	}

	/**
     * check the username and the password in order to login
	 *
     * @return void
    */
	public function validate(){
		$username	= $this->input->post('username');
		$password	= $this->input->post('password');
		$this->form_validation->set_rules('username' , 'login_err', 'trim|required');
		$this->form_validation->set_rules('password' , 'login_err', 'trim|required|callback_check_valid['.$username.']');
		$this->form_validation->set_error_delimiters('', '');
		if ($this->form_validation->run() == FALSE){
			echo validation_errors(); exit;
		} else {
            $info = $this->check_exception($this->users_model->get_user_type_basic_info_by_id(2));
			$user_info  = $this->users_model->getUserBasicInfoByUsername($username);
            if(!empty($info)){
                $user_types_info = json_decode($info->user_types_info,TRUE);
                $this->session->set_userdata(array('reminder_time'=>$user_types_info['ReminderTime']));
            }
			if($user_info->record_status != 1){
                echo "login_err|Your account is suspended by administrator, Please contact system admin."; exit;
			}
			$data_in_session = array(
				'user_id'					=> $user_info->user_id,
				'user_type_id' 				=> $user_info->user_type_id,
				'username' 					=> $username,
				'logged_in_user_email'		=> $user_info->email_address,
				'logged_in_user_full_name' 	=> $user_info->first_name." ".$user_info->last_name,
				'logged_in_user_initials' 	=> substr($user_info->first_name, 0, 1).substr($user_info->last_name, 0, 1),
				'logged_in_user_ip'			=> $_SERVER['REMOTE_ADDR'],
				'registered_since' 			=> date("M, Y",strtotime($user_info->created_date_time)),
				'is_logged_in' 				=> TRUE
			);
			$this->session->set_userdata($data_in_session);
			echo 1;exit;
		}
	}
	public function login(){
		include_once APPPATH . "libraries/vendor/autoload.php";
		$google_client = new Google_Client();
		$google_client->setClientId($this->config->item('GOOGLE_ID')); //Define your ClientID
		$google_client->setClientSecret($this->config->item('GOOGLE_SECRET')); //Define your Client Secret Key
		$google_client->setRedirectUri($this->config->item('GOOGLE_CALLBACK_URL')); //Define your Redirect Uri
		$google_client->addScope('email');
		$google_client->addScope('profile');
		if(isset($_GET["code"])){
			$token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);
			if(!isset($token["error"])){
				$google_client->setAccessToken($token['access_token']);
				$this->session->set_userdata('access_token', $token['access_token']);
				$google_service = new Google_Service_Oauth2($google_client);
				$data = $google_service->userinfo->get();
				$current_datetime = date('Y-m-d H:i:s');
				$parts = explode('@', $data['email']);
				$username = $parts[0];
				if($this->users_model->Is_already_register($data['email'])){
					//  update_data
					$user_data = array(
					'first_name'        => $data['given_name'],
					'last_name'         => $data['family_name'],
					'username'          => $username,
					'email_address'     => $data['email'],
					'updated_date_time' => $current_datetime
					);
					$this->users_model->Update_user_data($user_data, $data['email']);
				}else{  
					//insert_data 
					$user_data = array(
					'first_name'         => $data['given_name'],
					'last_name'          => $data['family_name'],
					'email_address'      => $data['email'],
					'username'           => $username,
					'record_status'      => 1,
					'user_type_id' 		 => 2,
					'created_date_time'  => $current_datetime,
					'updated_date_time'  => $current_datetime
					);      
					$this->users_model->Insert_user_data($user_data);
				}
				$user_info  = $this->users_model->getUserBasicInfoByEmail($data['email']);
				$data_in_session = array(
					'user_id'					=> $user_info->user_id,
					'user_type_id' 				=> $user_info->user_type_id,
					'logged_in_user_email'		=> $user_info->email_address,
					'logged_in_user_full_name' 	=> $user_info->first_name." ".$user_info->last_name,
					'logged_in_user_initials' 	=> substr($user_info->first_name, 0, 1).substr($user_info->last_name, 0, 1),
					'logged_in_user_ip'			=> $_SERVER['REMOTE_ADDR'],
					'registered_since' 			=> date("M, Y",strtotime($user_info->created_date_time)),
					'is_logged_in' 				=> TRUE
				);
				$this->session->set_userdata($data_in_session);
			}
		}
		$login_button = '';
		if(!$this->session->userdata('access_token')){  
			$login_button =	'<a href="'.$google_client->createAuthUrl().'" class="google btn1"><i class="fa fa-google fa-fw"></i> Login with Google+</a>';
			$data['login_button'] = $login_button;
			$meta['page_title'] = "Login";
			$data['login_button'] = $login_button;
			$this->load->view('includes/header-auth', $meta);
			$this->load->view('users/login', $data);
			$this->load->view('includes/footer-auth');	
		}else{
			redirect('home');
		}
	 }

	/**
      * Destroy the session, and logout the user.
        *
      * @return void
    */
    public function logout(){
        $this->session->unset_userdata('access_token');
		$this->session->sess_destroy();
        redirect('auth');
	}

    /**
     * Check for exception
     *
     * @param $data_array
     * @param $for
     * @return mixed
     * @throws Exception
     */
    function check_exception($data_array, $for = null){
        $exception_type = null;
        switch ($for){
            case 1://status
                $exception_type = $this->mJsonResponseArray['USER_STATUS_FAILURE_TEXT'];
                break;
            case 2://delete
                $exception_type = $this->mJsonResponseArray['USER_DELETE_FAILURE_TEXT'];
                break;
            case 3://create
                $exception_type = $this->mJsonResponseArray['USER_CREATE_FAILURE_TEXT'];
                break;
            case 4://update
                $exception_type = $this->mJsonResponseArray['USER_UPDATE_FAILURE_TEXT'];
                break;
            default://default
                $exception_type = $this->mJsonResponseArray['MESSAGE_TYPE_USER'];
        }
        if(!empty($data_array) && array_key_exists('message',$data_array)){
            if(array_key_exists('status',$data_array) && empty($data_array['status']) || $data_array['status'] != 200){
                throw new Exception($exception_type.' '.$data_array['message']);
            }
            return $data_array['message'];
        }
        throw new Exception($exception_type);
    }
}

/* End of file Auth.php */
/* Location: ./application/controllers/Auth.php */
