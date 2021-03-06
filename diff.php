<?php
require_once("database.php");
require_once("php-diff/lib/Diff.php");
require_once("php-diff/lib/Diff/Renderer/Html/Inline.php");

function generateEntryForFeed($feedid) {
  global $db;
  $texts = $db->getTwoNewestEntrys($feedid);
  $texts = applyRegex($texts, $feedid);
  if($texts[0] == $texts[1])
    return; //no changes => no diff generatable
  
  $diff = generateDiff($texts[1], $texts[0]);

  $db->saveDiff($feedid, $diff);
}

function applyRegex($texts, $feedid) {
  global $db;
  $regexArray = $db->getRegex($feedid);
  if((count($regexArray['search']) == 0) || (count($regexArray['replace']) == 0))
    return $texts; //we have no regex to replace
  else
    return preg_replace($regexArray['search'], $regexArray['replace'], $texts);
}

function generateDiff($a, $b) {
  $a = explode("\n", $a);
  $b = explode("\n", $b);

  $options = array(
    'ignoreWhitespace' => true,
    'ignoreCase' => true,
  );
  $diff = new Diff($a, $b, $options);

  $renderer = new Diff_Renderer_Html_Inline;

  return $diff->render($renderer);
}