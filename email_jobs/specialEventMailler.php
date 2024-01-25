<?php

// // API
require_once __DIR__ . "/../vendor/autoload.php";

if (isset($isDevMode) && ! $isDevMode) {
    require_once __DIR__ . "/../bootstrap.php";
}

// MAMA API
require_once __DIR__ . "/../api/services/userManagementService.php";
require_once __DIR__ . "/../api/services/emailManagementService.php";

/**
 *
 * @author Nils Paulhe
 *        
 */
class SpecialEventMailler
{

    /**
     * list users with new user create event alert
     *
     * @param unknown $newUserLogin
     * @param unknown $newUserEmail
     *
     */
    public static function sendEmailNewUser($newUserLogin, $newUserEmail)
    {
        $users = UserManagementService::fetchEmailUsers(User::$EMAIL_NOTIFICATION_EACH, "newUser");
        // for each user, build daily digest email
        foreach ($users as $k => $user) {

            // fetch basic user data
            $email = $user->getEmail();
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $lang = $user->getEmailLanguage();

            // send digest email
            EmailManagementService::sendNewUserNotificationEmail($user, $newUserLogin, $newUserEmail);
        }
    }

    /**
     * list users with new project create event alert
     *
     * @param unknown $newUserLogin
     * @param unknown $newUserEmail
     * @param unknown $newProjectOwner
     */
    public static function sendEmailNewProject($newProjectID, $newProjectName, $newProjectOwner)
    {
        $users = UserManagementService::fetchEmailUsers(User::$EMAIL_NOTIFICATION_EACH, "newProject");
        // for each user, build daily digest email
        foreach ($users as $k => $user) {

            // fetch basic user data
            $email = $user->getEmail();
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $lang = $user->getEmailLanguage();

            // send digest email
            EmailManagementService::sendNewProjectNotificationEmail($user, $newProjectID, $newProjectName, $newProjectOwner->getFullName());
        }

        // send email to pj owner
        EmailManagementService::sendNewProjectNotificationEmailForUser($newProjectOwner, $newProjectID, $newProjectName);
    }

    /**
     * list users with new project update event alert
     *
     * @param unknown $newUserLogin
     * @param unknown $newUserEmail
     * @param unknown $newProjectOwner
     */
    public static function sendEmailProjectUpdate($project)
    {
        // $users = UserManagementService::fetchEmailUsers ( User::$EMAIL_NOTIFICATION_EACH, "updateProject" );
        $users = array();
        $owner = $project->getOwner();
        $manager = $project->getAnalystInCharge();
        $involved = $project->getAnalystsInvolved();
        if ($owner->getEmailReception() == "each_notification" && $owner->isEmailAlertNewEventFollowedProject()) {
            array_push($users, $owner);
        }
        if ($manager != null && $manager->getEmailReception() == "each_notification" && $manager->isEmailAlertNewEventFollowedProject() && ! in_array($manager, $users)) {
            array_push($users, $manager);
        }
        foreach ($involved as $k => $user) {
            if ($user->getEmailReception() == "each_notification" && $user->isEmailAlertNewEventFollowedProject() && ! in_array($user, $users)) {
                array_push($users, $user);
            }
        }
        // for each user, build daily digest email
        foreach ($users as $k => $user) {
            // fetch basic user data
            $email = $user->getEmail();
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $lang = $user->getEmailLanguage();
            // send email
            EmailManagementService::sendProjectUpdateNotificationEmail($user, $project->getId(), $project->getTitle());
        }
    }

    /**
     *
     * @param unknown $message
     * @param unknown $project
     */
    public static function sendEmailNewMessage($message, $project)
    { // OrAppointment $appointment
        $users = array();
        // Project
        if (! is_null($project)) {
            $owner = $project->getOwner();
            $manager = $project->getAnalystInCharge();
            $involved = $project->getAnalystsInvolved();
            if ($owner->getEmailReception() == "each_notification" && $owner->isEmailAlertNewMessage()) {
                array_push($users, $owner);
            }
            if ($manager != null && $manager->getEmailReception() == "each_notification" && $manager->isEmailAlertNewMessage() && ! in_array($manager, $users)) {
                array_push($users, $manager);
            }
            foreach ($involved as $k => $user) {
                if ($user->getEmailReception() == "each_notification" && $user->isEmailAlertNewMessage() && ! in_array($user, $users)) {
                    array_push($users, $user);
                }
            }
        }

        // Message (to user)
        if (! is_null($message)) {
            $toUser = $message->getToUser();
            if (! is_null($toUser)) {
                if ($toUser->getEmailReception() == "each_notification" && $toUser->isEmailAlertNewMessage() && ! in_array($toUser, $users)) {
                    array_push($users, $toUser);
                }
            }
        }

        // for each user, build daily digest email
        foreach ($users as $k => $user) {
            // fetch basic user data
            $email = $user->getEmail();
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $lang = $user->getEmailLanguage();
            // send email
            EmailManagementService::sendNewMessageNotificationEmail($user, $message->getId(), $message->getMessage(), $message->getFromUser()->getFullName());
        }
    }

