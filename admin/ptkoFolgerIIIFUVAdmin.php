<?php

defined("ABSPATH") or die("Nothing to see here.");

class ptkoFolgerIIIFUVAdmin
{
	private $adminOwner;
	
	public function setAdminOwner($owner) {
		$this->adminOwner = $owner;
	}
	
	public function getAdminOwner() {
		return $this->adminOwner;
	}

	public function adminMenuRegister() {
		add_menu_page(
			'Miranda UV Plugin Options',
			'Miranda UV',
			'manage_options',
			'ptko-miranda-uv',
			array($this,'generateAdminPage'),
			get_site_url() . '/wp-content/plugins/folger_iiif_uv/include/uv/favicon.ico'
		);
	}

	public function generateAdminPage() {
	    wp_enqueue_style('uv-admin-css',$this->getAdminOwner()->getPluginPath() . 'include/uv_admin.css');
		?>
        <script type="text/javascript">
            var ptko_uvRoot = "<?php echo $this->getAdminOwner()->getPluginPath() . 'include/uv/';?>";
        </script>
		<div class="wrap">
			<div class="wrap-settings" >
				<h1>Miranda UV Plugin Options</h1>
				<form method="post" action="options.php">
					<?php
					settings_fields("miranda_endpoint");
					//settings_fields("miranda_return_options");
					do_settings_sections("ptko-miranda-uv");
					submit_button();
					?>
				</form>
			</div>
            <hr/>
            <div class="miranda-documentation">
                <span class="miranda-doc-bold miranda-doc-sec">GraphQL Endpoint:</span>
                <p>
                    <span class="miranda-doc-para">This field (seen above) specifies the GraphQL API endpoint used by Miranda to service GrraphQL queries.
                        During development of the system this value points to the staging server(s) - https://server.staging.miranda.folger.edu/graphql.
                        Once development on Miranda is finished this field should be updated with the correct value for the production/live environment - https://server.miranda.folger.edu/graphql.
                    </span>
                </p>
                <span class="miranda-doc-bold miranda-doc-sec">Using this plugin:</span>
                <p>
                    <span class="miranda-doc-para">To use this plugin all that needs to be done is to insert a "shortcode" into the page/post that Miranda content is desired to display.
                        This short code takes 1 value only, and that is the DapID of the desired Miranda asset.
                    </span>
                </p>
                <span class="miranda-doc-bold miranda-doc-sec">Short Code:</span><br/>
                    <pre>
                        [miranda_uv dapid="dapID"]
                    </pre>
                <p>
                    <span class="miranda-doc-para">
                        Copy and paste this short code into the desired page/post and replace dapID with the acctual dapID of the desired Miranda asset.
                        Please be sure to leave the "" marks around the dapID. Example:
                    </span>
                </p>
                    <pre>
                        [miranda_uv dapid="44733ff4-0de6-4042-99b8-48204d8be100"]
                    </pre>
                <p>
                    <span class="miranda-doc-para">
                        The above is how the shortcode should look inserted into the post.  Please insert the code in the rich editor at the place where it is desired to have the content appear.
                    </span>
                </p>
            </div>

		</div>
		<?php
	}


	public function displayEndpointConfig() {

	}

	public function displayEndpointField() {
		?>
		<input style="width: 100%;" type="url" class="ptko-endpoint-url" id="miranda_graphql_endpoint" name="miranda_graphql_endpoint" placeholder="Input the URL of the GraphQL enpoint" value="<?php echo esc_url(get_option("miranda_graphql_endpoint"));?>"/>
		<?php
	}

