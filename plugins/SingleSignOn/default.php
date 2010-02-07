<?php if (!defined('APPLICATION')) exit();

/*
Copyright 2009 Mark O'Sullivan
This file is part of the Vanilla Single Sign-on plugin for Vanilla 2.
The Vanilla Single Sign-on plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
The Vanilla Single Sign-on plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with the Vanilla Single Sign-on plugin.  If not, see <http://www.gnu.org/licenses/>.
Contact Mark O'Sullivan at mark [at] vanillaforums [dot] com
*/

// Define the plugin:
$PluginInfo['SingleSignOn'] = array(
   'Name' => 'Single Sign-on',
   'Description' => 'Allows users to sign in through existing sign-in pages in external applications.',
   'Version' => '0.1.2',
   'Author' => "Mark O'Sullivan",
   'AuthorEmail' => 'mark@vanillaforums.com',
   'AuthorUrl' => 'http://markosullivan.ca'
);

class SingleSignOnPlugin implements Gdn_IPlugin {
   
   // Adds a "Single Sign-on" menu option to the dashboard
   public function Base_GetAppSettingsMenuItems_Handler(&$Sender) {
      $Menu = &$Sender->EventArguments['SideMenu'];
      $Menu->AddLink('Site Settings', 'Single Sign-on', 'garden/plugin/singlesignon', 'Garden.Settings.Manage');
   }

   public function PluginController_SingleSignOn_Create($Sender, $EventArguments) {
      $Sender->Head->Title('Single Sign-on');
      $Sender->AddSideMenu('garden/plugin/singlesignon');
      $Validation = new Gdn_Validation();
      $ConfigurationModel = new Gdn_ConfigurationModel($Validation);
      $ConfigurationModel->SetField(array(
         'Garden.Authenticator.Type',
         'Garden.Authenticator.Encoding',
         'Garden.Authenticator.AuthenticateUrl',
         'Garden.Authenticator.SignInUrl',
         'Garden.Authenticator.SignOutUrl',
         'Garden.Authenticator.RegisterUrl',
         'Garden.Cookie.Path'
      ));

      // Set the model on the form.
      $Sender->Form = new Gdn_Form();
      $Sender->Form->SetModel($ConfigurationModel);
      
      // If seeing the form for the first time...
      if ($Sender->Form->AuthenticatedPostBack() === FALSE) {
         // Apply the config settings to the form.
         $Sender->Form->SetData($ConfigurationModel->Data);
         $Sender->Form->SetValue('EnableSSO', Gdn::Config('Garden.Authenticator.Type') == 'Handshake' ? 'TRUE' : '');
      } else {
         // Make sure to force some values
         $Sender->Form->SetFormValue('Garden.Authenticator.Type', $Sender->Form->GetFormValue('EnableSSO', '') == 'TRUE' ? 'Handshake' : 'Password');
         $Sender->Form->SetFormValue('Garden.Authenticator.Encoding', 'ini');
         $Sender->Form->SetFormValue('Garden.Cookie.Path', '/'); // <-- Make sure that Vanilla's cookies don't have a path
         if ($Sender->Form->Save() !== FALSE)
            $Sender->StatusMessage = Translate("Your changes have been saved successfully.");
      }

      $Sender->Render(PATH_PLUGINS . DS . 'SingleSignOn' . DS . 'views' . DS . 'index.php');
   }
   
   public function Gdn_HandshakeAuthenticator_AfterGetHandshakeData_Handler(&$Sender) {
      $HandshakeData = ArrayValue('HandshakeData', $Sender->EventArguments);
      if (is_array($HandshakeData)) {
         // var_dump($HandshakeData);
         // exit();
         // Do something based on the data returned from the handshake...
      }
   }
   
   public function Setup() {
      // No setup required.
   }
}