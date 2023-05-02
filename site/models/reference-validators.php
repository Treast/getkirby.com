<?php

use Kirby\Cms\Pages;
use Kirby\Toolkit\Str;
use Kirby\Toolkit\V;
use Kirby\Reference\SectionPage;

class ReferenceValidatorsPage extends SectionPage
{
	public function children(): Pages
	{
		if ($this->children !== null) {
			return $this->children;
		}

		$children   = [];
		$validators = array_keys(V::$validators);
		$pages      = parent::children();

		foreach ($validators as $validator) {
			$children[] = [
				'slug'     => $slug = Str::kebab($validator),
				'num'      => 0,
				'model'    => 'reference-validator',
				'template' => 'reference-validator',
				'parent'   => $this,
				'content'  => array_merge(
					$pages->find($slug)?->content()->toArray() ?? [],
					['title' => $validator]
				)
			];
		}

		return $this->children = Pages::factory($children, $this);
	}
}
