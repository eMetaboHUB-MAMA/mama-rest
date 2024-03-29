<?php

/**
 * 
 * Code skeleton generated by dia-uml2php5 plugin
 * written by KDO kdo@zpmag.com
 * 
 * @see        AbstractMAMAobject
 * 
 * @Entity @Table(name="projects")
 */
require_once('AbstractMAMAobject.class.php');
class Project extends AbstractMAMAobject
{

	// ////////////////////////////////////////////////////////////////////////
	// STATICS
	public static $AD_STATUS_REJECTED = -100;
	public static $AD_STATUS_WAITING = 0;
	public static $AD_STATUS_ASSIGNED = 10;
	public static $AD_STATUS_COMPLETED = 20;
	public static $AD_STATUS_ACCEPTED = 30;
	public static $AD_STATUS_RUNNING = 40;
	public static $AD_STATUS_BLOCKED = -90;
	public static $AD_STATUS_ARCHIVED = 100;

	// SAMPLE NUMBER
	public static $AD_SAMPLE_NUMBER__LESS_THAN_50 = 1;
	public static $AD_SAMPLE_NUMBER__51_TO_100 = 2;
	public static $AD_SAMPLE_NUMBER__101_TO_500 = 3;
	public static $AD_SAMPLE_NUMBER__MORE_THAN_501 = 4;

	// ////////////////////////////////////////////////////////////////////////
	// ATTRIBUTES (JSON)
	private $idLong;

	// ////////////////////////////////////////////////////////////////////////
	// ATTRIBUTES (DB)
	/**
	 * @Column(type="string", unique=false, nullable=false)
	 *
	 * @var String
	 * @access private
	 */
	private $title;

	/**
	 * -1: rejected
	 * 0: waiting
	 * 1: completed
	 * 2: assigned
	 * 3: running
	 * 6: blocked
	 * 10: archived
	 *
	 * @Column(type="smallint", name="project_status")
	 *
	 * @var Short
	 * @access private
	 */
	private $status;

	/**
	 * @ManyToOne(targetEntity="User", inversedBy="projectsOwner")
	 * @JoinColumn(name="owner_id", referencedColumnName="id", nullable=false)
	 *
	 * @var User
	 * @access private
	 */
	private $owner;

	/**
	 * @ManyToOne(targetEntity="User", inversedBy="projects")
	 * @JoinColumn(name="analyst_in_charge_id", referencedColumnName="id", nullable=true)
	 *
	 * @var User
	 * @access private
	 */
	private $analystInCharge;

	/**
	 * @ManyToMany(targetEntity="User", mappedBy="projectsInvolded")
	 *
	 * @var Array<User>()
	 * @access private
	 */
	private $analystsInvolved;

	/**
	 * @Column(type="string", name="interest_in_mth_collaboration", nullable=true)
	 *
	 * @var String
	 * @access private
	 */
	private $interestInMthCollaboration;

	/**
	 * @Column(type="boolean", name="demand_type_eq_provisioning")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $demandTypeEqProvisioning = false;

	/**
	 * @Column(type="boolean", name="demand_type_catalog_allowance")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $demandTypeCatalogAllowance = false;

	/**
	 * @Column(type="boolean", name="demand_type_feasibility_study")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $demandTypeFeasibilityStudy = false;

	/**
	 * @Column(type="boolean", name="demand_type_training")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $demandTypeTraining = false;

	/**
	 * @Column(type="boolean", name="demand_type_data_processing")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $demandTypeDataProcessing = false;

	/**
	 * @Column(type="boolean", name="demand_type_other")
	 *
	 * @var Boolean
	 * @access private
	 */
	private $demandTypeOther = false;

	/**
	 * 50: NB <=50
	 * 100: 50 < NB <= 100
	 * 500: 100 < NB <=500
	 * 501: NB > 500
	 *
	 * @Column(type="smallint", name="samples_number", nullable=true)
	 *
	 * @var Short
	 * @access private
	 */
	private $samplesNumber;

	/**
	 * @ManyToMany(targetEntity="ThematicCloudWord")
	 * @JoinTable(name="projects_to_thematic_words",
	 * joinColumns={@JoinColumn(name="project_id", referencedColumnName="id")},
	 * inverseJoinColumns={@JoinColumn(name="thematic_word_id", referencedColumnName="id")}
	 * )
	 *
	 * @var Array<ThematicCloudWord>
	 * @access private
	 */
	private $thematicWords;

