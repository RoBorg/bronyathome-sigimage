<?php

libxml_use_internal_errors(true);
define('NOW', date('Y-m-d G:i:s'));
define('CACHE_TIME', 3600); // Length of time to cache data, in seconds

/**
 * Retrieve data from the cache
 *
 * @param string $file The cache file name
 *
 * @return object The cached data
 */
function cacheGet($file)
{
	if (!file_exists($file)) {
		return (object) array();
	}
	
	$allItems = json_decode(file_get_contents($file));
	
	if ($allItems === null) {
		return (object) array();
	}
	
	return $allItems;
}

/**
 * Save data to the cache
 *
 * @param string $file The cache file name
 * @param object $allItems The data to cache
 */
function cachePut($file, $allItems)
{
	file_put_contents($file, json_encode($allItems));
}

/**
 * Get a DOMDocument from a URL
 *
 * @param string $url The URL
 *
 * @return DOMDocument The DOMDocument
 */
function getDOM($url)
{
	$html = file_get_contents($url);
	
	$dom = new DOMDocument();
	$dom->loadHTML($html);
	
	return $dom;
}

/**
 * Get the Folding@Home user data
 * @param int $id The user ID
 *
 * @return object The user data
 */
function FAHUser($id)
{
	$cacheFile = 'scripts/users.json';
	$allItems = cacheGet($cacheFile);
	
	if (empty($allItems->{$id}) || ((mktime() - strtotime($allItems->{$id}->date)) > CACHE_TIME)) {
		$dom = getDOM('http://folding.extremeoverclocking.com/user_summary.php?s=&u=' . $id);
		$td = $dom->getElementsByTagName('table')->item(6)->getElementsByTagName('tr')->item(1)->getElementsByTagName('td');
		
		$allItems->{$id} = (object) array(
			'userName' => $dom->getElementsByTagName('h1')->item(0)->textContent,
			'userTeamRank' => $td->item(0)->textContent,
			'userOverallRank' => $td->item(1)->textContent,
			'userPoints' => $td->item(6)->textContent,
			'userPPD' => $td->item(3)->textContent,
			'date' => NOW,
		);
		
		cachePut($cacheFile, $allItems);
	}
	
	return $allItems->{$id};
}

/**
 * Get the Folding@Home team data
 * @param int $id The team ID
 *
 * @return object The team data
 */
function FAHTeam($id)
{
	$cacheFile = 'scripts/teams.json';
	$allItems = cacheGet($cacheFile);
	
	if (empty($allItems->{$id}) || ((mktime() - strtotime($allItems->{$id}->date)) > CACHE_TIME)) {
		$dom = getDOM('http://folding.extremeoverclocking.com/team_summary.php?s=&t=' . $id);
		$td = $dom->getElementsByTagName('table')->item(6)->getElementsByTagName('tr')->item(1)->getElementsByTagName('td');
		
		$allItems->{$id} = (object) array(
			'teamName' => $dom->getElementsByTagName('h1')->item(0)->textContent,
			'teamRank' => $td->item(0)->textContent,
			'teamPoints' => $td->item(9)->textContent,
			'teamPPD' => $td->item(3)->textContent,
			'teamToday' => $td->item(7)->textContent,
			'date' => NOW,
		);
		
		cachePut($cacheFile, $allItems);
	}
	
	return $allItems->{$id};
}

/**
 * Get the BOINC user data
 * @param int $id The user ID
 *
 * @return object The user data
 */
function BOINCUser($id)
{
	$cacheFile = 'scripts/boincusers.json';
	$allItems = cacheGet($cacheFile);
	
	if (empty($allItems->{$id}) || ((mktime() - strtotime($allItems->{$id}->date)) > CACHE_TIME)) {
		$dom = getDOM('http://boincstats.com/en/stats/-1/user/detail/' . $id . '/projectList');
		$tr = $dom->getElementsByTagName('table')->item(0)->getElementsByTagName('tr')->item(1);
		$total = str_replace(',', '', $tr->getElementsByTagName('td')->item(1)->textContent);
		
		$allItems->{$id} = (object) array(
			'userName' => $dom->getElementsByTagName('span')->item(0)->textContent,
			'userTeamRank' => $tr->getElementsByTagName('td')->item(10)->textContent,
			'userOverallRank' => $tr->getElementsByTagName('td')->item(6)->textContent,
			'userPoints' => number_format(round($total)),
			'userPPD' => $tr->getElementsByTagName('td')->item(3)->textContent,
			'date' => NOW,
		);
		
		cachePut($cacheFile, $allItems);
	}
	
	return $allItems->{$id};
}

/**
 * Get the BOINC team data
 * @param int $id The team ID
 *
 * @return object The team data
 */
