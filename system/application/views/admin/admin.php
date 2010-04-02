<?php
    if (!empty($configwritewarning))
    {
        ?>
            <div class="error">
                WARNING: Unable to write to config file. Please ensure the web server can write to
                <em>system/application/config/easydeposit.php</em> before proceeding.
            </div>
        <?php
    }

    if (!empty($defaultpasswordwarning))
    {
        ?>
            <div class="error">
                WARNING: You are using the default EasyDeposit password. For
                security reasons should change this using the menu option below.
            </div>
        <?php
    }
?>

<p>
<?php if (empty($configwritewarning)) { ?>
    You can perform the following tasks from the administrative interface:

    <ul>
    <li><a href="./admin/credentials">Change admin username or password</a></li>
    <li>Edit a configuration setting</li>
    <li>Edit a file</li>
    <li>More to follow...</li>
    </ul>
<?php } ?>

    Go back to the <a href="./">homepage</a> or
    <a href="./admin/logout">logout</a> of the administrative interface.

</p>