    /**
     */
    public static function sendEmailNewAppointment($appointment, $project)
    { // OrAppointment $appointment
        $users = array();
        // Project
        if (! is_null($project)) {
            $owner = $project->getOwner();
            $manager = $project->getAnalystInCharge();
            $involved = $project->getAnalystsInvolved();
            if ($owner->getEmailReception() == "each_notification" && $owner->isEmailAlertNewMessage()) {
                array_push($users, $owner);
            }
            if ($manager != null && $manager->getEmailReception() == "each_notification" && $manager->isEmailAlertNewMessage() && ! in_array($manager, $users)) {
                array_push($users, $manager);
            }
            foreach ($involved as $k => $user) {
                if ($user->getEmailReception() == "each_notification" && $user->isEmailAlertNewMessage() && ! in_array($user, $users)) {
                    array_push($users, $user);
                }
            }
        }

        // Message (to user)
        if (! is_null($appointment)) {
            $toUser = $appointment->getToUser();
            if (! is_null($toUser)) {
                if ($toUser->getEmailReception() == "each_notification" && $toUser->isEmailAlertNewMessage() && ! in_array($toUser, $users)) {
                    array_push($users, $toUser);
                }
            }
        }

        // for each user, build daily digest email
        foreach ($users as $k => $user) {
            // fetch basic user data
            $email = $user->getEmail();
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $lang = $user->getEmailLanguage();
            // send email
            EmailManagementService::sendNewAppointmentNotificationEmail($user, $appointment->getId(), $appointment->getMessage(), $appointment->getFromUser()->getFullName());
        }
    }

    /**
     */
    public static function sendEmailUpdateAppointment($appointment, $project, $isSuccess = null, $appDate = null)
    { // OrAppointment $appointment
        if (is_null($isSuccess))
            return false;

        $users = array();
        // Project
        if (! is_null($project)) {
            $owner = $project->getOwner();
            $manager = $project->getAnalystInCharge();
            $involved = $project->getAnalystsInvolved();
            if ($owner->getEmailReception() == "each_notification" && $owner->isEmailAlertNewMessage()) {
                array_push($users, $owner);
            }
            if ($manager != null && $manager->getEmailReception() == "each_notification" && $manager->isEmailAlertNewMessage() && ! in_array($manager, $users)) {
                array_push($users, $manager);
            }
            foreach ($involved as $k => $user) {
                if ($user->getEmailReception() == "each_notification" && $user->isEmailAlertNewMessage() && ! in_array($user, $users)) {
                    array_push($users, $user);
                }
            }
        }

        // Message (to user)
        if (! is_null($appointment)) {
            $toUser = $appointment->getToUser();
            if (! is_null($toUser)) {
                if ($toUser->getEmailReception() == "each_notification" && $toUser->isEmailAlertNewMessage() && ! in_array($toUser, $users)) {
                    array_push($users, $toUser);
                }
            }
            // inform from user too!
            $fromUser = $appointment->getFromUser();
            if (! is_null($fromUser)) {
                if ($fromUser->getEmailReception() == "each_notification" && $fromUser->isEmailAlertNewMessage() && ! in_array($fromUser, $users)) {
                    array_push($users, $fromUser);
                }
            }
        }

        // for each user, build email
        foreach ($users as $k => $user) {
            // fetch basic user data
            $email = $user->getEmail();
            $fullName = $user->getFirstName() . " " . $user->getLastName();
            $lang = $user->getEmailLanguage();
            // send email
            EmailManagementService::sendUpdateAppointmentNotificationEmail($user, $appointment->getId(), $appointment->getMessage(), $appointment->getFromUser()->getFullName(), $isSuccess, $appDate);
        }
    }
}
