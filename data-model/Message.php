<?php

/**
 * @Entity @Table(name="messages")
 **/
class Message {
	/**
	 * @Id @Column(type="integer") @GeneratedValue *
	 */
	protected $id;
	
	/**
	 * @Column(type="string") *
	 */
	protected $text;
	public function getId() {
		return $this->id;
	}
	public function getText() {
		return $this->text;
	}
	public function setText($text) {
		$this->text = $text;
	}
}