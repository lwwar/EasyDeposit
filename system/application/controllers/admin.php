<?php

require_once('EasyDeposit.php');

class Admin extends EasyDeposit
{
    function Admin()
    {
        // State that this is an authentication class
        EasyDeposit::_adminInterface();

        // Initalise the parent
        parent::EasyDeposit();
    }

    function index()
    {
        // Set the page title
        $data['page_title'] = 'Menu';

        // Warn the user if they are using the default password
        if ($this->config->item('easydeposit_adminpassword') == '6da12e83ef06d1d59884a5ca724cbc75')
        {
            $data['defaultpasswordwarning'] = true;
        }

        // Display the header, page, and footer
        $this->load->view('admin/header', $data);
        $this->load->view('admin/admin', $data);
        $this->load->view('admin/footer');
    }

    function logout()
    {
        // Unset the admin session variable
        unset($_SESSION['easydeposit-admin-isadmin']);

        // Go to the home page
        redirect('/');
    }

    function steps()
    {
        // Set the page title
        $data['page_title'] = 'Configure deposit steps';

        // Set the steps for display
        $steps = $this->config->item('easydeposit_steps');
        $stepcounter = 0;
        foreach ($steps as $step)
        {
            // Get the details of the step
            $data['currentsteps'][$stepcounter++] = $step;
        }

        // Look up details of each step
        if ($controllers = opendir('system/application/controllers'))
        {
            while (($controller = readdir($controllers)) !== FALSE)
            {
                if (strstr($controller, '.php') == '.php')
                {
                    $stepcode = fopen('system/application/controllers/' . $controller, 'r');
                    $classname = str_replace('.php', '', $controller);
                    while (!feof($stepcode))
                    {
                        $line = trim(fgets($stepcode, 4096));
                        if (strpos($line, '// Name: ') === 0)
                        {
                            $data['allsteps'][$classname]['name'] = substr($line, 9);
                        }
                        else if (strpos($line, '// Description: ') === 0)
                        {
                            $data['allsteps'][$classname]['description'] = substr($line, 15);
                        }
                        else if (strpos($line, '// Notes: ') === 0)
                        {
                            $data['allsteps'][$classname]['notes'] = substr($line, 10);
                        }
                    }
                    fclose($stepcode);
                }
            }
            closedir($controllers);
        }

        // Display the header, page, and footer
        $this->load->view('admin/header', $data);
        $this->load->view('admin/steps', $data);
        $this->load->view('admin/footer');
    }

    function editwelcome()
    {
        // Did the user click 'cancel'?
        if (isset($_POST['cancel']))
        {
            redirect('/admin');
        }

        $this->form_validation->set_rules('content', 'Welcome screen', 'required');
        if (($this->form_validation->run() == FALSE) || (isset($_POST['revert'])))
        {
            // Set the page title
            $data['page_title'] = 'Edit the welcome screen content';

            // Load javascript
            $data['javascript'] = array('tiny_mce/tiny_mce.js');

            // Load the welcome screen html or revert to original?
            if (isset($_POST['revert']))
            {
                $data['html'] = file_get_contents('system/application/views/defaults/welcome_message.php');
            }
            else
            {
                $data['html'] = file_get_contents('system/application/views/welcome_message.php');
            }

            // Check we can write to the welcome screen
            if (is_writable('system/application/views/welcome_message.php'))
            {
                $data['canwrite'] = true;
            }

            // Display the header, page, and footer
            $this->load->view('admin/header', $data);
            $this->load->view('admin/editwelcome', $data);
            $this->load->view('admin/footer');
        }
        else
        {
            // Update the content
            file_put_contents('system/application/views/welcome_message.php', html_entity_decode(set_value('content')));

            // Go to the admin home page
            redirect('/admin');
        }
    }

    function credentials()
    {
        // Did the user click 'cancel'?
        if (isset($_POST['cancel']))
        {
            redirect('/admin');
        }

        // Set the current username
        $data['username'] = $this->config->item('easydeposit_adminusername');

        $this->form_validation->set_rules('username', 'Username', 'xss_clean|_clean|required');
        $this->form_validation->set_rules('oldpassword', 'Old password', 'xss_clean|_clean|callback__checkoldpassword|required');
        $this->form_validation->set_rules('newpassword', 'New password', 'xss_clean|_clean|required');
        if ($this->form_validation->run() == FALSE)
        {
            // Set the page title
            $data['page_title'] = 'Change the administrator username or password';

            // Display the header, page, and footer
            $this->load->view('admin/header', $data);
            $this->load->view('admin/credentials', $data);
            $this->load->view('admin/footer');
        }
        else
        {
            // Update the username and password
            $updates['easydeposit_adminusername'] = set_value('username');
            $updates['easydeposit_adminpassword'] = md5(set_value('newpassword'));
            $this->_updateconfigkeys($updates);

            // Go to the admin home page
            redirect('/admin');
        }
    }

    function coresettings()
    {
        // Set the page title
        $data['page_title'] = 'Edit the core settings';

        // Set the current setting values
        $data['supportemail'] = $this->config->item('easydeposit_supportemail');
        $data['librarylocation'] = $this->config->item('easydeposit_librarylocation');                

        // Display the header, page, and footer
        $this->load->view('admin/header', $data);
        $this->load->view('admin/coresettings', $data);
        $this->load->view('admin/footer');
    }

