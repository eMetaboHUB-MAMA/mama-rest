<?php

// //////////////////////////////////////////////////////////////////////////////////////////////
// MAMA EMAIL FUNCTIONS
require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . "/userManagementService.php";
require_once __DIR__ . "/projectManagementService.php";
require_once __DIR__ . "/eventManagementService.php";
require_once __DIR__ . "/messageManagementService.php";
require_once __DIR__ . "/appointmentManagementService.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class EmailManagementService
{

    // //////////////////////////////////////////////////////////////////////////////////////////////
    // CLASS STATIC METHODS

    /**
     * Send ONE email
     *
     * @return List of User(s)
     */
    public static function sendEmailAccountCreation($sendToEmail, $sendToUsername, $lang = "en")
    {
        $mail = EmailManagementService::initEmail();

        $mail->addAddress($sendToEmail, $sendToUsername); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] account creation';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] création de compte';
        // $mail->Body = 'This is the HTML message body <b>in bold!</b>';
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_account_creation_' . $lang . '.html');
        $message = str_replace('%login%', $sendToEmail, $message);
        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);
        // $message = str_replace ( '%testpassword%', $password, $message );

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // echo 'Message could not be sent.';
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            // echo 'Message has been sent';
            return true;
        }
    }

    /**
     *
     * @param unknown $sendToEmail
     * @param unknown $password
     * @param string $lang
     * @return boolean
     */
    public static function sendEmailResetPassword($sendToEmail, $password, $lang = "en")
    {
        $mail = EmailManagementService::initEmail();

        $mail->addAddress($sendToEmail); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] reset password';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] ré-initialisation du mot-de-passe';
        // $mail->Body = 'This is the HTML message body <b>in bold!</b>';
        // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_reset_password_' . $lang . '.html');
        $message = str_replace('%login%', $sendToEmail, $message);
        $message = str_replace('%password%', $password, $message);

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        // $message = str_replace ( '%testpassword%', $password, $message );

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // echo 'Message could not be sent.';
            // echo 'Mailer Error: ' . $mail->ErrorInfo;
            return false;
        } else {
            // echo 'Message has been sent';
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @return boolean
     */
    public static function sendDailyNotificationsEmail($user)
    {
        EmailManagementService::sendPeriodNotificationsEmail($user, "day");
    }

    /**
     *
     * @param unknown $user
     * @return boolean
     */
    public static function sendWeeklyNotificationsEmail($user)
    {
        EmailManagementService::sendPeriodNotificationsEmail($user, "week");
    }

    /**
     *
     * @param unknown $user
     * @return boolean
     */
    public static function sendPeriodNotificationsEmail($user, $period)
    {
        $dateRange = 'P1D';
        $loggerKey = "daily";
        $titleKeyEn = '[MAMA] Daily digest';
        $titleKeyFr = '[MAMA] notifications journalière ';
        switch ($period) {
            case "day":
                $dateRange = 'P1D';
                $loggerKey = "daily";
                $titleKeyEn = '[MAMA] Daily digest';
                $titleKeyFr = '[MAMA] Notifications journalière ';
                break;
            case "week":
                $dateRange = 'P7D';
                $loggerKey = "weekly";
                $titleKeyEn = '[MAMA] Weekly digest';
                $titleKeyFr = '[MAMA] Notifications hebdomadaire ';
                break;
        }

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        $needToSendEmail = false;
        $sendEmailNewUserAccount = false;
        $numberNewAccounts = 0;

        $sendEmailNewProject = false;
        $numberNewProjects = 0;

        $sendEmailNewProjectEvents = false;
        $numberNewProjectEvents = 0;

        $sendEmailNewMessages = false;
        $numberNewMessages = 0;

        $sendEmailNewProjectsMessages = false;
        $numberNewProjectsMessages = 0;

        $sendEmailNewAppointments = false;
        $numberNewAppointments = 0;
        $sendEmailInComingAppointments = false;
        $numberIncomingAppointments = 0;

        // $emailAlertNewUserAccount
        if ($user->isEmailAlertNewUserAccount() && $user->isAdmin()) {
            // fetch number of users with "waiting" status
            $numberNewAccounts = intval(UserManagementService::countUsers(User::$STATUS_NOT_VALIDATED));
            if ($numberNewAccounts != 0) {
                $needToSendEmail = true;
                $sendEmailNewUserAccount = true;
            }
        }

        // $emailAlertNewProject
        if ($user->isEmailAlertNewProject() && ($user->isAdmin() || $user->isProjectManager())) {
            // fetch number of projects with "waiting" status
            $numberNewProjects = intval(ProjectManagementService::countProjects(null, "waiting", null));
            if ($numberNewProjects != 0) {
                $needToSendEmail = true;
                $sendEmailNewProject = true;
            }
        }
        // $emailAlertNewEventFollowedProject
        if ($user->isEmailAlertNewEventFollowedProject()) {
            // fetch number of project events with age < 24 hours || 7 days
            $date = new \DateTime("now");
            $date->sub(new \DateInterval($dateRange));
            $_GET['from'] = "" . $date->format('Y-m-d');
            $listEvents = EventManagementService::getProjectsEvents($user, null);
            $numberNewProjectEvents = intval(sizeof($listEvents));
            if ($numberNewProjectEvents != 0) {
                $needToSendEmail = true;
                $sendEmailNewProjectEvents = true;
            }
        }

        // $emailAlertNewMessage
        if ($user->isEmailAlertNewMessage()) {
            // fetch number of messages with age < 24 hours || 7 days
            $date = new \DateTime("now");
            $date->sub(new \DateInterval($dateRange));
            $_GET['from'] = "" . $date->format('Y-m-d');
            $numberNewMessages = intval(MessageManagementService::countMessages($user, null, null, null));
            if ($numberNewMessages != 0) {
                $needToSendEmail = true;
                $sendEmailNewMessages = true;
            }
            // NEW 2017/02/01 get project's messages
            $numberNewProjectsMessages = intval(MessageManagementService::countMessages($user, $user, null, "all"));
            if ($numberNewProjectsMessages != 0) {
                $needToSendEmail = true;
                $sendEmailNewProjectsMessages = true;
            }
            // fetch number of appointments with age < 24 hours || 7 days
            $numberNewAppointments = intval(AppointmentManagementService::countAppointments($user, "to", null, null));
            if ($numberNewMessages != 0) {
                $needToSendEmail = true;
                $sendEmailNewAppointments = true;
            }
            // fetch number of appointments fixed in next +24 hours || +7 days
            $date2 = new \DateTime("now");
            $date2->add(new \DateInterval($dateRange));
            $_GET['app_to'] = "" . $date2->format('Y-m-d');
            $_GET['app_from'] = "" . (new \DateTime("now"))->format('Y-m-d');
            $numberIncomingAppointments = intval(AppointmentManagementService::countAppointments($user, null, null, null));
            if ($numberIncomingAppointments != 0) {
                $needToSendEmail = true;
                $sendEmailInComingAppointments = true;
            }
        }

        if (! $needToSendEmail) {
            echo "[" . date("Y-m-d H:i:s") . "] no need to send a " . $loggerKey . " digest email to $email \n";
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = $titleKeyEn;
        if ($lang == "fr")
            $mail->Subject = $titleKeyFr;

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_' . $loggerKey . '_digest_' . $lang . '.html');
        $message = str_replace('%username%', $fullName, $message);
        // $message = str_replace ( '%testpassword%', $password, $message );
        if ($sendEmailNewUserAccount) {
            $message = str_replace('%numberNewAccounts%', $numberNewAccounts, $message);
        } else {
            $message = preg_replace('/newAccountsStarts(.*)newAccountsEnd/is', "", $message);
        }
        if ($sendEmailNewProject) {
            $message = str_replace('%numberNewProjects%', $numberNewProjects, $message);
        } else {
            $message = preg_replace('/newProjectsStarts(.*)newProjectsEnd/is', "", $message);
        }
        if ($sendEmailNewProjectEvents) {
            $message = str_replace('%numberNewProjectEvents%', $numberNewProjectEvents, $message);
        } else {
            $message = preg_replace('/newProjectEventsStarts(.*)newProjectEventsEnd/is', "", $message);
        }
        // MESSAGES
        if ($sendEmailNewMessages) {
            $message = str_replace('%numberNewMessages%', $numberNewMessages, $message);
        } else {
            $message = preg_replace('/newMessagesStarts(.*)newMessagesEnd/is', "", $message);
        }
        if ($sendEmailNewProjectsMessages) {
            $message = str_replace('%numberNewProjectsMessages%', $numberNewProjectsMessages, $message);
        } else {
            $message = preg_replace('/newProjectsMessagesStarts(.*)newProjectsMessagesEnd/is', "", $message);
        }

        // APPOINTMENTS
        if ($sendEmailNewAppointments) {
            $message = str_replace('%numberNewAppointments%', $numberNewAppointments, $message);
        } else {
            $message = preg_replace('/newAppointmentsStarts(.*)newAppointmentsEnd/is', "", $message);
        }
        if ($sendEmailInComingAppointments) {
            $message = str_replace('%numberIncomingAppointments%', $numberIncomingAppointments, $message);
        } else {
            $message = preg_replace('/incomingAppointmentsStarts(.*)incomingAppointmentsEnd/is', "", $message);
        }

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // echo 'Message could not be sent.';
            echo "[" . date("Y-m-d H:i:s") . "] ERROR could not send " . $loggerKey . " digest email to $email because: '" . $mail->ErrorInfo . "' \n";
            return false;
        } else {
            echo "[" . date("Y-m-d H:i:s") . "] send " . $loggerKey . " digest email to $email \n";
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @return boolean
     */
    public static function sendNewUserNotificationEmail($user, $newUserLogin, $newUserEmail)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        // $emailAlertNewUserAccount
        if (! ($user->isEmailAlertNewUserAccount() && $user->isAdmin())) {
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] New user registration';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Nouvel utilisateur du service ';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_new_user_' . $lang . '.html');
        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%newUserLogin%', $newUserLogin, $message);
        $message = str_replace('%newUserEmail%', $newUserEmail, $message);

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @param unknown $newProjectID
     * @param unknown $newProjectName
     * @param unknown $newProjectOwner
     * @return boolean
     */
    public static function sendNewProjectNotificationEmail($user, $newProjectID, $newProjectName, $newProjectOwner)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        // $emailAlertNewUserAccount
        if (! ($user->isEmailAlertNewProject() && ($user->isAdmin() || $user->isProjectManager()))) {
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] New request';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Nouvelle sollicitation ';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_new_project_' . $lang . '.html');
        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%newProjectID%', $newProjectID, $message);
        $message = str_replace('%newProjectName%', $newProjectName, $message);
        $message = str_replace('%newProjectOwner%', $newProjectOwner, $message);

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @param unknown $newProjectID
     * @param unknown $newProjectName
     * @return boolean
     */
    public static function sendNewProjectNotificationEmailForUser($user, $newProjectID, $newProjectName)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] New request: ' . $newProjectName;
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Nouvelle sollicitation ' . $newProjectName;

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_new_project_user_' . $lang . '.html');
        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%newProjectID%', $newProjectID, $message);
        $message = str_replace('%newProjectName%', $newProjectName, $message);

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @param unknown $projectID
     * @param unknown $projectName
     * @return boolean
     */
    public static function sendProjectUpdateNotificationEmail($user, $projectID, $projectName)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        // $emailAlertNewUserAccount
        if (! ($user->isEmailAlertNewEventFollowedProject())) {
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] Update on a request / project';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Mise-à-jour d\'une sollicitation / d\'un projet ';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_project_update_' . $lang . '.html');
        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%projectID%', $projectID, $message);
        $message = str_replace('%projectName%', $projectName, $message);
        // $message = str_replace ( '%newProjectOwner%', $newProjectOwner, $message );

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @param unknown $messageID
     * @param unknown $messageMessage
     * @param unknown $messageFrom
     * @return boolean
     */
    public static function sendNewMessageNotificationEmail($user, $messageID, $messageMessage, $messageFrom)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        // $emailAlertNewUserAccount
        if (! ($user->isEmailAlertNewMessage())) {
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] New message!';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Nouveau message !';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_new_message_' . $lang . '.html');
        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%messageID%', $messageID, $message);
        $message = str_replace('%messageMessage%', $messageMessage, $message);
        $message = str_replace('%messageFrom%', $messageFrom, $message);
        // $message = str_replace ( '%newProjectOwner%', $newProjectOwner, $message );

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @param unknown $appointmentID
     * @param unknown $appointmentMessage
     * @param unknown $appointmentFrom
     * @return boolean
     */
    public static function sendNewAppointmentNotificationEmail($user, $appointmentID, $appointmentMessage, $appointmentFrom)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        // $emailAlertNewUserAccount
        if (! ($user->isEmailAlertNewMessage())) {
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] New appointment!';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Nouveau rendez-vous !';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_new_appointment_' . $lang . '.html');

        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%appointmentID%', $appointmentID, $message);
        $message = str_replace('%appointmentMessage%', $appointmentMessage, $message);
        $message = str_replace('%appointmentFrom%', $appointmentFrom, $message);
        // $message = str_replace ( '%newProjectOwner%', $newProjectOwner, $message );

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     *
     * @param unknown $user
     * @param unknown $appointmentID
     * @param unknown $appointmentMessage
     * @param unknown $appointmentFrom
     * @param unknown $isSuccess
     * @return boolean
     */
    public static function sendUpdateAppointmentNotificationEmail($user, $appointmentID, $appointmentMessage, $appointmentFrom, $isSuccess, $appointmentDate)
    {

        // INIT
        $email = $user->getEmail();
        $fullName = $user->getFirstName() . " " . $user->getLastName();
        $lang = $user->getEmailLanguage();

        // $emailAlertNewUserAccount
        if (! ($user->isEmailAlertNewMessage())) {
            return false;
        }

        $mail = EmailManagementService::initEmail();

        $mail->addAddress($email, $fullName); // Add a recipient
        $mail->isHTML(true); // Set email format to HTML

        $mail->Subject = '[MAMA] Update appointment!';
        if ($lang == "fr")
            $mail->Subject = '[MAMA] Mise-à-jour de rendez-vous !';

        $message = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_update_appointment_' . $lang . '.html');

        // $message = str_replace ( '%username%', $fullName, $message );
        $message = str_replace('%userName%', $fullName, $message);
        $message = str_replace('%appointmentID%', $appointmentID, $message);
        $message = str_replace('%appointmentMessage%', $appointmentMessage, $message);
        $message = str_replace('%appointmentFrom%', $appointmentFrom, $message);
        // $message = str_replace ( '%newProjectOwner%', $newProjectOwner, $message );
        if ($isSuccess) {
            $date = $appointmentDate->format('Y-m-d H:i');
            $message = str_replace('%appointmentDate%', $date, $message);
            $message = preg_replace('/failStarts(.*)failEnd/is', "", $message);
        } else {
            $message = preg_replace('/successStarts(.*)successEnd/is', "", $message);
        }

        // signature and webapp URL
        $message = EmailManagementService::setStaticString($message);

        $mail->MsgHTML($message);

        if (! $mail->send()) {
            // fail!
            return false;
        } else {
            // success!
            return true;
        }
    }

    /**
     * Init PHPMailer worker
     *
     * @return PHPMailer the PHPMailer worker object
     */
    private static function initEmail()
    {
        $mail = new PHPMailer();
        // $mail->SMTPDebug = 3; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host = smtp_host;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        if (smtp_smtpauth == "true") {
            $mail->SMTPAuth = true;
        } else {
            $mail->SMTPAuth = false;
        }
        $mail->Username = smtp_username;
        $mail->Password = smtp_password;
        if (smtp_secure != "") {
            // Enable TLS encryption, `ssl` also accepted
            $mail->SMTPSecure = smtp_secure;
        }
        $mail->Port = intval(smtp_port);
        $mail->setFrom(smtp_from_email, smtp_from_displayname);
        $mail->addReplyTo(smtp_replyto_email, smtp_replyto_displayname);
        $mail->CharSet = "utf-8";
        return $mail;
    }

    /**
     *
     * @param unknown $message
     * @return unknown
     */
    private static function setStaticString($message)
    {

        // signature
        $signature = file_get_contents(__DIR__ . '/../../mail_templates/tmpl_signature.html');
        $message = str_replace('%%EMAIL_SIGNATURE%%', $signature, $message);

        // webapp URL
        $message = str_replace('%%WEBAPP_URL%%', 'app_webapp_url', $message);

        return $message;
    }
}