	/**
	 * @ManyToMany(targetEntity="SubThematicCloudWord")
	 * @JoinTable(name="projects_to_sub_thematic_words",
	 * joinColumns={@JoinColumn(name="project_id", referencedColumnName="id")},
	 * inverseJoinColumns={@JoinColumn(name="sub_thematic_word_id", referencedColumnName="id")}
	 * )
	 *
	 * @var Array<SubThematicCloudWord>
	 * @access private
	 */
	private $subThematicWords;

	/**
	 * @Column(type="boolean", name="targeted", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $targeted;

	/**
	 * @ManyToMany(targetEntity="MTHplatform")
	 * @JoinTable(name="projects_to_metabohub_platforms",
	 * joinColumns={@JoinColumn(name="project_id", referencedColumnName="id")},
	 * inverseJoinColumns={@JoinColumn(name="metabohub_platform_id", referencedColumnName="id")}
	 * )
	 *
	 * @var Array<MTHplatform>
	 * @access private
	 */
	private $mthPlatforms;

	/**
	 * @Column(type="boolean", name="can_be_forwarded_to_copartner", nullable=true)
	 *
	 * @var boolean
	 * @access private
	 */
	private $canBeForwardedToCoPartner;

	/**
	 * @Column(type="string", name="scientific_context", nullable=true, length=4096)
	 *
	 * @var String
	 * @access private
	 */
	private $scientificContext;

	/**
	 * @Column(type="string", name="scientific_context_file", nullable=true)
	 *
	 * @var String
	 * @access private
	 */
	private $scientificContextFile;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_financed", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectFinanced;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_in_provisioning", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectInProvisioning;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_on_own_supply", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectOnOwnSupply;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_not_financed", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectNotFinanced;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_eu", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectEU;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_anr", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectANR;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_national", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectNational;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_regional", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectRegional;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_compagny_tutorship", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectCompagnyTutorship;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_international_outside_eu", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectInternationalOutsideEU;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_own_resources_laboratory", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectOwnResourcesLaboratory;

	/**
	 * @Column(type="boolean", name="financial_context_is_project_other", nullable=true)
	 *
	 * @var Boolean
	 * @access private
	 */
	private $financialContextIsProjectOther;

	/**
	 * @Column(type="string", name="financial_context_is_project_other_value", nullable=true)
	 *
	 * @var String
	 * @access private
	 */
	private $financialContextIsProjectOtherValue;

	/**
	 * @OneToOne(targetEntity="ProjectExtraData", mappedBy="analysisRequest")
	 *
	 * @var ProjectExtraData
	 * @access private
	 */
	private $analysisRequestExtraData;

	/**
	 * @OneToMany(targetEntity="ProjectEvent", mappedBy="project")
	 *
	 * @var ProjectEvent
	 * @access private
	 */
	private $projectEvents;

	// transilient field
	private $response_delay;

	/**
	 * @Column(type="string", unique=false, nullable=true, length=2048)
	 *
	 * @var String
	 * @access private
	 */
	private $labRNSR;

	// ////////////////////////////////////////////////////////////////////////
	// CONSTRUCTORS
	/**
	 *
	 * @param String $title        	
	 * @param User $owner        	
	 */
	public function __construct($title, $owner)
	{
		parent::__construct();
		$this->title = $title;
		$this->owner = $owner;
		// pikaboo!
		$this->status = Project::$AD_STATUS_WAITING;
		$this->analystsInvolved = new \Doctrine\Common\Collections\ArrayCollection();
		$this->thematicWords = new \Doctrine\Common\Collections\ArrayCollection();
		$this->subThematicWords = new \Doctrine\Common\Collections\ArrayCollection();
		// mama#35 fix bug
		$this->projectEvents = new \Doctrine\Common\Collections\ArrayCollection();
	}

