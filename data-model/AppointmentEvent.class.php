<?php
/**
 * 
 * Code skeleton generated by dia-uml2php5 plugin
 * written by KDO kdo@zpmag.com
 * @see        Event
 * 
 * @Entity @Table(name="appointments_events")
 */
require_once ('Event.class.php');
class AppointmentEvent extends Event {
	
	/**
	 * @ManyToOne(targetEntity="Appointment", inversedBy="events")
	 * @JoinColumn(name="appointment_id", referencedColumnName="id", nullable=false)
	 *
	 * @var Appointment
	 * @access private
	 */
	private $appointment;
	
	/**
	 *
	 * @param unknown $user        	
	 * @param unknown $type        	
	 * @param unknown $appointment        	
	 */
	public function __construct($user, $type, $appointment) {
		parent::__construct ( $user, $type );
		$this->appointment = $appointment;
	}
	
	// ////////////////////////////////////////////////////////////////////////
	// GETTERS / SETTERS
	public function getAppointment() {
		return $this->appointment;
	}
	public function setAppointment($appointment) {
		$this->appointment = $appointment;
	}
	
	// ////////////////////////////////////////////////////////////////////////
	// OTHER
	public function prune() {
		parent::prune ();
		$this->id = intval ( $this->getId () );
		$appointmentS = [ 
				"id" => intval ( $this->appointment->getId () ),
				"date" => $this->appointment->getAppointmentDate (),
				"message" => $this->appointment->getMessage (),
				"nbDatesProp" => $this->appointment->getNbDatesProp () 
		];
		if ($this->appointment->getFromUser () != null) {
			$appointmentS ['fromUser'] = $this->appointment->getFromUser ()->getFullName ();
		}
		if ($this->appointment->getToUser () != null) {
			$appointmentS ['toUser'] = $this->appointment->getToUser ()->getFullName ();
		}
		if ($this->appointment->getProject () != null) {
			$appointmentS ['project'] = $this->appointment->getProject ()->getTitle ();
		}
		$this->appointment = $appointmentS;
	}
	
	/**
	 */
	public function getJsonData() {
		$this->prune ();
		$var = get_object_vars ( $this );
		$var ['type'] = $var ['eventType'];
		unset ( $var ["__initializer__"] );
		unset ( $var ["__cloner__"] );
		unset ( $var ["__isInitialized__"] );
		unset ( $var ["deleted"] );
		unset ( $var ["eventType"] );
		foreach ( $var as &$value ) {
			if (is_object ( $value ) && method_exists ( $value, 'getJsonData' )) {
				$value = $value->getJsonData ();
			}
		}
		return $var;
	}
	
	/**
	 */
	public function getArrayData() {
		$this->prune ();
		$ret = Array ();
		$var = get_object_vars ( $this );
		$var ['type'] = $var ['eventType'];
		$var = get_object_vars ( $this );
		unset ( $var ["__initializer__"] );
		unset ( $var ["__cloner__"] );
		unset ( $var ["__isInitialized__"] );
		unset ( $var ["deleted"] );
		unset ( $var ["eventType"] );
		foreach ( $var as $key => $val ) {
			// if (is_object ( $val ) && method_exists ( $val, 'getArrayData' )) {
			// $val = $val->getArrayData ();
			// }
			$ret [$key] = object2array ( $val );
		}
		return $ret;
	}
}
?>