<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

global $mainframe;

jimport( 'joomla.plugin.plugin' );

/**
 * Example system plugin
 */
class plgContentAutometadescription extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */

	function onPrepareContent(&$article){
		$doc =& JFactory::getDocument();
		$plugin = & JPluginHelper::getPlugin('content', 'autometadescription');
		// Load plugin params info
		$pluginParams = new JParameter($plugin->params);
		$blacklist = explode('|',$pluginParams->def('blacklist'));

		$shortintro = shortIntro($article->introtext,$blacklist);
		if($article->metadesc == ''){
			 $doc->setDescription( $shortintro ); 	
			}
		}

}


	function shortIntro($introtext,$blacklist)
	{
		//break out the paragraphs

		//TODO: check for last position is a period if $i >  20
		//TODO: Check for length of current word if has period if only two and is capital continue

		// Cutoff is a threshold with a trigger

		//Introtext as an array of paragraphs
		$introtext = str_replace('<h2>','<h3>',$introtext) ;
		$introtext = str_replace('</h2>','</h3>',$introtext) ;
		foreach ($blacklist as $ignore){
			$introtext = str_replace($ignore,'',$introtext);			
		}

		$introtext = str_replace('class="caption"','',$introtext);
		$introtext = str_replace('<img','<img style="display:none"',$introtext);

		$introtext = str_replace('{loadposition bigisland}','',$introtext);
		//adds a flag after closed <p> in order to maintain <p> markers
		$introtext = str_replace('</p>','</p>{endpara}',$introtext);
		$introtext = strip_tags($introtext);
		$paras = explode('{endpara}',$introtext);

		//take out the first two paragraphs for analysis
		$newtext = $paras[0] . $paras[1];
		$words = explode(' ',$newtext);

		//iterator
		$i = 0;
		$trigger = 0;
		//new value for intro
		$newintro = '';

		while ($trigger == 0) {	
				$newintro = $newintro . $words[$i] . ' ';
				//original
				if (substr($words[$i],strlen($words[$i])-1) == '.'  and $i > 20){$trigger = 1;}
				//include check for </p> and other tags
				if (substr($words[$i],strlen($words[$i])-2) == 'p>'  and $i > 20){$trigger = 1;}
				if (substr($words[$i],strlen($words[$i])-3) == 'li>'  and $i > 20){$trigger = 1;}
				if (substr($words[$i],strlen($words[$i])-3) == 'dt>'  and $i > 20){$trigger = 1;}
				if (substr($words[$i],strlen($words[$i])-2) == '?>'  and $i > 20){$trigger = 1;}

				//increment
					$i++;
				// make sure the loop isn't infinite for short entries	
				if ($i == sizeof($words)){$trigger =1;}
				}

		return $newintro;

	}