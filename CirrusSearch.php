<?php

/**
 * CirrusSearch - Searching for MediaWiki with Elasticsearch.
 *
 * Set $wgSearchType to 'CirrusSearch'
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'CirrusSearch',
	'author'         => array( 'Nik Everett', 'Chad Horohoe' ),
	'descriptionmsg' => 'cirrussearch-desc',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:CirrusSearch',
	'version'        => '0.1'
);

/**
 * Configuration
 */

// ElasticSearch servers
$wgCirrusSearchServers = array( 'elasticsearch0', 'elasticsearch1', 'elasticsearch2', 'elasticsearch3' );

// Number of shards for each index
$wgCirrusSearchShardCount = array( 'content' => 4, 'general' => 4 );

// Number of replicas per shard for each index
$wgCirrusSearchContentReplicaCount = array( 'content' => 2, 'general' => 2 );

// When searching for a phrase how many words not searched for can be in the phrase
// before it doesn't match. If I search for "like yellow candy" then phraseSlop of 0
// won't match "like brownish yellow candy" but phraseSlop of 1 will.
$wgCirrusSearchPhraseSlop = 1;

// If the search doesn't include any phrases (delimited by quotes) then we try wrapping
// the whole thing in quotes because sometimes that can turn up better results. This is
// the boost that we give such matches. Set this less than or equal to 1.0 to turn off
// this feature.
$wgCirrusSearchPhraseRescoreBoost = 10.0;

// Number of documents for which automatic phrase matches are performed if it is enabled.
$wgCirrusSearchPhraseRescoreWindowSize = 1024;

// If true CirrusSearch asks Elasticsearch to perform searches using a mode that should
// product more accurate results at the cost of performance. See this for more info:
// http://www.elasticsearch.org/blog/understanding-query-then-fetch-vs-dfs-query-then-fetch/
$wgCirrusSearchMoreAccurateScoringMode = true;

// Maximum number of terms that we ask phrase suggest to correct.
// See max_errors on http://www.elasticsearch.org/guide/reference/api/search/suggest/
$wgCirrusSearchPhraseSuggestMaxErrors = 5;

// Confidence level required to suggest new phrases.
// See confidence on http://www.elasticsearch.org/guide/reference/api/search/suggest/
$wgCirrusSearchPhraseSuggestConfidence = 2.0;

// Maximum number of redirects per target page to index.  
$wgCirrusSearchIndexedRedirects = 1024;

// Maximum number of linked articles to update every time an article changes.
$wgCirrusSearchLinkedArticlesToUpdate = 5;

// Weight of fields relative to article text
$wgCirrusSearchWeights = array( 'title' => 20.0, 'redirect' => 15.0, 'heading' => 5.0 );

// How long to cache link counts for (in seconds)
$wgCirrusSearchLinkCountCacheTime = 0;

// Configuration parameters passed to more_like_this queries.
$wgCirrusSearchMoreLikeThisConfig = array(
	'min_doc_freq' => 2,              // Minimum number of documents (per shard) that need a term for it to be considered
	'max_query_terms' => 25,
	'min_term_freq' => 2,
	'percent_terms_to_match' => 0.3,
	'min_word_len' => 0,
	'max_word_len' => 0,
);

// Should CirrusSearch aggressively split up compound words?  Good for splitting camelCase, snake_case, etc.
// Changing it requires an in place reindex to take effect.  Currently only available in English.
$wgCirrusSearchUseAggressiveSplitting = true;

$dir = __DIR__ . '/';
/**
 * Classes
 */
$wgAutoloadClasses['CirrusSearch'] = $dir . 'includes/CirrusSearch.body.php';
$wgAutoloadClasses['CirrusSearchAnalysisConfigBuilder'] = $dir . 'includes/CirrusSearchAnalysisConfigBuilder.php';
$wgAutoloadClasses['CirrusSearchConnection'] = $dir . 'includes/CirrusSearchConnection.php';
$wgAutoloadClasses['CirrusSearchMappingConfigBuilder'] = $dir . 'includes/CirrusSearchMappingConfigBuilder.php';
$wgAutoloadClasses['CirrusSearchPrefixSearchHook'] = $dir . 'includes/CirrusSearchPrefixSearchHook.php';
$wgAutoloadClasses['CirrusSearchSearcher'] = $dir . 'includes/CirrusSearchSearcher.php';
$wgAutoloadClasses['CirrusSearchTextFormatter'] = $dir . 'includes/CirrusSearchTextFormatter.php';
$wgAutoloadClasses['CirrusSearchUpdater'] = $dir . 'includes/CirrusSearchUpdater.php';

/**
 * Hooks
 * Also check Setup for other hooks.
 */
$wgHooks['LinksUpdateComplete'][] = 'CirrusSearchUpdater::linksUpdateCompletedHook';

/**
 * i18n
 */
$wgExtensionMessagesFiles['CirrusSearch'] = $dir . 'CirrusSearch.i18n.php';


/**
 * Setup
 */
$wgExtensionFunctions[] = 'cirrusSearchSetup';
function cirrusSearchSetup() {
	global $wgSearchType, $wgHooks;
	// Install our prefix search hook only if we're enabled.
	if ( $wgSearchType === 'CirrusSearch' ) {
		$wgHooks['PrefixSearchBackend'][] = 'CirrusSearchPrefixSearchHook::prefixSearch';
	}
}
