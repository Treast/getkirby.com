<?php

use Kirby\Toolkit\Obj;

/**
 * Returns the currently active banner or
 * `null` if none is active;
 * sets the cache expiry appropriately
 */
function banner(): Obj|null {
	$banners = option('banners', []);

	// grab the first active configured banner
	// checked from top to bottom
	$banner = $expires = null;

	foreach ($banners as $candidate) {
		// normalize the dates to timestamps
		if (is_string($candidate['startDate'] ?? null) === true) {
			$candidate['startDate'] = strtotime($candidate['startDate']);
		}

		if (is_string($candidate['endDate'] ?? null) === true) {
			// the end date is inclusive, add one day
			$candidate['endDate'] = strtotime($candidate['endDate']) + 86400;
		}

		// use a separate text on the end date if configured
		if (
			$candidate['endDate'] - time() < 24 * 60 * 60 &&
			isset($candidate['textLastDay']) === true
		) {
			$candidate['text'] = $candidate['textLastDay'];
		}

		$candidate = new Obj($candidate);

		// the cache will expire once the *first* of the configured
		// banners will start, but only for banners that are not already started
		if (
			$candidate->startDate() &&
			$candidate->startDate() > time() &&
			($expires === null || $candidate->startDate() < $expires)
		) {
			$expires = $candidate->startDate();
		}

		// check if the banner is currently active:
		// - no start date or start date is in the past AND
		// - no end date or end date is in the future
		if (
			(!$candidate->startDate() || $candidate->startDate() <= time()) &&
			(!$candidate->endDate() || $candidate->endDate() >= time())
		) {
			$banner = $candidate;
			break;
		}
	}

	// if a banner is currently active, the cache
	// will also expire when the active banner ends
	if ($endDate = $banner?->endDate()) {
		// if the text changes on the last day,
		// expire one day before the end date
		if ($banner?->textLastDay()) {
			$endDate -= 24 * 60 * 60;
		}

		$expires = match (is_int($expires)) {
			true    => min($expires, $endDate),
			default => $endDate
		};
	}

	kirby()->response()->expires($expires);

	return $banner;
}
