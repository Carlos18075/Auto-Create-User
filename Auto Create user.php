<?php

// SESSION OPENING
function OpenSession()
{
    try {
        $api_url = "http://localhost/apirest.php";
        $user_token = "QU6a1yy6Fpf2aVxg4rFzjTiZUcpFbGOyB8uBApFA";
        $app_token = "Ia9UgZflvuqO7P1Sg5u4Qz3QsylIGfh6CMFxbp5l";
        $ch = curl_init();
        $url = $api_url . "/initSession?Content-Type=%20application/json&app_token=" . $app_token . "&user_token=" . $user_token;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($json, true);
        $sess_token = $obj['session_token'];
        $headers = array(
            'Content-Type: application/json',
            'App-Token: ' . $app_token,
            'Session-Token: ' . $sess_token
        );

        return $headers;
    } catch (Exception $e) {
        echo "session opening error: $e->getMessage()";
    }

}


// USER CREATION

function CreateUser($name,$realname = null,$firstname = null ,$entities_id,$pwd, $headers){
    try {
        $data = [
            'input' => [
                'name' => $name,
                "password" => $pwd,
                'realname' => $realname,
                'firstname' => $firstname,
                'language' => 'en_GB',
                'is_active' => 1,
                'entities_id' => $entities_id,
            ]
        ];
        $input = json_encode($data);

        $api_url = "http://localhost/apirest.php";
        $url=$api_url . "/User";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS,$input);

        $json = curl_exec($ch);
        curl_close ($ch);

        $res = json_decode($json, true);

        if ($res[0]== "ERROR_GLPI_ADD") {
            return ["status" => false, "message" => $res[1], "user" => $name];

        }
        else {
            return ["status" => true];
        }

    }
    catch (Exception $e) {echo "user creation error: $e->getMessage()";}
}


function AddEmail($name, $headers)
{
// GET USER ID
    try {
        $api_url="http://localhost/apirest.php";
        $url = $api_url . "/search/User?criteria[0][field]=1&criteria[0][searchtype]=contains&criteria[0][value]=^" . $name . "$&forcedisplay[0]=2";


        //$url=$api_url . "/listSearchOptions/User";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //curl_setopt($ch, CURLOPT_POSTFIELDS,$input);
        $json = curl_exec($ch);
        curl_close($ch);


    } catch (Exception $e) {
        echo "get user id error: $e->getMessage()";
    }

    $obj = json_decode($json, true);


// ADD USER EMAIL
    try {
        $user_id = $obj['data']['0']['2'];

        $api_url="http://localhost/apirest.php";
        $url = $api_url . "/User/" . $user_id . "/UserEmail/";
        $fields = '{"input":{"users_id":' . $user_id . ',"is_default":1,"is_dynamic":0,"email":"' . $name . '"}}';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $json = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($json, true);
//print_r($res);
        return true;

    } catch (Exception $e) {
        echo "email add error: $e->getMessage()";
    }

}

//  USER TEST
$name= "hello2@coucou.fr";
$realname = 'test';
$firstname = 'bbb';
$entities_id = 3;
$pwd = sha1($name);
$headers = OpenSession();

$CreateUser= CreateUser($name,$realname,$firstname,$entities_id,$pwd, $headers);

if ($CreateUser['status']) {

   $AddEmail = AddEmail($name, $headers);
   if (!$AddEmail){ echo "error for user: $name while adding email";}
   else {echo "user: $name added successfuly";}


}

else { echo "error: ". $CreateUser['message']. " User: ". $CreateUser['user'] ;}
