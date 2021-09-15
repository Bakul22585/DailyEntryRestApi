<?php
defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries//PHPMailer/class.phpmailer.php';
require APPPATH . '/libraries//PHPMailer/class.smtp.php';	

class User extends REST_Controller
{

    private $auth;

    public function __construct()
    {
        parent::__construct();

        $this->auth = new stdClass();
        $this->load->helper('form');
        // load database
        $this->load->database();

        $this->load->model(array('user_model' => 'user'));
    }

    function add_post() {
        $data['firstname'] = $this->input->post('firstname');
        $data['lastname'] = $this->input->post('lastname');
        $data['email'] = $this->input->post('email');
        $data['password'] = base64_encode($this->input->post('password'));
        $data['device_token'] = $this->input->post('device_token');
        $activation = $this->input->post('activation');

        $emailData = $this->user->CheckUserField('email', $data['email']);

        if (count($emailData) > 0) {
            $this->response([
                'field' => 'email',
                'success'  => FALSE,
                'message' => 'The email address you entered already exists, please use another email address.'
            ], REST_Controller::HTTP_OK);
            return false;
        }

        $Username = $data['firstname'].''. $data['lastname']. substr(rand(), 0, 6);
        $UsernameStatus = true;

        while ($UsernameStatus) {
            $UserData = $this->user->CheckUserField('username', $Username);

            if (count($UserData) == 0) {
                $data['username'] = $Username;
                $UsernameStatus = false;
            } else {
                $Username = $data['firstname'] . $data['lastname'] . substr(rand(), 0, 6);
            }
        }
        if ($activation == 'true') {
            $rpCode = substr(rand(), 0, 6);
            try {
                $mail = new PHPMailer(); // create a new object
                $mail->IsSMTP();
                $mail->SMTPDebug  = 0;
                $mail->SMTPAuth   = true;
                $mail->SMTPSecure = "tls";
                $mail->Host       = "smtp.hostinger.in";
                $mail->Port       = 587;
                $mail->Username   = "dailyentry@restrictionsolution.com";
                $mail->Password   = "dailyEntry@587";
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                $mail->setFrom('dailyentry@restrictionsolution.com', 'Daily Entry');
                $mail->AddAddress($data['email']);
                $mail->IsHTML(true);
                $mail->Subject = 'Verification for daily entry application';
                $mail->MsgHTML("<h1 style='color: #FF5400;text-align: center'>Verification</h1>
                                <p>You have created a <strong>Daily Entry</strong> account and the verification code is <strong>$rpCode</strong>.</p>
                                <p>Copy this code and go to the application and put the verification field</p>");

                $rtn = $mail->Send();

                if ($rtn) {
                    $this->response([
                        'field' => 'activation',
                        'activation' => $rpCode,
                        'message' => 'Check your enter email address, verification code has been sent to your email.',
                        'success'  => false,
                    ], REST_Controller::HTTP_OK);
                } else {
                    $this->response([
                        'field' => 'email',
                        'message' => 'Mail can not be sent. Please try again after some time.',
                        'success'  => false,
                    ], REST_Controller::HTTP_OK);
                }
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $data = $this->user->add_user($data);
            $fullname = $this->input->post('firstname') .' '.$this->input->post('lastname');
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            if ($data > 0) {
                try {
                    $mail = new PHPMailer(); // create a new object
                    $mail->IsSMTP();
                    $mail->SMTPDebug  = 0;
                    $mail->SMTPAuth   = true;
                    $mail->SMTPSecure = "tls";
                    $mail->Host       = "smtp.hostinger.in";
                    $mail->Port       = 587;
                    $mail->Username   = "dailyentry@restrictionsolution.com";
                    $mail->Password   = "dailyEntry@587";
                    $mail->SMTPOptions = array(
                        'ssl' => array(
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                            'allow_self_signed' => true
                        )
                    );
                    $mail->setFrom('dailyentry@restrictionsolution.com', 'Daily Entry');
                    $mail->AddAddress($email);
                    $mail->IsHTML(true);
                    $mail->Subject = 'Daily Entry';
                    $mail->MsgHTML("<h1 style='color: #FF5400;text-align: center'>Registration Successful</h1>
                                <h3 style='text-align: center'>Hi $fullname</h3>
                                <h3 style='color:green;text-align: center'>Congratulations!</h3>
                                <p style='text-align: center'>Congratulations, $fullname. Your voluntary account has been created successfully in Daily Entry.
                                Your User ID: <strong>$Username</strong> and Password is: <strong>$password</strong>. Don't share it with anybody.</p>");

                    $rtn = $mail->Send();
                } catch (Exception $e) {
                    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
                $this->response([
                    'message' => 'Registation has been successfully.',
                    'success'  => true,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'field' => 'registration',
                    'success'  => FALSE,
                    'message' => 'Your registration failed, please try again and check.'
                ], REST_Controller::HTTP_OK);
            }
        }

    }

    function edit_post() {

        $id = $this->input->post('id');
        $data['fullname'] = $this->input->post('fullname');
        $data['mobile'] = $this->input->post('mobile');
        $data['device_token'] = $this->input->post('device_token');
        $password = $this->input->post('password');
        $passwordStatus = $this->input->post('passwordStatus');

        if ($passwordStatus == 'true') {
            $data['password'] = base64_encode($password);
        }

        $status = $this->user->editUser($id, $data);

        if ($status) {
            $UserData = $this->user->get_user($id);
            $this->response([
                'data' => $UserData,
                'message' => 'Your data has been updated in our system successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'message' => 'Your data has been not updated in our system, Please check it and try again.',
                'success'  => false,
            ], REST_Controller::HTTP_OK);
        }
    }

    function login_post() {
        $data['username'] = $this->input->post('username');
        $data['password'] = base64_encode($this->input->post('password'));

        $data = $this->user->login_user($data);

        if (count($data) > 0) {
            $this->response([
                'data' => $data,
                'message' => 'User login has been successfully.',
                'success'  => true,
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'success'  => FALSE,
                'message' => 'Please check your username or password, something has been wrong and try again.'
            ], REST_Controller::HTTP_OK);
        }
    }

    function forgotpassword_post() {
        
        $data['email'] = $this->input->post('email');

        $data = $this->user->CheckUserField('email', $data['email']);
        
        if (count($data) > 0) {
            $fullname = $data[0]['firstname'].' '. $data[0]['lastname'];
            $Username = $data[0]['username'];
            $password = base64_decode($data[0]['password']);
            $rpCode = substr(rand(), 0, 6);
            $uniquCode = true;
            while ($uniquCode) {
                $RPdata = $this->user->CheckUserField('code', $rpCode);

                if (count($RPdata) == 0) {
                    try {
                        $mail = new PHPMailer(); // create a new object
                        $mail->IsSMTP();
                        $mail->SMTPDebug  = 0;
                        $mail->SMTPAuth   = true;
                        $mail->SMTPSecure = "tls";
                        $mail->Host       = "smtp.hostinger.in";
                        $mail->Port       = 587;
                        $mail->Username   = "dailyentry@restrictionsolution.com";
                        $mail->Password   = "dailyEntry@587";
                        $mail->SMTPOptions = array(
                                'ssl' => array(
                                    'verify_peer' => false,
                                    'verify_peer_name' => false,
                                    'allow_self_signed' => true
                                )
                            );
                        $mail->setFrom('dailyentry@restrictionsolution.com', 'Daily Entry');
                        $mail->AddAddress($data[0]['email']);
                        $mail->IsHTML(true);
                        $mail->Subject = 'Forgot Password';
                        $mail->MsgHTML("
                                        <h3 style='text-align: center'>Hi $fullname</h3>
                                        <p>This is your User ID: <strong>$Username</strong> and Password is: <strong>$password</strong>. Don't share it with anybody.</p>
                                        <p>If you want to change your password then copy Reset password code and Your Reset password Code is <strong>$rpCode</strong>.</p>
                                        <p>Copy this code and go to application and reset your password.</p>");

                        $rtn = $mail->Send();

                        if ($rtn) {
                            $status = $this->user->editUser($data[0]['id'], array('code' => $rpCode));
                            if ($status) {
                                $this->response([
                                    'message' => 'Mail has been sent successfully. Check your mail account.',
                                    'success'  => true,
                                ], REST_Controller::HTTP_OK);
                            } else {
                                $this->response([
                                    'success'  => FALSE,
                                    'message' => 'Something has been wrong, Please try again after some time.'
                                ], REST_Controller::HTTP_OK);
                            }
                            
                        } else {
                            $this->response([
                                'message' => 'Mail can not be sent. Please try again after some time.',
                                'success'  => false,
                            ], REST_Controller::HTTP_OK);
                        }
                    } catch (Exception $e) {
                        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    }
                    
                    $uniquCode = false;
                } else {
                    $rpCode = substr(rand(), 0, 6);
                }
            }
            
        } else {
            $this->response([
                'success'  => FALSE,
                'message' => 'The email you entered is not in our system, please check your email and try again.'
            ], REST_Controller::HTTP_OK);
        }
    }

    function restpassword_post() {
        $code = $this->input->post('code');
        $password = base64_encode($this->input->post('password'));

        $CodeData = $this->user->CheckUserField('code', $code);

        if (count($CodeData) > 0) {
            $status = $this->user->editUser($CodeData[0]['id'], array('password' => $password, 'code' => ''));
            if ($status) {
                $this->response([
                    'message' => 'Your password has been successfully reset..',
                    'success'  => true,
                ], REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'success'  => FALSE,
                    'message' => 'Something has been wrong, Please try again after some time.'
                ], REST_Controller::HTTP_OK);
            }
        } else {
            $this->response([
                'success'  => FALSE,
                'message' => 'Please check your code, Your code is wrong.'
            ], REST_Controller::HTTP_OK);
        }

    }

    function toUser_get() {
        $user_id = $this->input->get('user_id');

        $data = $this->user->get_touser(array("user_id" => $user_id));

        $UserName = array();
        foreach ($data as $key => $value) {
            $UserName[] = $value['name'];
        }

        $this->response([
            'data' => $UserName,
            'success'  => true,
        ], REST_Controller::HTTP_OK);
    }

    function SendPushNotification($title, $body) {
        $api_key = "AAAAzj3PAJA:APA91bGjVXm93wBaJHw5OrrgeslmdRauTyYGzN89Aw4rZrug0xxYgV-tkOvzo0KIfmf7vbWoaRuWr7r-GIrcVVCHk1pCh9CymkdcTThL8S3gO-NfGYzoUEUJdeU-NZKQC30JwJifQNLA";
        $url = 'https://fcm.googleapis.com/fcm/send';

        $UserData = $this->user->get_user(1);
        
        $id = array($UserData[0]['device_token']);
        
        $data_message = array(
            'body' => $body,
            'title' => $title
        );
        
        $fields = array(
            'registration_ids' => $id,
            'priority'  => 'high',
            'notification' => $data_message,
            'data' => $data_message
        );
        
        $headers = array(
            'Authorization: key=' . $api_key,
            'Content-Type: application/json'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }

    function updateUser_post() {
        $user_id = $this->input->post('user_id');
        $data['firstname'] = $this->input->post('first_name');
        $data['lastname'] = $this->input->post('last_name');
        $image = $this->input->post('image');
        $password = $this->input->post('password');

        if (!empty($password)) {
            $data['password'] = base64_encode($password);
        }

        if (!empty($image)) {
            $data['img'] = $image;
        }

        $status = $this->user->editUser($user_id, $data);

        if ($status) {
            $data = $this->user->CheckUserField('id', $user_id);
            $this->response([
                'data' => $data,
                'success'  => true,
                'message' => 'Your profile data has been successfully updated.'
            ], REST_Controller::HTTP_OK);
        } else {
            $this->response([
                'success'  => false,
                'message' => 'Your profile data was not updated, please try again after some time.'
            ], REST_Controller::HTTP_OK);
        }
    }
}