	// ////////////////////////////////////////////////////////////////////////
	// GETTERS / SETTERS
	public function getTitle()
	{
		return $this->title;
	}
	public function setTitle($title)
	{
		$this->title = $title;
	}
	public function getStatus()
	{
		// return $this->status;
		switch ($this->status) {
			case Project::$AD_STATUS_ARCHIVED:
				return "archived";
			case Project::$AD_STATUS_ASSIGNED:
				return "assigned";
			case Project::$AD_STATUS_ACCEPTED:
				return "accepted";
			case Project::$AD_STATUS_BLOCKED:
				return "blocked";
			case Project::$AD_STATUS_COMPLETED:
				return "completed";
			case Project::$AD_STATUS_REJECTED:
				return "rejected";
			case Project::$AD_STATUS_RUNNING:
				return "running";
			case Project::$AD_STATUS_WAITING:
			default:
				return "waiting";
		}
	}
	public function setStatus($status)
	{
		$statusInt = intval($status);
		switch (strtolower($status)) {
			case "archived":
				$statusInt = Project::$AD_STATUS_ARCHIVED;
				break;
			case "assigned":
				$statusInt = Project::$AD_STATUS_ASSIGNED;
				break;
			case "accepted":
				$statusInt = Project::$AD_STATUS_ACCEPTED;
				break;
			case "blocked":
				$statusInt = Project::$AD_STATUS_BLOCKED;
				break;
			case "completed":
				$statusInt = Project::$AD_STATUS_COMPLETED;
				break;
			case "rejected":
				$statusInt = Project::$AD_STATUS_REJECTED;
				break;
			case "running":
				$statusInt = Project::$AD_STATUS_RUNNING;
				break;
			case "waiting":
				// default :
				$statusInt = Project::$AD_STATUS_WAITING;
				break;
		}
		$this->status = $statusInt;
	}
	public function getOwner()
	{
		return $this->owner;
	}
	public function setOwner($owner)
	{
		$this->owner = $owner;
	}
	public function getAnalystInCharge()
	{
		return $this->analystInCharge;
	}
	public function setAnalystInCharge($analystInCharge)
	{
		$this->analystInCharge = $analystInCharge;
	}
	public function getAnalystsInvolved()
	{
		return $this->analystsInvolved;
	}
	public function setAnalystsInvolved($analystsInvolved)
	{
		$this->analystsInvolved = $analystsInvolved;
	}
	public function getInterestInMthCollaboration()
	{
		return $this->interestInMthCollaboration;
	}
	public function setInterestInMthCollaboration($interestInMthCollaboration)
	{
		$this->interestInMthCollaboration = $interestInMthCollaboration;
	}
	public function getDemandTypeEqProvisioning()
	{
		return $this->demandTypeEqProvisioning;
	}
	public function setDemandTypeEqProvisioning($demandTypeEqProvisioning)
	{
		$this->demandTypeEqProvisioning = $demandTypeEqProvisioning;
	}
	public function getDemandTypeCatalogAllowance()
	{
		return $this->demandTypeCatalogAllowance;
	}
	public function setDemandTypeCatalogAllowance($demandTypeCatalogAllowance)
	{
		$this->demandTypeCatalogAllowance = $demandTypeCatalogAllowance;
	}
	public function getDemandTypeFeasibilityStudy()
	{
		return $this->demandTypeFeasibilityStudy;
	}
	public function setDemandTypeFeasibilityStudy($demandTypeFeasibilityStudy)
	{
		$this->demandTypeFeasibilityStudy = $demandTypeFeasibilityStudy;
	}
	public function getDemandTypeTraining()
	{
		return $this->demandTypeTraining;
	}
	public function setDemandTypeTraining($demandTypeTraining)
	{
		$this->demandTypeTraining = $demandTypeTraining;
	}
	public function getDemandTypeDataProcessing()
	{
		return $this->demandTypeDataProcessing;
	}
	public function setDemandTypeDataProcessing($demandTypeDataProcessing)
	{
		$this->demandTypeDataProcessing = $demandTypeDataProcessing;
	}
	public function getDemandTypeOther()
	{
		return $this->demandTypeOther;
	}
	public function setDemandTypeOther($demandTypeOther)
	{
		$this->demandTypeOther = $demandTypeOther;
	}
	public function getSamplesNumber()
	{
		// return $this->samplesNumber;
		switch ($this->samplesNumber) {
			case Project::$AD_SAMPLE_NUMBER__LESS_THAN_50:
				return "50 or fewer";
			case Project::$AD_SAMPLE_NUMBER__51_TO_100:
				return "51 to 100";
			case Project::$AD_SAMPLE_NUMBER__101_TO_500:
				return "101 to 500";
			case Project::$AD_SAMPLE_NUMBER__MORE_THAN_501:
				return "more than 501";
			default:
				return null;
		}
	}
	public function setSamplesNumber($samplesNumber)
	{
		// $this->samplesNumber = $samplesNumber;
		$samplesNumberInt = intval($samplesNumber);
		switch ($samplesNumber) {
			case "<=50":
			case "50 or fewer":
				$samplesNumberInt = Project::$AD_SAMPLE_NUMBER__LESS_THAN_50;
				break;
			case "51<=NB<=100":
			case "51 to 100":
				$samplesNumberInt = Project::$AD_SAMPLE_NUMBER__51_TO_100;
				break;
			case "101<=NB<=500":
			case "101 to 500":
				$samplesNumberInt = Project::$AD_SAMPLE_NUMBER__101_TO_500;
				break;
			case ">=501":
			case "more than 501":
				$samplesNumberInt = Project::$AD_SAMPLE_NUMBER__MORE_THAN_501;
				break;
		}
		$this->samplesNumber = $samplesNumberInt;
	}
	public function getSamplesNumberAsString()
	{
		switch ($this->samplesNumber) {
			case Project::$AD_SAMPLE_NUMBER__LESS_THAN_50:
				return "50 or fewer";
			case Project::$AD_SAMPLE_NUMBER__51_TO_100:
				return "51 to 100";
			case Project::$AD_SAMPLE_NUMBER__101_TO_500:
				return "101 to 500";
			case Project::$AD_SAMPLE_NUMBER__MORE_THAN_501:
				return "more than 501";
		}
		return "";
	}
	public function getThematicWords()
	{
		return $this->thematicWords;
	}
	public function setThematicWords($thematicWords)
	{
		$this->thematicWords = $thematicWords;
	}
	public function getSubThematicWords()
	{
		return $this->subThematicWords;
	}
	public function setSubThematicWords($subThematicWords)
	{
		$this->subThematicWords = $subThematicWords;
	}
	public function getTargeted()
	{
		return $this->targeted;
	}
	public function setTargeted($targeted)
	{
		$this->targeted = $targeted;
	}
	public function getMthPlatforms()
	{
		return $this->mthPlatforms;
	}
	public function setMthPlatforms($mthPlatforms)
	{
		$this->mthPlatforms = $mthPlatforms;
	}
	public function getCanBeForwardedToCoPartner()
	{
		return $this->canBeForwardedToCoPartner;
	}
	public function setCanBeForwardedToCoPartner($canBeForwardedToCoPartner)
	{
		$this->canBeForwardedToCoPartner = $canBeForwardedToCoPartner;
	}
	public function getScientificContext()
	{
		return $this->scientificContext;
	}
	public function setScientificContext($scientificContext)
	{
		$this->scientificContext = $scientificContext;
	}
	public function getScientificContextFile()
	{
		return $this->scientificContextFile;
	}
	public function setScientificContextFile($scientificContextFile)
	{
		$this->scientificContextFile = $scientificContextFile;
	}
	public function getFinancialContextIsProjectFinanced()
	{
		return $this->financialContextIsProjectFinanced;
	}
	public function setFinancialContextIsProjectFinanced($financialContextIsProjectFinanced)
	{
		$this->financialContextIsProjectFinanced = $financialContextIsProjectFinanced;
	}
	public function getFinancialContextIsProjectInProvisioning()
	{
		return $this->financialContextIsProjectInProvisioning;
	}
	public function setFinancialContextIsProjectInProvisioning($financialContextIsProjectInProvisioning)
	{
		$this->financialContextIsProjectInProvisioning = $financialContextIsProjectInProvisioning;
	}
	public function getFinancialContextIsProjectOnOwnSupply()
	{
		return $this->financialContextIsProjectOnOwnSupply;
	}
	public function setFinancialContextIsProjectOnOwnSupply($financialContextIsProjectOnOwnSupply)
	{
		$this->financialContextIsProjectOnOwnSupply = $financialContextIsProjectOnOwnSupply;
	}
	public function getFinancialContextIsProjectNotFinanced()
	{
		return $this->financialContextIsProjectNotFinanced;
	}
	public function setFinancialContextIsProjectNotFinanced($financialContextIsProjectNotFinanced)
	{
		$this->financialContextIsProjectNotFinanced = $financialContextIsProjectNotFinanced;
	}
	public function getFinancialContextIsProjectEU()
	{
		return $this->financialContextIsProjectEU;
	}
	public function setFinancialContextIsProjectEU($financialContextIsProjectEU)
	{
		$this->financialContextIsProjectEU = $financialContextIsProjectEU;
	}
	public function getFinancialContextIsProjectANR()
	{
		return $this->financialContextIsProjectANR;
	}
	public function setFinancialContextIsProjectANR($financialContextIsProjectANR)
	{
		$this->financialContextIsProjectANR = $financialContextIsProjectANR;
	}
	public function getFinancialContextIsProjectNational()
	{
		return $this->financialContextIsProjectNational;
	}
	public function setFinancialContextIsProjectNational($financialContextIsProjectNational)
	{
		$this->financialContextIsProjectNational = $financialContextIsProjectNational;
	}
	public function getFinancialContextIsProjectRegional()
	{
		return $this->financialContextIsProjectRegional;
	}
	public function setFinancialContextIsProjectRegional($financialContextIsProjectRegional)
	{
		$this->financialContextIsProjectRegional = $financialContextIsProjectRegional;
	}
	public function getFinancialContextIsProjectCompagnyTutorship()
	{
		return $this->financialContextIsProjectCompagnyTutorship;
	}
	public function setFinancialContextIsProjectCompagnyTutorship($financialContextIsProjectCompagnyTutorship)
	{
		$this->financialContextIsProjectCompagnyTutorship = $financialContextIsProjectCompagnyTutorship;
	}
	public function getFinancialContextIsProjectInternationalOutsideEU()
	{
		return $this->financialContextIsProjectInternationalOutsideEU;
	}
	public function setFinancialContextIsProjectInternationalOutsideEU($financialContextIsProjectInternationalOutsideEU)
	{
		$this->financialContextIsProjectInternationalOutsideEU = $financialContextIsProjectInternationalOutsideEU;
	}
	public function getFinancialContextIsProjectOwnResourcesLaboratory()
	{
		return $this->financialContextIsProjectOwnResourcesLaboratory;
	}
	public function setFinancialContextIsProjectOwnResourcesLaboratory($financialContextIsProjectOwnResourcesLaboratory)
	{
		$this->financialContextIsProjectOwnResourcesLaboratory = $financialContextIsProjectOwnResourcesLaboratory;
	}
	public function getFinancialContextIsProjectOther()
	{
		return $this->financialContextIsProjectOther;
	}
	public function setFinancialContextIsProjectOther($financialContextIsProjectOther)
	{
		$this->financialContextIsProjectOther = $financialContextIsProjectOther;
	}
	public function getFinancialContextIsProjectOtherValue()
	{
		return $this->financialContextIsProjectOtherValue;
	}
	public function setFinancialContextIsProjectOtherValue($financialContextIsProjectOtherValue)
	{
		$this->financialContextIsProjectOtherValue = $financialContextIsProjectOtherValue;
	}
	public function getProjectExtraData()
	{
		return $this->analysisRequestExtraData;
	}
	public function setProjectExtraData($analysisRequestExtraData)
	{
		$this->analysisRequestExtraData = $analysisRequestExtraData;
	}
	// mama#60 lab RNSR
	public function getLabRNSR()
	{
		return $this->labRNSR;
	}
	public function setLabRNSR($labRNSR)
	{
		$this->labRNSR = $labRNSR;
	}
	// ////////////////////////////////////////////////////////////////////////
	// json
	public function getIdLong()
	{
		$prefix = $this->getCreated()->format('ym'); // Ymd
		$suffix = $this->getId() . "";
		while (strlen($suffix) < 5) {
			$suffix = "0" . $suffix;
		}
		// $this->idLong = $prefix . "" . $suffix;
		return $prefix . "" . $suffix;
	}
	// public function setIdLong($idLong) {
	// $this->idLong = $idLong;
	// }