	public function displayFields($in) {
	    $option = get_option($in);

        if (!empty($option)) {
	        $option = "checked";
        } else {
	        $option = "";
        };

        switch($in) {
	        case "miranda_return_dap_id":
	            $optionValue = 'dapID';
		        break;
	        case "miranda_return_record_type":
		        $optionValue = 'recordType';
		        break;
	        case "miranda_return_about":
		        $optionValue = 'about';
		        break;
	        case "miranda_return_creator":
		        $optionValue = 'creator';
		        break;
	        case "miranda_return_date_created":
		        $optionValue = 'dateCreated';
		        break;
	        case "miranda_return_date_published":
		        $optionValue = 'datePublished';
		        break;
	        case "miranda_return_description":
		        $optionValue = 'description';
		        break;
	        case "miranda_return_extent":
		        $optionValue = 'extent';
		        break;
	        case "miranda_return_folger_call_number":
		        $optionValue = 'folgerCallNumber';
		        break;
	        case "miranda_return_folger_provenance":
		        $optionValue = 'folgerProvenance';
		        break;
	        case "miranda_return_folger_related_items":
		        $optionValue = 'folgerRelatedItems';
		        break;
	        case "miranda_return_format":
		        $optionValue = 'format';
		        break;
	        case "miranda_return_genre":
		        $optionValue = 'genre';
		        break;
	        case "miranda_return_location_created":
		        $optionValue = 'locationCreated';
		        break;
	        case "miranda_return_title":
		        $optionValue = 'title';
		        break;
	        case "miranda_return_size":
		        $optionValue = 'size';
		        break;
        };

	    ?>
        <input type="checkbox" class="ptko-return-options" id="<?php echo $in;?>" name="<?php echo $in;?>" value="<?php echo $optionValue;?>" <?php echo $option;?> />
        <?php
    }

	public function sanitizeIIIFUVInputs($in) {
	    $out = array();

	    foreach($in as $key=>$item) {
	        switch ($key) {
                case 'miranda_graphql_endpoint':
                    $out[$key] = esc_url_raw($item);
                    break;
                default:
                    //$out[$key] = (is_string($item) === true) ? (($item === "on" || $item === '') ? $item : '') : '';
                    $out[$key] = $item;
                    break;
            };
        };
	    return apply_filters('sanitizeIIIFUVInputs',$out, $in);
    }

	public function registerSettings() {
		add_settings_section("miranda_endpoint", "Miranda GraphQL Endpoint", null,"ptko-miranda-uv");
        add_settings_field("miranda_graphql_endpoint","GraphQL Endpoint", array($this, "displayEndpointField"), "ptko-miranda-uv", "miranda_endpoint");

/*		add_settings_section("miranda_return_options","Miranda Fields To Return", null,"ptko-miranda-uv");
		add_settings_field("miranda_return_dap_id", "DAPID", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_dap_id");
		add_settings_field("miranda_return_record_type", "Record Type", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_record_type");
		add_settings_field("miranda_return_about", "About", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_about");
		add_settings_field("miranda_return_creator", "Creator", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_creator");
		add_settings_field("miranda_return_date_created", "Date Created", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_date_created");
		add_settings_field("miranda_return_date_published", "Date Published", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_date_published");
		add_settings_field("miranda_return_description", "Description", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_description");
		add_settings_field("miranda_return_extent", "Extent", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_extent");
		add_settings_field("miranda_return_folger_call_number", "Folger Call Number", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_folger_call_number");
		add_settings_field("miranda_return_folger_provenance", "Folger Provenance", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_folger_provenance");
		add_settings_field("miranda_return_folger_related_items", "Folger Related Items", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_folger_related_items");
		add_settings_field("miranda_return_format", "Format", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_format");
		add_settings_field("miranda_return_genre", "Genre", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_genre");
		add_settings_field("miranda_return_location_created", "Location Created", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_location_created");
		add_settings_field("miranda_return_title", "Title", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_title");
		add_settings_field("miranda_return_size", "Size", array($this, "displayFields"), "ptko-miranda-uv", "miranda_return_options", "miranda_return_size");
*/
		register_setting("miranda_endpoint", "miranda_graphql_endpoint", "sanitizeIIIFUVInputs");
/*		register_setting("miranda_return_options", "miranda_return_dap_id", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_record_type", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_about", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_creator", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_date_created", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_date_published", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_description", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_extent", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_folger_call_number", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_folger_provenance", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_folger_related_items", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_format", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_genre", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_location_created", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_title", "sanitizeIIIFUVInputs");
		register_setting("miranda_return_options", "miranda_return_size", "sanitizeIIIFUVInputs");
*/
	}

	public function readFromEndpoint($endPoint) {

    }

    function __construct($owner)
    {
	    add_action('admin_menu', array($this, 'adminMenuRegister'));
	    add_action('admin_init', array($this, 'registerSettings'));

        $this->adminOwner = $owner;
    }
}
