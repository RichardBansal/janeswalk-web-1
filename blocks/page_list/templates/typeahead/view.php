<?php 
defined('C5_EXECUTE') or die("Access Denied.");
$th = Loader::helper('text');
//Note that $nh (navigation helper) is already loaded for us by the controller (for legacy reasons)
function compare_parent($a,$b) {
  $ap = $a->getCollectionParentID();
  $bp = $b->getCollectionParentID();
	return ($ap == $bp) ? 0 : strcmp(Page::getByID($ap)->getCollectionName(), Page::getByID($bp)->getCollectionName());
}
?>

<div class="ccm-page-list-typeahead">
  <form>
    <input type="text" name="selected_option" class="typeahead" placeholder="Find a Walk" autocomplete="off" />
    <input type="submit" value="Go" />
  <ul>
	<?php 
	 uasort($pages, 'compare_parent');
   $lastParent = "";
	 foreach ($pages as $page):
    $parent = Page::getByID($page->getCollectionParentID())->getCollectionName();
    if($lastParent != $parent) {
      if($lastParent != "") { echo "</ul></li>"; }
      echo t("<li class='parent'>") . $parent . t("<ul>");
      $lastParent = $parent;
    } ?>
   <li>
   <?php
		// Prepare data for each page being listed...
		$title = $th->entities($page->getCollectionName());
		$url = $nh->getLinkToCollection($page);
		$target = ($page->getCollectionPointerExternalLink() != '' && $page->openCollectionPointerExternalLinkInNewWindow()) ? '_blank' : $page->getAttribute('nav_target');
		$target = empty($target) ? '_self' : $target; 
    ?>
      <a href="<?php  echo $url ?>" target="<?php  echo $target ?>"><?php  echo $title ?></a>
    </li>	
	<?php  endforeach; ?>
  </ul>
  </form>
</div><!-- end .ccm-page-list -->