	// ////////////////////////////////////////////////////////////////////////
	// OTHER

	/**
	 */
	public function prune()
	{
		$this->id = intval($this->getId());
		//
		$this->idLong = $this->getIdLong();
		//
		$this->status = $this->getStatus();

		// mama#47 - prep phone group
		$phoneGroup =  $this->owner->getPhoneGroup() != null ? "(+" . $this->owner->getPhoneGroup() . ") " : "";

		// clean
		$ownerS = [
			"id" => intval($this->owner->getId()),
			"fullName" => $this->owner->getFullName(),
			"email" => $this->owner->getEmail(),
			// mama#47 - add client phone and address
			"phone" => $phoneGroup . $this->owner->getPhoneNumber(),
			"workplace_address" => $this->owner->getWorkplaceAddress()
		];
		// "login" => $this->owner->getLogin (),
		// "email" => $this->owner->getEmail ()

		$inChage = null;
		if ($this->analystInCharge != null) {
			$inChage = [
				"id" => intval($this->analystInCharge->getId()),
				"fullName" => $this->analystInCharge->getFirstName() . " " . $this->analystInCharge->getLastName()
			];
			// "login" => $this->analystInCharge->getLogin (),
			// "email" => $this->analystInCharge->getEmail ()
		}

		$involved = array();
		foreach ($this->analystsInvolved as $k => $v) {
			$involvedT = [
				"id" => intval($v->getId()),
				"fullName" => $v->getFirstName() . " " . $v->getLastName()
			];
			array_push($involved, $involvedT);
		}

		$this->owner = $ownerS;
		$this->analystInCharge = $inChage;
		$this->analystsInvolved = $involved;
		$this->samplesNumber = $this->getSamplesNumber();

		$cloudWords = array();
		foreach ($this->thematicWords as $k => $v) {
			$cloudWord = [
				"id" => intval($v->getId()),
				"word" => $v->getWord()
			];
			array_push($cloudWords, $cloudWord);
		}
		$this->thematicWords = $cloudWords;

		$subCloudWords = array();
		foreach ($this->subThematicWords as $k => $v) {
			$cloudWord = [
				"id" => intval($v->getId()),
				"word" => $v->getWord()
			];
			array_push($subCloudWords, $cloudWord);
		}
		$this->subThematicWords = $subCloudWords;

		$platforms = array();
		foreach ($this->mthPlatforms as $k => $v) {
			$platform = [
				"id" => intval($v->getId()),
				"name" => $v->getName()
			];
			array_push($platforms, $platform);
		}
		$this->mthPlatforms = $platforms;

		$this->scientificContextFile = (preg_replace('/^(\d+)-/i', "", $this->scientificContextFile));

		// mama#60
		$this->labRNSR = $this->labRNSR;

		// mama#35 - prune events
		$this->projectEvents = array();
		// response delay
		$this->response_delay = $this->getResponseDelay();
	}