function BOINCTeam($id)
{
	$cacheFile = 'scripts/boincteams.json';
	$allItems = cacheGet($cacheFile);
	
	if (empty($allItems->{$id}) || ((mktime() - strtotime($allItems->{$id}->date)) > CACHE_TIME)) {
		$dom = getDOM('http://boincstats.com/en/stats/-1/team/detail/' . $id . '/projectList');
		$tr = $dom->getElementsByTagName('table')->item(0)->getElementsByTagName('tr')->item(1);
		$total = str_replace(',', '', $tr->getElementsByTagName('td')->item(1)->textContent);
		
		$allItems->{$id} = (object) array(
			'teamName' => $dom->getElementsByTagName('span')->item(0)->textContent,
			'teamRank' => $tr->getElementsByTagName('td')->item(6)->textContent,
			'teamPoints' => number_format(round($total)),
			'teamPPD' => $tr->getElementsByTagName('td')->item(3)->textContent,
			'teamToday' => $tr->getElementsByTagName('td')->item(7)->textContent,
			'date' => NOW,
		);
		
		cachePut($cacheFile, $allItems);
	}
	
	return $allItems->{$id};
}

/**
 * Get the EyeWire user data
 *
 * @param string %id The user ID
 *
 * @return object The user data
 */
function EWUser($id)
{
	$cacheFile = 'scripts/EWteams.json';
	$allItems = cacheGet($cacheFile);
	
	if (empty($allItems->{$id}) || ((mktime() - strtotime($allItems->{$id}->date)) > CACHE_TIME)) {
		$json = file_get_contents('http://eyewire.org/1.0/player/' . $id . '/stats');
		$obj = json_decode($json);
		
		$allItems->{$id} = (object)array(
			'userName' => $obj->username,
			'userPoints' => $obj->forever->points,
			'userCubes' => $obj->forever->cubes,
			'userTBs' => $obj->forever->trailblazes,
			'userTpL1' => $obj->fscore[0]->tp,
			'userFpL1' => $obj->fscore[0]->fp,
			'userFnL1' => $obj->fscore[0]->fn,
			'userTpL2' => $obj->fscore[1]->tp,
			'userFpL2' => $obj->fscore[1]->fp,
			'userFnL2' => $obj->fscore[1]->fn,
			'date' => NOW,
		);
		
		cachePut($cacheFile, $allItems);
	}
	
	return $allItems->{$id};
}

/**
 * Draw the text for a single project (Folding@Home or BOINC)
 *
 * @param resource $template The blank template image
 * @param string $FAHBOINC The project title
 * @param string $pointRank The title of the teamToday statistic
 * @param object $user The user data
 * @param object $team The team data
 */
function singleImage($template, $FAHBOINC, $pointRank, $user, $team)
{
	$username =  strlen($user->userName) > 16 ? substr($user->userName, 0, 13) . '...' : $user->userName;
	$teamname = strlen($team->teamName) > 16 ? substr($team->teamName, 0, 13) . '...' : $team->teamName;
	
	$white = imagecolorallocate($template, 255, 255, 255);
	$yellow = imagecolorallocate($template, 255, 255, 0);
	
	// Left column
	$centerLine = 110;
	
	drawTextLine($template, $FAHBOINC, $username, $centerLine, 25);
	drawTextLine($template, 'Rank on Team', $user->userTeamRank, $centerLine, 37);
	drawTextLine($template, 'Overall Rank', $user->userOverallRank, $centerLine, 49);
	drawTextLine($template, 'User Points', $user->userPoints, $centerLine, 61);
	drawTextLine($template, 'User PPD', $user->userPPD, $centerLine, 73);
	
	// Right column
	$centerLine = 285;
	
	drawTextLine($template, 'Team Name', $teamname, $centerLine, 25);
	drawTextLine($template, 'Rank of Team', $team->teamRank, $centerLine, 37);
	drawTextLine($template, 'Team Points', $team->teamPoints, $centerLine, 49);
	drawTextLine($template, 'Team PPD', $team->teamPPD, $centerLine, 61);
	drawTextLine($template, $pointRank, $team->teamToday, $centerLine, 73);
}

/**
 * Draw the text for combined Folding@Home and BOINC
 *
 * @param resource $template The blank template image
 * @param object $fahUser The Folding@Home user data
 * @param object $fahTeam The Folding@Home team data
 * @param object $boincUser The BOINC user data
 * @param object $boincTeam The BOINC team data
 */
function combiImage($template, $fahUser, $fahTeam, $boincUser, $boincTeam)
{
	$fahUsername =  strlen($fahUser->userName) > 16 ? substr($fahUser->userName, 0, 13) . '...' : $fahUser->userName;
	$boincUsername =  strlen($boincUser->userName) > 16 ? substr($boincUser->userName, 0, 13) . '...' : $boincUser->userName;
	
	$white = imagecolorallocate($template, 255, 255, 255);
	$yellow = imagecolorallocate($template, 255, 255, 0);
	
	// Left column
	$centerLine = 110;
	
	drawTextLine($template, 'F@H User', $fahUsername, $centerLine, 25);
	drawTextLine($template, 'Rank on Team', $fahUser->userTeamRank, $centerLine, 37);
	drawTextLine($template, 'Overall Rank', $fahUser->userOverallRank, $centerLine, 49);
	drawTextLine($template, 'User Points', $fahUser->userPoints, $centerLine, 61);
	drawTextLine($template, 'User PPD', $fahUser->userPPD, $centerLine, 73);
	
	// Right column
	$centerLine = 285;
	
	drawTextLine($template, 'BOINC User', $boincUsername, $centerLine, 25);
	drawTextLine($template, 'Rank on Team', $boincUser->userTeamRank, $centerLine, 37);
	drawTextLine($template, 'Overall Rank', $boincUser->userOverallRank, $centerLine, 49);
	drawTextLine($template, 'User Points', $boincUser->userPoints, $centerLine, 61);
	drawTextLine($template, 'User PPD', $boincUser->userPPD, $centerLine, 73);
}

