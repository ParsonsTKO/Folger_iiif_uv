<?php

defined("ABSPATH") or die("Nothing to see here.");

/**
* Handles querying Airtable and formatting output data.
*
* This class handles querying Airtable for data used to generate charts as well as
* formating the returned data into a format that can be passed to the charting
* library or formatted to CSV for download.
*/
class ptkoFolgerIIIFUV
{
	private $owner;
	private static $pageShortCodes;
	private $totalCodeCount;
	private static $embedJSEmbeded;
	private $requestdDapID;

	private $viewOnMiranda;
	private $endpoint;
	private $linkAddress;
	private $mirandaAddress;

	private function setRequestedDapID($dapID) {
		$this->requestdDapID = $dapID;
	}

	private function getRequestedDapID() {
		return $this->requestdDapID;
	}

	private function getOwner() {
		return $this->owner;
	}

	private function setOwner($o) {
		$this->owner = $o;
	}

	private function getTotalCodeCount() {
		return $this->totalCodeCount;
	}

	private function getEmbedJSEmbeded() {
		return $this->embedJSEmbeded;
	}

	private function setEmbedJSEmbeded($embeded) {
		$this->embedJSEmbeded = $embeded;
	}

	private function checkShortCodes($sc) {
		if (count($this->pageShortCodes) > 0) {
			$codeCount = 0;
			foreach($this->pageShortCodes as $key=>$code) {
				$this->totalCodeCount ++;
				if (trim($code) === trim($sc)) {
					$codeCount++;
				};
			};

			$this->pageShortCodes[] = $sc;

			return $codeCount;
		} else {
			$this->pageShortCodes[] = $sc;

			return 0;
		};
	}