	/**
	 */
	public function getJsonData()
	{
		$this->prune();
		$var = get_object_vars($this);
		unset($var["__initializer__"]);
		unset($var["__cloner__"]);
		unset($var["__isInitialized__"]);
		// unset ( $var ["password"] );
		unset($var["deleted"]);
		foreach ($var as &$value) {
			if (is_object($value) && method_exists($value, 'getJsonData')) {
				$value = $value->getJsonData();
			}
		}
		return $var;
	}

	/**
	 */
	public function getArrayData()
	{
		$this->prune();
		$ret = array();
		$var = get_object_vars($this);
		unset($var["__initializer__"]);
		unset($var["__cloner__"]);
		unset($var["__isInitialized__"]);
		unset($var["password"]);
		unset($var["deleted"]);
		foreach ($var as $key => $val) {
			$ret[$key] = object2array($val);
		}
		return $ret;
	}

	///////////////////////////////////////////////////////////////////////////
	// 

	/**
	 * Get the response delay (mama#35)
	 * computed: CREATION_DATE - DATE_ASSIGNED
	 * return a response delay in days
	 */
	public function getResponseDelay()
	{
		// init empty date
		$dateEventAssigned = null;
		// if events associated ⇒ get the date of the 'set to assigned' event
		foreach ($this->projectEvents as &$event) {
			if ($event->getType() === '_project_set_to_assigned') {
				$dateEventAssigned = $event->getCreated();
			}
		}

		// conpute date ≠ if possible
		if ($dateEventAssigned != null) {
			$interval = $this->getCreated()->diff($dateEventAssigned);
			return $interval->format('%R%a');
		}
		// retrun null otherwise
		else {
			return null;
		}
	}
}