    function supportemail()
    {
        // Did the user click 'cancel'?
        if (isset($_POST['cancel']))
        {
            redirect('/admin/coresettings');
        }

        // Set the current username
        $data['supportemail'] = $this->config->item('easydeposit_supportemail');

        $this->form_validation->set_rules('supportemail', 'Support Email', 'xss_clean|_clean|required');
        if ($this->form_validation->run() == FALSE)
        {
            // Set the page title
            $data['page_title'] = 'Change the support email address';

            // Display the header, page, and footer
            $this->load->view('admin/header', $data);
            $this->load->view('admin/supportemail', $data);
            $this->load->view('admin/footer');
        }
        else
        {
            // Update the support email
            $updates['easydeposit_supportemail'] = set_value('supportemail');
            $this->_updateconfigkeys($updates);

            // Go to the core settings page
            redirect('/admin/coresettings');
        }
    }

    function librarylocation()
    {
        // Did the user click 'cancel'?
        if (isset($_POST['cancel']))
        {
            redirect('/admin/coresettings');
        }

        // Set the current username
        $data['librarylocation'] = $this->config->item('easydeposit_librarylocation');

        $this->form_validation->set_rules('librarylocation', 'SWORDAPP PHP Library Location', 'xss_clean|_clean|callback__checkswordappapi|required');
        if ($this->form_validation->run() == FALSE)
        {
            // Set the page title
            $data['page_title'] = 'Change the location of the SWORDAPP PHP Library';

            // Display the header, page, and footer
            $this->load->view('admin/header', $data);
            $this->load->view('admin/librarylocation', $data);
            $this->load->view('admin/footer');
        }
        else
        {
            // Update the support email
            $updates['easydeposit_librarylocation'] = set_value('librarylocation');
            $this->_updateconfigkeys($updates);

            // Go to the core settings page
            redirect('/admin/coresettings');
        }
    }

    function systemcheck()
    {
        // Set the page title
        $data['page_title'] = 'System check';

        // See if we can write to the easydeposit.php config file
        $data['configwritewarning'] = false;
        if (!is_writable('system/application/config/easydeposit.php'))
        {
            $data['configwritewarning'] = true;
        }

        // See if we can write to the package upload directory
        $path = str_replace('index.php', '', $_SERVER["SCRIPT_FILENAME"]);
        $savepath = $path . $this->config->item('easydeposit_uploadfiles_savedir');
        $data['packagelocation'] = $savepath;
        $data['packagewritewarning'] = false;
        if (!is_writable($savepath))
        {
            $data['packagewritewarning'] = true;
        }

        // Warn the user if they are using the default password
        $data['defaultpasswordwarning'] = false;
        if ($this->config->item('easydeposit_adminpassword') == '6da12e83ef06d1d59884a5ca724cbc75')
        {
            $data['defaultpasswordwarning'] = true;
        }

        // Is the curl function available
        $data['curlfunctionwarning'] = false;
        if (!function_exists('curl_exec'))
        {
            $data['curlfunctionwarning'] = true;
        }

        // Is the curl function available
        $data['sxmlfunctionwarning'] = false;
        if (!function_exists('simplexml_load_string'))
        {
            $data['sxmlfunctionwarning'] = true;
        }

        // Is the zip function available
        $data['zipfunctionwarning'] = false;
        if (!function_exists('zip_open'))
        {
            $data['zipfunctionwarning'] = true;
        }

        // Is the ldap function available
        $data['ldapfunctionwarning'] = false;
        if (!function_exists('ldap_connect'))
        {
            $data['ldapfunctionwarning'] = true;
        }

        // Display the header, page, and footer
        $this->load->view('admin/header', $data);
        $this->load->view('admin/systemcheck', $data);
        $this->load->view('admin/footer');
    }

    function _checkoldpassword($password)
    {
        // Get the username
        $username = $_POST['username'];

        // Check the username and password are correct
        if (md5($password) != $this->config->item('easydeposit_adminpassword'))
        {
            $this->form_validation->set_message('_checkoldpassword', 'Old password is incorrect');
            return FALSE;
        }

        // Must be OK
        return TRUE;
    }

    function _checkswordappapi($apipath)
    {
        // Check the file exists
        if (!file_exists($apipath . '/swordappclient.php')) {
            $this->form_validation->set_message('_checkswordappapi', 'SWORDAPP API Library not found at <em>' . $apipath . '</em>');
            return FALSE;
        }

        // Must be OK
        return TRUE;
    }

    function _updateconfigkeys($updates)
    {
        // As a small bit of protection, make sure the user is an admin
        if (empty($_SESSION['easydeposit-admin-isadmin'])) {
            return;
        }

        // Open the config file to read
        $configin = fopen('system/application/config/easydeposit.php', 'r');
        $save = '';
        while (!feof($configin))
        {
            $line = trim(fgets($configin, 4096));
            foreach($updates as $key => $value)
            {
                if ((strpos($line, '$config[' . "'" . $key . "'" . ']') === 0) ||
                    (strpos($line, '$config["' . $key . '"]') === 0))
                {
                    $value = str_replace('"', '\"', $value);
                    $value = str_replace('&quot;', '\"', $value);          
                    $line = '$config[' . "'" . $key . "'" . '] = "' . $value . '";';
                }
            }
            if ($line == '?' . '>')
            {
                $save .= $line;
                break;
            }
            else
            {
                $save .= $line . "\n";
            }
        }
        @fclose($configin);

        // Save the config file
        file_put_contents('system/application/config/easydeposit.php', $save);
    }
}

?>