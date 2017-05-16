<?php

namespace Alexa\Response;

class StandardCard {
	public $type = 'Standard';
	public $title = '';
	public $content = '';
	public $image = null;

	public function __construct( $title, $content, $image ) {
		$this->title = $title;
		$this->content = $content;
		$this->image = array(
			'smallImageUrl' => wp_get_attachment_image_src( absint( $image ), 'alexa-small' )[0],
			'largeImageUrl' => wp_get_attachment_image_src( absint( $image ), 'alexa-large' )[0],
		);
	}

	public function render() {
		return array(
			'type' => $this->type,
			'title' => $this->title,
			'content' => $this->content,
			'image' => $this->image,
		);
	}
}