	private function doGraphQLQuery($dapID) {
		$queryString = '{records(dapID:"' . $dapID . '") {';

		$queryString .=
			'dapID, 
			creator, 
			title {
				displayTitle, 
				extendedTitle
				}, 
			dateCreated {
				displayDate, 
				isoDate
				},
			license,
			abstract,
			caption,
			folgerDisplayIdentifier,
			relatedFiles {
				url
			},
			remoteSystemUrl {
				oembed,
				url
			}
			binaryFileUrl  {
				oembed,
				url,
				type,
				remoteUrl
			},
			fileInfo {
				fileURL,
				encodingFormat,
				height,
				width,
				contentSize
				}, 
			folgerRelatedItems {
				dapID, 
				remoteUniqueID {
					remoteSystem, 
					remoteID
					}, 
				folgerRelationshipType, 
				folgerObjectType, 
				label, mpso}
				}
			}';

		$queryAddress = get_option('miranda_graphql_endpoint');

		$args = array(
			'method' => 'POST',
			'timeout' => 45,
			'redirection' => 5,
			'httpversion'=> '1.0',
			'blocking' => true,
			'header' => array(),
			'body' => array('query'=> $queryString),
			'cookies' => array()
		);

		return wp_remote_retrieve_body(wp_remote_post($queryAddress, $args));
	}

	function renderAssetType($input) {
		$tempEncodingFormat = "";
		$retVal = '<div class="wrapper-for-miranda-items"><hr/>';
		if (!isset($input->errors)) {
			$tempEncodingFormat = strtoupper($input->data->records[0]->fileInfo->encodingFormat);
			$tempEncodingFormat = explode('/', $tempEncodingFormat) [1];

			if($tempEncodingFormat === null) {
				$tempEncodingFormat = strtoupper($input->data->records[0]->fileInfo->encodingFormat);
			}

			if (isset($input->data->records[0]->remoteSystemUrl) && $input->data->records[0]->remoteSystemUrl->oembed === true) {
				$tempEncodingFormat = "OEMBED";
			}

			if (empty($tempEncodingFormat)) {
				$tempEncodingFormat = explode('.', $input->data->records[0]->binaryFileUrl->url);
				$tempEncodingFormat = $tempEncodingFormat[count($tempEncodingFormat) - 1];
			}

			switch (strtoupper($tempEncodingFormat)) {
				case 'PNG':
				case 'J2K':
				case 'JP2':
				case 'JPG':
				case 'TIF':
				case 'JPEG':
				case "TIFF":
					{
						$retVal .= $this->renderImages($input);
						break;
					};
				case 'M4V':
				case 'MP4':
					{
						$retVal .= $this->renderVideos($input);
						break;
					};
				case 'MP3':
				case 'OGG':
				case 'WAV':
						$retVal .= $this->renderAudio($input);
					break;
				case 'OEMBED':
						$retVal .= $this->renderOembed($input);
					break;
			};
			$retVal .= $this->renderContext($input);
		} else {
			$retVal .= $this->renderNotFound();
		};

		$retVal .= '<hr/></div>';

		return $retVal;
	}

	function renderNotFound() {

		$retVal = '<div class="miranda-not-found"><p>We couldn\'t find the record with a dapID of "' . $this->getRequestedDapID() . '"</p>' . $this->viewOnMiranda[0] . 'Try searching on:' . $this->viewOnMiranda[1] . $this->mirandaAddress . '">' . $this->viewOnMiranda[4] . '</div></div>';

		return $retVal;
	}

	function renderOembed($input) {

		$requestURL = $input->data->records[0]->remoteSystemUrl->url;

		return wp_oembed_get("$requestURL", array('width'=>'','height'=>''));
	}

	function renderVideos($input) {

		$video = '<video controls>';
			$video .= '<source src="' . $input->data->records[0]->binaryFileUrl->url . '">';
		$video .= '</video>';

		return $video;
	}

	function renderAudio($input) {

		$audio = '<audio controls>';
			$audio .= '<source src="' . $input->data->records[0]->binaryFileUrl->url . '">';
		$audio .= '</audio>';

		return $audio;
	}

	function renderImages($input) {

		$elementData = array(
			'data-locale'        => 'en-GB:English (GB)',
			'data-fullscreen'    => '',
			'data-config'        => $this->getOwner()->getPluginPath() . 'include/uv_miranda_config.json',
			'data-uri'           => $this->endpoint . '/iiif/manifest/from-dap-id/' . $input->data->records[0]->dapID . '.json',
			'data-sequenceindex' => '0',
			'data-canvasindex'   => '0',
			'data-rotation'      => '0'
		);

		$elDataString = '';
		foreach ( $elementData as $key => $elData ) {
			$elDataString .= $key . '= "' . $elData . '" ';
		};

		$uvDivScript = '<div class="uv" ' . $elDataString . ' style="width:100%; height: 500px;"></div>';
		$uvDivScript .= '<script type="text/javascript" id="embedUV" src="' . $this->getOwner()->getPluginPath() . 'include/uv/lib/embed.js' . '"></script><script type="text/javascript">/* wordpress fix */</script>';

		return $uvDivScript;
	}

	function renderContext($input) {
		$contextDivs = '<div class="returned-iiif-info">';
		$contextDivs .= '<p>';
		$contextDivs .= (!empty($input->data->records[0]->title->displayTitle)) ? 'Title: ' . $input->data->records[0]->title->displayTitle . '<br/>' : '';
		if (!empty($input->data->records[0]->creator)) {
			$contextDivs .= 'Creator: ' . $input->data->records[0]->creator . '<br/>';
		};
		if (!empty($input->data->records[0]->dateCreated->displayDate)) {
			$contextDivs .= 'Date Created: ' . $input->data->records[0]->dateCreated->displayDate . '<br/>';
		};
		if (!empty($input->data->records[0]->folgerDisplayIdentifier)) {
			$contextDivs .= 'Folger Reference ID: ' . $input->data->records[0]->folgerDisplayIdentifier . '<br/>';
		};
		if (!empty($input->data->records[0]->license)) {
			$contextDivs .= 'License: ' . $input->data->records[0]->license;
		};
		$contextDivs .= '</p>' . $this->viewOnMiranda[0] . $this->viewOnMiranda[1] . $this->viewOnMiranda[2] . $this->viewOnMiranda[3] . $this->viewOnMiranda[4] . '</div></div>';

		return $contextDivs;
	}

	function UVEmbedShortcode($args) {
		if($args) {
			wp_enqueue_style('miranda-uv-style',$this->getOwner()->getPluginPath() . 'include/style.css');
			$return = json_decode($this->doGraphQLQuery($args['dapid']));

			//echo '<pre>' . var_export($return,true) . '</pre>';
			$this->setRequestedDapID($args['dapid']);

			$this->mirandaAddress = $this->linkAddress = explode('server.', $this->endpoint);
			$this->linkAddress = $this->linkAddress[0] . $this->linkAddress[1] . '/detail/from-plugin/' . $this->getRequestedDapID();
			$this->mirandaAddress = $this->mirandaAddress[0] . $this->mirandaAddress[1];

			$this->viewOnMiranda = array(
				'<div class="view-on-miranda-wrapper">',
				'<a target="_blank" href="',
				$this->linkAddress .'">',
               '<span>View on: </span>',
				file_get_contents($this->getOwner()->getPluginFSPath() . 'include/images/Miranda_BETA.svg') .'</a>'
			);

			return $this->renderAssetType($return);
		};
		return "";
	}

	function __construct($owner) {
		$this->pageShortCodes = array();
		$this->setOwner($owner);

		$this->endpoint = explode( '/graphql', get_option( 'miranda_graphql_endpoint' ) )[0];
	}

}
