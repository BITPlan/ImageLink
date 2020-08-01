<?php
/**
 * Check if we are being called directly
 */
if ( !defined( 'MEDIAWIKI' ) ) {
  die( 'This file is an extension to MediaWiki and thus not a valid entry point.' );
}
 
/**
 * Register this extension on Special:Version
 */
$wgExtensionCredits['parserhook'][] = array(
  'name' => 'ImageLink',
  'description' => 'Allows creation of clickable Imagelinks',
  'author' => array( 'Wolfgang Fahl','Hanh Le','Alexander Krau&szlig;' ),
  'url' => 'http://wiki.bitplan.com/ImageLink',
  'version' => '0.0.3'
);

$wgHooks['ParserFirstCallInit'][] = 'wfRegisterImageLink';
 
/**
 * Sets the tag <imgLink></imgLink> that this extension looks for and the function by which it
 * operates
 */
function wfRegisterImageLink( Parser $parser ) {
  $parser->setHook( 'imgLink', 'renderImageLink' );
  return true;
}

/**
 * Renders an Imagelink based on the information provided by $input.
 * <imgLink src='http://capri.bitplan.com/sonstiges/icons/48x48/shadow/arrow_left_blue.png' 
 *   title='previous'>Workdocumentation 2015-03-02</imgLink> 
 *   
 * @tagcontent page
 *   The page is a name of a wikipage e.g. 'Workdocumentation 2015-03-02'
 * 
 * @param href 
 *   if set the href of the anchor - if not set a href is constructed
 *   from the page to point to the current wiki
 *
 * @param target 
 *   if set the target is set e.g. to "_blank" 
 *
 * @param img
 *  if set the img is a complete img tag e.g.
 *  <img src="http://capri.bitplan.com/sonstiges/icons/48x48/shadow/arrow_left_blue.png" alt="arrow_left_blue.png" />
 *
 * @param src (if img is not set)
 *  The src is an URI e.g. http://capri.bitplan.com/sonstiges/icons/48x48/shadow/arrow_left_blue.png
 * 
 * @param title 
 *  The title is is the title e.g. 'previous'
 * 
 * @return string
 *  Returns an image tag for the given input with an href embedded. 
 *  For the example above the return string would be
 *
 *     <a href='https://capri.bitplan.com/mediawiki/index.php/Workdocumentation_2015-03-02'>
 *       <img src='/sonstiges/icons/48x48/shadow/arrow_left_blue.png' title='previous'> 
 *     </a>
 */
function renderImageLink($input, array $args, Parser $parser, PPFrame $frame) {
	// in case WikiMarkup / Templates have been used to specify page ..
  $page = $parser->recursiveTagParse( $input, $frame );
  // http://stackoverflow.com/questions/17489382/mediawiki-parser-and-recursivetagparse
  // $parser->replaceLinkHolders($page);
  $title= $page;
  $target="_self";
  // default?
  global $wgLogo;
  global $wgScriptPath;
  $src=$wgLogo;
  // title part
  if (isset($args["title"])) {
    $title=$args["title"];
    $title=$parser->recursiveTagParse( $title, $frame );
  }
  // target part
  if (isset($args["target"])) {
    $target=$args["target"];
    $target=$parser->recursiveTagParse( $target, $frame );
  }
  // img part
  if (isset($args["img"])) {
  	$img=$args["img"];
  	$img=$parser->recursiveTagParse( $img, $frame );
  } else {
    // src part
    if (isset($args["src"])) {
  	  $src=$args["src"];
  	  $src=$parser->recursiveTagParse( $src, $frame );
    } else {
      // will use default wgLogo see below 
    }
    $img="<img src='".$src."' title='".$title."'>";
  }
  // href or page?
  $a="";
  $hreforg="";
  if (isset($args["href"])) {
    $href="";
  	$hreforg=$args["href"];
    // let Mediawiki resolve all templates and such
  	$hreforg=$parser->recursiveTagParse( $hreforg, $frame );
    wfDebug("ImageLink hreforg='".$hreforg."' \n");
    // now we have a complete anchor like
    // <a rel="nofollow" class="external free" href="https://www.magicdraw.com"</a>
    // we only want the href part ...
    // http://htmlparsing.com/php.html
    $dom = new DOMDocument;
    $dom->loadHTML($hreforg);
    $anchors=$dom->getElementsByTagName('a');
    // pseudo loop - we only expect one element ...
    foreach ($anchors as $anchor) {
      $href=$anchor->getAttribute('href');
    }
    $a=$title;
  } else {
    $href=$wgScriptPath."/index.php/".$page;
  }
  // Debug output
  wfDebug("ImageLink hreforg='".$hreforg."' href='".$href."' a='".$a."' page='".$page."' from input '".$input."' src='".$src."' title='".$title."' wgScriptPath='".$wgScriptPath."'\n");
  // https://www.mediawiki.org/wiki/Manual:$wgScriptPath/de
	$result	= "<a href='".$href."' target='".$target."' title='".$title."'>".$img.$a."</a>";
  return $result;
}
?>
