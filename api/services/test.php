<?php
require_once "../../bootstrap.php";

// Create a message
$message = new Message ();
$message->setText ( 'Hello world' );

// Save in the database
$entityManager->persist ( $message );
$entityManager->flush ();

echo "Created message with ID " . $message->getId () . "\n";

// Get all messages
$messages = $entityManager->getRepository ( 'Message' )->findAll ();

echo "Listing all messages:\n";
foreach ( $messages as $message )
	echo sprintf ( "- %s\n", $message->getText () );
echo "Done!\n";

// Now delete it
$entityManager->remove ( $message );
$entityManager->flush ();

echo "Message deleted!\n";