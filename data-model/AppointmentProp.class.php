<?php
/**
 * 
 * Code skeleton generated by dia-uml2php5 plugin
 * written by KDO kdo@zpmag.com
 * @see        AbstractMAMAobject
 * 
 *  @Entity @Table(name="appointment_propositions")
 */
require_once ('AbstractMAMAobject.class.php');
class AppointmentProp extends AbstractMAMAobject {
	
	// ////////////////////////////////////////////////////////////////////////
	// STATICS
	
	// ////////////////////////////////////////////////////////////////////////
	// ATTRIBUTES
	
	/**
	 * @ManyToMany(targetEntity="Appointment", inversedBy="appointmentDatesPropositions")
	 * @JoinTable(name="appointment_propositions_dates",
	 * joinColumns={@JoinColumn(name="appointment_proposition_id", referencedColumnName="id")},
	 * inverseJoinColumns={@JoinColumn(name="appointment_id", referencedColumnName="id")}
	 * )
	 *
	 * @var Appointment
	 * @access private
	 */
	private $appointment;
	
	/**
	 * @Column(type="datetime", nullable=false, name="appointment_proposition_date")
	 *
	 * @var Date
	 * @access private
	 */
	private $appointmentPropositionDate;
	
	/**
	 * @Column(type="boolean", nullable=true, name="appointment_selected")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $appointmentSelected;
	
	// ////////////////////////////////////////////////////////////////////////
	// CONSTRUCTORS
	
	/**
	 *
	 * @param DateTime $appointmentPropositionDate        	
	 */
	public function __construct($appointmentPropositionDate) {
		parent::__construct ();
		// $this->appointment = $appointment;
		$this->appointment = new \Doctrine\Common\Collections\ArrayCollection ();
		$this->appointmentPropositionDate = $appointmentPropositionDate;
	}
	
	// ////////////////////////////////////////////////////////////////////////
	// GETTERS / SETTERS
	
	/**
	 *
	 * @return String
	 */
	public function getAppointmentPropositionDate() {
		return $this->appointmentPropositionDate;
	}
	
	/**
	 *
	 * @param String $appointmentPropositionDate        	
	 */
	public function setAppointmentPropositionDate($appointmentPropositionDate) {
		$this->appointmentPropositionDate = $appointmentPropositionDate;
	}
	
	/**
	 *
	 * @return Appointment
	 */
	public function getAppointment() {
		return $this->appointment;
	}
	
	/**
	 *
	 * @param Appointment $appointment        	
	 */
	public function setAppointment($appointment) {
		$this->appointment = $appointment;
	}
	/**
	 */
	public function getAppointmentSelected() {
		return $this->appointmentSelected;
	}
	
	/**
	 *
	 * @param String $appointmentSelected        	
	 */
	public function setAppointmentSelected($appointmentSelected) {
		$this->appointmentSelected = $appointmentSelected;
	}
	
	// ////////////////////////////////////////////////////////////////////////
	// OTHER
	/**
	 */
	public function prune() {
		$this->id = intval ( $this->getId () );
		$this->appointmentSelected = boolval ( $this->getAppointmentSelected () );
	}
	
	/**
	 */
	public function getJsonData() {
		$this->prune ();
		$var = get_object_vars ( $this );
		unset ( $var ["appointment"] );
		unset ( $var ["appointments"] );
		unset ( $var ["__initializer__"] );
		unset ( $var ["__cloner__"] );
		unset ( $var ["__isInitialized__"] );
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
		unset ( $var ["appointment"] );
		unset ( $var ["appointments"] );
		unset ( $var ["__initializer__"] );
		unset ( $var ["__cloner__"] );
		unset ( $var ["__isInitialized__"] );
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