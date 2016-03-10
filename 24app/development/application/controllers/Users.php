<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {

    /**
     * @ Function Name		: class constructor
     * @ Function Purpose 	: constructor function for class to load default files
     * @ Function Returns	: 
     */
    public function __construct() {
        parent::__construct();
        $this->load->model("Frontend/Users/Usersmodel");
        $this->load->model("Frontend/Users/Profitmodel");
        $this->responseArr = (array) null;
    }

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see http://codeigniter.com/user_guide/general/urls.html
     */
    public function index() {
        $this->load->view('users/index.php');
    }

    public function facebooklogin() {
        $user_social_id = $this->input->post('user_social_id');
        $user_email = $this->input->post('user_email');
        $user_fname = $this->input->post('user_fname');
        $user_lname = $this->input->post('user_lname');
        $user_gender = $this->input->post('user_gender');
        $user_deviceType = $this->input->post('user_deviceType');
        $user_deviceToken = $this->input->post('user_deviceToken');
        $user_loginType = $this->input->post('user_loginType');
        $user_name = $user_fname . ' ' . $user_lname;
        if (empty($user_social_id) || empty($user_fname) || empty($user_deviceType) || empty($user_deviceToken) || empty($user_loginType)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $fbidexist = $this->Usersmodel->isFacebookIdExist($user_social_id);
        if (!empty($fbidexist) && $fbidexist['user_status'] != 1) {
            $this->responseArr = array(
                'result' => FALSE,
                'loggedIn' => FALSE,
                'message' => 'User is blocked by Adminstrator!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        if ($fbidexist['user_social_id'] == $user_social_id) {
            $userInformation = array(
                'user_id' => !empty($fbidexist['user_id']) ? $fbidexist['user_id'] : '',
                'user_email' => !empty($fbidexist['user_email']) ? $fbidexist['user_email'] : '',
                'user_fname' => !empty($fbidexist['user_fname']) ? $fbidexist['user_fname'] : '',
                'user_lname' => !empty($fbidexist['user_lname']) ? $fbidexist['user_lname'] : '',
                'user_gender' => !empty($fbidexist['user_gender']) ? $fbidexist['user_gender'] : '',
                'user_name' => !empty($fbidexist['user_name']) ? $fbidexist['user_name'] : '',
                'user_loginType' => !empty($fbidexist['user_loginType']) ? $fbidexist['user_loginType'] : '',
                'user_social_id' => !empty($fbidexist['user_social_id']) ? $fbidexist['user_social_id'] : '',
            );

            $device_info = array(
                'user_deviceToken' => $user_deviceToken,
                'user_deviceType' => $user_deviceType,
                'user_id' => $fbidexist['user_id'],
            );
            $deletedDevice = $this->Usersmodel->deleteDeviceId($device_info['user_deviceToken']);
            if ($deletedDevice) {
                $this->Usersmodel->saveUserDeviceInformation($device_info);
            }
            $this->responseArr = array(
                'result' => TRUE,
                'userInfo' => $userInformation,
            );
        } else {
            $user_info = array(
                'user_email' => !empty($user_email) ? $user_email : '',
                'user_fname' => !empty($user_fname) ? $user_fname : '',
                'user_lname' => !empty($user_lname) ? $user_lname : '',
                'user_gender' => !empty($user_gender) ? $user_gender : '',
                'user_name' => $user_name,
                'user_loginType' => $user_loginType,
                'user_social_id' => $user_social_id,
                'user_accessToken' => md5($this->input->post('user_email') . time()),
                'user_dateCreated' => date('Y-m-d H:i:s'),
            );
            $saveUserId = $this->Usersmodel->saveUserInformation($user_info);
            $registerUserInfo = $this->Usersmodel->getUserInfoById($saveUserId);
            if (!empty($registerUserInfo)) {
                $device_info = array(
                    'user_deviceToken' => $user_deviceToken,
                    'user_deviceType' => $user_deviceType,
                    'user_id' => $registerUserInfo['user_id'],
                );
                $deletedDevice = $this->Usersmodel->deleteDeviceId($device_info['user_deviceToken']);
                if ($deletedDevice) {
                    $this->Usersmodel->saveUserDeviceInformation($device_info);
                }
                $userInformation = array(
                    'user_id' => !empty($registerUserInfo['user_id']) ? $registerUserInfo['user_id'] : '',
                    'user_email' => !empty($registerUserInfo['user_email']) ? $registerUserInfo['user_email'] : '',
                    'user_fname' => !empty($registerUserInfo['user_fname']) ? $registerUserInfo['user_fname'] : '',
                    'user_lname' => !empty($registerUserInfo['user_lname']) ? $registerUserInfo['user_lname'] : '',
                    'user_gender' => !empty($registerUserInfo['user_gender']) ? $registerUserInfo['user_gender'] : '',
                    'user_name' => !empty($registerUserInfo['user_name']) ? $registerUserInfo['user_name'] : '',
                    'user_loginType' => !empty($registerUserInfo['user_loginType']) ? $registerUserInfo['user_loginType'] : '',
                    'user_social_id' => !empty($registerUserInfo['user_social_id']) ? $registerUserInfo['user_social_id'] : '',
                );
                $this->responseArr = array(
                    'result' => TRUE,
                    'userInfo' => $userInformation,
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Something went wrong please try again later.',
                );
            }
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function logout() {
        $user_deviceToken = $this->input->post('user_deviceToken');
        if (empty($user_deviceToken)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $userData = $this->Usersmodel->getDeviceToken($user_deviceToken);
        if ($userData) {
            $this->Usersmodel->logout($user_deviceToken);
            $this->responseArr = array(
                'result' => TRUE,
                'message' => 'Logout successfully.',
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'User does not exist.'
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function saveFeeds() {
        $user_id = $this->input->post('user_id');
        $title = $this->input->post('title');
        $description = $this->input->post('description');
        $media = $this->input->post('media');
        $type = $this->input->post('type'); //0-no image or video,1-> image,2->video
        if (empty($user_id) || empty($title) || empty($description)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $userStatus = $this->Usersmodel->getUserInfoById($user_id);
        //print_r($userStatus);die();
        if ($userStatus['user_status'] == 0) {
            $this->responseArr = array(
                'result' => FALSE,
                'loggedIn' => FALSE
            );
            echo json_encode($this->responseArr);
            exit();
        }
        if ($type == 0) {
            $savefeed = array(
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description,
                'type' => 0,
                'created' => date('Y-m-d H:i:s')
            );
        } elseif ($type == 1 && !empty($media)) {
            $directorypath1 = dirname(BASEPATH) . "/assets/images/uploads/feeds/";
            $media = str_replace('data:image/PNG;base64,', '', $media);
            $media = str_replace('', '+', $media);
            $encoded_string = base64_decode($media);
            $im = imagecreatefromstring($encoded_string);
            $feedImage = 'feed_' . time() . ".png";
            imagepng($im, $directorypath1 . $feedImage, 9);

            $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $feedImage;
            list($width_orig, $height_orig) = getimagesize($filename);
            $thumb_width = 640;
            $thumb_height = 640;
            $image_p = imagecreatetruecolor($thumb_width, $thumb_height);
            $image = imagecreatefrompng($filename);
            imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
            imagepng($image_p, $directorypath1 . 'thumbnail/' . $feedImage, 9);
            $media = $feedImage;
            $savefeed = array(
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description,
                'media' => $media,
                'type' => 1,
                'created' => date('Y-m-d H:i:s')
            );
        } else {

            $mediadirectorypath = dirname(BASEPATH) . "/assets/images/uploads/feeds/";
            $mfile = str_replace('data:video/MP4;base64,', '', $media);
            $mfile = str_replace('', '+', $mfile);
            $data = base64_decode($mfile);
            $mfile_name = 'feed_media' . time() . '_' . ".mp4";
            $mfile_path = $mediadirectorypath . $mfile_name;
            file_put_contents($mfile_path, $data);
            $nam = explode(".", $mfile_name);
            $new_image = $nam[0] . '.jpg';
            $path = $mediadirectorypath . $mfile_name;
            $new_image_path = $mediadirectorypath . $new_image;
            $command = "ffmpeg -i " . $path . " " . $new_image_path;
            exec($command);
            $savefeed = array(
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description,
                'media' => $mfile_name,
                'type' => 2,
                'created' => date('Y-m-d H:i:s')
            );
        }
        $feedData = $this->Usersmodel->saveUserFeed($savefeed);
        if ($feedData) {
            $getUserFeedData = $this->Usersmodel->getUserFeedData($feedData);
            if ($getUserFeedData['type'] == 0) {
                $getUserFeedData['thumbnail'] = '';
                $getUserFeedData['media'] = '';
            } elseif ($getUserFeedData['type'] == 1) {
                $getUserFeedData['thumbnail'] = '';
                $getUserFeedData['media'] = base_url() . "assets/images/uploads/feeds/" . $getUserFeedData['media'];
            } elseif ($getUserFeedData['type'] == 2) {
                $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $getUserFeedData['media'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $thumbnail = ($ext == 'MP4') ? base_url() . "assets/images/uploads/feeds/" . str_replace('MP4', 'jpg', $getUserFeedData['media']) : base_url() . "assets/images/uploads/feeds/" . str_replace('mp4', 'jpg', $getUserFeedData['media']);
                $getUserFeedData['thumbnail'] = $thumbnail;
                $getUserFeedData['media'] = base_url() . "assets/images/uploads/feeds/" . $getUserFeedData['media'];
            }
            $this->responseArr = array(
                'result' => TRUE,
                'message' => 'Feed save successfully.',
                'feed_id' => $feedData,
                'feedData' => $getUserFeedData
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Something went wrong please try again later.',
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function updateFbFeedId() {
        $feed_id = $this->input->post('feed_id');
        $fb_feed_id = $this->input->post('fb_feed_id');
        $fb_share_url = $this->input->post('fb_share_url');
        if (empty($feed_id) || empty($fb_feed_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $updateDataArr = array('fb_feed_id' => $fb_feed_id, 'fb_share_url' => $fb_share_url);
        $conditionArr = array('id' => $feed_id);
        $updateResult = $this->Usersmodel->updateFeed($updateDataArr, $conditionArr, 'tbl_feeds');
        if ($updateResult) {
            $this->responseArr = array(
                'result' => TRUE,
                'message' => 'Facebook feed id successfully saved.',
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Something went wrong please try again later.',
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function editFeed() {
        $feed_id = $this->input->post('feed_id');
        $user_id = $this->input->post('user_id');
        $title = $this->input->post('title');
        $description = $this->input->post('description');
        $media = $this->input->post('media');
        $type = $this->input->post('type');

        if (empty($user_id) || empty($title) || empty($description) || empty($feed_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        $getUserFeedData = $this->Usersmodel->getUserFeedData($feed_id);

        $savefeed = array();
        if ($getUserFeedData['user_id'] == $user_id) {
            $savefeed = array(
                'user_id' => $user_id,
                'title' => $title,
                'description' => $description,
            );

            if (($getUserFeedData['type'] == 1) || ($getUserFeedData['type'] == 2)) {
                $directorypath1 = dirname(BASEPATH) . "/assets/images/uploads/feeds/";
                $filename = $directorypath1 . $getUserFeedData['media'];
                if (file_exists($filename)) {
                    @unlink($filename);
                }
            }

            if ($type == 0) {
                $savefeed['media'] = '';
                $savefeed['type'] = 0;
            } elseif ($type == 1 && !empty($media)) {

                $directorypath1 = dirname(BASEPATH) . "/assets/images/uploads/feeds/";
                $media = str_replace('data:image/PNG;base64,', '', $media);
                $media = str_replace('', '+', $media);
                $encoded_string = base64_decode($media);
                $im = imagecreatefromstring($encoded_string);
                $feedImage = 'feed_' . time() . ".png";
                imagepng($im, $directorypath1 . $feedImage, 9);

                $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $feedImage;
                list($width_orig, $height_orig) = getimagesize($filename);
                $thumb_width = 640;
                $thumb_height = 640;
                $image_p = imagecreatetruecolor($thumb_width, $thumb_height);
                $image = imagecreatefrompng($filename);
                imagecopyresampled($image_p, $image, 0, 0, 0, 0, $thumb_width, $thumb_height, $width_orig, $height_orig);
                imagepng($image_p, $directorypath1 . 'thumbnail/' . $feedImage, 9);
                $media = $feedImage;
                $savefeed['media'] = $feedImage;
                $savefeed['type'] = 1;
            } elseif ($type == 2 && !empty($media)) {

                $mediadirectorypath = dirname(BASEPATH) . "/assets/images/uploads/feeds/";
                $mfile = str_replace('data:video/MP4;base64,', '', $media);
                $mfile = str_replace('', '+', $mfile);
                $data = base64_decode($mfile);
                $mfile_name = 'feed_media' . time() . '_' . ".mp4";
                $mfile_path = $mediadirectorypath . $mfile_name;
                file_put_contents($mfile_path, $data);
                $nam = explode(".", $mfile_name);
                $new_image = $nam[0] . '.jpg';
                $path = $mediadirectorypath . $mfile_name;
                $new_image_path = $mediadirectorypath . $new_image;
                $command = "ffmpeg -i " . $path . " " . $new_image_path;
                exec($command);
                $savefeed['media'] = $mfile_name;
                $savefeed['type'] = 2;
            }

            $conditionArr = array('id' => $feed_id);
            $feedData = $this->Usersmodel->updateFeed($savefeed, $conditionArr, 'tbl_feeds');
            if ($feedData) {
                $this->responseArr = array(
                    'result' => TRUE,
                    'message' => 'Feed successfully updated.',
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Something went wrong please try again later.',
                );
            }
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Not authorised to edit the feed.',
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function getFeedData() {
        $feed_id = $this->input->post('feed_id');
        $user_id = $this->input->post('user_id');

        if (empty($user_id) || empty($feed_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        $getUserFeedData = $this->Usersmodel->getUserFeedData($feed_id);
        // print_r($getUserFeedData);
        if (!empty($getUserFeedData)) {
            if ($getUserFeedData['user_id'] == $user_id)
                $getUserFeedData['feed_owner'] = 'yes';
            else
                $getUserFeedData['feed_owner'] = 'no';

            if ($getUserFeedData['type'] == 0) {
                $getUserFeedData['thumbnail'] = '';
                $getUserFeedData['media'] = '';
            } elseif ($getUserFeedData['type'] == 1) {
                $getUserFeedData['thumbnail'] = '';
                $getUserFeedData['media'] = base_url() . "assets/images/uploads/feeds/" . $getUserFeedData['media'];
            } elseif ($getUserFeedData['type'] == 2) {
                $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $getUserFeedData['media'];
                $ext = pathinfo($filename, PATHINFO_EXTENSION);
                $thumbnail = ($ext == 'MP4') ? base_url() . "assets/images/uploads/feeds/" . str_replace('MP4', 'jpg', $getUserFeedData['media']) : base_url() . "assets/images/uploads/feeds/" . str_replace('mp4', 'jpg', $getUserFeedData['media']);
                $getUserFeedData['thumbnail'] = $thumbnail;
                $getUserFeedData['media'] = base_url() . "assets/images/uploads/feeds/" . $getUserFeedData['media'];
            }

            $profitAmount = 0;
            //To get all Profit Parameters
            $getProfitParams = $this->Profitmodel->getProfitParams();

            $checkShareUser = $this->Usersmodel->checkShareUser($user_id, $feed_id);
            // echo $getUserFeedData['viewcount']."--".$checkShareUser."--".$getProfitParams['Profit']."--".$getProfitParams['Creator']."--".$getProfitParams['Sharer']."--".$getProfitParams['Adjustment'];	die;	
            $Profit = !empty($getProfitParams['Profit']) ? $getProfitParams['Profit'] : 0;
            $Creator = !empty($getProfitParams['Creator']) ? $getProfitParams['Creator'] : 0;
            $Sharer = !empty($getProfitParams['Sharer']) ? $getProfitParams['Sharer'] : 0;
            $Adjustment = !empty($getProfitParams['Adjustment']) ? $getProfitParams['Adjustment'] : 0;

            if ($getUserFeedData['user_id'] == $user_id) {
                $profitAmount = (($getUserFeedData['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
            } elseif ($checkShareUser > 0) {
                $profitAmount = (($getUserFeedData['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
            }
			
            //$getUserFeedData['profitAmount'] = !empty($profitAmount) ? '$' . (strlen(substr($profitAmount, -(strlen($profitAmount) - (strpos($profitAmount, '.') + 1)))) > 10 ? substr($profitAmount, 0, strpos($profitAmount, '.') + 11) : $profitAmount) : "0";
			
			if($profitAmount>0){$getUserFeedData['profitAmount'] =number_format($profitAmount,10, '.' ,' ');} else{$getUserFeedData['profitAmount']=0;}
			
         
            $cTime = date("Y-m-d H:i:s");
            $currentTime = strtotime($cTime);
           
            $createdTime = strtotime($getUserFeedData['created']);
            $getUserFeedData['created'] = (string) $createdTime;
            
            $seconds = $currentTime - $createdTime;
            $hours    = $seconds / 3600;

            if($hours >= 24){
              $getUserFeedData['time'] = 1;
            }else {
             $getUserFeedData['time'] = 0;
            }

            $this->responseArr = array(
                'result' => true,
                'message' => 'Feed data',
                'feedData' => $getUserFeedData
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'No feed data found'
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function feedSeen() {
        $feed_id = $this->input->post('feed_id');
        $user_id = $this->input->post('user_id');
        if (empty($feed_id) || empty($user_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $data = $this->Usersmodel->checkTime($user_id, $feed_id);
        if (!empty($data['created'])) {
            $databaseTime = $data['created'];
            $currentTime = date('Y-m-d H:i:s');
            $interval = abs(strtotime($currentTime) - strtotime($databaseTime));
            $minutes = round($interval / 60);
            if ($minutes <= 1) {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Seen less than one minute.'
                );
            } else {
                $seenArray = array(
                    'feed_id' => $feed_id,
                    'user_id' => $user_id,
                    'created' => date('Y-m-d H:i:s'),
                );
                $result = $this->Usersmodel->saveFeedSeen($seenArray);
                if ($result) {
                    $this->responseArr = array(
                        'result' => TRUE,
                        'message' => 'One more View saved.'
                    );
                } else {
                    $this->responseArr = array(
                        'result' => FALSE,
                        'message' => 'Something went wrong please try again later.',
                    );
                }
            }
        } else {
            $seenArray = array(
                'feed_id' => $feed_id,
                'user_id' => $user_id,
                'created' => date('Y-m-d H:i:s'),
            );
            $result = $this->Usersmodel->saveFeedSeen($seenArray);
            if ($result) {
                $this->responseArr = array(
                    'result' => TRUE,
                    'message' => 'One more View saved.'
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Something went wrong please try again later.',
                );
            }
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function recentFeed() {
        $user_id = $this->input->post('user_id');
        $page_number = $this->input->post('page_number');
        $tabType = $this->input->post('tabType');
        $no_of_feed = 10;
        $skip = ($page_number - 1) * $no_of_feed;
        $limit = $no_of_feed;
        if (empty($page_number)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        //To get Profit Parameters
        $getProfitParams = $this->Profitmodel->getProfitParams();

        if ($tabType == 1) {
            $latestfeed = $this->Usersmodel->getLatestFeed($skip, $limit);
            if (!empty($latestfeed)) {

                foreach ($latestfeed as $key => $data) {
                    $count = $this->Usersmodel->getSeenCount($data['id']);
                    $latestfeed[$key]['viewcount'] = ($count > 0) ? (string) $count : '';
                    $createdTime = strtotime($latestfeed[$key]['created']);
                    if ($data['type'] == 0) {
                        $latestfeed[$key]['media'] = '';
                        $latestfeed[$key]['thumbnail'] = '';
                        $latestfeed[$key]['created'] = (string) $createdTime;
                    } elseif ($data['type'] == 1) {
                        $latestfeed[$key]['media'] = !empty($data['media']) ? (base_url() . "assets/images/uploads/feeds/" . $data['media']) : '';
                        $latestfeed[$key]['thumbnail'] = '';
                        $latestfeed[$key]['created'] = (string) $createdTime;
                    } else {
                        $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $data['media'];
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $latestfeed[$key]['thumbnail'] = ($ext == 'MP4') ? base_url() . "assets/images/uploads/feeds/" . str_replace('MP4', 'jpg', $data['media']) : base_url() . "assets/images/uploads/feeds/" . str_replace('mp4', 'jpg', $data['media']);
                        $latestfeed[$key]['media'] = !empty($data['media']) ? (base_url() . "assets/images/uploads/feeds/" . $data['media']) : '';
                        $latestfeed[$key]['created'] = (string) $createdTime;
                    }

                    $checkShareUser = $this->Usersmodel->checkShareUser($user_id, $data['id']);
                    // echo "<br/>";
                    // print_r($data);
                    // echo "<br/>";
                    // echo $latestfeed[$key]['viewcount']."--".$checkShareUser."--".$getProfitParams['Profit']."--".$getProfitParams['Creator']."--".$getProfitParams['Sharer']."--".$getProfitParams['Adjustment'];
                    // echo "<br/>";
                    $profitAmount = 0;
                    $Profit = !empty($getProfitParams['Profit']) ? $getProfitParams['Profit'] : 0;
                    $Creator = !empty($getProfitParams['Creator']) ? $getProfitParams['Creator'] : 0;
                    $Sharer = !empty($getProfitParams['Sharer']) ? $getProfitParams['Sharer'] : 0;
                    $Adjustment = !empty($getProfitParams['Adjustment']) ? $getProfitParams['Adjustment'] : 0;
                    if ($data['user_id'] == $user_id) {
                        $profitAmount = (($latestfeed[$key]['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
                    } elseif ($checkShareUser > 0) {
                        $profitAmount = (($latestfeed[$key]['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
                    }
                    // echo $profitAmount;
                    // echo "<br/>";
                    //$latestfeed[$key]['profitAmount'] = !empty($profitAmount) ? '$' . (strlen(substr($profitAmount, -(strlen($profitAmount) - (strpos($profitAmount, '.') + 1)))) > 10 ? substr($profitAmount, 0, strpos($profitAmount, '.') + 11) : $profitAmount) : "0";
					
					   if($profitAmount>0){$latestfeed[$key]['profitAmount'] =number_format($profitAmount,10, '.' ,' ');}else{ $latestfeed[$key]['profitAmount'] =0;}
                }

                $this->responseArr = array(
                    'result' => TRUE,
                    'feedData' => $latestfeed
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Recent feed is not found'
                );
            }
        } elseif ($tabType == 2) {
            $mostViewfeed = $this->Usersmodel->mostViewedFeed($skip, $limit);

            if (!empty($mostViewfeed)) {
                $mostViewfeedData = array();


                foreach ($mostViewfeed as $key => $mostViewed) {
                    $createdTime = strtotime($mostViewed['created']);
                    if ($mostViewed['type'] == 0) {
                        $thumbnail = '';
                        $mostViewed['media'] = '';
                        $mostViewed['created'] = (string) $createdTime;
                    } elseif ($mostViewed['type'] == 1) {
                        $thumbnail = '';
                        $mostViewed['media'] = base_url() . "assets/images/uploads/feeds/" . $mostViewed['media'];
                        $mostViewed['created'] = (string) $createdTime;
                    } elseif ($mostViewed['type'] == 2) {
                        $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $mostViewed['media'];
                        $ext = pathinfo($filename, PATHINFO_EXTENSION);
                        $thumbnail = ($ext == 'MP4') ? base_url() . "assets/images/uploads/feeds/" . str_replace('MP4', 'jpg', $mostViewed['media']) : base_url() . "assets/images/uploads/feeds/" . str_replace('mp4', 'jpg', $mostViewed['media']);
                        $mostViewed['media'] = base_url() . "assets/images/uploads/feeds/" . $mostViewed['media'];
                        $mostViewed['created'] = (string) $createdTime;
                    }

                    $checkShareUser = $this->Usersmodel->checkShareUser($user_id, $mostViewed['id']);

                    $profitAmount = 0;
                    $Profit = !empty($getProfitParams['Profit']) ? $getProfitParams['Profit'] : 0;
                    $Creator = !empty($getProfitParams['Creator']) ? $getProfitParams['Creator'] : 0;
                    $Sharer = !empty($getProfitParams['Sharer']) ? $getProfitParams['Sharer'] : 0;
                    $Adjustment = !empty($getProfitParams['Adjustment']) ? $getProfitParams['Adjustment'] : 0;
                    if ($mostViewed['user_id'] == $user_id) {
                        $profitAmount = (($mostViewed['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
                    } elseif ($checkShareUser > 0) {
                        $profitAmount = (($mostViewed['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
                    }

                   // $mostViewfeedData[] = array_merge($mostViewed, array('thumbnail' => $thumbnail, 'profitAmount' => !empty($profitAmount) ? '$' . (strlen(substr($profitAmount, -(strlen($profitAmount) - (strpos($profitAmount, '.') + 1)))) > 10 ? substr($profitAmount, 0, strpos($profitAmount, '.') + 11) : $profitAmount) : "0"));
					 
					 
					if($profitAmount>0){$profit=number_format($profitAmount, 10, '.', '');}else{$profit=0;} 
					 $mostViewfeedData[] = array_merge($mostViewed, array('thumbnail' => $thumbnail, 'profitAmount' => $profit));

                }


                $this->responseArr = array(
                    'result' => TRUE,
                    'feedData' => $mostViewfeedData
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'No feed found'
                );
            }
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please pass the required tab type'
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function deleteFeed() {
        $feed_id = $this->input->post('feed_id');
        $user_id = $this->input->post('user_id');
        if (empty($feed_id) && empty($user_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $feedData = $this->Usersmodel->getUserFeedData($feed_id);

        if ($feedData['user_id'] != $user_id) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Permission denied!! unauthorised user'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        if (($feedData['type'] == 1) || ($feedData['type'] == 2)) {
            $directorypath1 = dirname(BASEPATH) . "/assets/images/uploads/feeds/";
            $filename = $directorypath1 . $feedData['media'];
            if (file_exists($filename)) {
                @unlink($filename);
            }
        }
        $deleteResult = $this->Usersmodel->deleteUserFeed($feed_id);
        if ($deleteResult) {
            $this->responseArr = array(
                'result' => TRUE,
                'message' => 'Feed successfully deleted'
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'feedData' => 'Something went wrong please try again later.'
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function getUserProfile() {
        $user_id = $this->input->post('user_id');
        $page_number = $this->input->post('page_number');

        $no_of_feed = 10;
        $skip = ($page_number - 1) * $no_of_feed;
        $limit = $no_of_feed;
        if (empty($user_id) && empty($page_number)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        $getProfitParams = $this->Profitmodel->getProfitParams();
        $Profit = !empty($getProfitParams['Profit']) ? $getProfitParams['Profit'] : 0;
        $Creator = !empty($getProfitParams['Creator']) ? $getProfitParams['Creator'] : 0;
        $Sharer = !empty($getProfitParams['Sharer']) ? $getProfitParams['Sharer'] : 0;
        $Adjustment = !empty($getProfitParams['Adjustment']) ? $getProfitParams['Adjustment'] : 0;

        $totalAmount = 0;
        $AllUserCreatedFeedsByMonth = $this->Usersmodel->getAllUserCreatedFeedsByMonth($user_id);
        $subTotal1 = 0;
        if (!empty($AllUserCreatedFeedsByMonth)) {
            foreach ($AllUserCreatedFeedsByMonth as $creatorFeedsByMonth) {
                $subTotal1 = $subTotal1 + (($creatorFeedsByMonth['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
            }
        }

        $AllUserSharedFeedsByMonth = $this->Usersmodel->getAllUserSharedFeedsByMonth($user_id);
        $subTotal2 = 0;
        if (!empty($AllUserSharedFeedsByMonth)) {
            foreach ($AllUserSharedFeedsByMonth as $sharedFeedsByMonth) {
                $subTotal2 = $subTotal2 + (($sharedFeedsByMonth['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
            }
        }

        $totalAmount = $subTotal1 + $subTotal2;

        $getUserFeeds = $this->Usersmodel->getUserFeeds($user_id, $skip, $limit);
        if (!empty($getUserFeeds)) {

            $mostViewfeedData = array();
            foreach ($getUserFeeds as $key => $mostViewed) {
                $createdTime = strtotime($mostViewed['created']);
                if ($mostViewed['type'] == 0) {
                    $thumbnail = '';
                    $mostViewed['media'] = '';
                    $mostViewed['created'] = (string) $createdTime;
                } elseif ($mostViewed['type'] == 1) {
                    $thumbnail = '';
                    $mostViewed['media'] = base_url() . "assets/images/uploads/feeds/" . $mostViewed['media'];
                    $mostViewed['created'] = (string) $createdTime;
                } elseif ($mostViewed['type'] == 2) {
                    $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $mostViewed['media'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $thumbnail = ($ext == 'MP4') ? base_url() . "assets/images/uploads/feeds/" . str_replace('MP4', 'jpg', $mostViewed['media']) : base_url() . "assets/images/uploads/feeds/" . str_replace('mp4', 'jpg', $mostViewed['media']);
                    $mostViewed['media'] = base_url() . "assets/images/uploads/feeds/" . $mostViewed['media'];
                    $mostViewed['created'] = (string) $createdTime;
                }

                $checkShareUser = $this->Usersmodel->checkShareUser($user_id, $mostViewed['id']);

                $profitAmount = 0;

                if ($mostViewed['user_id'] == $user_id) {
                    $profitAmount = (($mostViewed['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
                } elseif ($checkShareUser > 0) {
                    $profitAmount = (($mostViewed['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
                }

                //$mostViewfeedData[] = array_merge($mostViewed, array('thumbnail' => $thumbnail, 'profitAmount' => !empty($profitAmount) ? '$' . (strlen(substr($profitAmount, -(strlen($profitAmount) - (strpos($profitAmount, '.') + 1)))) > 10 ? substr($profitAmount, 0, strpos($profitAmount, '.') + 11) : $profitAmount) : "0"));
				 if($profitAmount>0){$profit=number_format($profitAmount, 10, '.', '');}else{$profit=0;} 
				 $mostViewfeedData[] = array_merge($mostViewed, array('thumbnail' => $thumbnail, 'profitAmount' => $profit));
				
            }


            $this->responseArr = array(
                'result' => TRUE,
                'feedData' => $mostViewfeedData,
                'totalAmount' => $totalAmount
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'No feed found',
                'totalAmount' => $totalAmount
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function getAllFeed() {
        $user_id = $this->input->post('user_id');

        if (empty($user_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $allfeeds = $this->Usersmodel->getAllFeeds();

        if (!empty($allfeeds)) {
            //To get Profit Parameters
            $getProfitParams = $this->Profitmodel->getProfitParams();

            $mostViewfeedData = array();
            foreach ($allfeeds as $key => $mostViewed) {
                $createdTime = strtotime($mostViewed['created']);
                if ($mostViewed['type'] == 0) {
                    $thumbnail = '';
                    $mostViewed['media'] = '';
                    $mostViewed['created'] = (string) $createdTime;
                } elseif ($mostViewed['type'] == 1) {
                    $thumbnail = '';
                    $mostViewed['media'] = base_url() . "assets/images/uploads/feeds/" . $mostViewed['media'];
                    $mostViewed['created'] = (string) $createdTime;
                } elseif ($mostViewed['type'] == 2) {
                    $filename = dirname(BASEPATH) . "/assets/images/uploads/feeds/" . $mostViewed['media'];
                    $ext = pathinfo($filename, PATHINFO_EXTENSION);
                    $thumbnail = ($ext == 'MP4') ? base_url() . "assets/images/uploads/feeds/" . str_replace('MP4', 'jpg', $mostViewed['media']) : base_url() . "assets/images/uploads/feeds/" . str_replace('mp4', 'jpg', $mostViewed['media']);
                    $mostViewed['media'] = base_url() . "assets/images/uploads/feeds/" . $mostViewed['media'];
                    $mostViewed['created'] = (string) $createdTime;
                }

                $checkShareUser = $this->Usersmodel->checkShareUser($user_id, $mostViewed['id']);

                $profitAmount = 0;
                $Profit = !empty($getProfitParams['Profit']) ? $getProfitParams['Profit'] : 0;
                $Creator = !empty($getProfitParams['Creator']) ? $getProfitParams['Creator'] : 0;
                $Sharer = !empty($getProfitParams['Sharer']) ? $getProfitParams['Sharer'] : 0;
                $Adjustment = !empty($getProfitParams['Adjustment']) ? $getProfitParams['Adjustment'] : 0;
                if ($mostViewed['user_id'] == $user_id) {
                    $profitAmount = (($mostViewed['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
                } elseif ($checkShareUser > 0) {
                    $profitAmount = (($mostViewed['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
                }

                //$mostViewfeedData[] = array_merge($mostViewed, array('thumbnail' => $thumbnail, 'profitAmount' => !empty($profitAmount) ? '$' . (strlen(substr($profitAmount, -(strlen($profitAmount) - (strpos($profitAmount, '.') + 1)))) > 10 ? substr($profitAmount, 0, strpos($profitAmount, '.') + 11) : $profitAmount) : "0"));
				
				 if($profitAmount>0){$profit=number_format($profitAmount, 10, '.', '');}else{$profit=0;} 
				 $mostViewfeedData[] = array_merge($mostViewed, array('thumbnail' => $thumbnail, 'profitAmount' => $profit));
				
            }


            $this->responseArr = array(
                'result' => TRUE,
                'feedData' => $mostViewfeedData
            );
        } else {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'No feed found'
            );
        }

        echo json_encode($this->responseArr);
        exit();
    }

    public function addEditPaypalInfo() {
        $user_id = $this->input->post('user_id');
        $paypal_email = $this->input->post('paypal_email');
        if (empty($user_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        $checkPaypalInfo = $this->Usersmodel->checkPaypalInfo($user_id);
        if (!empty($checkPaypalInfo)) {//if exists update paypal info 
            $update_array = array('paypal_email' => $paypal_email);
            $where_array = array('user_id' => $user_id);
            $updateResult = $this->Usersmodel->updateFeed($update_array, $where_array, 'tbl_paypal');
            if ($updateResult) {
                $this->responseArr = array(
                    'result' => TRUE,
                    'message' => 'Paypal Info updated successfully'
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Something went wrong please try again later.'
                );
            }
        } else {
            $insertArray = array(
                'user_id' => $user_id,
                'paypal_email' => $paypal_email,
                'created_date' => date('Y-m-d H:i:s')
            );
            $insertResult = $this->Usersmodel->saveInfo($insertArray, 'tbl_paypal');
            if ($insertResult) {
                $this->responseArr = array(
                    'result' => TRUE,
                    'message' => 'Paypal Info successfully added'
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Something went wrong please try again later.'
                );
            }
        }

        echo json_encode($this->responseArr);
        exit();
    }

    public function getPaypalInfo() {
        $user_id = $this->input->post('user_id');
        if (empty($user_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }

        $checkPaypalInfo = $this->Usersmodel->checkPaypalInfo($user_id);
        if (!empty($checkPaypalInfo)) {

            $this->responseArr = array(
                'result' => TRUE,
                'paypalInfo' => $checkPaypalInfo
            );
        } else {

            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'No paypal info found.'
            );
        }
        echo json_encode($this->responseArr);
        exit();
    }

    public function testApi() {
        $user_id = $this->input->post('user_id');
        $getProfitParams = $this->Profitmodel->getProfitParams();

        $Profit = !empty($getProfitParams['Profit']) ? $getProfitParams['Profit'] : 0;
        $Creator = !empty($getProfitParams['Creator']) ? $getProfitParams['Creator'] : 0;
        $Sharer = !empty($getProfitParams['Sharer']) ? $getProfitParams['Sharer'] : 0;
        $Adjustment = !empty($getProfitParams['Adjustment']) ? $getProfitParams['Adjustment'] : 0;

        $AllUserCreatedFeedsByMonth = $this->Usersmodel->getAllUserCreatedFeedsByMonth($user_id);
        $subTotal1 = 0;
        if (!empty($AllUserCreatedFeedsByMonth)) {
            foreach ($AllUserCreatedFeedsByMonth as $creatorFeedsByMonth) {
                $subTotal1 = $subTotal1 + (($creatorFeedsByMonth['viewcount']) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
            }
        }

        $AllUserSharedFeedsByMonth = $this->Usersmodel->getAllUserSharedFeedsByMonth($user_id);
        $subTotal2 = 0;
        if (!empty($AllUserSharedFeedsByMonth)) {
            foreach ($AllUserSharedFeedsByMonth as $sharedFeedsByMonth) {
                $subTotal2 = $subTotal2 + (($sharedFeedsByMonth['viewcount']) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
            }
        }

        echo $totalAmount = $subTotal1 + $subTotal2;
        die;
    }

    public function addShareUser() {
        $user_id = $this->input->post('user_id');
        $feed_id = $this->input->post('feed_id');
        if (empty($user_id) && empty($feed_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $userStatus = $this->Usersmodel->getUserInfoById($user_id);
        //print_r($userStatus);die();
        if ($userStatus['user_status'] == 0) {
            $this->responseArr = array(
                'result' => FALSE,
                'loggedIn' => FALSE
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $checkShareUser = $this->Usersmodel->checkShareUser($user_id, $feed_id);
        if ($checkShareUser > 0) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'You have already shared this feed'
            );
        } else {
            $insertArray = array(
                'feed_id' => $feed_id,
                'user_id' => $user_id,
                'created' => date('Y-m-d H:i:s')
            );
            $insertResult = $this->Usersmodel->saveInfo($insertArray, 'tbl_share');
            if ($insertResult) {
                $this->responseArr = array(
                    'result' => TRUE,
                    'message' => 'Feed successfully shared'
                );
            } else {
                $this->responseArr = array(
                    'result' => FALSE,
                    'message' => 'Something went wrong please try again later.'
                );
            }
        }
        echo json_encode($this->responseArr);
        exit();
    }

//    public function monthProfit() {
//        $user_id = $this->input->post('user_id');
//        if (empty($user_id)) {
//            $this->responseArr = array(
//                'result' => FALSE,
//                'message' => 'Please provide the required parameters!'
//            );
//            echo json_encode($this->responseArr);
//            exit();
//        }
//        $first_day_this_month = date('Y-m-d H:i:s', strtotime(date('Y-m-01 00:00:59')));
//        $last_day_this_month = date('Y-m-d H:i:s', strtotime(date('Y-m-t 23:59:59')));
//        $UserspostsData = $this->Usersmodel->getUserFeedsByMonth($user_id, $first_day_this_month, $last_day_this_month);
//        $profitAmount = 0;
//        $SharerAmount = 0;
//        foreach ($UserspostsData as $Usersposts) {
//
//            $Profit = !empty($Usersposts['Profit']) ? $Usersposts['Profit'] : 0;
//            $Creator = !empty($Usersposts['Creator']) ? $Usersposts['Creator'] : 0;
//            $Sharer = !empty($Usersposts['Sharer']) ? $Usersposts['Sharer'] : 0;
//            $Adjustment = !empty($Usersposts['Adjustment']) ? $Usersposts['Adjustment'] : 0;
//            $viewcount = !empty($Usersposts['viewcount']) ? $Usersposts['viewcount'] : 0;
//            $shares = !empty($Usersposts['shares']) ? $Usersposts['shares'] : 0;
//            $profitAmount = $profitAmount + (($viewcount) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
//            $SharerAmount = $SharerAmount + (($viewcount) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
//        }
//
//        $this->responseArr = array(
//            'result' => TRUE,
//            'user_id' => $user_id,
//            'profitAmount' => $profitAmount,
//            'SharerAmount' => $SharerAmount,
//            'message' => 'Total Profit by month.',
//        );
//        echo json_encode($this->responseArr);
//        exit();
//    }

    /* 	public function TotalMonthProfit() {                 
      $user_id = $this->input->post('user_id');
      if (empty($user_id)) {
      $this->responseArr = array(
      'result' => FALSE,
      'message' => 'Please provide the required parameters!'
      );
      echo json_encode($this->responseArr);
      exit();
      }
      } */

//    public function TotalMonthsProfit() {
//        $user_id = $this->input->post('user_id');
//        if (empty($user_id)) {
//            $this->responseArr = array(
//                'result' => FALSE,
//                'message' => 'Please provide the required parameters!'
//            );
//            echo json_encode($this->responseArr);
//            exit();
//        }
//
//        $UserspostsData = $this->Usersmodel->getUserFeedsAllMonth($user_id);
//        $profitAmount = 0;
//
//        foreach ($UserspostsData as $Usersposts) {
//
//            $Profit = !empty($Usersposts['Profit']) ? $Usersposts['Profit'] : 0;
//            $Creator = !empty($Usersposts['Creator']) ? $Usersposts['Creator'] : 0;
//            $Sharer = !empty($Usersposts['Sharer']) ? $Usersposts['Sharer'] : 0;
//            $Adjustment = !empty($Usersposts['Adjustment']) ? $Usersposts['Adjustment'] : 0;
//            $viewcount = !empty($Usersposts['viewcount']) ? $Usersposts['viewcount'] : 0;
//            $shares = !empty($Usersposts['shares']) ? $Usersposts['shares'] : 0;
//            $profitAmount = $profitAmount + (($viewcount) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
//        }
//
//        $UserSharesAllMonth = $this->Usersmodel->getUserSharesAllMonth($user_id);
//        $SharerAmount = 0;
//
//        foreach ($UserSharesAllMonth as $UsersSharesposts) {
//
//            $Profit = !empty($UsersSharesposts['Profit']) ? $UsersSharesposts['Profit'] : 0;
//            $Creator = !empty($UsersSharesposts['Creator']) ? $UsersSharesposts['Creator'] : 0;
//            $Sharer = !empty($UsersSharesposts['Sharer']) ? $UsersSharesposts['Sharer'] : 0;
//            $Adjustment = !empty($UsersSharesposts['Adjustment']) ? $UsersSharesposts['Adjustment'] : 0;
//            $viewcount = !empty($UsersSharesposts['viewcount']) ? $UsersSharesposts['viewcount'] : 0;
//            $shares = !empty($UsersSharesposts['shares']) ? $UsersSharesposts['shares'] : 0;
//            $SharerAmount = $SharerAmount + (($viewcount) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
//            ;
//        }
//
//        $TotalAmount = $SharerAmount + $profitAmount;
//        $Userallpayments = $this->Usersmodel->getUserallPayments($user_id);
//
//        $received = $Userallpayments[0]['payment'];
//        $Unreceived = $TotalAmount - $received;
//
//        $this->responseArr = array(
//            'result' => TRUE,
//            'user_id' => $user_id,
//            'profitAmount' => $profitAmount,
//            'SharerAmount' => $SharerAmount,
//            'TotalAmount' => $TotalAmount,
//            'ReceivedProfit' => $received,
//            'UnreceivedProfit' => $Unreceived,
//            'today_profit' => json_decode($this->todayProfit($user_id)),
//            'message' => 'Total Profit by month.'
//        );
//        echo json_encode($this->responseArr);
//        exit();
//    }
//    public function todayProfit($user_id = null) {
//        if (empty($user_id)) {
//            $this->responseArr = array(
//                'result' => FALSE,
//                'message' => 'Please provide the required parameters!'
//            );
//            echo json_encode($this->responseArr);
//            exit();
//        }
//
//        $UserspostsData = $this->Usersmodel->getUserFeedsAllToday($user_id);
//        $profitAmount = 0;
//
//        foreach ($UserspostsData as $Usersposts) {
//
//            $Profit = !empty($Usersposts['Profit']) ? $Usersposts['Profit'] : 0;
//            $Creator = !empty($Usersposts['Creator']) ? $Usersposts['Creator'] : 0;
//            $Sharer = !empty($Usersposts['Sharer']) ? $Usersposts['Sharer'] : 0;
//            $Adjustment = !empty($Usersposts['Adjustment']) ? $Usersposts['Adjustment'] : 0;
//            $viewcount = !empty($Usersposts['viewcount']) ? $Usersposts['viewcount'] : 0;
//            $shares = !empty($Usersposts['shares']) ? $Usersposts['shares'] : 0;
//            $profitAmount = $profitAmount + (($viewcount) * ($Profit) * ($Creator / 100) * ($Adjustment / 100));
//        }
//
//        $UserSharesAllMonth = $this->Usersmodel->getUserSharesAllToday($user_id);
//        $SharerAmount = 0;
//
//        foreach ($UserSharesAllMonth as $UsersSharesposts) {
//
//            $Profit = !empty($UsersSharesposts['Profit']) ? $UsersSharesposts['Profit'] : 0;
//            $Creator = !empty($UsersSharesposts['Creator']) ? $UsersSharesposts['Creator'] : 0;
//            $Sharer = !empty($UsersSharesposts['Sharer']) ? $UsersSharesposts['Sharer'] : 0;
//            $Adjustment = !empty($UsersSharesposts['Adjustment']) ? $UsersSharesposts['Adjustment'] : 0;
//            $viewcount = !empty($UsersSharesposts['viewcount']) ? $UsersSharesposts['viewcount'] : 0;
//            $shares = !empty($UsersSharesposts['shares']) ? $UsersSharesposts['shares'] : 0;
//            $SharerAmount = $SharerAmount + (($viewcount) * ($Profit) * ($Sharer / 100) * ($Adjustment / 100));
//        }
//
//        $TotalAmount = $SharerAmount + $profitAmount;
//        $Userallpayments = $this->Usersmodel->getUserallPayments($user_id);
//
//        $received = $Userallpayments[0]['payment'];
//        $Unreceived = $TotalAmount - $received;
////strlen(substr(strrchr($str, "."), 1));
//        $this->responseArr = array(
//            'result' => TRUE,
//            'user_id' => $user_id,
//            'profitAmount' => !empty($profitAmount) ? number_format($profitAmount, 10, '.', '') : 0.00,
//            'SharerAmount' => !empty($SharerAmount) ? number_format($SharerAmount, 10, '.', '') : 0.00,
//            'TotalAmount' => !empty($TotalAmount) ? number_format($TotalAmount, 10, '.', '') : 0.00,
//            'ReceivedProfit' => !empty($received) ? number_format($received, 10, '.', '') : 0.00,
//            'UnreceivedProfit' => !empty($Unreceived) ? number_format($Unreceived, 10, '.', '') : 0.00,
//            'message' => 'Total Profit of today.'
//        );
//        echo json_encode($this->responseArr);
//        exit();
//    }
//2016-01-23 09:38:49

    public function profitAmount() {
        $user_id = $this->input->post('user_id');
        if (empty($user_id)) {
            $this->responseArr = array(
                'result' => FALSE,
                'message' => 'Please provide the required parameters!'
            );
            echo json_encode($this->responseArr);
            exit();
        }
        $TodayProfitAmount = 0;
        $TodaySharerAmount = 0;
        $TotalAmountToday = 0;
        $profitAmountMonth = 0;
        $SharerAmountMonth = 0;
        $totalMonthProfit = 0;
        $profitAmountAllMonth = 0;
        $SharerAmountAllMonth = 0;
        $received = 0;
        $Unreceived = 0;

        $UserspostsDataToday = $this->Usersmodel->getUserFeedsAllToday($user_id);
    //   echo "<pre>"; print_r($UserspostsDataToday);
        if ($UserspostsDataToday) {
            foreach ($UserspostsDataToday as $UserspostsToday) {

                $TodayProfit = !empty($UserspostsToday['Profit']) ? $UserspostsToday['Profit'] : 0;
                $TodayCreator = !empty($UserspostsToday['Creator']) ? $UserspostsToday['Creator'] : 0;
                $TodaySharer = !empty($UserspostsToday['Sharer']) ? $UserspostsToday['Sharer'] : 0;
                $TodayAdjustment = !empty($UserspostsToday['Adjustment']) ? $UserspostsToday['Adjustment'] : 0;
                $Todayviewcount = !empty($UserspostsToday['viewcount']) ? $UserspostsToday['viewcount'] : 0;
                $Todayshares = !empty($UserspostsToday['shares']) ? $UserspostsToday['shares'] : 0;
                $TodayProfitAmount = $TodayProfitAmount + (($Todayviewcount) * ($TodayProfit) * ($TodayCreator / 100) * ($TodayAdjustment / 100));
            }
        }

        $UserSharesAllToday = $this->Usersmodel->getUserSharesAllToday($user_id);
        if ($UserSharesAllToday) {
            foreach ($UserSharesAllToday as $UsersSharespostsToday) {

                $TodayProfit = !empty($UsersSharespostsToday['Profit']) ? $UsersSharespostsToday['Profit'] : 0;
                $TodayCreator = !empty($UsersSharespostsToday['Creator']) ? $UsersSharespostsToday['Creator'] : 0;
                $TodaySharer = !empty($UsersSharespostsToday['Sharer']) ? $UsersSharespostsToday['Sharer'] : 0;
                $TodayAdjustment = !empty($UsersSharespostsToday['Adjustment']) ? $UsersSharespostsToday['Adjustment'] : 0;
                $Todayviewcount = !empty($UsersSharespostsToday['viewcount']) ? $UsersSharespostsToday['viewcount'] : 0;
                $Todayshares = !empty($UsersSharespostsToday['shares']) ? $UsersSharespostsToday['shares'] : 0;
                $TodaySharerAmount = $TodaySharerAmount + (($Todayviewcount) * ($TodayProfit) * ($TodaySharer / 100) * ($TodayAdjustment / 100));
            }
        }

    $TotalAmountToday = (float) $TodaySharerAmount + (float) $TodayProfitAmount;

//This month============================================================================		
		
		 $d = new DateTime('first day of this month');
		 $first_day_this_month = $d->format('Y-m-d H:i:s');
		
		$e = new DateTime('last day of this month');
		$last_day_this_month  = $e->format('Y-m-d H:i:s');
	
		

     $UserspostsDataMonth = $this->Usersmodel->getUserFeedsByMonth($user_id, $first_day_this_month, $last_day_this_month);
	 
	 

        if ($UserspostsDataMonth) {
            foreach ($UserspostsDataMonth as $UserspostsMonth) {
                $ProfitMonth = !empty($UserspostsMonth['Profit']) ? $UserspostsMonth['Profit'] : 0;
                $CreatorMonth = !empty($UserspostsMonth['Creator']) ? $UserspostsMonth['Creator'] : 0;
                $SharerMonth = !empty($UserspostsMonth['Sharer']) ? $UserspostsMonth['Sharer'] : 0;
                $AdjustmentMonth = !empty($UserspostsMonth['Adjustment']) ? $UserspostsMonth['Adjustment'] : 0;
                $viewcountMonth = !empty($UserspostsMonth['viewcount']) ? $UserspostsMonth['viewcount'] : 0;
                $sharesMonth = !empty($UserspostsMonth['shares']) ? $UserspostsMonth['shares'] : 0;
                $profitAmountMonth = $profitAmountMonth + (($viewcountMonth) * ($ProfitMonth) * ($CreatorMonth / 100) * ($AdjustmentMonth / 100));
              
            }
    
        }
		
		
		$UserssharesDataMonth = $this->Usersmodel->getUserSharesByMonth($user_id, $first_day_this_month, $last_day_this_month);

			if ($UserssharesDataMonth) {

            foreach ($UserssharesDataMonth as $UsersharesMonth) {
                $ProfitMonth = !empty($UsersharesMonth['Profit']) ? $UsersharesMonth['Profit'] : 0;
                $CreatorMonth = !empty($UsersharesMonth['Creator']) ? $UsersharesMonth['Creator'] : 0;
                $SharerMonth = !empty($UsersharesMonth['Sharer']) ? $UsersharesMonth['Sharer'] : 0;
                $AdjustmentMonth = !empty($UsersharesMonth['Adjustment']) ? $UsersharesMonth['Adjustment'] : 0;
                $viewcountMonth = !empty($UsersharesMonth['viewcount']) ? $UsersharesMonth['viewcount'] : 0;
                $sharesMonth = !empty($UsersharesMonth['shares']) ? $UsersharesMonth['shares'] : 0;
                
                $SharerAmountMonth = $SharerAmountMonth + (($viewcountMonth) * ($ProfitMonth) * ($SharerMonth / 100) * ($AdjustmentMonth / 100));
            }
        
         
        }
		
	
		    $totalMonthProfit += $profitAmountMonth + $SharerAmountMonth;
		
		
//All months============================================================================
        $UserspostsDataAllMonth = $this->Usersmodel->getUserFeedsAllMonth($user_id);
		
		
        if ($UserspostsDataAllMonth) {

            foreach ($UserspostsDataAllMonth as $UserspostsAllMonth) {

                $ProfitAllMonth = !empty($UserspostsAllMonth['Profit']) ? $UserspostsAllMonth['Profit'] : 0;
                $CreatorAllMonth = !empty($UserspostsAllMonth['Creator']) ? $UserspostsAllMonth['Creator'] : 0;
                $SharerAllMonth = !empty($UserspostsAllMonth['Sharer']) ? $UserspostsAllMonth['Sharer'] : 0;
                $AdjustmentAllMonth = !empty($UserspostsAllMonth['Adjustment']) ? $UserspostsAllMonth['Adjustment'] : 0;
                $viewcountAllMonth = !empty($UserspostsAllMonth['viewcount']) ? $UserspostsAllMonth['viewcount'] : 0;
                $sharesAllMonth = !empty($UserspostsAllMonth['shares']) ? $UserspostsAllMonth['shares'] : 0;
                $profitAmountAllMonth = $profitAmountAllMonth + (($viewcountAllMonth) * ($ProfitAllMonth) * ($CreatorAllMonth / 100) * ($AdjustmentAllMonth / 100));
            }
        }
        $UserSharesAllMonth = $this->Usersmodel->getUserSharesAllMonth($user_id);

        if ($UserSharesAllMonth) {
            foreach ($UserSharesAllMonth as $UsersSharesposts) {

                $ProfitAllMonth = !empty($UsersSharesposts['Profit']) ? $UsersSharesposts['Profit'] : 0;
                $CreatorAllMonth = !empty($UsersSharesposts['Creator']) ? $UsersSharesposts['Creator'] : 0;
                $SharerAllMonth = !empty($UsersSharesposts['Sharer']) ? $UsersSharesposts['Sharer'] : 0;
                $AdjustmentAllMonth = !empty($UsersSharesposts['Adjustment']) ? $UsersSharesposts['Adjustment'] : 0;
                $viewcountAllMonth = !empty($UsersSharesposts['viewcount']) ? $UsersSharesposts['viewcount'] : 0;
                $sharesAllMonth = !empty($UsersSharesposts['shares']) ? $UsersSharesposts['shares'] : 0;
                $SharerAmountAllMonth = $SharerAmountAllMonth + (($viewcountAllMonth) * ($ProfitAllMonth) * ($SharerAllMonth / 100) * ($AdjustmentAllMonth / 100));
            }
        }
		$TotalAmountAllMonth = (float) $SharerAmountAllMonth + (float) $profitAmountAllMonth;
        $Userallpayments = $this->Usersmodel->getUserallPayments($user_id);

        $received = $Userallpayments[0]['payment'];
        $Unreceived = (float) $TotalAmountAllMonth - (float) $received;



        $this->responseArr = array(
            'result' => TRUE,
            'user_id' => $user_id,
            'todayProfit' => !empty($TotalAmountToday) ? number_format($TotalAmountToday, 10, '.', '') : "$TotalAmountToday",
            'thisMonthProfit' => !empty($totalMonthProfit) ? number_format($totalMonthProfit, 10, '.', '') : "$totalMonthProfit",
            'unreceivedProfit' => !empty($Unreceived) ? number_format($Unreceived, 10, '.', '') : "$Unreceived",
            'totalProfit' => !empty($TotalAmountAllMonth) ? number_format($TotalAmountAllMonth, 10, '.', '') : "$TotalAmountAllMonth",
        );
        echo json_encode($this->responseArr);
        exit();
    }

}
