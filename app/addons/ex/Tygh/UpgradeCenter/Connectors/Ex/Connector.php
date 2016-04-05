<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/
namespace Tygh\UpgradeCenter\Connectors\Ex;
use Tygh\Addons\SchemesManager;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\UpgradeCenter\Connectors\BaseAddonConnector;
/**
 * Core upgrade connector interface
 */
class Connector extends BaseAddonConnector
{
    /**
     * @var string Version of product addon runs in
     */
    protected $environment_version;
    /**
     * Prepares request data for request to Upgrade server (Check for the new upgrades)
     *
     * @return array Prepared request information
     */
    public function getConnectionData()
    {
        $request_data = parent::getConnectionData();
        $request_data['data']['product_id'] = $this->addon_id;
        $request_data['data']['dispatch'] = 'product_packages.get_upgrades';
        // "ver" is used for addon version
        // while "product_version" is for environment version
        $request_data['data']['product_version'] = $this->environment_version;
        return $request_data;
    }
    /**
     * Downloads upgrade package from the Upgade server
     *
     * @param  array  $schema       Package schema
     * @param  string $package_path Path where the upgrade pack must be saved
     * @return bool   True if upgrade package was successfully downloaded, false otherwise
     */
    public function downloadPackage($schema, $package_path)
    {
        $data = fn_get_contents($this->updates_server . '/index.php?' . http_build_query(array(
            'dispatch' => 'product_packages.get_package',
            'package_id' => $schema['package_id'],
            'product_id' => $this->addon_id,
            'license_number' => $this->license_number,
        )));
        if (!empty($data)) {
            fn_put_contents($package_path, $data);
            $result = array(true, '');
        } else {
            $result = array(false, __('text_uc_cant_download_package'));
        }
        return $result;
    }
    public function __construct()
    {
        parent::__construct();
        $this->addon_id = '1303'; // Fill with product_id before uploading
        // Initial settings
        $addon_scheme = SchemesManager::getScheme('ex');
        $this->updates_server = 'http://php53.sandbox.smtk.us/marketplace';
        $this->product_name        = $addon_scheme->getName();
        $this->product_version     = $addon_scheme->getVersion();
        $this->environment_version = PRODUCT_VERSION;
        $this->product_edition     = PRODUCT_EDITION;
        $this->license_number = Registry::get('addons.ex.license_number');
    }
}