/**
 * Draw the text for EyeWire
 *
 * @param resource $template The blank template image
 * @param object $user The EyeWire user data
 */
function EWImage($template, $user)
{
	$ewUsername = strlen($user->userName) > 17 ? substr($user->userName, 0, 14) . '...' : $user->userName;
	
	$tp1 = $user->userTpL1; // Accuracy calculation for L1 cubes
	$fn1 = $user->userFnL1; // Accuracy calculation for L1 cubes
	$tp2 = $user->userTpL2; // Accuracy calculation for L2 cubes
	$fn2 = $user->userFnL2; // Accuracy calculation for L2 cubes
	
	// Accuracy calculation,  averages the values of L1 and L2
	$userAccuracy = ((($tp1 / ($tp1 + $fn1)) + ($tp2 / ($tp2 + $fn2))) / 2) * 100;
	
	$white = imagecolorallocate($template, 255, 255, 255);
	$yellow = imagecolorallocate($template, 255, 255, 0);
	$centerLine = 110;
	
	drawTextLine($template, 'EyeWire User', $ewUsername, $centerLine, 25);
	drawTextLine($template, 'Points', number_format($user->userPoints), $centerLine, 37);
	drawTextLine($template, 'Cubes', number_format($user->userCubes), $centerLine, 49);
	drawTextLine($template, 'Trailblazes', number_format($user->userTBs), $centerLine, 61);
	drawTextLine($template, 'Accuracy', number_format($userAccuracy) . '%', $centerLine, 73);
	
	$logo = imagecreatefrompng('images/eyewire.png');
	imagecopy($template, $logo, 300, 25, 0, 0, 40, 40);
}

/**
 * Draw a line of text
 *
 * @param resource $img The image to draw onto
 * @param string $label The label, e.g. "F@H Username"
 * @param string $value The value, e.g. "Princess Celestia"
 * @param string $center The x-coordinate to align the label and value to
 * @param string $y The y-coordinate of the text
 */
function drawTextLine($img, $label, $value, $center, $y)
{
	static $yellow = null;
	static $white = null;
	
	if ($yellow === null)
		$yellow = imagecolorallocate($img, 255, 255, 0);
	
	if ($white === null)
		$white = imagecolorallocate($img, 255, 255, 255);
	
	drawText($img, $white, $label . ': ', $center, $y, 'right');
	drawText($img, $yellow, $value, $center, $y, 'left');
}

/**
 * Draw some text
 *
 * @param resource $img The image to draw onto
 * @param int $colour The colour from imagecolorallocate()
 * @param string $text The text to draw
 * @param string $x The x-coordinate to align the label and value to
 * @param string $y The y-coordinate of the text
 * @param string $align "left" or "right" to align the text
 */
function drawText($img, $colour, $text, $x, $y, $align)
{
	$font = 'images/fonts/UbuntuMono-R.ttf';
	$size = 10;
	$angle = 0;
	
	if ($align === 'left')
	{
		imagettftext($img, $size, $angle, $x, $y, $colour, $font, $text);
	}
	else
	{
		$bbox = imagettfbbox($size, $angle, $font, $text);
		$x -= $bbox[0];
		$x -= $bbox[2];
		
		imagettftext($img, $size, $angle, $x, $y, $colour, $font, $text);
	}
}

$templateFile = isset($_GET['b']) && file_exists('images/sigimages/' . $_GET['b'] . '.png') ? 'images/sigimages/' . $_GET['b'] . '.png' : 'images/sigimages/luna1.png';
$template = imagecreatefrompng($templateFile);

if (isset($_GET['u']) && isset($_GET['t']) && !isset($_GET['w'])) {
	singleImage($template, 'F@H User', 'Points Today', FAHUser($_GET['u']), FAHTeam($_GET['t']));
} elseif (!isset($_GET['u']) && isset($_GET['t']) && isset($_GET['w'])) {
	singleImage($template, 'BOINC User', 'Ranks Risen', BOINCUser($_GET['w']), BOINCTeam($_GET['t']));
} elseif (isset($_GET['u']) && isset($_GET['t']) && isset($_GET['w']) && isset($_GET['p'])) {
	combiImage($template, FAHUser($_GET['u']), FAHTeam($_GET['t']), BOINCUser($_GET['w']), BOINCTeam($_GET['p']));
} elseif (isset($_GET['e'])) {
	EWImage($template, EWUser($_GET['e']));
}

header('Content-Type: image/png');
imagepng($template);