<?php
    require('lib/http.php');
    require('lib/oauth_client.php');
    
    $client = new oauth_client_class;
    $client->server = 'Microsoft';
    $client->redirect_uri = 'http://'.$_SERVER['HTTP_HOST'].'';

    $client->client_id = '0000000040161A07'; $application_line = __LINE__;
    $client->client_secret = 'q5n-sflXQBTdJcaWZDrfBddpm0oie6uq23Lf';

    if(strlen($client->client_id) == 0
    || strlen($client->client_secret) == 0)
        die('Please go to Microsoft Live Connect Developer Center page '.
            'https://manage.dev.live.com/AddApplication.aspx and create a new'.
            'application, and in the line '.$application_line.
            ' set the client_id to Client ID and client_secret with Client secret. '.
            'The callback URL must be '.$client->redirect_uri.' but make sure '.
            'the domain is valid and can be resolved by a public DNS.');

    /* API permissions
     */
    $client->scope = 'wl.basic wl.emails wl.birthday';
    if(($success = $client->Initialize()))
    {
        if(($success = $client->Process()))
        {
            if(strlen($client->authorization_error))
            {
                $client->error = $client->authorization_error;
                $success = false;
            }
            elseif(strlen($client->access_token))
            {
                $success = $client->CallAPI(
                    'https://apis.live.net/v5.0/me',
                    'GET', array(), array('FailOnAccessError'=>true), $user);
            }
        }
        $success = $client->Finalize($success);
    }
    if($client->exit)
        exit;
    if($success)
    {
        session_start();
        echo $_SESSION['userdata']=$user;
        header("location: index.php");
    }
    else
    {
      echo 'Error:'.HtmlSpecialChars($client->error); 
    }
?>