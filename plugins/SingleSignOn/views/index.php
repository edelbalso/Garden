<?php if (!defined('APPLICATION')) exit();
echo $this->Form->Open();
echo $this->Form->Errors();
?>
<h1><?php echo Gdn::Translate('Single Sign-on'); ?></h1>
<ul>
   <li>
      <?php
         echo $this->Form->CheckBox('EnableSSO', "Enable Single Sign-on");
      ?>
   </li>
</ul>
<div class="Info"><?php echo Translate('Copy & paste the following value into the single sign-on configuration screen in the external application:'); ?></div>
<table class="Label AltColumns">
   <thead>
      <tr>
         <th><?php echo Gdn::Translate('Setting'); ?></th>
         <th class="Alt"><?php echo Gdn::Translate('Value'); ?></th>
      </tr>
   </thead>
   <tbody>
      <tr>
         <th><?php echo Gdn::Translate('Vanilla Cookie Domain'); ?></th>
         <td class="Alt"><?php echo Gdn::Config('Garden.Cookie.Domain', ''); ?></td>
      </tr>
   </tbody>
</table>
<div class="Info"><?php echo Translate('Grab these values from the single sign-on configuration screen in the external application:'); ?></div>
<ul>
   <li>
      <!-- <div class="Info"><?php echo Translate("The \"Authenticate Url\" is the url in your external application where Vanilla will look for user information. Get this value from your external application's single sign-on configuration screen."); ?></div> -->
      <?php
         echo $this->Form->Label('Authenticate Url', 'Garden.Authenticator.AuthenticateUrl');
         echo $this->Form->TextBox('Garden.Authenticator.AuthenticateUrl', array('style' => 'width: 500px;'));
      ?>
   </li>
   <li>
      <!-- <div class="Info"><?php echo Translate("The \"Registration Url\" is the url in your external application where users register for membership."); ?></div> -->
      <?php
         echo $this->Form->Label('Registration Url', 'Garden.Authenticator.RegisterUrl');
         echo $this->Form->TextBox('Garden.Authenticator.RegisterUrl', array('style' => 'width: 500px;'));
      ?>
   </li>
   <li>
      <!-- <div class="Info"><?php echo Translate("The \"Sign-in Url\" is the url in your external application where users sign in."); ?></div> -->
      <?php
         echo $this->Form->Label('Sign-in Url', 'Garden.Authenticator.SignInUrl');
         echo $this->Form->TextBox('Garden.Authenticator.SignInUrl', array('style' => 'width: 500px;'));
      ?>
   </li>
   <li>
      <!-- <div class="Info"><?php echo Translate("The \"Sign-out Url\" is the url in your external application where users sign out."); ?></div> -->
      <?php
         echo $this->Form->Label('Sign-out Url', 'Garden.Authenticator.SignOutUrl');
         echo $this->Form->TextBox('Garden.Authenticator.SignOutUrl', array('style' => 'width: 500px;'));
      ?>
   </li>
</ul>
<?php echo $this->Form->Close('Save');