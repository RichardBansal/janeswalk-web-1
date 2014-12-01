<?php
defined('C5_EXECUTE') or die("Access Denied.");
$th = Loader::helper('text');

/**
 * The original pagelist has some funny mixing of echo w/ inline HTML, as well
 * as mixing logic to put out end-tags. This has been very error-prone, so let's
 * just build a JSON instead, to manage the control logic nicely
 */

$countryList = [];
foreach ($pages as $city) {
    $pcID = (int) $city->getCollectionParentID();
    if (!isset($countryList[$pcID])) {
        $countryList[$pcID] = [
            'name' => Page::getByID($pcID)->getCollectionName(),
            'cities' => []
        ];
    }
    $countryList[$pcID]['cities'][] = [
        'id' => $city->getCollectionID(),
        'name' => $city->getCollectionName(),
        'href' => $nh->getLinkToCollection($city)
    ];
}

// Add as JSON for client data
// TODO: use generalized addToJanesWalk
?>
<script type="text/javascript">window.JanesWalk = window.JanesWalk || {}; window.JanesWalk.countries = <?= json_encode($countryList) ?>;</script>
<div id="ccm-jw-page-list-typeahead"></div>
